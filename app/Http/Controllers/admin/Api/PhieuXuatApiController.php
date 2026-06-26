<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Phieu;
use App\Models\PhieuXuat;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\SanPham;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhieuXuatApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $loai = $request->query('loai_xuat');
        $tuNgay = $request->query('tu_ngay');
        $denNgay = $request->query('den_ngay');

        $query = PhieuXuat::with([
            'phieu',
            'phieuNhapLienQuan',
            'chiTietPhieu',
        ])
            ->whereHas('phieu', fn($p) => $p->where('loai_phieu_enum', 'like', 'xuat%'))
            ->orderByDesc('id');

        if (!empty($loai)) {
            $query->where('loai_xuat', $loai);
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
        $phieuXuat = PhieuXuat::with([
            'phieu',
            'phieuNhapLienQuan',
            'chiTietPhieu' => fn($ct) => $ct->with('sanPham', 'chiTietLoHang.loHang'),
        ])->find($id);

        if (!$phieuXuat) {
            return response()->json(['success' => false, 'message' => 'Phiếu xuất không tồn tại.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $phieuXuat,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'loai_xuat' => 'required|in:tra_hang_nha_cung_cap,tieu_huy',
            'id_phieu_nhap_lien_quan' => 'nullable|integer|exists:phieu,id',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ly_do' => 'nullable|string|max:500',
            'ghi_chu' => 'nullable|string',
            'chi_tiet' => 'required|array|min:1',
            'chi_tiet.*.id_san_pham' => 'required|integer|exists:san_pham,id',
            'chi_tiet.*.id_chi_tiet_lo_hang' => 'required|integer|exists:chi_tiet_lo_hang,id',
            'chi_tiet.*.so_luong' => 'required|integer|min:1',
        ], [
            'chi_tiet.required' => 'Phải có ít nhất một sản phẩm.',
            'chi_tiet.*.so_luong.min' => 'Số lượng xuất phải lớn hơn 0.',
            'chi_tiet.*.id_chi_tiet_lo_hang.required' => 'Phải chọn lô hàng cho từng sản phẩm.',
            'chi_tiet.*.id_chi_tiet_lo_hang.exists' => 'Lô hàng đã chọn không tồn tại.',
        ]);

        $loaiPhieuEnum = $data['loai_xuat'] === 'tra_hang_nha_cung_cap'
            ? 'xuat_tra_hang_nha_cung_cap'
            : 'xuat_tieu_huy';

        $loaiPhieuLabel = $data['loai_xuat'] === 'tra_hang_nha_cung_cap'
            ? 'Trả hàng NCC'
            : 'Tiêu hủy';

        try {
            $result = DB::transaction(function () use ($data, $loaiPhieuEnum, $loaiPhieuLabel) {
                $idNguoiDung = auth()->id();

                $phieu = Phieu::create([
                    'loai_phieu' => $loaiPhieuLabel,
                    'loai_phieu_enum' => $loaiPhieuEnum,
                    'id_nguoi_dung' => $idNguoiDung,
                    'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                    'ly_do' => $data['ly_do'] ?? null,
                    'ghi_chu' => $data['ghi_chu'] ?? null,
                ]);

                $phieuXuat = PhieuXuat::create([
                    'id_phieu' => $phieu->id,
                    'loai_xuat' => $data['loai_xuat'],
                    'id_phieu_nhap_lien_quan' => $data['id_phieu_nhap_lien_quan'] ?? null,
                    'ly_do' => $data['ly_do'] ?? null,
                    'ghi_chu' => $data['ghi_chu'] ?? null,
                ]);

                foreach ($data['chi_tiet'] as $ct) {
                    $soLuongCanXuat = (int) $ct['so_luong'];

                    $ctLo = ChiTietLoHang::where('id', $ct['id_chi_tiet_lo_hang'])
                        ->where('id_san_pham', $ct['id_san_pham'])
                        ->lockForUpdate()
                        ->first();

                    if (!$ctLo) {
                        throw new \Exception("Lô hàng đã chọn không tồn tại hoặc không thuộc sản phẩm ID {$ct['id_san_pham']}.");
                    }

                    if ($ctLo->so_luong_ton < $soLuongCanXuat) {
                        throw new \Exception("Sản phẩm ID {$ct['id_san_pham']}: lô đã chọn chỉ tồn {$ctLo->so_luong_ton}, không đủ để xuất {$soLuongCanXuat}.");
                    }

                    $soLuongTonTruocKhiXuat = (int) $ctLo->so_luong_ton;
                    $soLuongTonSauKhiXuat = $soLuongTonTruocKhiXuat - $soLuongCanXuat;

                    $ctLo->decrement('so_luong_ton', $soLuongCanXuat);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $ct['id_san_pham'],
                        'id_lo_hang' => $ctLo->id_lo_hang,
                        'id_chi_tiet_lo_hang' => $ctLo->id,
                        'so_luong' => $soLuongCanXuat,
                        'gia_nhap' => $ctLo->gia_nhap ?? 0,
                        'han_su_dung' => $ctLo->han_su_dung,
                        'so_luong_con_lai' => $soLuongTonSauKhiXuat,
                    ]);

                    SanPham::where('id', $ct['id_san_pham'])->decrement('so_luong_ton_kho', $soLuongCanXuat);
                }

                return $phieuXuat->load('phieu', 'chiTietPhieu.sanPham', 'chiTietPhieu.chiTietLoHang.loHang');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo phiếu xuất thành công.',
            'data' => $result,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $phieuXuat = PhieuXuat::with('phieu')->find($id);
        if (!$phieuXuat) {
            return response()->json(['success' => false, 'message' => 'Phiếu xuất không tồn tại.'], 404);
        }

        $data = $request->validate([
            'loai_xuat' => 'required|in:tra_hang_nha_cung_cap,tieu_huy',
            'id_nha_cung_cap' => 'nullable|integer|exists:nha_cung_cap,id',
            'ly_do' => 'nullable|string|max:500',
            'ghi_chu' => 'nullable|string',
        ]);

        $loaiLabel = $data['loai_xuat'] === 'tra_hang_nha_cung_cap' ? 'Trả hàng NCC' : 'Tiêu hủy';
        $loaiEnum = $data['loai_xuat'] === 'tra_hang_nha_cung_cap'
            ? 'xuat_tra_hang_nha_cung_cap'
            : 'xuat_tieu_huy';

        $phieuXuat->phieu->update([
            'loai_phieu' => $loaiLabel,
            'loai_phieu_enum' => $loaiEnum,
            'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
            'ly_do' => $data['ly_do'] ?? null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);
        $phieuXuat->update([
            'loai_xuat' => $data['loai_xuat'],
            'ly_do' => $data['ly_do'] ?? null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phiếu xuất thành công.',
            'data' => $phieuXuat->load('phieu'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $phieuXuat = PhieuXuat::with('phieu')->find($id);
        if (!$phieuXuat) {
            return response()->json(['success' => false, 'message' => 'Phiếu xuất không tồn tại.'], 404);
        }

        $chiTiets = \App\Models\ChiTietPhieu::where('id_phieu', $phieuXuat->id_phieu)->get();
        foreach ($chiTiets as $ct) {
            \App\Models\ChiTietLoHang::where('id', $ct->id_chi_tiet_lo_hang)
                ->increment('so_luong_ton', $ct->so_luong);
            \App\Models\SanPham::where('id', $ct->id_san_pham)
                ->increment('so_luong_ton_kho', $ct->so_luong);
        }

        DB::transaction(function () use ($phieuXuat) {
            \App\Models\ChiTietPhieu::where('id_phieu', $phieuXuat->id_phieu)->delete();
            $phieuXuat->phieu->delete();
            $phieuXuat->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phiếu xuất và hoàn tăng tồn kho.',
        ]);
    }
}
