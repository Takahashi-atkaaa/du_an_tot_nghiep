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

        // Flat list theo kien truc POS chuan:
        // - Variant co units > 0: hien thi don vi co ban (bien_the) + TAT CA don vi quy doi (don_vi_quy_doi)
        // - Variant co units == 0: hien thi variant binh thuong
        // - MOI row deu co 'product_id' de click chi tiet dung san_pham
        $dataArray = [];
        foreach ($items as $variant) {
            $thuocTinhs = $variant->thuocTinhs();
            $tonKho = $variant->chiTietLoHangTon->sum('so_luong_ton');
            $units = $variant->units;
            $product = $variant->product;

            $tenSanPham = $product->ten_san_pham ?? '';
            $tenBienThe = $variant->ten_bien_the ?? '';
            $tenHienThiGoc = $tenBienThe ? ($tenSanPham . ' - ' . $tenBienThe) : $tenSanPham;

            if ($units->count() > 0) {
                // 1. Don vi co ban: bien_the chinh la don vi co ban
                $dataArray[] = [
                    'id' => $variant->id,
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'type' => 'unit',
                    'ten_san_pham' => $tenSanPham,
                    'ten_bien_the' => $variant->ten_bien_the,
                    'ten_hien_thi' => $tenSanPham . ' - ' . ($variant->ten_bien_the ?: 'Đơn vị tiêu chuẩn'),
                    'danh_muc' => $product->danhMuc->ten_danh_muc ?? '',
                    'ma_hang' => $variant->ma_hang,
                    'ma_vach' => $variant->ma_vach,
                    'gia_von' => $variant->gia_von,
                    'gia_ban' => $variant->gia_ban,
                    'so_luong_ton' => $variant->so_luong_ton,
                    'ton_kho_hien_thi' => $variant->so_luong_ton,
                    'chi_tiet_lo_hang_ton' => $tonKho,
                    'thuoc_tinh_labels' => $thuocTinhs->pluck('ten_thuoc_tinh')->toArray(),
                    'hinh_anh' => $variant->hinh_anh,
                    'trang_thai' => $variant->trang_thai,
                ];

                // 2. Don vi quy doi: tung dong trong don_vi_quy_doi
                foreach ($units as $unit) {
                    $dataArray[] = [
                        'id' => $unit->id,
                        'variant_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'type' => 'unit',
                        'ten_san_pham' => $tenSanPham,
                        'ten_bien_the' => $unit->ten_don_vi,
                        'ten_hien_thi' => $tenSanPham . ' - ' . $unit->ten_don_vi,
                        'danh_muc' => '',
                        'ma_hang' => $unit->ma_hang,
                        'ma_vach' => $unit->ma_vach,
                        'gia_von' => $unit->gia_von_quy_doi,
                        'gia_ban' => $unit->gia_ban_quy_doi,
                        'gia_ban_si' => $unit->gia_ban_si,
                        'ty_le_quy_doi' => $unit->ty_le_quy_doi,
                        'so_luong_ton' => $variant->so_luong_ton,
                        'ton_kho_hien_thi' => (int)floor($variant->so_luong_ton / $unit->ty_le_quy_doi),
                        'chi_tiet_lo_hang_ton' => $tonKho,
                        'thuoc_tinh_labels' => $thuocTinhs->pluck('ten_thuoc_tinh')->toArray(),
                        'hinh_anh' => $unit->hinh_anh ?: $variant->hinh_anh,
                        'trang_thai' => $variant->trang_thai,
                    ];
                }
            } else {
                // Khong co don vi quy doi -> hien thi variant binh thuong
                $dataArray[] = [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'type' => 'bien_the',
                    'ten_san_pham' => $tenSanPham,
                    'ten_bien_the' => $tenBienThe,
                    'ten_hien_thi' => $tenHienThiGoc,
                    'danh_muc' => $product->danhMuc->ten_danh_muc ?? '',
                    'ma_hang' => $variant->ma_hang,
                    'ma_vach' => $variant->ma_vach,
                    'gia_von' => $variant->gia_von,
                    'gia_ban' => $variant->gia_ban,
                    'so_luong_ton' => $variant->so_luong_ton,
                    'ton_kho_hien_thi' => $variant->so_luong_ton,
                    'chi_tiet_lo_hang_ton' => $tonKho,
                    'thuoc_tinh_labels' => $thuocTinhs->pluck('ten_thuoc_tinh')->toArray(),
                    'thuoc_tinh_ids' => $variant->thuoc_tinh_ids,
                    'hinh_anh' => $variant->hinh_anh,
                    'trang_thai' => $variant->trang_thai,
                ];
            }
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
        \Log::info('[SanPhamApi show] id=' . $id . ' variant_id=' . ($requestedVariantId ?? 'null'));

        $product = Product::with([
            'variants' => fn($q) => $q->with('units')->orderBy('id'),
            'danhMuc',
        ])->find($id);

        $variant = null;

        // Ưu tiên variant_id từ query (click dòng con → hiển thị variant cụ thể)
        if ($requestedVariantId && $product) {
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

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $variant->product->toArray(),
                'variant' => $variant->toArray(),
                'allVariants' => $variant->product->variants->toArray(),
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
