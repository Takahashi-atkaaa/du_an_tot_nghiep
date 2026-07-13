<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\LoHang;
use App\Models\ChiTietLoHang;
use App\Models\NhaCungCap;
use App\Models\Phieu;
use App\Models\BienTheSanPham;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoHangApiController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $ncc = $request->query('id_nha_cung_cap');

        $query = LoHang::with(['nhaCungCap', 'chiTietLoHang.variant'])
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
            'chiTietLoHang.variant',
            'phieu',
        ])->find($id);

        if (!$loHang) {
            return response()->json(['success' => false, 'message' => 'Lô hàng không tồn tại.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $loHang->toArray(),
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

        $this->audit->ghi(
            'tao_lo_hang',
            'Tạo lô hàng #' . $loHang->id . ' (' . count($data['chi_tiet']) . ' biến thể)',
            [
                'bang' => 'lo_hang',
                'id_ban_ghi' => $loHang->id,
                'tao_canh_bao' => true,
                'tieu_de_cb' => 'Tạo lô hàng mới',
                'noi_dung_cb' => 'Lô hàng #' . $loHang->id . ' (' . count($data['chi_tiet']) . ' biến thể) - NV: ' . (auth()->user()?->ho_ten ?? 'N/A'),
                'url_lien_ket' => '/lo-hang',
            ]
        );

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

        $this->audit->ghi(
            'cap_nhat_lo_hang',
            'Cập nhật lô hàng #' . $loHang->id,
            [
                'bang' => 'lo_hang',
                'id_ban_ghi' => $loHang->id,
                'tao_canh_bao' => true,
                'tieu_de_cb' => 'Cập nhật lô hàng',
                'noi_dung_cb' => 'Lô hàng #' . $loHang->id . ' - NV: ' . (auth()->user()?->ho_ten ?? 'N/A'),
                'url_lien_ket' => '/lo-hang',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật lô hàng thành công.',
            'data' => $loHang,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
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

        $this->audit->ghi(
            'xoa_lo_hang',
            'Xóa lô hàng #' . $loHang->id,
            [
                'bang' => 'lo_hang',
                'id_ban_ghi' => $loHang->id,
                'tao_canh_bao' => true,
                'tieu_de_cb' => 'Xóa lô hàng',
                'noi_dung_cb' => 'Lô hàng #' . $loHang->id . ' đã bị xóa - NV: ' . (auth()->user()?->ho_ten ?? 'N/A'),
                'url_lien_ket' => '/lo-hang',
            ]
        );

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

        $query = ChiTietLoHang::with('loHang.nhaCungCap', 'variant');

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->where('id_san_pham', $sanPhamId);
        }

        $tonKho = $query->orderBy('han_su_dung', 'asc')->get();
        $tongTon = $tonKho->sum('so_luong_ton');

        return response()->json([
            'success' => true,
            'data' => [
                'tong_ton' => $tongTon,
                'chi_tiet' => $tonKho->toArray(),
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

        $items = BienTheSanPham::with('product.danhMuc')
            ->get(['id', 'product_id', 'ten_bien_the', 'ma_vach', 'so_luong_ton', 'dinh_muc_toi_thieu'])
            ->map(function ($variant) use ($sumByVariant, $sumByProduct) {
                $fallbackTon = $variant->so_luong_ton ?? 0;
                $productTon = $sumByProduct[$variant->product_id] ?? 0;
                $variantTon = $sumByVariant[$variant->id] ?? 0;
                $tongTon = $variantTon + $productTon;
                if ($tongTon === 0) {
                    $tongTon = $fallbackTon;
                }

                return [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'ten_bien_the' => $variant->ten_bien_the,
                    'ma_vach' => $variant->ma_vach,
                    'so_luong_ton' => $variant->so_luong_ton,
                    'tong_ton' => $tongTon,
                    'ten_san_pham' => $variant->product->ten_san_pham ?? '',
                    'dinh_muc_toi_thieu' => $variant->dinh_muc_toi_thieu ?? 0,
                    'thuong_hieu' => $variant->product->thuong_hieu ?? '',
                    'danh_muc' => $variant->product->danhMuc->ten_danh_muc ?? '',
                ];
            })
            ->sortBy(fn($v) => $v['ten_san_pham'] ?? '')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $items->toArray(),
        ]);
    }
}
