<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KiemKho\BulkActionKiemKhoRequest;
use App\Http\Requests\KiemKho\HistoryFilterRequest;
use App\Http\Requests\KiemKho\ImportKiemKhoRequest;
use App\Http\Requests\KiemKho\StoreKiemKhoRequest;
use App\Http\Requests\KiemKho\UpdateKiemKhoRequest;
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
     * POST /admin/api/kiem-kho/import-preview
     * Body: { items: [{ma_vach|ma_hang, so_luong_thuc_te, ten_san_pham, ghi_chu}] }
     * Trả về preview: dòng nào khớp lô tồn, dòng nào bị lỗi (không tìm thấy, hết hàng).
     * KHÔNG ghi vào DB - chỉ để user xem trước khi bấm "Thêm vào phiếu".
     */
    public function importPreview(ImportKiemKhoRequest $request): JsonResponse
    {
        $items = $request->normalizedItems();
        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dòng hợp lệ nào để import. Mỗi dòng cần có ít nhất mã vạch hoặc mã hàng.',
            ], 422);
        }

        $results = $this->resolveImportItems($items);

        $ok = collect($results)->where('status', 'ok')->count();
        $warn = collect($results)->where('status', 'warning')->count();
        $err = collect($results)->where('status', 'error')->count();

        return response()->json([
            'success' => true,
            'data' => $results,
            'summary' => [
                'total' => count($results),
                'ok' => $ok,
                'warning' => $warn,
                'error' => $err,
            ],
        ]);
    }

    /**
     * POST /admin/api/kiem-kho/import-execute
     * Body: { items: [{id_chi_tiet_lo_hang, variant_id, ma_vach, so_luong_thuc_te, ...}] }
     * Lưu các dòng OK vào phiếu kiểm kho tạm.
     * Lưu ý: items đã được resolve qua preview (frontend gửi lại id_chi_tiet_lo_hang).
     */
    public function importExecute(ImportKiemKhoRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $ghiChu = $request->input('ghi_chu');
        $items = $request->items();

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Không có dòng hợp lệ để lưu.'], 422);
        }

        // Check trùng
        $dupError = $this->detectDuplicate($items);
        if ($dupError !== null) {
            return response()->json(['success' => false, 'message' => $dupError], 422);
        }

        try {
            DB::transaction(function () use ($userId, $ghiChu, $items) {
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
                        'ghi_chu' => $ghiChu ?: 'Import từ Excel/CSV',
                    ]);
                }

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
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi import: ' . $e->getMessage()], 500);
        }

        $phieu = PhieuKiemKho::with('chiTietKiemKho')
            ->where('id_nguoi_dung', $userId)
            ->where('trang_thai', 'phieu_tam')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm các dòng OK vào phiếu.',
            'data' => $phieu ? $this->serializePhieu($phieu) : null,
        ]);
    }

    /**
     * Resolve danh sách item nhập vào -> ChiTietLoHang tương ứng.
     * Trả về mảng các dòng kèm status (ok|warning|error) + message.
     *
     * @return array<int, array<string, mixed>>
     */
    private function resolveImportItems(array $items): array
    {
        $results = [];
        // Pre-load các variant có mã trùng
        $maVaches = collect($items)->pluck('ma_vach')->filter()->unique()->values()->all();
        $maHangs = collect($items)->pluck('ma_hang')->filter()->unique()->values()->all();

        $variantByVach = empty($maVaches) ? collect() : BienTheSanPham::whereIn('ma_vach', $maVaches)->get()->keyBy('ma_vach');
        $variantByHang = empty($maHangs) ? collect() : BienTheSanPham::whereIn('ma_hang', $maHangs)->get()->keyBy('ma_hang');
        $donViByVach = empty($maVaches) ? collect() : DonViQuyDoi::whereIn('ma_vach', $maVaches)->get()->keyBy('ma_vach');

        foreach ($items as $it) {
            $row = $it;
            $variant = null;

            if (!empty($it['ma_vach'])) {
                $variant = $variantByVach[$it['ma_vach']] ?? null;
                if (!$variant) {
                    $donVi = $donViByVach[$it['ma_vach']] ?? null;
                    if ($donVi) {
                        $variant = BienTheSanPham::find($donVi->variant_id);
                    }
                }
            }
            if (!$variant && !empty($it['ma_hang'])) {
                $variant = $variantByHang[$it['ma_hang']] ?? null;
            }

            if (!$variant) {
                $results[] = array_merge($row, [
                    'status' => 'error',
                    'message' => 'Không tìm thấy sản phẩm với mã: ' . ($it['ma_vach'] ?? $it['ma_hang'] ?? ''),
                ]);
                continue;
            }

            $ctlo = ChiTietLoHang::with('loHang')
                ->where('variant_id', $variant->id)
                ->where('so_luong_ton', '>', 0)
                ->orderBy('han_su_dung', 'asc')
                ->first();

            if (!$ctlo) {
                $results[] = array_merge($row, [
                    'status' => 'error',
                    'message' => "Sản phẩm \"{$variant->ten_bien_the}\" hiện không còn tồn kho.",
                    'variant_id' => $variant->id,
                    'ma_vach' => $variant->ma_vach,
                ]);
                continue;
            }

            $ton = (int) $ctlo->so_luong_ton;
            $sl = $it['so_luong_thuc_te'];
            $status = 'ok';
            $message = 'Khớp lô tồn.';
            if ($sl !== null && $sl > $ton) {
                $status = 'warning';
                $message = "SL thực tế ({$sl}) lớn hơn tồn kho ({$ton}).";
            }

            $results[] = array_merge($row, [
                'status' => $status,
                'message' => $message,
                'variant_id' => $variant->id,
                'id_chi_tiet_lo_hang' => $ctlo->id,
                'ma_vach' => $variant->ma_vach,
                'ten_san_pham' => $ctlo->variant?->product?->ten_san_pham ?? '',
                'ten_bien_the' => $variant->ten_bien_the,
                'ten_don_vi' => $this->buildDonViLabel($variant),
                'han_su_dung' => $ctlo->han_su_dung?->format('Y-m-d'),
                'ma_lo' => $ctlo->loHang?->ma_lo,
                'so_luong_ton' => $ton,
                'gia_von' => (float) $ctlo->gia_nhap,
            ]);
        }

        return $results;
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
    public function storeDraft(StoreKiemKhoRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $ghiChu = $request->input('ghi_chu');
        $items = $request->items();

        // Kiểm tra trùng lặp variant_id + id_chi_tiet_lo_hang
        $dupError = $this->detectDuplicate($items);
        if ($dupError !== null) {
            return response()->json(['success' => false, 'message' => $dupError], 422);
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
     * PUT /admin/api/kiem-kho/{id}
     * Cập nhật phiếu tạm đã có. Validate bằng UpdateKiemKhoRequest.
     * Chỉ phiếu 'phieu_tam' mới cho sửa.
     */
    public function updateDraft(UpdateKiemKhoRequest $request, int $id): JsonResponse
    {
        $userId = Auth::id();
        $items = $request->items();

        $dupError = $this->detectDuplicate($items);
        if ($dupError !== null) {
            return response()->json(['success' => false, 'message' => $dupError], 422);
        }

        try {
            DB::transaction(function () use ($id, $userId, $request, $items) {
                $phieu = PhieuKiemKho::where('id', $id)->lockForUpdate()->first();
                if (!$phieu) {
                    throw new \Exception('Phiếu kiểm kho không tồn tại.');
                }
                if ($phieu->trang_thai !== 'phieu_tam') {
                    throw new \Exception('Chỉ được sửa phiếu ở trạng thái "Phiếu tạm".');
                }
                if ($phieu->id_nguoi_dung !== $userId) {
                    throw new \Exception('Bạn không có quyền sửa phiếu này.');
                }

                $phieu->update(['ghi_chu' => $request->input('ghi_chu')]);

                ChiTietKiemKho::where('id_phieu_kiem_kho', $phieu->id)->delete();

                foreach ($items as $it) {
                    $ctlo = ChiTietLoHang::with(['variant.product', 'loHang'])->find($it['id_chi_tiet_lo_hang']);
                    if (!$ctlo) {
                        continue;
                    }
                    $variant = $ctlo->variant;
                    $product = $variant?->product;

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
                        'so_luong_thuc_te' => $it['so_luong_thuc_te'],
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
                'message' => $e->getMessage(),
            ], 422);
        }

        $phieu = PhieuKiemKho::with('chiTietKiemKho')->find($id);
        return response()->json([
            'success' => true,
            'data' => $phieu ? $this->serializePhieu($phieu) : null,
        ]);
    }

    /**
     * Phát hiện dòng trùng lặp theo cặp (variant_id + id_chi_tiet_lo_hang).
     * Trả về null nếu OK, ngược lại trả về thông báo lỗi.
     */
    private function detectDuplicate(array $items): ?string
    {
        $seen = [];
        foreach ($items as $idx => $it) {
            $key = $it['variant_id'] . '|' . $it['id_chi_tiet_lo_hang'];
            if (isset($seen[$key])) {
                $ten = $it['ten_san_pham'] ?? ('Dòng ' . ($idx + 1));
                return "Sản phẩm \"{$ten}\" bị trùng lô trong phiếu. Mỗi lô chỉ được kiểm 1 lần.";
            }
            $seen[$key] = true;
        }
        return null;
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
     * GET /admin/api/kiem-kho/history?page=1&per_page=15&trang_thai=&q=&thoi_gian=&tu_ngay=&den_ngay=
     */
    public function history(HistoryFilterRequest $request): JsonResponse
    {
        try {
            $q = trim((string) $request->query('q', ''));
            $trangThai = $request->query('trang_thai');
            [$tuNgay, $denNgay] = $request->resolvedDateRange();

            $query = PhieuKiemKho::with('nguoiDung')
                ->orderByDesc('id');

            if (!empty($trangThai)) {
                $query->where('trang_thai', $trangThai);
            }
            if ($q !== '') {
                $query->where('ma_kiem_kho', 'like', "%{$q}%");
            }
            if ($tuNgay) {
                $query->whereDate('created_at', '>=', $tuNgay);
            }
            if ($denNgay) {
                $query->whereDate('created_at', '<=', $denNgay);
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
        } catch (\Throwable $e) {
            \Log::error('LichSuKiemKho error', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /admin/api/kiem-kho/trash
     * Liệt kê các phiếu đã bị soft delete (Thùng rác).
     */
    public function trash(Request $request): JsonResponse
    {
        try {
            $q = trim((string) $request->query('q', ''));

            $query = PhieuKiemKho::onlyTrashed()
                ->with('nguoiDung')
                ->orderByDesc('deleted_at');

            if ($q !== '') {
                $query->where('ma_kiem_kho', 'like', "%{$q}%");
            }

            $paginator = $query->paginate((int) $request->query('per_page', 15));

            $items = $paginator->getCollection()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'ma_kiem_kho' => $p->ma_kiem_kho,
                    'trang_thai' => $p->trang_thai,
                    'nguoi_tao' => $p->nguoiDung?->ho_ten ?? 'N/A',
                    'tong_sl_thuc_te' => (int) $p->tong_sl_thuc_te,
                    'tong_sl_lech' => (int) $p->tong_sl_lech,
                    'tong_gia_tri_lech' => (float) $p->tong_gia_tri_lech,
                    'so_dong' => $p->chiTietKiemKho()->count(),
                    'created_at' => $p->created_at?->format('d/m/Y H:i'),
                    'deleted_at' => $p->deleted_at?->format('d/m/Y H:i'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('KiemKhoTrash error', ['msg' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /admin/api/kiem-kho/{id}/restore
     * Khôi phục phiếu đã bị soft delete.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $phieu = PhieuKiemKho::onlyTrashed()->find($id);
            if (!$phieu) {
                return response()->json(['success' => false, 'message' => 'Phiếu không tồn tại trong thùng rác.'], 404);
            }
            $phieu->restore();
            return response()->json(['success' => true, 'message' => 'Đã khôi phục phiếu.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /admin/api/kiem-kho/{id}
     * Soft delete 1 phiếu (chuyển vào thùng rác).
     * Chỉ cho xóa khi phiếu chưa hoàn thành (đã cân bằng kho) - vì lúc đó đã phát sinh phiếu nhập/xuất.
     */
    public function softDelete(int $id): JsonResponse
    {
        try {
            $phieu = PhieuKiemKho::find($id);
            if (!$phieu) {
                return response()->json(['success' => false, 'message' => 'Phiếu không tồn tại.'], 404);
            }
            if ($phieu->trang_thai === 'hoan_thanh') {
                return response()->json([
                    'success' => false,
                    'message' => 'Phiếu đã hoàn thành (đã cân bằng kho), không thể xóa. Hãy dùng chức năng "Hủy" hoặc tạo phiếu bù.',
                ], 422);
            }
            $phieu->delete();
            return response()->json(['success' => true, 'message' => 'Đã chuyển phiếu vào thùng rác.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /admin/api/kiem-kho/{id}/force
     * Xóa vĩnh viễn (kèm các chi tiết).
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $phieu = PhieuKiemKho::onlyTrashed()->where('id', $id)->first();
                if (!$phieu) {
                    // Có thể chưa bị xóa mềm -> vẫn cho xóa cứng
                    $phieu = PhieuKiemKho::find($id);
                }
                if (!$phieu) {
                    throw new \Exception('Phiếu không tồn tại.');
                }
                ChiTietKiemKho::where('id_phieu_kiem_kho', $phieu->id)->forceDelete();
                $phieu->forceDelete();
            });
            return response()->json(['success' => true, 'message' => 'Đã xóa vĩnh viễn phiếu.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /admin/api/kiem-kho/bulk-action
     * Body: { action: 'delete'|'restore'|'force_delete'|'cancel', ids: [int,...] }
     */
    public function bulkAction(BulkActionKiemKhoRequest $request): JsonResponse
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);
        $count = 0;
        $errors = [];

        try {
            DB::transaction(function () use ($action, $ids, &$count, &$errors) {
                foreach ($ids as $id) {
                    $id = (int) $id;
                    try {
                        switch ($action) {
                            case 'delete':
                                $p = PhieuKiemKho::find($id);
                                if (!$p) { $errors[] = "ID $id: không tồn tại."; break; }
                                if ($p->trang_thai === 'hoan_thanh') { $errors[] = "$p->ma_kiem_kho: đã hoàn thành, không thể xóa."; break; }
                                $p->delete();
                                break;
                            case 'restore':
                                $p = PhieuKiemKho::onlyTrashed()->find($id);
                                if (!$p) { $errors[] = "ID $id: không có trong thùng rác."; break; }
                                $p->restore();
                                break;
                            case 'force_delete':
                                $p = PhieuKiemKho::withTrashed()->find($id);
                                if (!$p) { $errors[] = "ID $id: không tồn tại."; break; }
                                ChiTietKiemKho::where('id_phieu_kiem_kho', $p->id)->forceDelete();
                                $p->forceDelete();
                                break;
                            case 'cancel':
                                $p = PhieuKiemKho::find($id);
                                if (!$p) { $errors[] = "ID $id: không tồn tại."; break; }
                                if ($p->trang_thai === 'hoan_thanh') { $errors[] = "$p->ma_kiem_kho: đã hoàn thành."; break; }
                                $p->update(['trang_thai' => 'da_huy']);
                                break;
                        }
                        $count++;
                    } catch (\Throwable $e) {
                        $errors[] = "ID $id: " . $e->getMessage();
                    }
                }
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã xử lý {$count}/" . count($ids) . " phiếu.",
            'processed' => $count,
            'errors' => $errors,
        ]);
    }

    /**
     * GET /admin/api/kiem-kho/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $phieu = PhieuKiemKho::with(['nguoiDung', 'chiTietKiemKho'])->find($id);
            if (!$phieu) {
                return response()->json(['success' => false, 'message' => 'Phiếu không tồn tại'], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $this->serializePhieu($phieu),
            ]);
        } catch (\Throwable $e) {
            \Log::error('KiemKhoShow error', [
                'id' => $id,
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
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
