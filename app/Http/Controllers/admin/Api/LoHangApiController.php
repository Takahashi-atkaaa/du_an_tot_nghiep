<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\LoHang;
use App\Models\ChiTietLoHang;
use App\Models\NhaCungCap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoHangApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $ncc = $request->query('id_nha_cung_cap');

        $query = LoHang::with(['nhaCungCap', 'chiTietLoHang.sanPham'])
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

        // Lấy tất cả IDs trước (không cần paginate ở đây)
        $idsQuery = clone $query;
        $total = (clone $idsQuery)->count();
        $perPage = 15;
        $page = (int) $request->query('page', 1);
        $offset = ($page - 1) * $perPage;

        // Query với relationships + withSum
        $items = LoHang::with(['nhaCungCap', 'chiTietLoHang.sanPham'])
            ->withSum('chiTietLoHang', 'so_luong_ton')
            ->withSum('chiTietLoHang', 'so_luong_nhap')
            ->orderByDesc('id')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // json_encode bỏ qua relationships → dùng toArray() để serialize đúng
        $dataArray = $items->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $dataArray,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $loHang = LoHang::with([
            'nhaCungCap',
            'chiTietLoHang.sanPham',
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
            'chi_tiet.*.id_san_pham' => 'required|integer|exists:san_pham,id',
            'chi_tiet.*.so_luong_nhap' => 'required|integer|min:1',
            'chi_tiet.*.gia_nhap' => 'required|numeric|min:0',
            'chi_tiet.*.han_su_dung' => 'required|date|after_or_equal:ngay_nhap',
        ], [
            'chi_tiet.required' => 'Phải có ít nhất một sản phẩm trong lô.',
            'chi_tiet.*.id_san_pham.required' => 'Mỗi sản phẩm phải có ID.',
            'chi_tiet.*.so_luong_nhap.min' => 'Số lượng nhập phải lớn hơn 0.',
            'chi_tiet.*.han_su_dung.after_or_equal' => 'Hạn sử dụng phải sau ngày nhập.',
        ]);

        $loHang = DB::transaction(function () use ($data) {
            $lo = LoHang::create([
                'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                'ma_lo' => $data['ma_lo'] ?? null,
                'ngay_nhap' => $data['ngay_nhap'],
                'ghi_chu' => $data['ghi_chu'] ?? null,
            ]);

            foreach ($data['chi_tiet'] as $ct) {
                ChiTietLoHang::create([
                    'id_lo_hang' => $lo->id,
                    'id_san_pham' => $ct['id_san_pham'],
                    'so_luong_nhap' => $ct['so_luong_nhap'],
                    'so_luong_ton' => $ct['so_luong_nhap'],
                    'gia_nhap' => $ct['gia_nhap'],
                    'han_su_dung' => $ct['han_su_dung'],
                ]);
            }

            return $lo->load('chiTietLoHang.sanPham', 'nhaCungCap');
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
        $idSanPham = $request->query('id_san_pham');
        if (!$idSanPham) {
            return response()->json(['success' => false, 'message' => 'Thiếu id_san_pham.'], 400);
        }

        $tonKho = ChiTietLoHang::with('loHang.nhaCungCap', 'sanPham')
            ->where('id_san_pham', $idSanPham)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->get();

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
        return response()->json($ncc);
    }

    public function thongKe(): JsonResponse
    {
        $tongLo = LoHang::count();
        $loConHang = LoHang::whereHas('chiTietLoHang', fn($q) => $q->where('so_luong_ton', '>', 0))->count();

        $ngayHsdNguyHiem = now()->addDays(30)->toDateString();
        $homNay = now()->toDateString();

        // Sản phẩm sắp hết HSD (distinct product count)
        $sapHetHsd = ChiTietLoHang::where('so_luong_ton', '>', 0)
            ->where('han_su_dung', '>=', $homNay)
            ->where('han_su_dung', '<=', $ngayHsdNguyHiem)
            ->distinct('id_san_pham')
            ->count('id_san_pham');

        $duoiDinhMuc = \App\Models\SanPham::whereColumn('so_luong_ton_kho', '<=', 'dinh_muc_toi_thieu')
            ->where('so_luong_ton_kho', '>', 0)
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

        $hetHan = ChiTietLoHang::with('loHang.nhaCungCap', 'sanPham')
            ->where('han_su_dung', '<', $homNay)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->limit(50)
            ->get();

        $sapHetHan = ChiTietLoHang::with('loHang.nhaCungCap', 'sanPham')
            ->where('han_su_dung', '>=', $homNay)
            ->where('han_su_dung', '<=', $ngayHsdNguyHiem)
            ->where('so_luong_ton', '>', 0)
            ->orderBy('han_su_dung', 'asc')
            ->limit(50)
            ->get();

        $duoiDinhMuc = \App\Models\SanPham::with('danhMuc')
            ->whereColumn('so_luong_ton_kho', '<=', 'dinh_muc_toi_thieu')
            ->where('so_luong_ton_kho', '>', 0)
            ->orderBy('so_luong_ton_kho', 'asc')
            ->limit(50)
            ->get();

        $hetHang = \App\Models\SanPham::with('danhMuc')
            ->where('so_luong_ton_kho', 0)
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
        $items = \App\Models\SanPham::with('danhMuc', 'chiTietLoHang')
            ->get(['id', 'ten_san_pham', 'ma_vach', 'so_luong_ton_kho', 'dinh_muc_toi_thieu', 'id_danh_muc'])
            ->map(function ($sp) {
                $sp->tong_ton = $sp->chiTietLoHang->sum('so_luong_ton');
                return $sp;
            })
            ->sortBy('ten_san_pham')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $items->toArray(),
        ]);
    }
}
