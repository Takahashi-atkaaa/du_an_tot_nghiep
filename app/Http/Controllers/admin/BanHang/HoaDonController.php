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
            ->leftJoin('ca_lam_viec', 'hoa_don.id_ca_lam_viec', '=', 'ca_lam_viec.id')
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'nguoi_dung.ho_ten as ten_nhan_vien',
                'ca_lam_viec.ten_ca as ten_ca'
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

        if ($request->filled('id_ca_lam_viec')) {
            $query->where('hoa_don.id_ca_lam_viec', $request->id_ca_lam_viec);
        }

        $hoaDons = $query->paginate(10)->withQueryString();

        $caLamViecs = DB::table('ca_lam_viec')
            ->orderBy('id')
            ->get();

        return view('admin_xem_truoc.hoa-don', compact('hoaDons', 'caLamViecs'));
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
            ->leftJoin('bien_the_san_pham', 'chi_tiet_hoa_don.id_chi_tiet_phieu', '=', 'bien_the_san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach'
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        $diemTichDiems = DB::table('lich_su_tich_diem')
            ->where('id_hoa_don', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $return_invoice_id = session('return_invoice_id');
        $phieuDoiTra = null;
        if ($return_invoice_id) {
            $phieuDoiTra = DB::table('phieu')->where('id', $return_invoice_id)->first();
            if ($phieuDoiTra) {
                $phieuDoiTra->chi_tiet = DB::table('chi_tiet_phieu')
                    ->join('san_pham', 'chi_tiet_phieu.id_san_pham', '=', 'san_pham.id')
                    ->leftJoin('bien_the_san_pham', 'chi_tiet_phieu.variant_id', '=', 'bien_the_san_pham.id')
                    ->select('chi_tiet_phieu.*', 'san_pham.ten_san_pham', 'bien_the_san_pham.ten_bien_the', 'bien_the_san_pham.ten_don_vi')
                    ->where('id_phieu', $return_invoice_id)
                    ->get();
            }
        }

        return view('admin_xem_truoc.hoa-don-chi-tiet', compact('hoaDon', 'chiTiet', 'diemTichDiems', 'phieuDoiTra'));
    }

    public function showModal($id)
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
            ->leftJoin('bien_the_san_pham', 'chi_tiet_hoa_don.id_chi_tiet_phieu', '=', 'bien_the_san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach'
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        $diemTichDiems = DB::table('lich_su_tich_diem')
            ->where('id_hoa_don', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin_xem_truoc.partials.hoa-don-modal-content', compact('hoaDon', 'chiTiet', 'diemTichDiems'));
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
                if ($item->id_chi_tiet_phieu) {
                    DB::table('bien_the_san_pham')
                        ->where('id', $item->id_chi_tiet_phieu)
                        ->increment('so_luong_ton', $item->so_luong);
                } else {
                    DB::table('san_pham')
                        ->where('id', $item->id_san_pham)
                        ->increment('so_luong_ton_kho', $item->so_luong);
                }
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

    public function searchProduct(Request $request)
    {
        $keyword = $request->q;

        $query = DB::table('bien_the_san_pham')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->select(
                'bien_the_san_pham.id as id',
                'san_pham.id as id_san_pham',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                'bien_the_san_pham.gia_ban',
                'bien_the_san_pham.so_luong_ton'
            )
            ->where('bien_the_san_pham.trang_thai', 1);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('san_pham.ten_san_pham', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ma_vach', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ma_hang', 'like', "%{$keyword}%");
            });
        }

        $products = $query->limit(20)->get();

        return response()->json($products);
    }

    public function formDoiTra($id)
    {
        $hoaDon = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->select('hoa_don.*', 'khach_hang.ten_khach_hang')
            ->where('hoa_don.id', $id)
            ->first();

        abort_if(!$hoaDon, 404);

        if (in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'])) {
            return back()->with('error', 'Hóa đơn này không thể đổi/trả hàng.');
        }

        $chiTiet = DB::table('chi_tiet_hoa_don')
            ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
            ->leftJoin('bien_the_san_pham', 'chi_tiet_hoa_don.id_chi_tiet_phieu', '=', 'bien_the_san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                'bien_the_san_pham.gia_ban as gia_ban_hien_tai'
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        $daTra = DB::table('phieu')
            ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
            ->where('phieu.id_hoa_don', $id)
            ->where('phieu.loai_phieu', 'Đổi / Trả hàng')
            ->where('chi_tiet_phieu.ghi_chu', 'like', 'Hàng khách trả%')
            ->select(
                DB::raw('COALESCE(chi_tiet_phieu.variant_id, chi_tiet_phieu.id_san_pham) as key_id'),
                DB::raw('SUM(chi_tiet_phieu.so_luong) as tong_da_tra')
            )
            ->groupBy('key_id')
            ->pluck('tong_da_tra', 'key_id');

        return view('admin_xem_truoc.hoa-don-doi-tra', compact('hoaDon', 'chiTiet', 'daTra'));
    }

    public function xuLyDoiTra(Request $request, $id)
    {
        $request->validate([
            'items_tra' => 'nullable|array',
            'items_tra.*.id' => 'required|integer',
            'items_tra.*.so_luong' => 'required|integer|min:1',
            'items_tra.*.is_loi' => 'nullable|boolean',
            'items_tra.*.so_luong_loi' => 'nullable|integer|min:0',
            'items_doi' => 'nullable|array',
            'items_doi.*.variant_id' => 'required|integer',
            'items_doi.*.so_luong' => 'required|integer|min:1',
            'ly_do' => 'nullable|string|max:1000',
        ]);

        $itemsTra = $request->items_tra ?? [];
        $itemsDoi = $request->items_doi ?? [];

        if (count($itemsTra) === 0 && count($itemsDoi) === 0) {
            return back()->with('error', 'Vui lòng chọn ít nhất một sản phẩm để trả hoặc đổi.');
        }

        return DB::transaction(function () use ($request, $id, $itemsTra, $itemsDoi) {
            $hoaDon = DB::table('hoa_don')->where('id', $id)->lockForUpdate()->first();
            if (!$hoaDon || in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'])) {
                return back()->with('error', 'Hóa đơn không hợp lệ.');
            }

            $chiTietHoaDon = DB::table('chi_tiet_hoa_don')->where('id_hoa_don', $id)->get()->keyBy('id');

            $daTraTheoSanPham = DB::table('phieu')
                ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
                ->where('phieu.id_hoa_don', $id)
                ->where('phieu.loai_phieu', 'Đổi / Trả hàng')
                ->where('chi_tiet_phieu.ghi_chu', 'like', 'Hàng khách trả%')
                ->select(
                    DB::raw('COALESCE(chi_tiet_phieu.variant_id, chi_tiet_phieu.id_san_pham) as key_id'),
                    DB::raw('SUM(chi_tiet_phieu.so_luong) as tong_da_tra')
                )
                ->groupBy('key_id')
                ->pluck('tong_da_tra', 'key_id');

            $tongTienTra = 0;
            $danhSachTra = [];
            foreach ($itemsTra as $reqItem) {
                $ct = $chiTietHoaDon[$reqItem['id']] ?? null;
                if (!$ct) return back()->with('error', 'Sản phẩm trả không hợp lệ.');

                $key = $ct->id_chi_tiet_phieu ?: $ct->id_san_pham;
                $daTra = (int) ($daTraTheoSanPham[$key] ?? 0);
                $conDuocTra = $ct->so_luong - $daTra;
                if ($reqItem['so_luong'] > $conDuocTra) {
                    return back()->with('error', 'Số lượng trả vượt quá số lượng còn được trả.');
                }

                $soLuongLoi = isset($reqItem['so_luong_loi']) ? (int) $reqItem['so_luong_loi'] : 0;
                if (!empty($reqItem['is_loi']) && $soLuongLoi === 0) {
                    $soLuongLoi = $reqItem['so_luong'];
                }
                if ($soLuongLoi < 0) {
                    return back()->with('error', 'Số lượng hàng lỗi không được âm.');
                }
                if ($soLuongLoi > $reqItem['so_luong']) {
                    return back()->with('error', 'Số lượng hàng lỗi không được lớn hơn số lượng trả.');
                }

                $tongTienTra += $reqItem['so_luong'] * $ct->gia_ban;
                $danhSachTra[] = [
                    'ct' => $ct,
                    'so_luong' => $reqItem['so_luong'],
                    'so_luong_loi' => $soLuongLoi,
                    'so_luong_tot' => $reqItem['so_luong'] - $soLuongLoi,
                    'is_loi' => !empty($reqItem['is_loi']),
                    'gia_ban' => $ct->gia_ban
                ];
            }

            $tongTienDoi = 0;
            $danhSachDoi = [];
            if (count($itemsDoi) > 0) {
                $variantIds = array_column($itemsDoi, 'variant_id');
                $variants = DB::table('bien_the_san_pham')
                    ->whereIn('id', $variantIds)
                    ->get()
                    ->keyBy('id');

                foreach ($itemsDoi as $reqItem) {
                    $variant = $variants[$reqItem['variant_id']] ?? null;
                    if (!$variant) return back()->with('error', 'Sản phẩm đổi không tồn tại.');
                    if ($variant->so_luong_ton < $reqItem['so_luong']) {
                        return back()->with('error', 'Sản phẩm ' . $variant->ten_bien_the . ' không đủ tồn kho.');
                    }

                    $tongTienDoi += $reqItem['so_luong'] * $variant->gia_ban;
                    $danhSachDoi[] = [
                        'variant' => $variant,
                        'so_luong' => $reqItem['so_luong']
                    ];
                }
            }

            $tienChenhLech = $tongTienDoi - $tongTienTra;
            $ghiChu = ($request->ly_do ?? '') . ' | Chênh lệch: ' . number_format($tienChenhLech, 0, ',', '.') . 'đ';

            $phieuId = DB::table('phieu')->insertGetId([
                'loai_phieu' => 'Đổi / Trả hàng',
                'id_nguoi_dung' => $hoaDon->id_nguoi_dung,
                'id_nha_cung_cap' => null,
                'id_hoa_don' => $hoaDon->id,
                'ghi_chu' => trim($ghiChu),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($danhSachTra as $item) {
                if ($item['so_luong_tot'] > 0) {
                    DB::table('chi_tiet_phieu')->insert([
                        'id_phieu' => $phieuId,
                        'id_san_pham' => $item['ct']->id_san_pham,
                        'variant_id' => $item['ct']->id_chi_tiet_phieu,
                        'so_luong' => $item['so_luong_tot'],
                        'gia_nhap' => $item['gia_ban'],
                        'ghi_chu' => 'Hàng khách trả',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($item['so_luong_tot'] > 0) {
                        if ($item['ct']->id_chi_tiet_phieu) {
                            DB::table('bien_the_san_pham')
                                ->where('id', $item['ct']->id_chi_tiet_phieu)
                                ->increment('so_luong_ton', $item['so_luong_tot']);
                        } else {
                            DB::table('san_pham')
                                ->where('id', $item['ct']->id_san_pham)
                                ->increment('so_luong_ton_kho', $item['so_luong_tot']);
                        }
                    }
                }

                if ($item['so_luong_loi'] > 0) {
                    DB::table('chi_tiet_phieu')->insert([
                        'id_phieu' => $phieuId,
                        'id_san_pham' => $item['ct']->id_san_pham,
                        'variant_id' => $item['ct']->id_chi_tiet_phieu,
                        'so_luong' => $item['so_luong_loi'],
                        'gia_nhap' => $item['gia_ban'],
                        'ghi_chu' => 'Hàng khách trả - Lỗi (Không hoàn kho)',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            foreach ($danhSachDoi as $item) {
                DB::table('chi_tiet_phieu')->insert([
                    'id_phieu' => $phieuId,
                    'id_san_pham' => $item['variant']->product_id,
                    'variant_id' => $item['variant']->id,
                    'so_luong' => $item['so_luong'],
                    'gia_nhap' => $item['variant']->gia_ban,
                    'ghi_chu' => 'Hàng đổi mới',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('bien_the_san_pham')
                    ->where('id', $item['variant']->id)
                    ->decrement('so_luong_ton', $item['so_luong']);
            }

            $tongDaMua = DB::table('chi_tiet_hoa_don')->where('id_hoa_don', $id)->sum('so_luong');
            $tongDaTraSau = DB::table('phieu')
                ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
                ->where('phieu.id_hoa_don', $id)
                ->where('phieu.loai_phieu', 'Đổi / Trả hàng')
                ->where('chi_tiet_phieu.ghi_chu', 'like', 'Hàng khách trả%')
                ->sum('chi_tiet_phieu.so_luong');

            $trangThaiMoi = $tongDaTraSau >= $tongDaMua ? 'Đã trả toàn bộ' : 'Đã đổi/trả hàng';

            DB::table('hoa_don')->where('id', $id)->update([
                'trang_thai' => $trangThaiMoi,
                'updated_at' => now(),
            ]);

            return redirect()->route('admin.hoa-don.show', $id)
                ->with('success', 'Đã xử lý Giao dịch Đổi/Trả hàng. Chênh lệch: ' . number_format($tienChenhLech, 0, ',', '.') . 'đ')
                ->with('return_invoice_id', $phieuId);
        });
    }
}
