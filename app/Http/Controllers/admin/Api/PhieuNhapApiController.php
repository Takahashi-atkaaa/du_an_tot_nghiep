<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Phieu;
use App\Models\PhieuNhap;
use App\Models\LoHang;
use App\Models\ChiTietLoHang;
use App\Models\ChiTietPhieu;
use App\Models\BienTheSanPham;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhieuNhapApiController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

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
            'chiTietPhieu' => fn($ct) => $ct->with('variant.product', 'chiTietLoHang'),
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
            'chi_tiet.*.variant_id' => 'required|integer|exists:bien_the_san_pham,id',
            'chi_tiet.*.don_vi_id' => 'nullable|string|max:50',
            'chi_tiet.*.ty_le_quy_doi' => 'nullable|integer|min:1',
            'chi_tiet.*.so_luong_nhap' => 'required|integer|min:1',
            'chi_tiet.*.so_luong_thuc' => 'nullable|integer|min:0',
            'chi_tiet.*.gia_nhap' => 'required|numeric|min:0',
            'chi_tiet.*.han_su_dung' => 'required|date',
        ], [
            'chi_tiet.required' => 'Phải có ít nhất một sản phẩm.',
            'chi_tiet.*.variant_id.required' => 'Mỗi sản phẩm phải có variant_id.',
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
                $variantIds = collect($data['chi_tiet'])->pluck('variant_id')->unique()->all();
                $variantMap = BienTheSanPham::whereIn('id', $variantIds)
                    ->pluck('product_id', 'id')
                    ->toArray();

                $loHang = LoHang::create([
                    'id_phieu' => $phieu->id,
                    'id_nha_cung_cap' => $data['id_nha_cung_cap'] ?? null,
                    'ngay_nhap' => now()->toDateString(),
                ]);

                foreach ($data['chi_tiet'] as $ct) {
                    // Tính số lượng thực (đã quy đổi về đơn vị cơ bản nếu user nhập theo đơn vị quy đổi)
                    $tyLeQuyDoi = (int)($ct['ty_le_quy_doi'] ?? 1);
                    $slNhap = (int)$ct['so_luong_nhap'];
                    $slThuc = $tyLeQuyDoi > 1
                        ? (int)($ct['so_luong_thuc'] ?? ($slNhap * $tyLeQuyDoi))
                        : $slNhap;

                    // Tính ghi chú cho chi_tiet_phieu (lưu thông tin đơn vị nhập)
                    $donViNhap = $tyLeQuyDoi > 1
                        ? "Nhập {$slNhap} đơn vị quy đổi × {$tyLeQuyDoi}"
                        : null;

                    $chiTietLoHang = ChiTietLoHang::create([
                        'id_lo_hang' => $loHang->id,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'so_luong_nhap' => $slThuc,
                        'so_luong_ton' => $slThuc,
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                    ]);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'id_lo_hang' => $loHang->id,
                        'id_chi_tiet_lo_hang' => $chiTietLoHang->id,
                        'so_luong' => $slThuc,
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                        'so_luong_con_lai' => $slThuc,
                        'ghi_chu' => $donViNhap,
                    ]);
                }
            } else {
                $idLoHang = $data['id_lo_hang'];
                $variantIds = collect($data['chi_tiet'])->pluck('variant_id')->unique()->all();
                $variantMap = BienTheSanPham::whereIn('id', $variantIds)
                    ->pluck('product_id', 'id')
                    ->toArray();

                foreach ($data['chi_tiet'] as $ct) {
                    $tyLeQuyDoi = (int)($ct['ty_le_quy_doi'] ?? 1);
                    $slNhap = (int)$ct['so_luong_nhap'];
                    $slThuc = $tyLeQuyDoi > 1
                        ? (int)($ct['so_luong_thuc'] ?? ($slNhap * $tyLeQuyDoi))
                        : $slNhap;

                    $donViNhap = $tyLeQuyDoi > 1
                        ? "Nhập {$slNhap} đơn vị quy đổi × {$tyLeQuyDoi}"
                        : null;

                    $chiTietLoHang = ChiTietLoHang::where('id_lo_hang', $idLoHang)
                        ->where('variant_id', $ct['variant_id'])
                        ->whereDate('han_su_dung', $ct['han_su_dung'])
                        ->first();

                    ChiTietLoHang::create([
                        'id_lo_hang' => $idLoHang,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'so_luong_nhap' => $slThuc,
                        'so_luong_ton' => $slThuc,
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                    ]);

                    ChiTietPhieu::create([
                        'id_phieu' => $phieu->id,
                        'id_san_pham' => $variantMap[$ct['variant_id']] ?? null,
                        'variant_id' => $ct['variant_id'],
                        'id_lo_hang' => $idLoHang,
                        'id_chi_tiet_lo_hang' => $chiTietLoHang?->id,
                        'so_luong' => $slThuc,
                        'gia_nhap' => $ct['gia_nhap'],
                        'han_su_dung' => $ct['han_su_dung'],
                        'so_luong_con_lai' => $slThuc,
                        'ghi_chu' => $donViNhap,
                    ]);
                }
            }

            // Cộng tồn kho cho bien_the_san_pham.so_luong_ton theo số lượng THỰC (đã quy đổi)
            foreach ($data['chi_tiet'] as $ct) {
                $tyLeQuyDoi = (int)($ct['ty_le_quy_doi'] ?? 1);
                $slNhap = (int)$ct['so_luong_nhap'];
                $slThuc = $tyLeQuyDoi > 1
                    ? (int)($ct['so_luong_thuc'] ?? ($slNhap * $tyLeQuyDoi))
                    : $slNhap;
                BienTheSanPham::where('id', $ct['variant_id'])
                    ->increment('so_luong_ton', $slThuc);
            }

            return $phieuNhap->load('phieu', 'chiTietPhieu.variant', 'chiTietPhieu.chiTietLoHang');
        });

        $this->audit->ghi(
            'tao_phieu_nhap',
            'Tạo phiếu nhập #' . $result->id . ' (' . count($data['chi_tiet']) . ' sản phẩm)',
            [
                'bang' => 'phieu_nhap',
                'id_ban_ghi' => $result->id,
                'tao_canh_bao' => true,
                'tieu_de_cb' => 'Tạo phiếu nhập kho',
                'noi_dung_cb' => 'Phiếu nhập #' . $result->id . ' (' . count($data['chi_tiet']) . ' sản phẩm) - NV: ' . (auth()->user()?->ho_ten ?? 'N/A'),
                'url_lien_ket' => '/phieu-nhap',
            ]
        );

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

        $this->audit->ghi(
            'cap_nhat_phieu_nhap',
            'Cập nhật phiếu nhập #' . $phieuNhap->id,
            [
                'bang' => 'phieu_nhap',
                'id_ban_ghi' => $phieuNhap->id,
                'tao_canh_bao' => true,
                'tieu_de_cb' => 'Cập nhật phiếu nhập',
                'noi_dung_cb' => 'Phiếu nhập #' . $phieuNhap->id . ' - NV: ' . (auth()->user()?->ho_ten ?? 'N/A'),
                'url_lien_ket' => '/phieu-nhap',
            ]
        );

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

        DB::transaction(function () use ($phieuNhap) {
            ChiTietPhieu::where('id_phieu', $phieuNhap->id_phieu)->delete();
            $phieuNhap->phieu->delete();
            $phieuNhap->delete();
        });

        $this->audit->ghi(
            'xoa_phieu_nhap',
            'Xóa phiếu nhập #' . $phieuNhap->id,
            [
                'bang' => 'phieu_nhap',
                'id_ban_ghi' => $phieuNhap->id,
                'tao_canh_bao' => true,
                'tieu_de_cb' => 'Xóa phiếu nhập',
                'noi_dung_cb' => 'Phiếu nhập #' . $phieuNhap->id . ' đã bị xóa - NV: ' . (auth()->user()?->ho_ten ?? 'N/A'),
                'url_lien_ket' => '/phieu-nhap',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa phiếu nhập.',
        ]);
    }

    public function danhSachLoHang(Request $request): JsonResponse
    {
        $variantId = $request->query('variant_id');
        $query = LoHang::with('nhaCungCap', 'chiTietLoHang')
            ->whereHas('chiTietLoHang', fn($q) => $q->where('so_luong_ton', '>', 0));

        if ($variantId) {
            $query->whereHas('chiTietLoHang', fn($q) => $q->where('variant_id', $variantId));
        }

        $items = $query->orderByDesc('id')->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }
}
