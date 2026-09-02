<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KiemKho\CapNhatSoLuongRequest;
use App\Http\Requests\KiemKho\HuyPhieuRequest;
use App\Http\Requests\KiemKho\TuChoiPhieuRequest;
use App\Models\BienTheSanPham;
use App\Models\ChiTietKiemKho;
use App\Models\PhieuKiemKho;
use App\Services\KiemKhoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KiemKhoApiController extends Controller
{
    public function __construct(private readonly KiemKhoService $service) {}

    /**
     * GET /admin/api/kiem-kho/{id}/detail
     * Tra ve chi tiet phieu (cho trang dem)
     */
    public function layChiTietPhieu(int $id): JsonResponse
    {
        $phieu = PhieuKiemKho::with([
            'nguoiTao:id,ho_ten',
            'nguoiKiem:id,ho_ten',
            'nguoiDuyet:id,ho_ten',
            'chiTietKiemKho' => fn ($q) => $q->orderBy('ten_san_pham'),
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'phieu' => $phieu,
                'thong_ke' => [
                    'tong_so_san_pham' => $phieu->tong_so_san_pham,
                    'so_sp_dung' => $phieu->so_sp_dung,
                    'so_sp_thieu' => $phieu->so_sp_thieu,
                    'so_sp_thua' => $phieu->so_sp_thua,
                    'so_sp_chua_dem' => $phieu->tong_so_san_pham - $phieu->so_sp_dung - $phieu->so_sp_thieu - $phieu->so_sp_thua,
                    'tong_sl_he_thong' => $phieu->tong_sl_he_thong,
                    'tong_sl_thuc_te' => $phieu->tong_sl_thuc_te,
                    'tong_sl_lech' => $phieu->tong_sl_lech,
                    'tong_gia_tri_lech' => (float) $phieu->tong_gia_tri_lech,
                ],
                'trang_thai_label' => $phieu->trang_thai_label,
                'trang_thai_badge' => $phieu->trang_thai_badge,
                'co_the_bat_dau_kiem' => $phieu->co_the_bat_dau_kiem,
                'co_the_dem' => $phieu->co_the_dem,
                'co_the_hoan_tat_dem' => $phieu->co_the_hoan_tat_dem,
                'co_the_duyet' => $phieu->co_the_duyet,
                'co_the_tu_choi' => $phieu->co_the_tu_choi,
                'co_the_dem_lai' => $phieu->co_the_dem_lai,
                'co_the_hoan_tat' => $phieu->co_the_hoan_tat,
                'co_the_huy' => $phieu->co_the_huy,
            ],
        ]);
    }

    /**
     * POST /admin/api/kiem-kho/{id}/items/{itemId}
     * Cap nhat so luong thuc te cho 1 chi tiet
     */
    public function capNhatSoLuongThucTe(int $id, int $itemId, CapNhatSoLuongRequest $request): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_dem'), 403, 'Bạn không có quyền kiểm đếm hàng.');
        try {
            $chiTiet = $this->service->capNhatSoLuongThucTe(
                $id,
                $itemId,
                (int) $request->so_luong_thuc_te,
                $request->ly_do,
                Auth::user()
            );

            $phieu = PhieuKiemKho::find($id);

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật số lượng.',
                'data' => [
                    'chi_tiet' => $chiTiet,
                    'thong_ke' => [
                        'so_sp_dung' => $phieu->so_sp_dung,
                        'so_sp_thieu' => $phieu->so_sp_thieu,
                        'so_sp_thua' => $phieu->so_sp_thua,
                        'so_sp_chua_dem' => $phieu->tong_so_san_pham - $phieu->so_sp_dung - $phieu->so_sp_thieu - $phieu->so_sp_thua,
                        'tong_sl_lech' => $phieu->tong_sl_lech,
                        'tong_gia_tri_lech' => (float) $phieu->tong_gia_tri_lech,
                    ],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /admin/api/kiem-kho/{id}/items/bulk
     * Cap nhat nhieu chi tiet cung luc
     */
    public function capNhatHangLo(int $id, Request $request): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_dem'), 403, 'Bạn không có quyền cập nhật kiểm đếm.');
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.chi_tiet_id' => 'required|integer',
            'items.*.so_luong_thuc_te' => 'required|integer|min:0',
            'items.*.ly_do' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $success = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                try {
                    $this->service->capNhatSoLuongThucTe(
                        $id,
                        (int) $item['chi_tiet_id'],
                        (int) $item['so_luong_thuc_te'],
                        $item['ly_do'] ?? null,
                        Auth::user()
                    );
                    $success++;
                } catch (\Exception $e) {
                    $errors[] = "Chi tiết {$item['chi_tiet_id']}: {$e->getMessage()}";
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        $phieu = PhieuKiemKho::find($id);

        return response()->json([
            'success' => $success > 0,
            'message' => "Đã cập nhật {$success} sản phẩm.",
            'errors' => $errors,
            'data' => [
                'thong_ke' => [
                    'so_sp_dung' => $phieu->so_sp_dung,
                    'so_sp_thieu' => $phieu->so_sp_thieu,
                    'so_sp_thua' => $phieu->so_sp_thua,
                    'so_sp_chua_dem' => $phieu->tong_so_san_pham - $phieu->so_sp_dung - $phieu->so_sp_thieu - $phieu->so_sp_thua,
                    'tong_sl_lech' => $phieu->tong_sl_lech,
                ],
            ],
        ]);
    }

    /**
     * POST /admin/api/kiem-kho/{id}/bat-dau-kiem
     */
    public function batDauKiem(int $id): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_dem'), 403, 'Bạn không có quyền bắt đầu kiểm kho.');
        try {
            $phieu = $this->service->batDauKiem($id);
            return response()->json([
                'success' => true,
                'message' => 'Đã bắt đầu kiểm. Các biến thể đã bị khoá tạm thời.',
                'data' => ['trang_thai' => $phieu->trang_thai, 'trang_thai_label' => $phieu->trang_thai_label],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /admin/api/kiem-kho/{id}/hoan-tat-kiem
     */
    public function hoanTatKiem(int $id): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_dem'), 403, 'Bạn không có quyền hoàn tất kiểm đếm.');
        try {
            $phieu = $this->service->hoanTatKiem($id);
            return response()->json([
                'success' => true,
                'message' => 'Đã hoàn tất kiểm đếm.',
                'data' => ['trang_thai' => $phieu->trang_thai, 'trang_thai_label' => $phieu->trang_thai_label],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /admin/api/kiem-kho/{id}/duyet
     */
    public function duyet(int $id): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_duyet'), 403, 'Bạn không có quyền duyệt phiếu kiểm kho.');
        try {
            $phieu = $this->service->duyetPhieu($id, Auth::user());
            return response()->json([
                'success' => true,
                'message' => 'Đã duyệt phiếu kiểm kho.',
                'data' => ['trang_thai' => $phieu->trang_thai, 'trang_thai_label' => $phieu->trang_thai_label],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /admin/api/kiem-kho/{id}/tu-choi
     */
    public function tuChoi(int $id, TuChoiPhieuRequest $request): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_duyet'), 403, 'Bạn không có quyền từ chối phiếu kiểm kho.');
        try {
            $phieu = $this->service->tuChoiPhieu($id, $request->ly_do);
            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối phiếu kiểm kho.',
                'data' => ['trang_thai' => $phieu->trang_thai, 'trang_thai_label' => $phieu->trang_thai_label],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /admin/api/kiem-kho/{id}/dem-lai
     */
    public function demLai(int $id): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_dem'), 403, 'Bạn không có quyền yêu cầu đếm lại.');
        try {
            $phieu = $this->service->demLai($id);
            return response()->json([
                'success' => true,
                'message' => 'Đã reset phiếu về trạng thái đếm lại.',
                'data' => ['trang_thai' => $phieu->trang_thai, 'trang_thai_label' => $phieu->trang_thai_label],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /admin/api/kiem-kho/{id}/hoan-tat
     * Ghi phieu dieu chinh ton kho
     */
    public function hoanTat(int $id): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_dieu_chinh'), 403, 'Bạn không có quyền điều chỉnh tồn kho.');
        try {
            $phieu = $this->service->hoanTatDieuChinh($id, Auth::user());
            return response()->json([
                'success' => true,
                'message' => 'Đã hoàn tất điều chỉnh kho. Tồn kho đã được cập nhật.',
                'data' => ['trang_thai' => $phieu->trang_thai, 'trang_thai_label' => $phieu->trang_thai_label],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /admin/api/kiem-kho/{id}/huy
     */
    public function huy(int $id, HuyPhieuRequest $request): JsonResponse
    {
        abort_unless(userHasPermission('kiem_kho_huy'), 403, 'Bạn không có quyền hủy phiếu kiểm kho.');
        try {
            $phieu = $this->service->huyPhieu($id, $request->ly_do);
            return response()->json([
                'success' => true,
                'message' => '�ã hủy phiếu kiểm kho.',
                'data' => ['trang_thai' => $phieu->trang_thai, 'trang_thai_label' => $phieu->trang_thai_label],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /admin/api/kiem-kho/{id}/thong-ke
     */
    public function thongKe(int $id): JsonResponse
    {
        $phieu = PhieuKiemKho::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $this->service->thongKePhieu($phieu),
        ]);
    }

    /**
     * GET /admin/api/kiem-kho/bao-cao
     */
    public function baoCao(Request $request): JsonResponse
    {
        $data = $this->service->baoCaoTongHop($request->only(['tu_ngay', 'den_ngay']));
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * GET /admin/api/kiem-kho/tim-variant?q=...
     * Autocomplete khi chon san pham theo pham vi = chon_san_pham
     */
    public function timVariant(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['success' => true, 'data' => []]);
        }

        $variants = BienTheSanPham::with('product')
            ->where('trang_thai', 1)
            ->where(function ($query) use ($q) {
                $query->where('ma_vach', 'like', "%{$q}%")
                    ->orWhere('ma_hang', 'like', "%{$q}%")
                    ->orWhereHas('product', function ($q2) use ($q) {
                        $q2->where('ten_san_pham', 'like', "%{$q}%");
                    });
            })
            ->limit(20)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'ma_vach' => $v->ma_vach,
                    'ma_hang' => $v->ma_hang,
                    'ten_hien_thi' => $v->ten_hien_thi,
                    'so_luong_ton' => (int) $v->so_luong_ton,
                ];
            });

        return response()->json(['success' => true, 'data' => $variants]);
    }
}
