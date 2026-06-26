<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Phieu;
use App\Models\PhieuNhap;
use App\Models\LoHang;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\SanPham;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhieuNhapApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $loai = $request->query('loai_nhap');
        $tuNgay = $request->query('tu_ngay');
        $denNgay = $request->query('den_ngay');

        $query = PhieuNhap::with([
            'phieu' => fn($p) => $p->with('nhaCungCap', 'nguoiDung'),
            'hoaDon',
            'phieuXuatGoc',
            'chiTietPhieu',
        ])
            ->whereHas('phieu', fn($p) => $p->where('loai_phieu_enum', 'like', 'nhap%'))
            ->orderByDesc('id');

        if (!empty($loai)) {
            $query->where('loai_nhap', $loai);
        }

        if (!empty($tuNgay)) {
            $query->whereDate('created_at', '>=', $tuNgay);
        }

        if (!empty($denNgay)) {
            $query->whereDate('created_at', '<=', $denNgay);
        }

        $items = $query->paginate(15)->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $phieuNhap = PhieuNhap::with([
            'phieu',
            'hoaDon',
            'chiTietPhieu' => fn($ct) => $ct->with('sanPham', 'chiTietLoHang'),
        ])->find($id);

        if (!$phieuNhap) {
            return response()->json(['success' => false, 'message' => 'Phiếu nhập không tồn tại.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $phieuNhap,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'loai_nhap' => 'required|in:mua_hang,tra_lai_tu_khach',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'id_hoa_don' => 'nullable|integer|exists:hoa_don,id',
            'id_phieu_xuat_goc' => 'nullable|integer|exists:phieu,id',
            'ghi_chu' => 'nullable|string',
            'tao_lo_moi' => 'required|in:0,1',
            'id_lo_hang' => 'required_if:tao_lo_moi,0|nullable|integer|exists:lo_hang,id',
            'chi_tiet' => 'required|array|min:1',
            'chi_tiet.*.id_san_pham' => 'required|integer|exists:san_pham,id',
            'chi_tiet.*.so_luong_nhap' => 'required|integer|min:1',
            'chi_tiet.*.gia_nhap' => 'required|numeric|min:0',
            'chi_tiet.*.han_su_dung' => 'required|date',
        ], [
            'chi_tiet.required' => 'Phải có ít nhất một sản phẩm.',
            'chi_tiet.*.id_san_pham.required' => 'Mỗi sản phẩm phải có ID.',
            'chi_tiet.*.so_luong_nhap.min' => 'Số lượng nhập phải lớn hơn 0.',
            'id_lo_hang.required_if' => 'Vui lòng chọn lô hàng khi không tạo lô mới.',
        ]);

        $loaiPhieuEnum = $data['loai_nhap'] === 'mua_hang' ? 'nhap_mua_hang' : 'nhap_tra_lai_tu_khach';

        $result = DB::transaction(function () use ($data, $loaiPhieuEnum) {
            $idNguoiDung = auth()->id();

            $phieu = Phieu::create([
                'loai_phieu' => $data['loai_nhap'] === 'mua_hang' ? 'Nhập hàng' : 'Trả hàng từ khách',
                'loai_phieu_enum' => $loaiPhieuEnum,
                'id_nguoi_dung' => $idNguoiDung,
                'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                'id_hoa_don' => $data['id_hoa_don'] ?? null,
                'ghi_chu' => $data['ghi_chu'] ?? null,
            ]);

            $phieuNhap = PhieuNhap::create([
                'id_phieu' => $phieu->id,
                'loai_nhap' => $data['loai_nhap'],
                'id_hoa_don' => $data['id_hoa_don'] ?? null,
                'id_phieu_xuat_goc' => $data['id_phieu_xuat_goc'] ?? null,
                'ghi_chu' => $data['ghi_chu'] ?? null,
            ]);

            if ($data['tao_lo_moi'] == '1') {
                $loHang = LoHang::create([
                    'id_phieu' => $phieu->id,
                    'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                    'ngay_nhap' => now()->toDateString(),
                ]);

                foreach ($data['chi_tiet'] as $ct) {
                    $chiTietLoHang = ChiTietLoHang::create([
                        'id_lo_hang' => $loHang->id,
                        'id_san_pham' => $ct['id_san_pham'],
                        'so_luong_nhap' => $ct['so_luong_nhap'],
                        'so_luong_ton' => $ct['so_luong_nhap'],
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                    ]);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $ct['id_san_pham'],
                        'id_lo_hang' => $loHang->id,
                        'id_chi_tiet_lo_hang' => $chiTietLoHang->id,
                        'so_luong' => $ct['so_luong_nhap'],
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                        'so_luong_con_lai' => $ct['so_luong_nhap'],
                    ]);

                    SanPham::where('id', $ct['id_san_pham'])->increment('so_luong_ton_kho', $ct['so_luong_nhap']);
                }
            } else {
                $idLoHang = $data['id_lo_hang'];
                foreach ($data['chi_tiet'] as $ct) {
                    $chiTietLoHang = ChiTietLoHang::where('id_lo_hang', $idLoHang)
                        ->where('id_san_pham', $ct['id_san_pham'])
                        ->whereDate('han_su_dung', $ct['han_su_dung'])
                        ->first();

                    ChiTietLoHang::create([
                        'id_lo_hang' => $idLoHang,
                        'id_san_pham' => $ct['id_san_pham'],
                        'so_luong_nhap' => $ct['so_luong_nhap'],
                        'so_luong_ton' => $ct['so_luong_nhap'],
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                    ]);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $ct['id_san_pham'],
                        'id_lo_hang' => $idLoHang,
                        'id_chi_tiet_lo_hang' => $chiTietLoHang?->id,
                        'so_luong' => $ct['so_luong_nhap'],
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                        'so_luong_con_lai' => $ct['so_luong_nhap'],
                    ]);

                    SanPham::where('id', $ct['id_san_pham'])->increment('so_luong_ton_kho', $ct['so_luong_nhap']);
                }
            }

            return $phieuNhap->load('phieu', 'chiTietPhieu.sanPham', 'chiTietPhieu.chiTietLoHang');
    });

        return response()->json([
            'success' => true,
            'message' => 'Tạo phiếu nhập thành công.',
            'data' => $result,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $phieuNhap = PhieuNhap::with('phieu')->find($id);
        if (!$phieuNhap) {
            return response()->json(['success' => false, 'message' => 'Phiếu nhập không tồn tại.'], 404);
        }

        $data = $request->validate([
            'loai_nhap' => 'required|in:mua_hang,tra_lai_tu_khach',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ghi_chu' => 'nullable|string',
        ]);

        $phieuNhap->phieu->update([
            'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);
        $phieuNhap->update([
            'loai_nhap' => $data['loai_nhap'],
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phiếu nhập thành công.',
            'data' => $phieuNhap->load('phieu'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $phieuNhap = PhieuNhap::with('phieu')->find($id);
        if (!$phieuNhap) {
            return response()->json(['success' => false, 'message' => 'Phiếu nhập không tồn tại.'], 404);
        }

        $chiTiets = \App\Models\ChiTietPhieu::where('id_phieu', $phieuNhap->id_phieu)->get();
        foreach ($chiTiets as $ct) {
            \App\Models\SanPham::where('id', $ct->id_san_pham)
                ->decrement('so_luong_ton_kho', $ct->so_luong);
        }

        DB::transaction(function () use ($phieuNhap) {
            \App\Models\ChiTietPhieu::where('id_phieu', $phieuNhap->id_phieu)->delete();
            $phieuNhap->phieu->delete();
            $phieuNhap->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phiếu nhập.',
        ]);
    }

    public function danhSachLoHang(Request $request): JsonResponse
    {
        $idSanPham = $request->query('id_san_pham');
        $query = LoHang::with('nhaCungCap', 'chiTietLoHang')
            ->whereHas('chiTietLoHang', fn($q) => $q->where('so_luong_ton', '>', 0));

        if ($idSanPham) {
            $query->whereHas('chiTietLoHang', fn($q) => $q->where('id_san_pham', $idSanPham));
        }

        $items = $query->orderByDesc('id')->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }
}
