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
                        'ty_le_quy_doi' => $u->so_luong_san_pham_trong_don_vi,
                        'gia_von_quy_doi' => $u->gia_von_quy_doi,
                        'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                        'gia_ban_si' => $u->gia_ban_si,
                        'ma_hang' => $u->ma_hang,
                        'ma_vach' => $u->ma_vach,
                        'hinh_anh' => $u->hinh_anh,
                        'so_luong_ton' => $u->so_luong_ton,
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

    public function thongKe(int $id): JsonResponse
    {
        $product = Product::with(['danhMuc', 'variants'])->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $days = (int) request()->query('days', 30);
        $days = $days > 0 ? min($days, 365) : 30;
        $to = now()->endOfDay();
        $from = now()->subDays($days - 1)->startOfDay();

        $baseQuery = DB::table('chi_tiet_hoa_don as cth')
            ->join('hoa_don as hd', 'cth.id_hoa_don', '=', 'hd.id')
            ->where('cth.id_san_pham', $product->id)
            ->where('hd.trang_thai', 'Hoàn thành');

        $summary = (clone $baseQuery)
            ->whereBetween('hd.created_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(cth.so_luong), 0) as total_quantity')
            ->selectRaw('COALESCE(SUM(cth.thanh_tien), 0) as total_revenue')
            ->selectRaw('COUNT(DISTINCT cth.id_hoa_don) as total_orders')
            ->first();

        $dailySales = (clone $baseQuery)
            ->whereBetween('hd.created_at', [$from, $to])
            ->selectRaw('DATE(hd.created_at) as date')
            ->selectRaw('COALESCE(SUM(cth.so_luong), 0) as quantity')
            ->selectRaw('COALESCE(SUM(cth.thanh_tien), 0) as revenue')
            ->groupByRaw('DATE(hd.created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $salesByDay = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i)->toDateString();
            $row = $dailySales->get($date);
            $salesByDay[] = [
                'date' => $date,
                'quantity' => (int) ($row->quantity ?? 0),
                'revenue' => (float) ($row->revenue ?? 0),
            ];
        }

        $topVariants = (clone $baseQuery)
            ->leftJoin('bien_the_san_pham as v', 'cth.id_chi_tiet_phieu', '=', 'v.id')
            ->whereBetween('hd.created_at', [$from, $to])
            ->selectRaw('COALESCE(cth.id_chi_tiet_phieu, 0) as variant_id')
            ->selectRaw('COALESCE(v.ten_bien_the, v.ten_don_vi, ?) as variant_name', [$product->ten_san_pham])
            ->selectRaw('COALESCE(SUM(cth.so_luong), 0) as quantity')
            ->selectRaw('COALESCE(SUM(cth.thanh_tien), 0) as revenue')
            ->groupBy('cth.id_chi_tiet_phieu', 'variant_name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'variant_id' => $row->variant_id,
                'variant_name' => $row->variant_name,
                'quantity' => (int) $row->quantity,
                'revenue' => (float) $row->revenue,
            ])->values()->all();

        $recentOrders = DB::table('chi_tiet_hoa_don as cth')
            ->join('hoa_don as hd', 'cth.id_hoa_don', '=', 'hd.id')
            ->leftJoin('khach_hang as kh', 'hd.id_khach_hang', '=', 'kh.id')
            ->where('cth.id_san_pham', $product->id)
            ->where('hd.trang_thai', 'Hoàn thành')
            ->whereBetween('hd.created_at', [$from, $to])
            ->selectRaw('cth.id_hoa_don as order_id')
            ->selectRaw("CONCAT('#', hd.id) as ma_hoa_don")
            ->selectRaw('hd.created_at as order_date')
            ->selectRaw('cth.so_luong as quantity')
            ->selectRaw('cth.thanh_tien as revenue')
            ->selectRaw('kh.ten_khach_hang as customer_name')
            ->orderByDesc('hd.created_at')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'order_id' => $row->order_id,
                'ma_hoa_don' => $row->ma_hoa_don,
                'order_date' => $row->order_date,
                'quantity' => (int) $row->quantity,
                'revenue' => (float) $row->revenue,
                'customer_name' => $row->customer_name,
            ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'ten_san_pham' => $product->ten_san_pham,
                    'danh_muc' => $product->danhMuc ? [
                        'id' => $product->danhMuc->id,
                        'ten_danh_muc' => $product->danhMuc->ten_danh_muc,
                    ] : null,
                    'tong_ton_kho' => $product->variants->sum('so_luong_ton'),
                    'bien_the_count' => $product->variants->count(),
                ],
                'summary' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'days' => $days,
                    'total_orders' => (int) $summary->total_orders,
                    'total_quantity' => (int) $summary->total_quantity,
                    'total_revenue' => (float) $summary->total_revenue,
                    'average_price' => $summary->total_quantity > 0
                        ? round($summary->total_revenue / $summary->total_quantity, 2)
                        : 0,
                ],
                'sales_by_day' => $salesByDay,
                'top_variants' => $topVariants,
                'recent_orders' => $recentOrders,
            ],
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
            ->leftJoin('lo_hang', 'lo_hang.id', '=', 'chi_tiet_phieu.id_lo_hang')
            ->where('chi_tiet_phieu.variant_id', $variant->id)
            ->select(
                DB::raw("CONCAT('PN-', phieu.id) as maPhieu"),
                'phieu.created_at as thoiGian',
                'phieu.loai_phieu as loaiPhieu',
                'chi_tiet_phieu.gia_nhap as gia',
                'chi_tiet_phieu.so_luong as soLuong',
                'chi_tiet_phieu.so_luong_con_lai as soLuongConLai',
                'chi_tiet_phieu.ma_lo as maLoChiTietPhieu',
                'lo_hang.ma_lo as maLoLoHang',
                'chi_tiet_phieu.id_lo_hang as idLoHang',
                'chi_tiet_phieu.han_su_dung as hanSuDung'
            )
            ->orderByDesc('phieu.created_at')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                // Ưu tiên chi_tiet_phieu.ma_lo, fallback sang lo_hang.ma_lo hoặc L-{id}
                $item->maLo = $item->maLoChiTietPhieu ?: $item->maLoLoHang ?: ($item->idLoHang ? 'L-' . $item->idLoHang : '-');
                unset($item->maLoChiTietPhieu, $item->maLoLoHang, $item->idLoHang);
                return $item;
            });

        $loHang = DB::table('chi_tiet_lo_hang as ct')
            ->join('lo_hang as lh', 'lh.id', '=', 'ct.id_lo_hang')
            ->where('ct.variant_id', $variant->id)
            ->where('ct.so_luong_ton', '>', 0)
            ->orderBy('ct.han_su_dung', 'asc')
            ->select(
                'ct.id as idChiTietLo',
                'ct.id_lo_hang as idLoHang',
                'lh.ma_lo as maLo',
                'ct.han_su_dung as hanSuDung',
                'ct.so_luong_nhap as so_luong',
                'ct.so_luong_ton as soLuongConLai',
                'ct.gia_nhap as giaNhap'
            )
            ->get()
            ->map(function ($item) {
                // Bảo đảm luôn có mã lô hiển thị: dùng ma_lo DB hoặc fallback L-{id}
                $item->maLo = $item->maLo ?: ('L-' . $item->idLoHang);
                return $item;
            });

        // Ensure product always has danhMuc loaded
        if (!$variant->product->relationLoaded('danhMuc')) {
            $variant->product->load('danhMuc');
        }

        // NOTE: thuocTinhs là ACCESSOR (getThuocTinhsAttribute), KHÔNG phải relationship
        // KHÔNG dùng $variant->load('thuocTinhs') - sẽ bị lỗi!
        // Chỉ cần truy cập $variant->thuocTinhs - accessor tự query

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

        // Tạo bienThe[] với thuocTinhs từ accessor cho JS drawer
        // Lưu ý: thuocTinhs là accessor, KHÔNG gọi load() được
        // BAO GỒM cả units (đơn vị quy đổi)
        $bienTheArr = [];
        foreach ($variant->product->variants as $v) {
            $bienTheArr[] = [
                'id' => $v->id,
                'ten_bien_the' => $v->ten_bien_the,
                'ten_don_vi' => $v->ten_don_vi ?? '',
                'ma_vach' => $v->ma_vach,
                'gia_ban' => $v->gia_ban,
                'so_luong_ton_kho' => $v->so_luong_ton,
                'dinh_muc_toi_thieu' => $v->dinh_muc_toi_thieu,
                'trang_thai' => $v->trang_thai,
                'hinh_anh' => $v->hinh_anh,
                'thuoc_tinhs' => $v->thuocTinhs->map(fn($tt) => [
                    'id' => $tt->id,
                    'ten_thuoc_tinh' => $tt->ten_thuoc_tinh,
                ])->all(),
                // Đơn vị quy đổi - BẮT BUỘC để frontend hiển thị trong Tab Biến thể
                'units' => $v->units->map(fn($u) => [
                    'id' => $u->id,
                    'ten_don_vi' => $u->ten_don_vi,
                    'so_luong_san_pham_trong_don_vi' => $u->so_luong_san_pham_trong_don_vi,
                    'gia_von_quy_doi' => $u->gia_von_quy_doi,
                    'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                    'gia_ban_si' => $u->gia_ban_si,
                    'ma_hang' => $u->ma_hang,
                    'ma_vach' => $u->ma_vach,
                ])->all(),
            ];
        }

        // Tạo sanPham object phẳng cho JS drawer (tương thích với sp.ten_don_vi, sp.ma_vach)
        $sanPham = array_merge($variant->product->toArray(), [
            'ten_don_vi' => $variant->ten_don_vi ?? '',
            'so_luong_ton_kho' => $variant->so_luong_ton,
            'ma_vach' => $variant->ma_vach, // Lấy từ variant!
            'thuoc_tinhs' => $variant->thuocTinhs->map(fn($tt) => [
                'id' => $tt->id,
                'ten_thuoc_tinh' => $tt->ten_thuoc_tinh,
            ])->all(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $variant->product->toArray(),
                'variant' => $variant->toArray(),
                // ============================================================
                // TUYỆT ĐỐI: Dùng key 'sanPham' cho JS drawer, KHÔNG dùng 'product'
                // (JS dùng data.sanPham.ten_don_vi)
                // ============================================================
                'sanPham' => $sanPham,
                // ============================================================
                // TUYỆT ĐỐI: Dùng key 'bienThe' cho JS drawer, KHÔNG dùng 'allVariants'
                // (JS dùng data.bienThe.map(...))
                // ============================================================
                'bienThe' => $bienTheArr,
                'selectedUnit' => $selectedUnit?->toArray(),
                'allVariants' => $variant->product->variants->toArray(),
                'units' => $variant->units->map(fn($u) => [
                    'id' => $u->id,
                    'ten_don_vi' => $u->ten_don_vi,
                    'so_luong_san_pham_trong_don_vi' => $u->so_luong_san_pham_trong_don_vi,
                    'ty_le_quy_doi' => $u->so_luong_san_pham_trong_don_vi,
                    'gia_von_quy_doi' => $u->gia_von_quy_doi,
                    'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                    'gia_ban_si' => $u->gia_ban_si,
                    'ma_hang' => $u->ma_hang,
                    'ma_vach' => $u->ma_vach,
                    'hinh_anh' => $u->hinh_anh,
                    'so_luong_ton' => $u->so_luong_ton,
                ])->toArray(),
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
