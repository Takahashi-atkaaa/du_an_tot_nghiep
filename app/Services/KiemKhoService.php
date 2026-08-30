<?php

namespace App\Services;

use App\Models\BienTheSanPham;
use App\Models\ChiTietKiemKho;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\KhoaKiemKho;
use App\Models\LoHang;
use App\Models\NguoiDung;
use App\Models\Phieu;
use App\Models\PhieuKiemKho;
use App\Models\PhieuNhap;
use App\Models\PhieuXuat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KiemKhoService
{
    // ============================================================
    // 1. Tao phieu
    // ============================================================
    public function taoPhieu(array $payload, NguoiDung $user): PhieuKiemKho
    {
        return DB::transaction(function () use ($payload, $user) {
            $phieu = PhieuKiemKho::create([
                'ma_kiem_kho' => PhieuKiemKho::generateMaKiemKho(),
                'id_nguoi_tao' => $user->id,
                'id_nguoi_kiem' => $payload['id_nguoi_kiem'] ?? $user->id,
                'pham_vi' => $payload['pham_vi'],
                'id_danh_muc' => $payload['id_danh_muc'] ?? null,
                'variant_ids' => $payload['variant_ids'] ?? null,
                'ngay_kiem' => $payload['ngay_kiem'] ?? now()->toDateString(),
                'trang_thai' => 'phieu_tam',
                'ghi_chu' => $payload['ghi_chu'] ?? null,
            ]);

            $variants = $this->resolveVariantsForPhieu($phieu);

            if ($variants->isEmpty()) {
                throw ValidationException::withMessages([
                    'pham_vi' => 'Không có biến thể nào trong phạm vi đã chọn.',
                ]);
            }

            foreach ($variants as $variant) {
                $this->taoChiTietKiemKho($phieu, $variant);
            }

            $phieu->refresh();
            $phieu->recomputeTotals();

            return $phieu;
        });
    }

    /**
     * Tao mot ChiTietKiemKho snapshot cho 1 variant
     */
    private function taoChiTietKiemKho(PhieuKiemKho $phieu, BienTheSanPham $variant): ChiTietKiemKho
    {
        // Lay cac lo con ton cua variant, sap xep FEFO
        $loHangs = ChiTietLoHang::where('variant_id', $variant->id)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->get();

        $tongHeThong = (int) $loHangs->sum('so_luong_ton');

        // Gia von binh quan gia quyen theo FEFO
        $tongGiaTri = 0;
        $tongSl = 0;
        foreach ($loHangs as $lo) {
            $tongGiaTri += (float) $lo->gia_nhap * (int) $lo->so_luong_ton;
            $tongSl += (int) $lo->so_luong_ton;
        }
        $giaVon = $tongSl > 0 ? round($tongGiaTri / $tongSl, 2) : (float) $variant->gia_von;

        $hanSuDungGanNhat = $loHangs->first()?->han_su_dung;

        // Snapshot lo hang cho audit
        $loSnapshot = $loHangs->map(fn ($lo) => [
            'id_chi_tiet_lo_hang' => $lo->id,
            'so_luong_ton' => (int) $lo->so_luong_ton,
            'gia_nhap' => (float) $lo->gia_nhap,
            'han_su_dung' => $lo->han_su_dung?->format('Y-m-d'),
        ])->values()->all();

        return ChiTietKiemKho::create([
            'id_phieu_kiem_kho' => $phieu->id,
            'variant_id' => $variant->id,
            'ma_vach' => $variant->ma_vach,
            'ma_hang' => $variant->ma_hang,
            'ten_san_pham' => $variant->product?->ten_san_pham,
            'ten_bien_the' => $variant->ten_bien_the_hien_thi,
            'ten_don_vi' => $variant->ten_hien_thi_don_vi,
            'han_su_dung_gan_nhat' => $hanSuDungGanNhat,
            'so_lo_con_ton' => $loHangs->count(),
            'so_luong_he_thong' => $tongHeThong,
            'so_luong_thuc_te' => null,
            'so_luong_lech' => 0,
            'gia_von' => $giaVon,
            'gia_tri_lech' => 0,
            'lo_hang_snapshot' => $loSnapshot,
        ]);
    }

    /**
     * Lay danh sach variant theo pham vi
     */
    private function resolveVariantsForPhieu(PhieuKiemKho $phieu)
    {
        $query = BienTheSanPham::query()->with('product')
            ->where('trang_thai', 1);

        switch ($phieu->pham_vi) {
            case 'toan_bo':
                // tat ca
                break;
            case 'theo_danh_muc':
                if (!$phieu->id_danh_muc) {
                    throw ValidationException::withMessages([
                        'id_danh_muc' => 'Vui lòng chọn danh mục.',
                    ]);
                }
                $query->whereHas('product', function ($q) use ($phieu) {
                    $q->where('id_danh_muc', $phieu->id_danh_muc);
                });
                break;
            case 'chon_san_pham':
                $ids = $phieu->variant_ids ?? [];
                if (empty($ids)) {
                    throw ValidationException::withMessages([
                        'variant_ids' => 'Vui lòng chọn ít nhất một sản phẩm.',
                    ]);
                }
                $query->whereIn('id', $ids);
                break;
        }

        return $query->orderBy('id')->get();
    }

    // ============================================================
    // 2. Cap nhat so luong thuc te (khi NV dem)
    // ============================================================
    public function capNhatSoLuongThucTe(int $phieuId, int $chiTietId, int $soLuongThucTe, ?string $lyDo, ?NguoiDung $user = null): ChiTietKiemKho
    {
        return DB::transaction(function () use ($phieuId, $chiTietId, $soLuongThucTe, $lyDo, $user) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_dem) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Phiếu đang ở trạng thái '{$phieu->trang_thai}', không thể cập nhật số lượng.",
                ]);
            }

            $chiTiet = ChiTietKiemKho::where('id_phieu_kiem_kho', $phieuId)
                ->where('id', $chiTietId)
                ->lockForUpdate()
                ->firstOrFail();

            $chiTiet->so_luong_thuc_te = $soLuongThucTe;
            $chiTiet->ly_do = $lyDo;
            $chiTiet->dem_luc = now();
            $chiTiet->id_nguoi_dem = $user?->id;
            $chiTiet->recomputeLech();
            $chiTiet->save();

            $phieu->recomputeTotals();

            return $chiTiet;
        });
    }

    // ============================================================
    // 3. Bat dau dem (phieu_tam -> counting) + khoa bien the
    // ============================================================
    public function batDauKiem(int $phieuId): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_bat_dau_kiem) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Chỉ có thể bắt đầu kiểm khi phiếu ở trạng thái 'Phiếu tạm'. Hiện tại: '{$phieu->trang_thai_label}'",
                ]);
            }

            // Kiem tra khong co phieu nao khac dang dem cac variant nay
            $variantIds = $phieu->chiTietKiemKho()->pluck('variant_id')->all();
            $conflict = KhoaKiemKho::whereIn('variant_id', $variantIds)
                ->whereNull('ngay_mo')
                ->where('id_phieu_kiem_kho', '!=', $phieuId)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'trang_thai' => 'Có biến thể đang bị khoá bởi phiếu kiểm kho khác.',
                ]);
            }

            // Tao record khoa cho moi variant
            $now = now();
            foreach ($variantIds as $vid) {
                KhoaKiemKho::updateOrCreate(
                    [
                        'id_phieu_kiem_kho' => $phieuId,
                        'variant_id' => $vid,
                    ],
                    [
                        'ngay_khoa' => $now,
                        'ngay_mo' => null,
                        'ly_do' => 'Đang kiểm kho',
                    ]
                );
            }

            $phieu->trang_thai = 'counting';
            $phieu->bat_dau_luc = $now;
            $phieu->save();

            return $phieu;
        });
    }

    // ============================================================
    // 4. Hoan tat dem (counting -> cho_duyet)
    // ============================================================
    public function hoanTatKiem(int $phieuId): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_hoan_tat_dem) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Chỉ có thể hoàn tất đếm khi phiếu ở trạng thái 'Đang đếm'. Hiện tại: '{$phieu->trang_thai_label}'",
                ]);
            }

            if (!$phieu->da_dem_xong) {
                throw ValidationException::withMessages([
                    'trang_thai' => 'Vẫn còn sản phẩm chưa được đếm xong.',
                ]);
            }

            $phieu->trang_thai = 'cho_duyet';
            $phieu->hoan_tat_dem_luc = now();
            $phieu->save();

            return $phieu;
        });
    }

    // ============================================================
    // 5. Duyet phieu (cho_duyet -> da_duyet)
    // ============================================================
    public function duyetPhieu(int $phieuId, NguoiDung $user): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId, $user) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_duyet) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Chỉ duyệt được phiếu ở trạng thái 'Chờ duyệt'. Hiện tại: '{$phieu->trang_thai_label}'",
                ]);
            }

            $phieu->trang_thai = 'da_duyet';
            $phieu->id_nguoi_duyet = $user->id;
            $phieu->duyet_luc = now();
            $phieu->save();

            return $phieu;
        });
    }

    // ============================================================
    // 6. Tu choi phieu (cho_duyet -> tu_choi)
    // ============================================================
    public function tuChoiPhieu(int $phieuId, string $lyDo): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId, $lyDo) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_tu_choi) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Chỉ từ chối được phiếu ở trạng thái 'Chờ duyệt'. Hiện tại: '{$phieu->trang_thai_label}'",
                ]);
            }

            $phieu->trang_thai = 'tu_choi';
            $phieu->ly_do_tu_choi = $lyDo;
            $phieu->save();

            // Mo khoa cac bien the
            KhoaKiemKho::where('id_phieu_kiem_kho', $phieuId)
                ->whereNull('ngay_mo')
                ->update(['ngay_mo' => now()]);

            return $phieu;
        });
    }

    // ============================================================
    // 7. Dem lai (tu_choi -> counting)
    // ============================================================
    public function demLai(int $phieuId): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_dem_lai) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Chỉ đếm lại được khi phiếu ở trạng thái 'Từ chối'. Hiện tại: '{$phieu->trang_thai_label}'",
                ]);
            }

            // Reset so_luong_thuc_te
            ChiTietKiemKho::where('id_phieu_kiem_kho', $phieuId)
                ->update([
                    'so_luong_thuc_te' => null,
                    'so_luong_lech' => 0,
                    'gia_tri_lech' => 0,
                    'ly_do' => null,
                    'dem_luc' => null,
                    'id_nguoi_dem' => null,
                ]);

            // Khoi tao lai khoa
            $now = now();
            $variantIds = $phieu->chiTietKiemKho()->pluck('variant_id')->all();
            foreach ($variantIds as $vid) {
                KhoaKiemKho::updateOrCreate(
                    ['id_phieu_kiem_kho' => $phieuId, 'variant_id' => $vid],
                    ['ngay_khoa' => $now, 'ngay_mo' => null, 'ly_do' => 'Đếm lại sau khi bị từ chối']
                );
            }

            $phieu->trang_thai = 'counting';
            $phieu->ly_do_tu_choi = null;
            $phieu->bat_dau_luc = $now;
            $phieu->save();

            $phieu->recomputeTotals();

            return $phieu;
        });
    }

    // ============================================================
    // 8. Hoan tat dieu chinh (da_duyet -> hoan_thanh)
    // ============================================================
    public function hoanTatDieuChinh(int $phieuId, NguoiDung $user): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId, $user) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_hoan_tat) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Chỉ hoàn tất được phiếu ở trạng thái 'Đã duyệt'. Hiện tại: '{$phieu->trang_thai_label}'",
                ]);
            }

            // Lay danh sach chi tiet
            $chiTiets = $phieu->chiTietKiemKho()->lockForUpdate()->get();

            // Tach thanh nhap (lech > 0) va xuat (lech < 0)
            $nhapItems = [];
            $xuatItems = [];
            foreach ($chiTiets as $ct) {
                if ($ct->so_luong_thuc_te === null) {
                    continue;
                }
                if ($ct->so_luong_lech > 0) {
                    $nhapItems[] = $ct;
                } elseif ($ct->so_luong_lech < 0) {
                    $xuatItems[] = $ct;
                }
            }

            // Tao phieu nhap kiem ke (neu co lech duong)
            if (!empty($nhapItems)) {
                $phieuNhap = $this->taoPhieuNhapKiemKe($phieu, $nhapItems, $user);
                $this->canBangTonKhoNhap($nhapItems, $phieuNhap);
            }

            // Tao phieu xuat kiem ke (neu co lech am)
            if (!empty($xuatItems)) {
                $phieuXuat = $this->taoPhieuXuatKiemKe($phieu, $xuatItems, $user);
                $this->canBangTonKhoXuat($xuatItems, $phieuXuat);
            }

            // Mo khoa cac bien the
            KhoaKiemKho::where('id_phieu_kiem_kho', $phieuId)
                ->whereNull('ngay_mo')
                ->update(['ngay_mo' => now()]);

            $phieu->trang_thai = 'hoan_thanh';
            $phieu->hoan_thanh_luc = now();
            $phieu->save();

            return $phieu;
        });
    }

    /**
     * Tao phieu nhap kiem ke (ghi nhan tang ton)
     */
    private function taoPhieuNhapKiemKe(PhieuKiemKho $phieu, array $chiTiets, NguoiDung $user): Phieu
    {
        $phieuMoi = Phieu::create([
            'loai_phieu' => 'nhap',
            'loai_phieu_enum' => 'nhap_kiem_ke',
            'id_nguoi_dung' => $user->id,
            'ly_do' => "Điều chỉnh tăng tồn kho từ phiếu kiểm kho {$phieu->ma_kiem_kho}",
            'ghi_chu' => "Tự động tạo từ phiếu kiểm kho {$phieu->ma_kiem_kho}",
        ]);

        $phieuNhap = PhieuNhap::create([
            'id_phieu' => $phieuMoi->id,
            'loai_nhap' => 'kiem_ke',
            'ghi_chu' => "Kiểm kho {$phieu->ma_kiem_kho}",
        ]);

        foreach ($chiTiets as $ct) {
            $variant = BienTheSanPham::lockForUpdate()->find($ct->variant_id);
            if (!$variant) continue;

            ChiTietPhieu::create([
                'id_phieu' => $phieuMoi->id,
                'id_san_pham' => $variant->product_id,
                'variant_id' => $variant->id,
                'so_luong' => $ct->so_luong_lech,
                'gia_nhap' => $ct->gia_von,
                'ghi_chu' => $ct->ly_do,
            ]);
        }

        return $phieuMoi;
    }

    /**
     * Tao phieu xuat kiem ke (ghi nhan giam ton)
     */
    private function taoPhieuXuatKiemKe(PhieuKiemKho $phieu, array $chiTiets, NguoiDung $user): Phieu
    {
        $phieuMoi = Phieu::create([
            'loai_phieu' => 'xuat',
            'loai_phieu_enum' => 'xuat_kiem_ke',
            'id_nguoi_dung' => $user->id,
            'ly_do' => "Điều chỉnh giảm tồn kho từ phiếu kiểm kho {$phieu->ma_kiem_kho}",
            'ghi_chu' => "Tự động tạo từ phiếu kiểm kho {$phieu->ma_kiem_kho}",
        ]);

        $phieuXuat = PhieuXuat::create([
            'id_phieu' => $phieuMoi->id,
            'loai_xuat' => 'tieu_huy',
            'ly_do' => "Kiểm kho {$phieu->ma_kiem_kho} - hàng thiếu",
            'ghi_chu' => "Hàng thiếu sau kiểm kho",
        ]);

        foreach ($chiTiets as $ct) {
            $variant = BienTheSanPham::lockForUpdate()->find($ct->variant_id);
            if (!$variant) continue;

            ChiTietPhieu::create([
                'id_phieu' => $phieuMoi->id,
                'id_san_pham' => $variant->product_id,
                'variant_id' => $variant->id,
                'so_luong' => abs($ct->so_luong_lech),
                'gia_nhap' => $ct->gia_von,
                'ghi_chu' => $ct->ly_do,
            ]);
        }

        return $phieuMoi;
    }

    /**
     * Can bang ton kho khi lech duong (tang ton) - FEFO
     * Phan bo chenh lech cho cac lo theo FEFO
     */
    private function canBangTonKhoNhap(array $chiTiets, Phieu $phieuNhap): void
    {
        foreach ($chiTiets as $ct) {
            $variantId = $ct->variant_id;
            $chenhLech = $ct->so_luong_lech; // > 0

            // Phan bo cho cac lo theo FEFO
            $loHangs = ChiTietLoHang::where('variant_id', $variantId)
                ->orderBy('han_su_dung', 'asc')
                ->lockForUpdate()
                ->get();

            $conLai = $chenhLech;
            foreach ($loHangs as $lo) {
                if ($conLai <= 0) break;
                $them = min((int) $lo->so_luong_ton == 0 ? $conLai : 0, $conLai);
                if ($them > 0) {
                    $lo->increment('so_luong_ton', $them);
                    $conLai -= $them;
                    // Cap nhat chi_tiet_phieu
                }
            }

            // Neu con du, tao lo moi (snapshot)
            if ($conLai > 0) {
                $loMoi = LoHang::create([
                    'ma_lo' => 'KK-' . now()->format('YmdHis') . '-' . $variantId,
                    'ngay_nhap' => now(),
                    'ghi_chu' => "Tự động tạo từ phiếu kiểm kho (hàng dư)",
                ]);
                ChiTietLoHang::create([
                    'id_lo_hang' => $loMoi->id,
                    'id_san_pham' => $ct->variant->product_id,
                    'variant_id' => $variantId,
                    'so_luong_nhap' => $conLai,
                    'so_luong_ton' => $conLai,
                    'gia_nhap' => $ct->gia_von,
                    'han_su_dung' => now()->addYear(),
                ]);
            }
        }
    }

    /**
     * Can bang ton kho khi lech am (giam ton) - FEFO
     */
    private function canBangTonKhoXuat(array $chiTiets, Phieu $phieuXuat): void
    {
        foreach ($chiTiets as $ct) {
            $variantId = $ct->variant_id;
            $chenhLech = abs($ct->so_luong_lech); // > 0

            $loHangs = ChiTietLoHang::where('variant_id', $variantId)
                ->orderBy('han_su_dung', 'asc')
                ->lockForUpdate()
                ->get();

            $conLai = $chenhLech;
            foreach ($loHangs as $lo) {
                if ($conLai <= 0) break;
                $tru = min((int) $lo->so_luong_ton, $conLai);
                if ($tru > 0) {
                    $lo->decrement('so_luong_ton', $tru);
                    $conLai -= $tru;
                }
            }
            // Neu conLai > 0: ton am -> canh bao nhung van ghi nhan de audit
        }
    }

    // ============================================================
    // 9. Huy phieu
    // ============================================================
    public function huyPhieu(int $phieuId, string $lyDo): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId, $lyDo) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_huy) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Không thể hủy phiếu ở trạng thái '{$phieu->trang_thai_label}'",
                ]);
            }

            $phieu->trang_thai = 'da_huy';
            $phieu->ly_do_huy = $lyDo;
            $phieu->huy_luc = now();
            $phieu->save();

            // Mo khoa cac bien the
            KhoaKiemKho::where('id_phieu_kiem_kho', $phieuId)
                ->whereNull('ngay_mo')
                ->update(['ngay_mo' => now()]);

            return $phieu;
        });
    }

    // ============================================================
    // 10. Khoi phuc tu thung rac
    // ============================================================
    public function khoiPhuc(int $phieuId): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId) {
            $phieu = PhieuKiemKho::withTrashed()->lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->trashed()) {
                throw ValidationException::withMessages([
                    'trang_thai' => 'Phiếu không nằm trong thùng rác.',
                ]);
            }

            $phieu->restore();
            return $phieu;
        });
    }

    // ============================================================
    // 11. Update thong tin phieu (chi cho phep khi phieu_tam)
    // ============================================================
    public function updatePhieu(int $phieuId, array $payload): PhieuKiemKho
    {
        return DB::transaction(function () use ($phieuId, $payload) {
            $phieu = PhieuKiemKho::lockForUpdate()->findOrFail($phieuId);

            if (!$phieu->co_the_sua) {
                throw ValidationException::withMessages([
                    'trang_thai' => "Chỉ sửa được phiếu ở trạng thái 'Phiếu tạm'. Hiện tại: '{$phieu->trang_thai_label}'",
                ]);
            }

            $phieu->fill(array_intersect_key($payload, array_flip([
                'id_nguoi_kiem',
                'ngay_kiem',
                'ghi_chu',
            ])));
            $phieu->save();

            return $phieu;
        });
    }

    // ============================================================
    // 12. Helpers
    // ============================================================

    /**
     * Kiem tra 1 variant co dang bi khoa boi phieu kiem kho nao khong
     */
    public function bienTheDangBiKhoa(int $variantId, ?int $truPhieuId = null): bool
    {
        $q = KhoaKiemKho::where('variant_id', $variantId)
            ->whereNull('ngay_mo');
        if ($truPhieuId) {
            $q->where('id_phieu_kiem_kho', '!=', $truPhieuId);
        }
        return $q->exists();
    }

    /**
     * Lay phieu kiem kho dang dem chua variant nay (neu co)
     */
    public function phieuDangKhoaBienThe(int $variantId): ?PhieuKiemKho
    {
        $khoa = KhoaKiemKho::with('phieuKiemKho')
            ->where('variant_id', $variantId)
            ->whereNull('ngay_mo')
            ->first();
        return $khoa?->phieuKiemKho;
    }

    /**
     * Thong ke theo phieu
     */
    public function thongKePhieu(PhieuKiemKho $phieu): array
    {
        $phieu->refresh();
        return [
            'tong_so_san_pham' => $phieu->tong_so_san_pham,
            'so_sp_dung' => $phieu->so_sp_dung,
            'so_sp_thieu' => $phieu->so_sp_thieu,
            'so_sp_thua' => $phieu->so_sp_thua,
            'so_sp_chua_dem' => $phieu->tong_so_san_pham - $phieu->so_sp_dung - $phieu->so_sp_thieu - $phieu->so_sp_thua,
            'tong_sl_he_thong' => $phieu->tong_sl_he_thong,
            'tong_sl_thuc_te' => $phieu->tong_sl_thuc_te,
            'tong_sl_lech' => $phieu->tong_sl_lech,
            'tong_gia_tri_lech' => (float) $phieu->tong_gia_tri_lech,
        ];
    }

    /**
     * Bao cao tong hop
     */
    public function baoCaoTongHop(array $filters = []): array
    {
        $query = PhieuKiemKho::query()
            ->where('trang_thai', 'hoan_thanh');

        if (!empty($filters['tu_ngay'])) {
            $query->whereDate('hoan_thanh_luc', '>=', $filters['tu_ngay']);
        }
        if (!empty($filters['den_ngay'])) {
            $query->whereDate('hoan_thanh_luc', '<=', $filters['den_ngay']);
        }

        $phieus = $query->get();

        return [
            'tong_phieu' => $phieus->count(),
            'tong_san_pham_kiem' => (int) $phieus->sum('tong_so_san_pham'),
            'tong_sp_thieu' => (int) $phieus->sum('so_sp_thieu'),
            'tong_sp_thua' => (int) $phieus->sum('so_sp_thua'),
            'tong_sp_dung' => (int) $phieus->sum('so_sp_dung'),
            'tong_sl_thieu' => abs((int) $phieus->where('tong_sl_lech', '<', 0)->sum('tong_sl_lech')),
            'tong_sl_thua' => (int) $phieus->where('tong_sl_lech', '>', 0)->sum('tong_sl_lech'),
            'tong_gia_tri_lech' => (float) $phieus->sum('tong_gia_tri_lech'),
        ];
    }
}