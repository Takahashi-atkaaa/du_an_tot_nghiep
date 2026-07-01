<?php

namespace App\Http\Controllers\admin\BanHang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoaDonController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'nguoi_dung.ho_ten as ten_nhan_vien'
            )
            ->orderByDesc('hoa_don.id');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $keyword = $request->q;
                $id = preg_replace('/[^0-9]/', '', $keyword);

                if ($id !== '') {
                    $q->orWhere('hoa_don.id', $id);
                }

                $q->orWhere('khach_hang.ten_khach_hang', 'like', "%{$keyword}%")
                  ->orWhere('nguoi_dung.ho_ten', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('ngay')) {
            $query->whereDate('hoa_don.created_at', $request->ngay);
        }

        if ($request->filled('trang_thai')) {
            $query->where('hoa_don.trang_thai', $request->trang_thai);
        }

        if ($request->filled('phuong_thuc')) {
            $query->where('hoa_don.phuong_thuc_thanh_toan', $request->phuong_thuc);
        }

        $hoaDons = $query->paginate(10)->withQueryString();

        return view('admin_xem_truoc.hoa-don', compact('hoaDons'));
    }

    public function show($id)
    {
        $hoaDon = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->leftJoin('khuyen_mai', 'hoa_don.id_khuyen_mai', '=', 'khuyen_mai.id')
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'khach_hang.so_dien_thoai',
                'nguoi_dung.ho_ten as ten_nhan_vien',
                'khuyen_mai.ten_chuong_trinh as ten_khuyen_mai'
            )
            ->where('hoa_don.id', $id)
            ->first();

        abort_if(!$hoaDon, 404);

        $chiTiet = DB::table('chi_tiet_hoa_don')
            ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'san_pham.ma_vach'
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        return view('admin_xem_truoc.hoa-don-chi-tiet', compact('hoaDon', 'chiTiet'));
    }
    public function huy($id)
{
    return DB::transaction(function () use ($id) {
        $hoaDon = DB::table('hoa_don')
            ->where('id', $id)
            ->lockForUpdate()
            ->first();

        if (!$hoaDon) {
            return back()->with('error', 'Không tìm thấy hóa đơn.');
        }

        if ($hoaDon->trang_thai === 'Đã hủy') {
            return back()->with('error', 'Hóa đơn này đã bị hủy trước đó.');
        }

        $chiTiet = DB::table('chi_tiet_hoa_don')
            ->where('id_hoa_don', $id)
            ->get();

        foreach ($chiTiet as $item) {
            DB::table('san_pham')
                ->where('id', $item->id_san_pham)
                ->increment('so_luong_ton_kho', $item->so_luong);
        }

        DB::table('hoa_don')
            ->where('id', $id)
            ->update([
                'trang_thai' => 'Đã hủy',
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Đã hủy hóa đơn và hoàn lại tồn kho.');
    });
}
public function formTraHang($id)
{
    $hoaDon = DB::table('hoa_don')
        ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
        ->select('hoa_don.*', 'khach_hang.ten_khach_hang')
        ->where('hoa_don.id', $id)
        ->first();

    abort_if(!$hoaDon, 404);

    if (in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'])) {
        return back()->with('error', 'Hóa đơn này không thể trả hàng.');
    }

    $chiTiet = DB::table('chi_tiet_hoa_don')
        ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
        ->select(
            'chi_tiet_hoa_don.*',
            'san_pham.ten_san_pham',
            'san_pham.ma_vach'
        )
        ->where('chi_tiet_hoa_don.id_hoa_don', $id)
        ->get();

    $daTra = DB::table('phieu')
        ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
        ->where('phieu.id_hoa_don', $id)
        ->where('phieu.loai_phieu', 'Trả hàng')
        ->select('chi_tiet_phieu.id_san_pham', DB::raw('SUM(chi_tiet_phieu.so_luong) as tong_da_tra'))
        ->groupBy('chi_tiet_phieu.id_san_pham')
        ->pluck('tong_da_tra', 'id_san_pham');

    return view('admin_xem_truoc.hoa-don-tra-hang', compact('hoaDon', 'chiTiet', 'daTra'));
}

public function xuLyTraHang(Request $request, $id)
{
    $request->validate([
        'so_luong_tra' => 'required|array',
        'so_luong_tra.*' => 'nullable|integer|min:0',
        'ly_do' => 'nullable|string|max:1000',
    ]);

    return DB::transaction(function () use ($request, $id) {
        $hoaDon = DB::table('hoa_don')
            ->where('id', $id)
            ->lockForUpdate()
            ->first();

        if (!$hoaDon) {
            return back()->with('error', 'Không tìm thấy hóa đơn.');
        }

        if (in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'])) {
            return back()->with('error', 'Hóa đơn này không thể trả hàng.');
        }

        $chiTiet = DB::table('chi_tiet_hoa_don')
            ->where('id_hoa_don', $id)
            ->get()
            ->keyBy('id');

        $daTraTheoSanPham = DB::table('phieu')
            ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
            ->where('phieu.id_hoa_don', $id)
            ->where('phieu.loai_phieu', 'Trả hàng')
            ->select('chi_tiet_phieu.id_san_pham', DB::raw('SUM(chi_tiet_phieu.so_luong) as tong_da_tra'))
            ->groupBy('chi_tiet_phieu.id_san_pham')
            ->pluck('tong_da_tra', 'id_san_pham');

        $itemsTra = [];
        $tongTienHoan = 0;

        foreach ($request->so_luong_tra as $chiTietId => $soLuongTra) {
            $soLuongTra = (int) $soLuongTra;

            if ($soLuongTra <= 0) {
                continue;
            }

            if (!isset($chiTiet[$chiTietId])) {
                return back()->with('error', 'Chi tiết hóa đơn không hợp lệ.');
            }

            $item = $chiTiet[$chiTietId];

            $daTra = (int) ($daTraTheoSanPham[$item->id_san_pham] ?? 0);
            $conDuocTra = $item->so_luong - $daTra;

            if ($soLuongTra > $conDuocTra) {
                return back()->with('error', 'Số lượng trả vượt quá số lượng còn được trả.');
            }

            $itemsTra[] = [
                'id_san_pham' => $item->id_san_pham,
                'so_luong' => $soLuongTra,
                'gia_ban' => $item->gia_ban,
                'thanh_tien' => $soLuongTra * $item->gia_ban,
            ];

            $tongTienHoan += $soLuongTra * $item->gia_ban;
        }

        if (count($itemsTra) === 0) {
            return back()->with('error', 'Vui lòng nhập số lượng sản phẩm cần trả.');
        }

        $phieuId = DB::table('phieu')->insertGetId([
            'loai_phieu' => 'Trả hàng',
            'id_nguoi_dung' => $hoaDon->id_nguoi_dung,
            'id_nha_cung_cap' => null,
            'id_hoa_don' => $hoaDon->id,
            'ghi_chu' => trim(($request->ly_do ?? '') . ' | Tiền hoàn: ' . number_format($tongTienHoan, 0, ',', '.') . 'đ'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($itemsTra as $item) {
            DB::table('chi_tiet_phieu')->insert([
                'id_phieu' => $phieuId,
                'id_san_pham' => $item['id_san_pham'],
                'so_luong' => $item['so_luong'],
                'gia_nhap' => $item['gia_ban'],
                'ma_lo' => null,
                'han_su_dung' => null,
                'so_luong_con_lai' => $item['so_luong'],
                'ghi_chu' => 'Trả hàng từ hóa đơn #' . $hoaDon->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('san_pham')
                ->where('id', $item['id_san_pham'])
                ->increment('so_luong_ton_kho', $item['so_luong']);
        }

        $tongDaMua = DB::table('chi_tiet_hoa_don')
            ->where('id_hoa_don', $id)
            ->sum('so_luong');

        $tongDaTraSau = DB::table('phieu')
            ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
            ->where('phieu.id_hoa_don', $id)
            ->where('phieu.loai_phieu', 'Trả hàng')
            ->sum('chi_tiet_phieu.so_luong');

        $trangThaiMoi = $tongDaTraSau >= $tongDaMua
            ? 'Đã trả toàn bộ'
            : 'Đã trả một phần';

        DB::table('hoa_don')
            ->where('id', $id)
            ->update([
                'trang_thai' => $trangThaiMoi,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.hoa-don.show', $id)
            ->with('success', 'Đã xử lý trả hàng. Tiền hoàn: ' . number_format($tongTienHoan, 0, ',', '.') . 'đ');
    });
}
}