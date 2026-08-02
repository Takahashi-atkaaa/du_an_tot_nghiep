<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\BienTheSanPham;
use App\Models\ChiTietKiemKho;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\DonViQuyDoi;
use App\Models\LoHang;
use App\Models\Phieu;
use App\Models\PhieuKiemKho;
use App\Models\PhieuNhap;
use App\Models\PhieuXuat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KiemKhoApiController extends Controller
{
    /**
     * GET /admin/api/kiem-kho/search?q=...
     * Nhập mã vạch hoặc tên. Trả về TẤT CẢ chi_tiet_lo_hang còn tồn
     * (so_luong_ton > 0) khớp với mã vạch hoặc tên sản phẩm.
     *
     * Logic tra cứu tương tự PhieuNhapImport::findVariant:
     *  - Bước 1: tìm theo ma_vach trong bien_the_san_pham
     *  - Bước 2: tìm theo ma_vach trong don_vi_quy_doi
     *  - Bước 3: tìm theo tên sản phẩm (LIKE)
     */
    public function searchItems(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['success' => true, 'data' => [], 'matched_type' => null]);
        }

        // Bước 1 + 2: Tìm theo mã vạch (biến thể hoặc đơn vị quy đổi)
        $variantIds = collect();

        $variantByMaVach = BienTheSanPham::where('ma_vach', $q)->first();
        if ($variantByMaVach) {
            $variantIds->push($variantByMaVach->id);
        } else {
            $donViByMaVach = DonViQuyDoi::where('ma_vach', $q)->first();
            if ($donViByMaVach) {
                $variantIds->push($donViByMaVach->variant_id);
            }
        }

        // Nếu không khớp mã vạch, tìm theo tên sản phẩm
        $matchedType = 'ma_vach';
        if ($variantIds->isEmpty()) {
            $matchedType = 'ten_san_pham';
            $productIds = \App\Models\Product::where('ten_san_pham', 'like', "%{$q}%")
                ->pluck('id');
            if ($productIds->isNotEmpty()) {
                // Lấy TẤT CẢ biến thể của các sản phẩm này
                $variantIds = BienTheSanPham::whereIn('product_id', $productIds)->pluck('id');
            }
        }

        if ($variantIds->isEmpty()) {
            return response()->json(['success' => true, 'data' => [], 'matched_type' => $matchedType]);
        }

        // Join chi_tiet_lo_hang + lo_hang + bien_the_san_pham + product
        $loHang = ChiTietLoHang::query()
            ->with([
                'loHang',
                'variant.product',
            ])
            ->whereIn('variant_id', $variantIds)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->get();

        $data = $loHang->map(function ($row) {
            $variant = $row->variant;
            $product = $variant?->product;
            return [
                'id_chi_tiet_lo_hang' => $row->id,
                'variant_id' => $row->variant_id,
                'ma_vach' => $variant?->ma_vach ?? '',
                'ma_hang' => $variant?->ma_hang ?? '',
                'ten_san_pham' => $product?->ten_san_pham ?? '',
                'ten_bien_the' => $variant?->ten_bien_the ?? '',
                'don_vi' => $this->buildDonViLabel($variant),
                'han_su_dung' => $row->han_su_dung ? $row->han_su_dung->format('Y-m-d') : null,
                'ma_lo' => $row->loHang?->ma_lo ?? null,
                'so_luong_ton' => (int) $row->so_luong_ton,
                'gia_von' => (float) $row->gia_nhap, // snapshot giá nhập vào lô
            ];
        })->values();

        return response()->json([
            'success' => true,
            'matched_type' => $matchedType,
            'data' => $data,
        ]);
    }

    /**
     * GET /admin/api/kiem-kho/draft
     * Lấy phiếu kiểm kho đang ở trạng thái "phieu_tam" của user hiện tại.
     * Mỗi user chỉ có tối đa 1 phiếu tạm.
     */
    public function getDraft(): JsonResponse
    {
        $userId = Auth::id();
        $phieu = PhieuKiemKho::with('chiTietKiemKho')
            ->where('id_nguoi_dung', $userId)
            ->where('trang_thai', 'phieu_tam')
            ->orderByDesc('id')
            ->first();

        if (!$phieu) {
            return response()->json([
                'success' => true,
                'data' => null,
                'ma_kiem_kho_preview' => PhieuKiemKho::generateMaKiemKho(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializePhieu($phieu),
            'ma_kiem_kho_preview' => $phieu->ma_kiem_kho,
        ]);
    }

    /**
     * POST /admin/api/kiem-kho/draft
     * Lưu nháp / cập nhật phiếu tạm.
     * Payload:
     *  - ghi_chu: string|null
     *  - items: [{id_chi_tiet_lo_hang, variant_id, so_luong_thuc_te, ...}]
     */
    public function storeDraft(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $ghiChu = $request->input('ghi_chu');
        $items = $request->input('items', []);

        if (!is_array($items)) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu items không hợp lệ'], 422);
        }

        try {
            DB::transaction(function () use ($userId, $ghiChu, $items) {
                // Lấy hoặc tạo mới phiếu tạm
                $phieu = PhieuKiemKho::where('id_nguoi_dung', $userId)
                    ->where('trang_thai', 'phieu_tam')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                if (!$phieu) {
                    $phieu = PhieuKiemKho::create([
                        'ma_kiem_kho' => PhieuKiemKho::generateMaKiemKho(),
                        'id_nguoi_dung' => $userId,
                        'trang_thai' => 'phieu_tam',
                        'ghi_chu' => $ghiChu,
                    ]);
                } else {
                    $phieu->update(['ghi_chu' => $ghiChu]);
                }

                // Đồng bộ danh sách dòng: xóa dòng cũ, insert lại các dòng mới (đơn giản & an toàn)
                ChiTietKiemKho::where('id_phieu_kiem_kho', $phieu->id)->delete();

                foreach ($items as $it) {
                    $ctloId = (int) ($it['id_chi_tiet_lo_hang'] ?? 0);
                    if ($ctloId <= 0) {
                        continue;
                    }
                    $ctlo = ChiTietLoHang::with(['variant.product', 'loHang'])->find($ctloId);
                    if (!$ctlo) {
                        continue;
                    }

                    $variant = $ctlo->variant;
                    $product = $variant?->product;
                    $soLuongThucTe = $it['so_luong_thuc_te'] ?? null;
                    if ($soLuongThucTe === '' || $soLuongThucTe === null) {
                        $soLuongThucTe = null;
                    } else {
                        $soLuongThucTe = (int) $soLuongThucTe;
                    }

                    $row = ChiTietKiemKho::create([
                        'id_phieu_kiem_kho' => $phieu->id,
                        'variant_id' => $ctlo->variant_id,
                        'id_chi_tiet_lo_hang' => $ctlo->id,
                        'ma_vach' => $variant?->ma_vach ?? '',
                        'ten_san_pham' => $product?->ten_san_pham ?? '',
                        'ten_bien_the' => $variant?->ten_bien_the ?? '',
                        'ten_don_vi' => $this->buildDonViLabel($variant),
                        'han_su_dung' => $ctlo->han_su_dung,
                        'ma_lo' => $ctlo->loHang?->ma_lo ?? null,
                        'so_luong_ton' => (int) $ctlo->so_luong_ton,
                        'so_luong_thuc_te' => $soLuongThucTe,
                        'so_luong_lech' => 0,
                        'gia_von' => (float) $ctlo->gia_nhap,
                        'gia_tri_lech' => 0,
                    ]);
                    $row->recomputeLech();
                    $row->save();
                }

                $phieu->refresh();
                $phieu->recomputeTotals();
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lưu nháp: ' . $e->getMessage(),
            ], 500);
        }

        // Trả về dữ liệu phiếu sau khi lưu
        $phieu = PhieuKiemKho::with('chiTietKiemKho')
            ->where('id_nguoi_dung', $userId)
            ->where('trang_thai', 'phieu_tam')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $phieu ? $this->serializePhieu($phieu) : null,
        ]);
    }

    /**
     * POST /admin/api/kiem-kho/{id}/balance
     * Cân bằng kho: bọc DB::transaction.
     *
     *  - Lệch âm (mất hàng): tạo 1 phieu_xuat với loai_xuat=tieu_huy,
     *    trừ thẳng so_luong_ton của chi_tiet_lo_hang.
     *  - Lệch dương (dư hàng): tạo 1 phieu_nhap với loai_nhap=kiem_ke,
     *    cộng thêm so_luong vào chi_tiet_lo_hang.
     *  - Cập nhật đồng bộ tổng tồn kho ở bien_the_san_pham.
     *  - Đổi trang_thái phiếu kiểm -> hoan_thanh + set hoan_thanh_luc.
     */
    public function balanceInventory(int $id): JsonResponse
    {
        $userId = Auth::id();

        try {
            $phieuKk = DB::transaction(function () use ($id, $userId) {
                $pk = PhieuKiemKho::with('chiTietKiemKho')->lockForUpdate()->find($id);
                if (!$pk) {
                    throw new \Exception('Phiếu kiểm kho không tồn tại.');
                }
                if ($pk->trang_thai !== 'phieu_tam') {
                    throw new \Exception('Phiếu đã ' . $pk->trang_thai . ', không thể cân bằng lại.');
                }
                if ($pk->id_nguoi_dung !== $userId) {
                    throw new \Exception('Bạn không có quyền cân bằng phiếu này.');
                }
                if ($pk->chiTietKiemKho->isEmpty()) {
                    throw new \Exception('Phiếu chưa có dòng nào để cân bằng.');
                }

                // Gom các dòng lệch theo variant. Nếu cùng biến thể có nhiều lô lệch,
                // ta cộng dồn vào 1 phiếu nhập/xuất duy nhất.
                $lechAm = []; // variant_id => ['variant_id' => x, 'sl' => y, 'g_von' => z, 'items' => []]
                $lechDuong = [];

                foreach ($pk->chiTietKiemKho as $d) {
                    if ($d->so_luong_thuc_te === null) {
                        continue; // Bỏ qua dòng chưa kiểm
                    }
                    $le = (int) $d->so_luong_lech;
                    if ($le === 0) {
                        continue;
                    }
                    $vid = $d->variant_id;
                    $bucket = &$lechAm;
                    if ($le > 0) {
                        $bucket = &$lechDuong;
                    }
                    if (!isset($bucket[$vid])) {
                        $bucket[$vid] = [
                            'variant_id' => $vid,
                            'sl' => 0,
                            'g_von' => (float) $d->gia_von,
                            'items' => [],
                        ];
                    }
                    $bucket[$vid]['sl'] += abs($le);
                    $bucket[$vid]['items'][] = $d;
                    unset($bucket);
                }

                // Tạo 1 phiếu xuất hủy (gộp tất cả các variant lệch âm vào 1 phiếu)
                if (!empty($lechAm)) {
                    $phX = Phieu::create([
                        'loai_phieu' => 'Xuất hủy (kiểm kê)',
                        'loai_phieu_enum' => 'xuat_tieu_huy',
                        'id_nguoi_dung' => $userId,
                        'ly_do' => 'Cân bằng từ phiếu kiểm kho ' . $pk->ma_kiem_kho,
                        'ghi_chu' => $pk->ghi_chu,
                    ]);
                    PhieuXuat::create([
                        'id_phieu' => $phX->id,
                        'loai_xuat' => 'tieu_huy',
                        'ly_do' => 'Hàng mất phát hiện qua kiểm kê',
                    ]);

                    foreach ($lechAm as $vid => $data) {
                        $variant = BienTheSanPham::with('product')->find($vid);
                        if (!$variant) {
                            continue;
                        }
                        // Xử lý từng dòng lệch âm riêng để giảm tồn từng chi_tiet_lo_hang
                        foreach ($data['items'] as $d) {
                            $absLech = abs((int) $d->so_luong_lech);
                            if ($absLech <= 0) {
                                continue;
                            }
                            ChiTietPhieu::create([
                                'id_phieu' => $phX->id,
                                'id_san_pham' => $variant->product_id,
                                'variant_id' => $vid,
                                'id_lo_hang' => $d->chiTietLoHang?->id_lo_hang,
                                'id_chi_tiet_lo_hang' => $d->id_chi_tiet_lo_hang,
                                'so_luong' => $absLech,
                                'gia_nhap' => (float) $d->gia_von,
                                'han_su_dung' => $d->han_su_dung,
                                'so_luong_con_lai' => 0,
                            ]);
                            // Trừ tồn trực tiếp ở chi_tiet_lo_hang.
                            // ChiTietLoHangObserver sẽ tự tính SUM lại tổng tồn
                            // trên bien_the_san_pham sau khi ChiTietLoHang thay đổi.
                            ChiTietLoHang::where('id', $d->id_chi_tiet_lo_hang)
                                ->decrement('so_luong_ton', $absLech);
                        }
                    }
                }

                // Tạo 1 phiếu nhập kiểm kê
                if (!empty($lechDuong)) {
                    $phN = Phieu::create([
                        'loai_phieu' => 'Nhập kiểm kê (thừa)',
                        'loai_phieu_enum' => 'nhap_kiem_ke',
                        'id_nguoi_dung' => $userId,
                        'ly_do' => 'Cân bằng từ phiếu kiểm kho ' . $pk->ma_kiem_kho,
                        'ghi_chu' => $pk->ghi_chu,
                    ]);
                    PhieuNhap::create([
                        'id_phieu' => $phN->id,
                        'loai_nhap' => 'kiem_ke',
                    ]);

                    foreach ($lechDuong as $vid => $data) {
                        $variant = BienTheSanPham::with('product')->find($vid);
                        if (!$variant) {
                            continue;
                        }
                        foreach ($data['items'] as $d) {
                            $slDu = (int) $d->so_luong_lech;
                            if ($slDu <= 0) {
                                continue;
                            }
                            ChiTietPhieu::create([
                                'id_phieu' => $phN->id,
                                'id_san_pham' => $variant->product_id,
                                'variant_id' => $vid,
                                'id_lo_hang' => $d->chiTietLoHang?->id_lo_hang,
                                'id_chi_tiet_lo_hang' => $d->id_chi_tiet_lo_hang,
                                'so_luong' => $slDu,
                                'gia_nhap' => (float) $d->gia_von,
                                'han_su_dung' => $d->han_su_dung,
                                'so_luong_con_lai' => $slDu,
                            ]);
                            // Cộng tồn trực tiếp ở chi_tiet_lo_hang.
                            // ChiTietLoHangObserver tự đồng bộ tổng tồn
                            // trên bien_the_san_pham.
                            ChiTietLoHang::where('id', $d->id_chi_tiet_lo_hang)
                                ->increment('so_luong_ton', $slDu);
                        }
                    }
                }

                // Cập nhật phiếu kiểm kho -> hoan_thanh
                $pk->refresh();
                $pk->recomputeTotals();
                $pk->update([
                    'trang_thai' => 'hoan_thanh',
                    'hoan_thanh_luc' => now(),
                ]);

                return $pk;
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cân bằng kho thành công.',
            'data' => $this->serializePhieu($phieuKk->load('chiTietKiemKho')),
        ]);
    }

    /**
     * GET /admin/api/kiem-kho/history?page=1&per_page=15&trang_thai=&q=
     */
    public function history(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $trangThai = $request->query('trang_thai');

        $query = PhieuKiemKho::with('nguoiDung')
            ->orderByDesc('id');

        if (!empty($trangThai)) {
            $query->where('trang_thai', $trangThai);
        }
        if ($q !== '') {
            $query->where('ma_kiem_kho', 'like', "%{$q}%");
        }

        $paginator = $query->paginate((int) $request->query('per_page', 15));

        $items = $paginator->getCollection()->map(function ($p) {
            return [
                'id' => $p->id,
                'ma_kiem_kho' => $p->ma_kiem_kho,
                'trang_thai' => $p->trang_thai,
                'trang_thai_label' => $p->trang_thai_label,
                'trang_thai_badge' => $p->trang_thai_badge,
                'nguoi_tao' => $p->nguoiDung?->ho_ten ?? 'N/A',
                'tong_sl_thuc_te' => (int) $p->tong_sl_thuc_te,
                'tong_sl_lech' => (int) $p->tong_sl_lech,
                'tong_gia_tri_lech' => (float) $p->tong_gia_tri_lech,
                'so_dong' => $p->chiTietKiemKho()->count(),
                'created_at' => $p->created_at?->format('d/m/Y H:i'),
                'hoan_thanh_luc' => $p->hoan_thanh_luc?->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ]);
    }

    /**
     * GET /admin/api/kiem-kho/{id}
     */
    public function show(int $id): JsonResponse
    {
        $phieu = PhieuKiemKho::with(['nguoiDung', 'chiTietKiemKho'])->find($id);
        if (!$phieu) {
            return response()->json(['success' => false, 'message' => 'Phiếu không tồn tại'], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $this->serializePhieu($phieu),
        ]);
    }

    /**
     * POST /admin/api/kiem-kho/{id}/cancel
     * Đổi trạng thái -> da_huy.
     */
    public function cancel(int $id): JsonResponse
    {
        $phieu = PhieuKiemKho::find($id);
        if (!$phieu) {
            return response()->json(['success' => false, 'message' => 'Phiếu không tồn tại'], 404);
        }
        if ($phieu->trang_thai === 'hoan_thanh') {
            return response()->json([
                'success' => false,
                'message' => 'Phiếu đã hoàn thành, không thể hủy. Hãy tạo phiếu cân bằng ngược nếu cần.',
            ], 422);
        }
        $phieu->update(['trang_thai' => 'da_huy']);
        return response()->json(['success' => true, 'message' => 'Đã hủy phiếu.']);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function buildDonViLabel(?BienTheSanPham $variant): string
    {
        if (!$variant) {
            return '';
        }
        if ($variant->ten_bien_the) {
            return $variant->ten_bien_the;
        }
        return 'Mặc định';
    }

    private function serializePhieu(PhieuKiemKho $phieu): array
    {
        return [
            'id' => $phieu->id,
            'ma_kiem_kho' => $phieu->ma_kiem_kho,
            'trang_thai' => $phieu->trang_thai,
            'trang_thai_label' => $phieu->trang_thai_label,
            'trang_thai_badge' => $phieu->trang_thai_badge,
            'ghi_chu' => $phieu->ghi_chu,
            'tong_sl_thuc_te' => (int) $phieu->tong_sl_thuc_te,
            'tong_sl_lech' => (int) $phieu->tong_sl_lech,
            'tong_gia_tri_lech' => (float) $phieu->tong_gia_tri_lech,
            'nguoi_tao' => $phieu->nguoiDung?->ho_ten ?? 'N/A',
            'created_at' => $phieu->created_at?->format('d/m/Y H:i'),
            'hoan_thanh_luc' => $phieu->hoan_thanh_luc?->format('d/m/Y H:i'),
            'items' => $phieu->chiTietKiemKho->map(fn ($d) => [
                'id' => $d->id,
                'id_chi_tiet_lo_hang' => $d->id_chi_tiet_lo_hang,
                'variant_id' => $d->variant_id,
                'ma_vach' => $d->ma_vach,
                'ten_san_pham' => $d->ten_san_pham,
                'ten_bien_the' => $d->ten_bien_the,
                'ten_don_vi' => $d->ten_don_vi,
                'han_su_dung' => $d->han_su_dung?->format('Y-m-d'),
                'ma_lo' => $d->ma_lo,
                'so_luong_ton' => (int) $d->so_luong_ton,
                'so_luong_thuc_te' => $d->so_luong_thuc_te !== null ? (int) $d->so_luong_thuc_te : null,
                'so_luong_lech' => (int) $d->so_luong_lech,
                'gia_von' => (float) $d->gia_von,
                'gia_tri_lech' => (float) $d->gia_tri_lech,
                'da_kiem' => $d->da_kiem,
                'khop' => $d->khop,
            ])->values(),
        ];
    }
}
