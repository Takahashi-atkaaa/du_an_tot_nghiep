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
        $includeVariants = $request->boolean('include_variants', false);

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

        // Flat list
        $dataArray = [];
        foreach ($items as $variant) {
            $thuocTinhs = $variant->thuocTinhs();
            $tonKho = $variant->chiTietLoHangTon->sum('so_luong_ton');

            $unitRows = $variant->units->map(fn($u) => [
                'id' => $u->id,
                'ten_don_vi' => $u->ten_don_vi,
                'ty_le_quy_doi' => $u->ty_le_quy_doi,
                'ma_hang' => $u->ma_hang,
                'ma_vach' => $u->ma_vach,
                'gia_von_quy_doi' => $u->gia_von_quy_doi,
                'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                'gia_ban_si' => $u->gia_ban_si,
                'hinh_anh' => $u->hinh_anh,
            ])->toArray();

            $baseRow = [
                'id' => $variant->id,
                'product_id' => $variant->product_id,
                'type' => 'variant',
                'ten_san_pham' => $variant->product->ten_san_pham ?? '',
                'ten_bien_the' => $variant->ten_bien_the,
                'ten_hien_thi' => $variant->ten_hien_thi,
                'danh_muc' => $variant->product->danhMuc->ten_danh_muc ?? '',
                'ma_hang' => $variant->ma_hang,
                'ma_vach' => $variant->ma_vach,
                'gia_von' => $variant->gia_von,
                'gia_ban' => $variant->gia_ban,
                'so_luong_ton' => $variant->so_luong_ton,
                'chi_tiet_lo_hang_ton' => $tonKho,
                'thuoc_tinh_labels' => $thuocTinhs->pluck('ten_thuoc_tinh')->toArray(),
                'thuoc_tinh_ids' => $variant->thuoc_tinh_ids,
                'hinh_anh' => $variant->hinh_anh,
                'trang_thai' => $variant->trang_thai,
                'units' => $unitRows,
            ];
            $dataArray[] = $baseRow;
        }

        return response()->json([
            'success' => true,
            'data' => $dataArray,
            'danh_muc_list' => DanhMucSanPham::orderBy('ten_danh_muc')->get(['id', 'ten_danh_muc'])->toArray(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $variant = BienTheSanPham::with(['product.danhMuc', 'product', 'units'])->find($id);

        if (!$variant) {
            return response()->json(['success' => false, 'message' => 'Biến thể không tồn tại.'], 404);
        }

        $theKho = DB::table('phieu')
            ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
            ->where('chi_tiet_phieu.variant_id', $id)
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
            ->where('ct.variant_id', $id)
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

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $variant->product->toArray(),
                'variant' => $variant->toArray(),
                'units' => $variant->units->toArray(),
                'theKho' => $theKho,
                'loHang' => $loHang,
            ],
        ]);
    }

    public function destroyVariant(int $id): JsonResponse
    {
        $variant = BienTheSanPham::find($id);

        if (!$variant) {
            return response()->json(['success' => false, 'message' => 'Biến thể không tồn tại.'], 404);
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
            'message' => 'Đã xóa biến thể.',
        ]);
    }

    public function destroyAllVariants(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
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
            'message' => 'Đã xóa ' . $variants->count() . ' biến thể.',
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
