<?php

namespace App\Http\Controllers\admin\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMucSanPham;
use App\Models\SanPham;
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

        $query = SanPham::with(['danhMuc', 'donVi', 'chiTietLoHangTon'])
            ->sanPhamCha()
            ->when($q, fn($w) => $w->where(fn($inner) =>
                $inner->where('ten_san_pham', 'like', "%{$q}%")
                    ->orWhere('ma_vach', 'like', "%{$q}%")
                    ->orWhere('ma_hang', 'like', "%{$q}%")
            ))
            ->when($danhMuc, fn($w) => $w->where('id_danh_muc', $danhMuc))
            ->when(!is_null($trangThai) && $trangThai !== '', fn($w) => $w->where('trang_thai', $trangThai))
            ->orderBy('ten_san_pham')
            ->limit(20);

        $items = $query->get(['id', 'ten_san_pham', 'ma_vach', 'ma_hang', 'hinh_anh', 'gia_ban', 'id_danh_muc', 'id_don_vi', 'trang_thai', 'san_pham_cha_id', 'la_san_pham_cha']);

        if ($includeVariants) {
            $items->load(['bienThe' => fn($q) => $q->orderBy('ten_san_pham'), 'bienThe.thuocTinhs', 'bienThe.chiTietLoHangTon']);
        }

        $items->each(function ($sp) use ($includeVariants) {
            $sp->chi_tiet_lo_hang_ton_sum_so_luong_ton = $sp->chiTietLoHangTon->sum('so_luong_ton');
            if ($includeVariants) {
                $sp->bien_the = $sp->bienThe->map(function ($bt) {
                    $bt->chi_tiet_lo_hang_ton_sum_so_luong_ton = $bt->chiTietLoHangTon->sum('so_luong_ton');
                    return $bt;
                })->toArray();
            }
        });

        $dataArray = $items->toArray();

        return response()->json([
            'success' => true,
            'data' => $dataArray,
            'danh_muc_list' => DanhMucSanPham::orderBy('ten_danh_muc')->get(['id', 'ten_danh_muc'])->toArray(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $sanPham = SanPham::with([
            'danhMuc',
            'donVi',
            'thuocTinhs',
            'bienThe.thuocTinhs',
        ])->find($id);

        if (!$sanPham) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $theKho = DB::table('phieu')
            ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
            ->where('chi_tiet_phieu.id_san_pham', $id)
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
            ->where('ct.id_san_pham', $id)
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

        $sanPham->load(['danhMuc', 'donVi', 'thuocTinhs', 'bienThe.thuocTinhs']);

        return response()->json([
            'success' => true,
            'data' => [
                'sanPham' => $sanPham->toArray(),
                'bienThe' => $sanPham->bienThe->toArray(),
                'theKho' => $theKho,
                'loHang' => $loHang,
            ],
        ]);
    }

    public function destroyVariant(int $id): JsonResponse
    {
        $bienThe = SanPham::whereNotNull('san_pham_cha_id')->find($id);

        if (!$bienThe) {
            return response()->json(['success' => false, 'message' => 'Biến thể không tồn tại.'], 404);
        }

        if ($bienThe->hinh_anh && !str_starts_with($bienThe->hinh_anh, 'http')) {
            $this->deleteImageIfUnused($bienThe->hinh_anh);
        }

        $bienThe->thuocTinhs()->detach();
        $bienThe->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa biến thể.',
        ]);
    }

    public function destroyAllVariants(int $parentId): JsonResponse
    {
        $sanPham = SanPham::sanPhamCha()->find($parentId);

        if (!$sanPham) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm cha không tồn tại.'], 404);
        }

        $bienThes = $sanPham->bienThe;

        foreach ($bienThes as $bienThe) {
            if ($bienThe->hinh_anh && !str_starts_with($bienThe->hinh_anh, 'http')) {
                $this->deleteImageIfUnused($bienThe->hinh_anh);
            }
            $bienThe->thuocTinhs()->detach();
            $bienThe->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ' . $bienThes->count() . ' biến thể.',
        ]);
    }

    private function deleteImageIfUnused(string $path): void
    {
        $existsInDb = SanPham::where('hinh_anh', $path)->exists();
        if (!$existsInDb) {
            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
