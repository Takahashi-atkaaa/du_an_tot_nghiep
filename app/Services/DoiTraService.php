<?php

namespace App\Services;

use App\Models\BienTheSanPham;
use App\Models\ChiTietDoiTra;
use App\Models\ChiTietHoaDon;
use App\Models\ChiTietLoHang;
use App\Models\DoiTra;
use App\Models\HangLoi;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\LichSuTichDiem;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DoiTraService
{
    private function chiTietLoaiSql(): string
    {
        return "COALESCE(chi_tiet_doi_tra.loai, CASE WHEN doi_tra.Loai = 'doi_tra' THEN 'doi_hang' ELSE 'tra_hang' END)";
    }

    private function tenHienThiBienTheSelect(): \Illuminate\Database\Query\Expression
    {
        return DB::raw("
            TRIM(
                CONCAT(
                    COALESCE(san_pham.ten_san_pham, ''),
                    CASE
                        WHEN COALESCE(
                            CASE
                                WHEN bien_the_san_pham.la_don_vi = 1 THEN NULLIF(bien_the_san_pham.ten_don_vi, '')
                                ELSE NULLIF(bien_the_san_pham.ten_bien_the, '')
                            END,
                            NULLIF(bien_the_san_pham.ten_don_vi, '')
                        ) IS NOT NULL
                            THEN CONCAT(
                                ' - ',
                                COALESCE(
                                    CASE
                                        WHEN bien_the_san_pham.la_don_vi = 1 THEN NULLIF(bien_the_san_pham.ten_don_vi, '')
                                        ELSE NULLIF(bien_the_san_pham.ten_bien_the, '')
                                    END,
                                    NULLIF(bien_the_san_pham.ten_don_vi, '')
                                )
                            )
                        ELSE ''
                    END
                )
            ) as ten_hien_thi_san_pham
        ");
    }

    public function getInvoiceReturnSummary(int $hoaDonId): array
    {
        $lichSuDoiTra = $this->getDoiTraHistory($hoaDonId);
        $tongHopDoiTra = $this->getReturnTotals($hoaDonId);
        $chiTietTheoBienThe = $this->getInvoiceItemReturnBreakdown($hoaDonId);

        return [
            'lichSuDoiTra' => $lichSuDoiTra,
            'tongHopDoiTra' => $tongHopDoiTra,
            'chiTietTheoBienThe' => $chiTietTheoBienThe,
            'coDoiTra' => $lichSuDoiTra->isNotEmpty(),
        ];
    }

    public function getInvoiceReturnData(int $hoaDonId): array
    {
        $hoaDon = HoaDon::query()
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->select('hoa_don.*', 'khach_hang.ten_khach_hang')
            ->where('hoa_don.id', $hoaDonId)
            ->first();

        if (!$hoaDon) {
            abort(404);
        }

        $chiTiet = ChiTietHoaDon::query()
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.id as id_san_pham',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                'bien_the_san_pham.so_luong_ton',
                $this->tenHienThiBienTheSelect()
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $hoaDonId)
            ->orderBy('chi_tiet_hoa_don.id')
            ->get();

        $daDoiTraTheoBienThe = $this->getReturnedQuantitiesByVariant($hoaDonId);
        $replacementOptions = $this->getReplacementOptionsByVariantIds(
            $chiTiet->pluck('id_bien_the_san_pham')->unique()->values()->all()
        );

        foreach ($chiTiet as $item) {
            $item->da_doi_tra = (int) ($daDoiTraTheoBienThe[$item->id_bien_the_san_pham] ?? 0);
            $item->so_luong_con_lai = max(0, (int) $item->so_luong - $item->da_doi_tra);
            $replacement = $replacementOptions->get((int) $item->id_bien_the_san_pham);
            $item->replacement_options = $replacement ? collect([$replacement]) : collect();
            $item->tong_da_tra = $item->da_doi_tra;
        }

        return [
            'hoaDon' => $hoaDon,
            'chiTiet' => $chiTiet,
        ];
    }

    public function getReturnedQuantitiesByVariant(int $hoaDonId): Collection
    {
        return ChiTietDoiTra::query()
            ->join('doi_tra', 'chi_tiet_doi_tra.id_doi_tra', '=', 'doi_tra.id')
            ->whereNull('doi_tra.deleted_at')
            ->whereNull('chi_tiet_doi_tra.deleted_at')
            ->where('doi_tra.id_hoa_don', $hoaDonId)
            ->groupBy('chi_tiet_doi_tra.id_bien_the')
            ->select(
                'chi_tiet_doi_tra.id_bien_the',
                DB::raw('SUM(chi_tiet_doi_tra.so_luong) as tong_so_luong')
            )
            ->pluck('tong_so_luong', 'chi_tiet_doi_tra.id_bien_the');
    }

    public function getDoiTraHistory(int $hoaDonId): Collection
    {
        return DoiTra::query()
            ->with([
                'nguoiDung.vaiTro',
                'chiTietDoiTras.bienTheSanPham.product',
                'chiTietDoiTras.bienTheThayThe.product',
            ])
            ->where('id_hoa_don', $hoaDonId)
            ->orderByDesc('ngay')
            ->orderByDesc('id')
            ->get()
            ->each(fn (DoiTra $doiTra) => $this->hydrateSellerDisplayName($doiTra));
    }

    public function getInvoiceItemReturnBreakdown(int $hoaDonId): Collection
    {
        $chiTietLoaiSql = $this->chiTietLoaiSql();

        return ChiTietDoiTra::query()
            ->join('doi_tra', 'chi_tiet_doi_tra.id_doi_tra', '=', 'doi_tra.id')
            ->whereNull('doi_tra.deleted_at')
            ->whereNull('chi_tiet_doi_tra.deleted_at')
            ->where('doi_tra.id_hoa_don', $hoaDonId)
            ->groupBy('chi_tiet_doi_tra.id_bien_the')
            ->select(
                'chi_tiet_doi_tra.id_bien_the',
                DB::raw("SUM(CASE WHEN {$chiTietLoaiSql} = 'tra_hang' THEN chi_tiet_doi_tra.so_luong ELSE 0 END) as tong_tra_hang"),
                DB::raw("SUM(CASE WHEN {$chiTietLoaiSql} = 'doi_hang' THEN chi_tiet_doi_tra.so_luong ELSE 0 END) as tong_doi_hang"),
                DB::raw('SUM(chi_tiet_doi_tra.so_luong) as tong_doi_tra')
            )
            ->get()
            ->keyBy('id_bien_the');
    }

    public function getReturnTotals(int $hoaDonId): array
    {
        $chiTietLoaiSql = $this->chiTietLoaiSql();

        $tongTraHang = (float) ChiTietDoiTra::query()
            ->join('doi_tra', 'chi_tiet_doi_tra.id_doi_tra', '=', 'doi_tra.id')
            ->whereNull('doi_tra.deleted_at')
            ->whereNull('chi_tiet_doi_tra.deleted_at')
            ->where('doi_tra.id_hoa_don', $hoaDonId)
            ->whereRaw("{$chiTietLoaiSql} = 'tra_hang'")
            ->sum('chi_tiet_doi_tra.thanh_tien');

        return [
            'tong_tra_hang' => $tongTraHang,
        ];
    }

    public function getEligibleSalesUsers(): Collection
    {
        return NguoiDung::query()
            ->with('vaiTro')
            ->where('trang_thai', 1)
            ->whereNull('deleted_at')
            ->orderBy('ho_ten')
            ->get()
            ->filter(fn (NguoiDung $nguoiDung) => $this->isEligibleSellerRole(optional($nguoiDung->vaiTro)->ten_vai_tro))
            ->values();
    }

    public function process(HoaDon $hoaDon, array $payload, ?NguoiDung $nguoiXuLy = null): DoiTra
    {
        $items = collect($payload['items'] ?? [])
            ->filter(fn (array $item) => ($item['action'] ?? 'none') !== 'none')
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Vui lòng chọn ít nhất 1 sản phẩm để đổi hoặc trả.',
            ]);
        }

        return DB::transaction(function () use ($hoaDon, $payload, $items) {
            $nguoiBan = $this->resolveSelectedSeller((int) ($payload['id_nguoi_dung'] ?? 0));

            $hoaDon = HoaDon::query()
                ->where('id', $hoaDon->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'], true)) {
                throw ValidationException::withMessages([
                    'hoa_don' => 'Hóa đơn này không thể đổi/trả hàng.',
                ]);
            }

            $chiTietHoaDon = ChiTietHoaDon::query()
                ->with('bienThe')
                ->where('id_hoa_don', $hoaDon->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $daDoiTraTheoBienThe = $this->getReturnedQuantitiesByVariant($hoaDon->id);

            $normalizedItems = [];
            $tongTienTra = 0;
            $coTraHang = false;
            $coDoiHang = false;
            $coHangLoi = false;

            foreach ($items as $item) {
                $chiTiet = $chiTietHoaDon->get((int) $item['id_chi_tiet_hoa_don']);

                if (!$chiTiet) {
                    throw ValidationException::withMessages([
                        'items' => 'Có sản phẩm không thuộc hóa đơn gốc.',
                    ]);
                }

                $action = (string) ($item['action'] ?? 'none');
                $soLuong = (int) ($item['so_luong'] ?? 0);
                $isHangLoi = filter_var($item['hang_loi'] ?? false, FILTER_VALIDATE_BOOL);
                $daDoiTra = (int) ($daDoiTraTheoBienThe[$chiTiet->id_bien_the_san_pham] ?? 0);
                $conLai = max(0, (int) $chiTiet->so_luong - $daDoiTra);

                if ($soLuong < 1 || $soLuong > $conLai) {
                    throw ValidationException::withMessages([
                        'items' => 'Số lượng trả/đổi vượt quá số lượng còn lại của hóa đơn.',
                    ]);
                }

                $replacementVariantId = null;
                $lineLoai = $action === 'exchange' ? 'doi_hang' : 'tra_hang';

                if ($action === 'exchange') {
                    if (!$isHangLoi) {
                        throw ValidationException::withMessages([
                            'items' => 'Đổi hàng chỉ áp dụng cho hàng lỗi.',
                        ]);
                    }

                    $replacementVariantId = (int) ($item['id_bien_the_thay_the'] ?? 0);

                    if ($replacementVariantId !== (int) $chiTiet->id_bien_the_san_pham) {
                        throw ValidationException::withMessages([
                            'items' => 'Đổi hàng chỉ được phép đổi đúng cùng biến thể đã mua. Nếu khách muốn biến thể khác, hãy xử lý trả hàng và tạo hóa đơn mới.',
                        ]);
                    }

                    $replacementVariant = BienTheSanPham::query()
                        ->where('id', $replacementVariantId)
                        ->whereNull('deleted_at')
                        ->where('trang_thai', true)
                        ->lockForUpdate()
                        ->first();

                    if (!$replacementVariant) {
                        throw ValidationException::withMessages([
                            'items' => 'Biến thể thay thế không hợp lệ hoặc đã ngừng kinh doanh.',
                        ]);
                    }

                    $this->deductVariantStockByLots($replacementVariant->id, $soLuong, true);
                    $coDoiHang = true;
                }

                if ($action === 'return') {
                    if (!$isHangLoi) {
                        $this->restoreVariantStock($chiTiet->id_bien_the_san_pham, $soLuong);
                    }

                    $tongTienTra += $soLuong * (float) $chiTiet->gia_ban;
                    $coTraHang = true;
                }

                if ($isHangLoi) {
                    $coHangLoi = true;
                }

                $normalizedItems[] = [
                    'chi_tiet' => $chiTiet,
                    'action' => $action,
                    'loai' => $lineLoai,
                    'so_luong' => $soLuong,
                    'hang_loi' => $isHangLoi,
                    'id_bien_the_thay_the' => $replacementVariantId ?: null,
                    'gia_ban' => (float) $chiTiet->gia_ban,
                    'thanh_tien' => $soLuong * (float) $chiTiet->gia_ban,
                ];
            }

            $doiTra = DoiTra::query()->create([
                'id_nguoi_dung' => $nguoiBan->id,
                'id_hoa_don' => $hoaDon->id,
                'Loai' => $coDoiHang ? 'doi_tra' : 'tra_hang',
                'hang_loi' => $coHangLoi,
                'ngay' => Carbon::now(),
                'tru_diem_cua_khach' => false,
                'ly_do' => $this->normalizeReason(
                    (string) ($payload['ly_do'] ?? ''),
                    $coTraHang,
                    $coDoiHang,
                    $coHangLoi
                ),
            ]);

            foreach ($normalizedItems as $item) {
                $chiTietDoiTra = ChiTietDoiTra::query()->create([
                    'id_doi_tra' => $doiTra->id,
                    'id_bien_the' => $item['chi_tiet']->id_bien_the_san_pham,
                    'id_bien_the_thay_the' => $item['id_bien_the_thay_the'],
                    'loai' => $item['loai'],
                    'so_luong' => $item['so_luong'],
                    'gia_ban' => $item['gia_ban'],
                    'thanh_tien' => $item['thanh_tien'],
                ]);

                if ($item['hang_loi']) {
                    if (!Schema::hasTable('hang_loi')) {
                        throw ValidationException::withMessages([
                            'items' => 'Bảng hàng lỗi chưa được tạo. Vui lòng chạy migration trước khi xử lý sản phẩm hàng lỗi.',
                        ]);
                    }

                    HangLoi::query()->create([
                        'id_doi_tra' => $doiTra->id,
                        'id_chi_tiet_doi_tra' => $chiTietDoiTra->id,
                        'id_bien_the' => $item['chi_tiet']->id_bien_the_san_pham,
                        'so_luong' => $item['so_luong'],
                        'trang_thai' => 'cho_tieu_huy',
                        'ly_do' => $doiTra->ly_do,
                    ]);
                }
            }

            if ($coTraHang && $tongTienTra > 0) {
                $this->deductCustomerPointsForReturn($hoaDon, $doiTra, $tongTienTra);
            }

            $this->updateInvoiceStatus($hoaDon);

            $doiTra->load([
                'nguoiDung.vaiTro',
                'chiTietDoiTras.bienTheSanPham.product',
                'chiTietDoiTras.bienTheThayThe.product',
            ]);

            $this->hydrateSellerDisplayName($doiTra);

            return $doiTra;
        });
    }

    private function resolveSelectedSeller(int $idNguoiDung): NguoiDung
    {
        $nguoiBan = NguoiDung::query()
            ->with('vaiTro')
            ->whereKey($idNguoiDung)
            ->where('trang_thai', 1)
            ->whereNull('deleted_at')
            ->first();

        if (!$nguoiBan || !$this->isEligibleSellerRole(optional($nguoiBan->vaiTro)->ten_vai_tro)) {
            throw ValidationException::withMessages([
                'id_nguoi_dung' => 'Người thực hiện đổi/trả phải là người dùng đang hoạt động với vai trò Admin, Nhân viên hoặc Trưởng ca.',
            ]);
        }

        return $nguoiBan;
    }

    private function hydrateSellerDisplayName(DoiTra $doiTra): void
    {
        if ($doiTra->relationLoaded('nguoiDung') && $doiTra->nguoiDung) {
            $doiTra->setAttribute('nguoi_thuc_hien_doi_tra_hien_thi', $doiTra->nguoiDung->ho_ten_kem_vai_tro);
        }
    }

    private function getReplacementOptionsByVariantIds(array $variantIds): Collection
    {
        if (empty($variantIds)) {
            return collect();
        }

        return BienTheSanPham::query()
            ->with('product:id,ten_san_pham')
            ->whereIn('id', $variantIds)
            ->whereNull('deleted_at')
            ->where('trang_thai', true)
            ->orderBy('id')
            ->get([
                'id',
                'product_id',
                'la_don_vi',
                'ten_bien_the',
                'ten_don_vi',
                'ma_vach',
                'so_luong_ton',
            ])
            ->keyBy('id');
    }

    private function deductVariantStockByLots(int $variantId, int $soLuong, bool $sameVariantExchange = false): void
    {
        $remaining = $soLuong;

        $lots = ChiTietLoHang::query()
            ->where('variant_id', $variantId)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($lots as $lot) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min($remaining, (int) $lot->so_luong_ton);
            $lot->so_luong_ton -= $deduct;
            $lot->save();
            $remaining -= $deduct;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => $sameVariantExchange
                    ? 'Sản phẩm cùng biến thể hiện đã hết hàng, không thể thực hiện đổi hàng.'
                    : 'Tồn kho theo lô không đủ để thực hiện nghiệp vụ này.',
            ]);
        }
    }

    private function restoreVariantStock(int $variantId, int $soLuong): void
    {
        $lot = ChiTietLoHang::query()
            ->where('variant_id', $variantId)
            ->orderBy('han_su_dung')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if ($lot) {
            $lot->so_luong_ton += $soLuong;
            $lot->save();
            return;
        }

        BienTheSanPham::query()
            ->where('id', $variantId)
            ->lockForUpdate()
            ->increment('so_luong_ton', $soLuong);
    }

    private function deductCustomerPointsForReturn(HoaDon $hoaDon, DoiTra $doiTra, float $tongTienTra): void
    {
        if (!$hoaDon->id_khach_hang || $doiTra->tru_diem_cua_khach) {
            return;
        }

        $khachHang = KhachHang::query()
            ->where('id', $hoaDon->id_khach_hang)
            ->lockForUpdate()
            ->first();

        if (!$khachHang) {
            return;
        }

        $diemCanTru = (int) floor($tongTienTra / 10000);

        if ($diemCanTru <= 0) {
            $khachHang->decrement('tong_chi_tieu', min((float) $khachHang->tong_chi_tieu, $tongTienTra));
            $doiTra->update(['tru_diem_cua_khach' => true]);
            return;
        }

        $diemThucTe = min($diemCanTru, (int) $khachHang->diem_tich_luy);

        $khachHang->decrement('tong_chi_tieu', min((float) $khachHang->tong_chi_tieu, $tongTienTra));

        if ($diemThucTe > 0) {
            $khachHang->decrement('diem_tich_luy', $diemThucTe);

            LichSuTichDiem::query()->create([
                'id_khach_hang' => $khachHang->id,
                'id_hoa_don' => $hoaDon->id,
                'loai_bien_dong' => 'tru',
                'so_diem' => $diemThucTe,
                'ly_do' => 'Trả hàng hóa đơn #' . $hoaDon->id . ' - Đổi/trả #' . $doiTra->id,
            ]);
        }

        $doiTra->update(['tru_diem_cua_khach' => true]);
    }

    private function updateInvoiceStatus(HoaDon $hoaDon): void
    {
        $tongDaMua = (int) ChiTietHoaDon::query()
            ->where('id_hoa_don', $hoaDon->id)
            ->sum('so_luong');

        $chiTietLoaiSql = $this->chiTietLoaiSql();

        $tongDaTraHang = (int) ChiTietDoiTra::query()
            ->join('doi_tra', 'chi_tiet_doi_tra.id_doi_tra', '=', 'doi_tra.id')
            ->whereNull('doi_tra.deleted_at')
            ->whereNull('chi_tiet_doi_tra.deleted_at')
            ->where('doi_tra.id_hoa_don', $hoaDon->id)
            ->whereRaw("{$chiTietLoaiSql} = 'tra_hang'")
            ->sum('chi_tiet_doi_tra.so_luong');

        $hoaDon->update([
            'trang_thai' => $tongDaTraHang >= $tongDaMua ? 'Đã trả toàn bộ' : 'Đã đổi/trả hàng',
        ]);
    }

    private function normalizeReason(string $reason, bool $coTraHang, bool $coDoiHang, bool $coHangLoi): ?string
    {
        $trimmed = trim($reason);

        if ($trimmed !== '') {
            return $trimmed;
        }

        if ($coTraHang && $coDoiHang) {
            return $coHangLoi ? 'Đổi / trả hàng lỗi' : 'Đổi / trả hàng';
        }

        if ($coDoiHang) {
            return 'Hàng lỗi';
        }

        return $coHangLoi ? 'Trả hàng lỗi' : 'Trả hàng';
    }

    private function isEligibleSellerRole(?string $roleName): bool
    {
        $normalized = Str::of((string) $roleName)
            ->lower()
            ->ascii()
            ->replace('-', ' ')
            ->squish()
            ->value();

        return in_array($normalized, ['admin', 'nhan vien', 'truong ca'], true);
    }
}
