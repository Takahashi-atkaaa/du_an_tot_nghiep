<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\LoHang;
use App\Models\ChiTietLoHang;
use App\Models\NhaCungCap;
use App\Models\Phieu;
use App\Models\BienTheSanPham;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoHangApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $ncc = $request->query('id_nha_cung_cap');
        $coTon = $request->query('co_ton'); // Filter chỉ lô có tồn kho

        $query = LoHang::with(['nhaCungCap', 'chiTietLoHang.variant.product'])
            ->withSum('chiTietLoHang', 'so_luong_ton')
            ->withSum('chiTietLoHang', 'so_luong_nhap')
            ->orderByDesc('id');

        if (!empty($q)) {
            $query->where(function ($w) use ($q) {
                $w->where('ma_lo', 'like', "%{$q}%")
                    ->orWhere('ghi_chu', 'like', "%{$q}%")
                    ->orWhereHas('nhaCungCap', fn($n) => $n->where('ten_nha_cung_cap', 'like', "%{$q}%"));
            });
        }

        if (!empty($ncc)) {
            $query->where('id_nha_cung_cap', $ncc);
        }
        
        // Chỉ lấy lô có tồn kho > 0
        if (!empty($coTon)) {
            $query->whereHas('chiTietLoHang', fn($ct) => $ct->where('so_luong_ton', '>', 0));
        }

        $perPage = 15;
        $page = (int) $request->query('page', 1);

        $items = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $items->toArray(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $loHang = LoHang::with([
            'nhaCungCap',
            'chiTietLoHang.variant.product',
            'phieu',
        ])->find($id);

        if (!$loHang) {
            return response()->json(['success' => false, 'message' => 'Lô hàng không tồn tại.'], 404);
        }

        $data = $loHang->toArray();
        foreach ($data['chi_tiet_lo_hang'] as &$ct) {
            if (!empty($ct['variant'])) {
                $ct['thuoc_tinh_labels'] = $ct['variant']['thuoc_tinh_labels'] ?? [];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ma_lo' => 'nullable|string|max:255|unique:lo_hang,ma_lo',
            'ngay_nhap' => 'required|date',
            'ghi_chu' => 'nullable|string',
            'chi_tiet' => 'required|array|min:1',
            'chi_tiet.*.variant_id' => 'required|integer|exists:bien_the_san_pham,id',
            'chi_tiet.*.so_luong_nhap' => 'required|integer|min:1',
            'chi_tiet.*.gia_nhap' => 'required|numeric|min:0',
            'chi_tiet.*.han_su_dung' => 'required|date|after_or_equal:ngay_nhap',
        ], [
            'chi_tiet.required' => 'Phải có ít nhất một sản phẩm trong lô.',
            'chi_tiet.*.variant_id.required' => 'Mỗi sản phẩm phải có variant_id.',
            'chi_tiet.*.so_luong_nhap.min' => 'Số lượng nhập phải lớn hơn 0.',
            'chi_tiet.*.han_su_dung.after_or_equal' => 'Hạn sử dụng phải sau ngày nhập.',
        ]);

        $loHang = DB::transaction(function () use ($data) {
            $phieu = Phieu::create([
                'loai_phieu' => 'Lô hàng độc lập',
                'loai_phieu_enum' => 'lo_hang_doc_lap',
                'id_nguoi_dung' => auth()->id(),
            ]);

            $lo = LoHang::create([
                'id_phieu' => $phieu->id,
                'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                'ma_lo' => $data['ma_lo'] ?? null,
                'ngay_nhap' => $data['ngay_nhap'],
                'ghi_chu' => $data['ghi_chu'] ?? null,
            ]);

            $variantIds = collect($data['chi_tiet'])->pluck('variant_id')->unique()->all();
            $variantMap = BienTheSanPham::whereIn('id', $variantIds)
                ->pluck('product_id', 'id')
                ->toArray();

            foreach ($data['chi_tiet'] as $ct) {
                ChiTietLoHang::create([
                    'id_lo_hang' => $lo->id,
                    'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                    'variant_id' => $ct['variant_id'],
                    'so_luong_nhap' => $ct['so_luong_nhap'],
                    'so_luong_ton' => $ct['so_luong_nhap'],
                    'gia_nhap' => $ct['gia_nhap'],
                    'han_su_dung' => $ct['han_su_dung'],
                ]);
            }

            return $lo->load('chiTietLoHang.variant', 'nhaCungCap');
        });

        return response()->json([
            'success' => true,
            'message' => 'Tạo lô hàng thành công.',
            'data' => $loHang,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $loHang = LoHang::find($id);
        if (!$loHang) {
            return response()->json(['success' => false, 'message' => 'Lô hàng không tồn tại.'], 404);
        }

        $data = $request->validate([
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ma_lo' => 'nullable|string|max:255|unique:lo_hang,ma_lo,' . $id,
            'ngay_nhap' => 'required|date',
            'ghi_chu' => 'nullable|string',
        ]);

        $loHang->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật lô hàng thành công.',
            'data' => $loHang,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        abort_unless(userHasPermission('xoa_lo_hang'), 403, 'Bạn không có quyền xóa lô hàng.');
        $loHang = LoHang::with('chiTietLoHang')->find($id);
        if (!$loHang) {
            return response()->json(['success' => false, 'message' => 'Lô hàng không tồn tại.'], 404);
        }

        $coTon = $loHang->chiTietLoHang()->where('so_luong_ton', '>', 0)->exists();
        if ($coTon) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa lô hàng đang có tồn kho. Vui lòng xuất hết hàng trước.',
            ], 422);
        }

        DB::transaction(function () use ($loHang) {
            $loHang->chiTietLoHang()->delete();
            $loHang->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lô hàng.',
        ]);
    }

    public function tonKho(Request $request): JsonResponse
    {
        $variantId = $request->query('variant_id');
        $sanPhamId = $request->query('id_san_pham');

        if (!$variantId && !$sanPhamId) {
            return response()->json(['success' => false, 'message' => 'Thiếu variant_id hoặc id_san_pham.'], 400);
        }

        $query = ChiTietLoHang::with('loHang.nhaCungCap', 'variant.units.donViChuan');

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->where('id_san_pham', $sanPhamId);
        }

        $tonKho = $query->orderBy('han_su_dung', 'asc')->get();
        $tongTon = $tonKho->sum('so_luong_ton');

        // Lay thong tin don vi quy doi cua variant
        $variantUnits = [];
        if ($variantId) {
            $variant = BienTheSanPham::with('units.donViChuan')->find($variantId);
            if ($variant) {
                $variantUnits = $variant->units->map(fn($u) => [
                    'id' => $u->id,
                    'ten_don_vi' => $u->ten_don_vi,
                    'so_luong' => $u->so_luong_san_pham_trong_don_vi,
                    'don_vi_chuan_id' => $u->don_vi_chuan_id,
                    'ten_don_vi_chuan' => $u->donViChuan?->ten_hien_thi,
                    'so_luong_chuan' => $u->donViChuan?->so_luong_san_pham_trong_don_vi,
                ])->values()->all();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tong_ton' => $tongTon,
                'chi_tiet' => $tonKho->toArray(),
                'variant_units' => $variantUnits,
            ],
        ]);
    }

    public function nhaCungCaps(): JsonResponse
    {
        $ncc = NhaCungCap::orderBy('ten_nha_cung_cap')->get(['id', 'ten_nha_cung_cap']);
        return response()->json([
            'success' => true,
            'data' => $ncc,
        ]);
    }

    public function thongKe(): JsonResponse
    {
        $tongLo = LoHang::count();
        $loConHang = LoHang::whereHas('chiTietLoHang', fn($q) => $q->where('so_luong_ton', '>', 0))->count();

        $ngayHsdNguyHiem = now()->addDays(30)->toDateString();
        $homNay = now()->toDateString();

        // Đếm distinct variant (hoặc id_san_pham nếu variant_id NULL) sắp hết HSD
        $sapHetHsd = DB::table('chi_tiet_lo_hang')
            ->where('so_luong_ton', '>', 0)
            ->where('han_su_dung', '>=', $homNay)
            ->where('han_su_dung', '<=', $ngayHsdNguyHiem)
            ->selectRaw('COUNT(DISTINCT COALESCE(variant_id, id_san_pham)) as cnt')
            ->value('cnt');

        $duoiDinhMuc = BienTheSanPham::with('product')
            ->whereColumn('bien_the_san_pham.so_luong_ton', '<=', 'bien_the_san_pham.dinh_muc_toi_thieu')
            ->where('bien_the_san_pham.so_luong_ton', '>', 0)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'tong_lo' => $tongLo,
                'lo_con_hang' => $loConHang,
                'sap_het_hsd' => $sapHetHsd,
                'duoi_dinh_muc' => $duoiDinhMuc,
            ],
        ]);
    }

    public function canhBao(Request $request): JsonResponse
    {
        $ngayHsdNguyHiem = now()->addDays(30)->toDateString();
        $homNay = now()->toDateString();

        $hetHan = ChiTietLoHang::with(['loHang.nhaCungCap', 'variant.product', 'product.danhMuc'])
            ->where('han_su_dung', '<', $homNay)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->limit(50)
            ->get();

        $sapHetHan = ChiTietLoHang::with(['loHang.nhaCungCap', 'variant.product', 'product.danhMuc'])
            ->where('han_su_dung', '>=', $homNay)
            ->where('han_su_dung', '<=', $ngayHsdNguyHiem)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->limit(50)
            ->get();

        $duoiDinhMuc = BienTheSanPham::with('product.danhMuc')
            ->whereColumn('bien_the_san_pham.so_luong_ton', '<=', 'bien_the_san_pham.dinh_muc_toi_thieu')
            ->where('bien_the_san_pham.so_luong_ton', '>', 0)
            ->orderBy('bien_the_san_pham.so_luong_ton', 'asc')
            ->limit(50)
            ->get();

        $hetHang = BienTheSanPham::with('product.danhMuc')
            ->where('so_luong_ton', 0)
            ->where('trang_thai', true)
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'het_han' => $hetHan,
                'sap_het_han' => $sapHetHan,
                'duoi_dinh_muc' => $duoiDinhMuc,
                'het_hang' => $hetHang,
            ],
        ]);
    }

    public function tonKhoTong(): JsonResponse
    {
        $variantRows = DB::table('chi_tiet_lo_hang')
            ->selectRaw('variant_id, SUM(so_luong_ton) AS total_ton')
            ->whereNotNull('variant_id')
            ->groupBy('variant_id')
            ->get();

        $genericProductRows = DB::table('chi_tiet_lo_hang')
            ->selectRaw('id_san_pham, SUM(so_luong_ton) AS total_ton')
            ->whereNull('variant_id')
            ->groupBy('id_san_pham')
            ->get();

        $sumByVariant = [];
        foreach ($variantRows as $row) {
            $sumByVariant[$row->variant_id] = (int) $row->total_ton;
        }

        $sumByProduct = [];
        foreach ($genericProductRows as $row) {
            if ($row->id_san_pham) {
                $sumByProduct[$row->id_san_pham] = (int) $row->total_ton;
            }
        }

        $items = BienTheSanPham::with([
                'product.danhMuc',
                'chiTietLoHangTon.loHang.nhaCungCap:id,ten_nha_cung_cap',
            ])
            ->get(['id', 'product_id', 'ten_bien_the', 'ma_vach', 'so_luong_ton', 'dinh_muc_toi_thieu'])
            ->map(function ($variant) use ($sumByVariant, $sumByProduct) {
                $fallbackTon = $variant->so_luong_ton ?? 0;
                $productTon = $sumByProduct[$variant->product_id] ?? 0;
                $variantTon = $sumByVariant[$variant->id] ?? 0;
                $tongTon = $variantTon + $productTon;
                if ($tongTon === 0) {
                    $tongTon = $fallbackTon;
                }

                // Gom danh sách nhà cung cấp duy nhất từ các lô còn tồn
                $nhaCungCaps = [];
                foreach (($variant->chiTietLoHangTon ?? []) as $ct) {
                    $nccName = $ct->loHang?->nhaCungCap?->ten_nha_cung_cap;
                    if ($nccName && !in_array($nccName, $nhaCungCaps, true)) {
                        $nhaCungCaps[] = $nccName;
                    }
                }

                return [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'ten_bien_the' => $variant->ten_bien_the,
                    'thuoc_tinh_labels' => $variant->thuoc_tinh_labels ?? [],
                    'ma_vach' => $variant->ma_vach,
                    'so_luong_ton' => $variant->so_luong_ton,
                    'tong_ton' => $tongTon,
                    'ten_san_pham' => $variant->product->ten_san_pham ?? '',
                    'dinh_muc_toi_thieu' => $variant->dinh_muc_toi_thieu ?? 0,
                    'thuong_hieu' => $variant->product->thuong_hieu ?? '',
                    'danh_muc' => $variant->product->danhMuc->ten_danh_muc ?? '',
                    'nha_cung_caps' => $nhaCungCaps,
                ];
            })
            ->sortBy(fn($v) => $v['ten_san_pham'] ?? '')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $items->toArray(),
        ]);
    }

    /**
     * GET /admin/api/lo-hang/ton-kho-list?q=&per_page=500
     * Trả về danh sách ChiTietLoHang còn tồn kho (>0), đính kèm variant + product.
     * Dùng cho modal "Chọn sản phẩm" trong trang Kiểm kho (Bước 5).
     */
    public function tonKhoList(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 200);
        $perPage = max(1, min(500, $perPage));

        $query = ChiTietLoHang::with([
                'loHang:id,ma_lo,id_nha_cung_cap',
                'loHang.nhaCungCap:id,ten_nha_cung_cap',
                'variant:id,product_id,ten_bien_the,ma_vach,ma_hang',
                'variant.product:id,ten_san_pham,ma_san_pham',
                'product:id,ten_san_pham',
            ])
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereHas('variant', fn ($v) => $v->where('ma_vach', 'like', "%{$q}%")
                    ->orWhere('ma_hang', 'like', "%{$q}%")
                    ->orWhere('ten_bien_the', 'like', "%{$q}%"))
                    ->orWhereHas('variant.product', fn ($p) => $p->where('ten_san_pham', 'like', "%{$q}%"))
                    ->orWhereHas('loHang', fn ($l) => $l->where('ma_lo', 'like', "%{$q}%"));
            });
        }

        $items = $query->limit($perPage)->get();

        $data = $items->map(function ($row) {
            $variant = $row->variant;
            $product = $variant?->product ?? $row->product;
            $loHang = $row->loHang;
            return [
                'id' => $row->id,
                'id_chi_tiet_lo_hang' => $row->id,
                'variant_id' => $row->variant_id,
                'id_san_pham' => $row->id_san_pham,
                'ma_vach' => $variant?->ma_vach ?? '',
                'ma_hang' => $variant?->ma_hang ?? '',
                'ten_san_pham' => $product?->ten_san_pham ?? '',
                'ten_bien_the' => $variant?->ten_bien_the ?? '',
                'ten_don_vi' => $variant?->ten_bien_the ?: 'Mặc định',
                'han_su_dung' => $row->han_su_dung?->format('Y-m-d'),
                'ma_lo' => $loHang?->ma_lo,
                'so_luong_ton' => (int) $row->so_luong_ton,
                'gia_nhap' => (float) $row->gia_nhap,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }
}
