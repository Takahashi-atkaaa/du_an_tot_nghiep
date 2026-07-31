<?php

namespace App\Http\Controllers\admin\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMucSanPham;
use App\Models\Product;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SanPhamApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $danhMuc = $request->query('danh_muc');
        $trangThai = $request->query('trang_thai');

        // Lay tat ca bien the phu hop
        $query = BienTheSanPham::with(['product.danhMuc', 'units'])
            ->whereHas('product', fn($p) => $p->whereNull('deleted_at'))
            ->when($q, fn($w) => $w
                ->whereHas('product', fn($inner) => $inner
                    ->whereRaw('LOWER(ten_san_pham) LIKE ?', ["%".mb_strtolower($q)."%"]))
                ->orWhereRaw('LOWER(ten_bien_the) LIKE ?', ["%".mb_strtolower($q)."%"])
                ->orWhereRaw('LOWER(ma_vach) LIKE ?', ["%".mb_strtolower($q)."%"])
                ->orWhereRaw('LOWER(ma_hang) LIKE ?', ["%".mb_strtolower($q)."%"])
            )
            ->when($danhMuc, fn($w) => $w->whereHas('product', fn($i) => $i->where('id_danh_muc', $danhMuc)))
            ->when(!is_null($trangThai) && $trangThai !== '', fn($w) => $w->where('trang_thai', $trangThai))
            ->orderBy('product_id')
            ->orderBy('ten_bien_the');

        $items = $query->limit(50)->get();

        // Nhom theo product_id, tra ve cau truc nested cho JS searchProductsNhap()
        $grouped = $items->groupBy('product_id');

        $dataArray = [];
        foreach ($grouped as $productId => $variants) {
            $product = $variants->first()->product;

            // CHỈ trả variant (BienTheSanPham), KHÔNG nhét DonViQuyDoi (đơn vị quy đổi)
            // vào bien_the[] nữa — vì frontend (kho-hang.js) coi bien_the[] là variants.
            // Nếu trộn unit vào, data-id của button "Chọn" sẽ là unit_id (DonViQuyDoi.id)
            // không phải variant_id (BienTheSanPham.id) → submit sai variant_id → khi xem
            // phiếu nhập, eager load `variant.product` với variant_id sai sẽ trả về variant
            // ngẫu nhiên có cùng ID (ví dụ "Áo thun") thay vì sản phẩm user chọn ("Nước ép táo").
            $bienTheArray = [];
            foreach ($variants as $variant) {
                $thuocTinhs = $variant->thuocTinhs;
                $tonKho = $variant->chiTietLoHangTon->sum('so_luong_ton');

                $bienTheArray[] = [
                    'id' => $variant->id,
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'type' => 'variant',
                    'ten_bien_the' => $variant->ten_bien_the,
                    'ma_hang' => $variant->ma_hang,
                    'ma_vach' => $variant->ma_vach,
                    'gia_von' => (float) $variant->gia_von,
                    'gia_ban' => (float) $variant->gia_ban,
                    'so_luong_ton' => $variant->so_luong_ton,
                    'chi_tiet_lo_hang_ton' => $tonKho,
                    'thuoc_tinh_labels' => $thuocTinhs->pluck('ten_thuoc_tinh')->toArray(),
                    'thuoc_tinh_ids' => $variant->thuoc_tinh_ids,
                    'hinh_anh' => $variant->hinh_anh,
                    'trang_thai' => $variant->trang_thai,
                    'units' => $variant->units->map(fn($u) => [
                        'id' => $u->id,
                        'ten_don_vi' => $u->ten_don_vi,
                        'so_luong_san_pham_trong_don_vi' => $u->so_luong_san_pham_trong_don_vi,
                        'gia_von_quy_doi' => $u->gia_von_quy_doi,
                        'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                        'ma_hang' => $u->ma_hang,
                        'ma_vach' => $u->ma_vach,
                    ])->all(),
                ];
            }

            $dataArray[] = [
                'id' => $product->id,
                'product_id' => $product->id,
                'ten_san_pham' => $product->ten_san_pham,
                'danh_muc' => $product->danhMuc ? [
                    'id' => $product->danhMuc->id,
                    'ten_danh_muc' => $product->danhMuc->ten_danh_muc,
                ] : null,
                'hinh_anh' => $variants->first()->hinh_anh,
                'bien_the' => $bienTheArray,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $dataArray,
            'danh_muc_list' => DanhMucSanPham::orderBy('ten_danh_muc')->get(['id', 'ten_danh_muc'])->toArray(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $requestedVariantId = request()->query('variant_id');
        $requestedUnitId = request()->query('unit_id');
        $isMaster = request()->query('is_master') === '1';
        \Log::info('[SanPhamApi show] id=' . $id . ' variant_id=' . ($requestedVariantId ?? 'null') . ' unit_id=' . ($requestedUnitId ?? 'null') . ' is_master=' . ($isMaster ? '1' : '0'));

        $product = Product::with([
            'variants' => fn($q) => $q->with('units')->orderBy('id'),
            'danhMuc',
        ])->find($id);

        $variant = null;
        $selectedUnit = null;

        // Ưu tiên unit_id (đơn vị quy đổi): tìm variant sở hữu unit này
        if ($requestedUnitId && $product) {
            foreach ($product->variants as $v) {
                $foundUnit = $v->units->first(fn($u) => (string)$u->id === (string)$requestedUnitId);
                if ($foundUnit) {
                    $variant = $v;
                    $selectedUnit = $foundUnit;
                    break;
                }
            }
        }

        // Ưu tiên variant_id từ query (click dòng con → hiển thị variant cụ thể)
        if (!$variant && $requestedVariantId && $product) {
            $variant = $product->variants->first(fn($v) => (string)$v->id === (string)$requestedVariantId);
        }

        // Fallback: click dòng cha → lấy variant đầu tiên
        if (!$variant && $product && $product->variants->isNotEmpty()) {
            $variant = $product->variants->first();
        }

        // Fallback cuối: $id chính là variant ID
        if (!$variant) {
            $variant = BienTheSanPham::with([
                'product.danhMuc',
                'product.variants.units',
                'units',
            ])->find($id);
        }

        if (!$variant || !$variant->product) {
            return response()->json(['success' => false, 'message' => 'San pham khong ton tai.'], 404);
        }

        $theKho = DB::table('phieu')
            ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
            ->where('chi_tiet_phieu.variant_id', $variant->id)
            ->select(
                DB::raw("CONCAT('PN-', phieu.id) as maPhieu"),
                'phieu.created_at as thoiGian',
                'phieu.loai_phieu as loaiPhieu',
                'chi_tiet_phieu.gia_nhap as gia',
                'chi_tiet_phieu.so_luong as soLuong',
                'chi_tiet_phieu.so_luong_con_lai as soLuongConLai',
                'chi_tiet_phieu.ma_lo as maLo',
                'chi_tiet_phieu.han_su_dung as hanSuDung'
            )
            ->orderByDesc('phieu.created_at')
            ->limit(20)
            ->get();

        $loHang = DB::table('chi_tiet_lo_hang as ct')
            ->join('lo_hang as lh', 'lh.id', '=', 'ct.id_lo_hang')
            ->where('ct.variant_id', $variant->id)
            ->where('ct.so_luong_ton', '>', 0)
            ->orderBy('ct.han_su_dung', 'asc')
            ->select(
                'ct.id as idChiTietLo',
                'lh.ma_lo as maLo',
                'ct.han_su_dung as hanSuDung',
                'ct.so_luong_nhap as so_luong',
                'ct.so_luong_ton as soLuongConLai',
                'ct.gia_nhap as giaNhap'
            )
            ->get();

        // Ensure product always has danhMuc loaded
        if (!$variant->product->relationLoaded('danhMuc')) {
            $variant->product->load('danhMuc');
        }

        // Tính toán thông tin tổng hợp cho Master Product (nhiều biến thể)
        $allVariantsData = $variant->product->variants;
        $tongTonKho = $allVariantsData->sum('so_luong_ton');
        $giaVonMin = $allVariantsData->min('gia_von');
        $giaVonMax = $allVariantsData->max('gia_von');
        $giaBanMin = $allVariantsData->min('gia_ban');
        $giaBanMax = $allVariantsData->max('gia_ban');
        $hasMultipleVariants = $allVariantsData->count() > 1;

        // Tạo summary cho Master Product
        $masterSummary = null;
        if ($hasMultipleVariants) {
            $masterSummary = [
                'tong_ton_kho' => $tongTonKho,
                'gia_von_min' => $giaVonMin,
                'gia_von_max' => $giaVonMax,
                'gia_ban_min' => $giaBanMin,
                'gia_ban_max' => $giaBanMax,
                'so_bien_the' => $allVariantsData->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $variant->product->toArray(),
                'variant' => $variant->toArray(),
                'selectedUnit' => $selectedUnit?->toArray(),
                'allVariants' => $variant->product->variants->toArray(),
                'units' => $variant->units->toArray(),
                'theKho' => $theKho,
                'loHang' => $loHang,
                'masterSummary' => $masterSummary,
                'hasMultipleVariants' => $hasMultipleVariants,
                'isMaster' => $isMaster,
            ],
        ]);
    }

    public function destroyVariant(int $id): JsonResponse
    {
        $variant = BienTheSanPham::find($id);

        if (!$variant) {
            return response()->json(['success' => false, 'message' => 'Bien the khong ton tai.'], 404);
        }

        foreach ($variant->units as $unit) {
            if ($unit->hinh_anh && !str_starts_with($unit->hinh_anh, 'http')) {
                $this->deleteImageIfUnused($unit->hinh_anh);
            }
            $unit->delete();
        }

        if ($variant->hinh_anh && !str_starts_with($variant->hinh_anh, 'http')) {
            $this->deleteImageIfUnused($variant->hinh_anh);
        }

        $variant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Da xoa bien the.',
        ]);
    }

    public function destroyAllVariants(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'San pham khong ton tai.'], 404);
        }

        $variants = $product->variants;

        foreach ($variants as $variant) {
            foreach ($variant->units as $unit) {
                if ($unit->hinh_anh && !str_starts_with($unit->hinh_anh, 'http')) {
                    $this->deleteImageIfUnused($unit->hinh_anh);
                }
                $unit->delete();
            }
            if ($variant->hinh_anh && !str_starts_with($variant->hinh_anh, 'http')) {
                $this->deleteImageIfUnused($variant->hinh_anh);
            }
            $variant->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Da xoa ' . $variants->count() . ' bien the.',
        ]);
    }

    private function deleteImageIfUnused(string $path): void
    {
        $existsInVariant = BienTheSanPham::where('hinh_anh', $path)->exists();
        $existsInUnit = DonViQuyDoi::where('hinh_anh', $path)->exists();
        if (!$existsInVariant && !$existsInUnit) {
            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
