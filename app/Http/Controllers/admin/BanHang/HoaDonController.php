<?php

namespace App\Http\Controllers\admin\BanHang;

use App\Http\Controllers\Controller;
use App\Http\Requests\BanHang\XuLyDoiTraRequest;
use App\Models\HoaDon;
use App\Services\DoiTraService;
use App\Services\KiemKhoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HoaDonController extends Controller
{
    private function tenHienThiBienTheSelect(): \Illuminate\Database\Query\Expression
    {
        return DB::raw("
            TRIM(
                CONCAT(
                    COALESCE(san_pham.ten_san_pham, ''),
                    CASE
                        WHEN COALESCE(
                            CASE
                                WHEN bien_the_san_pham.la_don_vi = 1 THEN NULLIF(bien_the_san_pham.ten_don_vi, '')
                                ELSE NULLIF(bien_the_san_pham.ten_bien_the, '')
                            END,
                            NULLIF(bien_the_san_pham.ten_don_vi, '')
                        ) IS NOT NULL
                            THEN CONCAT(
                                ' - ',
                                COALESCE(
                                    CASE
                                        WHEN bien_the_san_pham.la_don_vi = 1 THEN NULLIF(bien_the_san_pham.ten_don_vi, '')
                                        ELSE NULLIF(bien_the_san_pham.ten_bien_the, '')
                                    END,
                                    NULLIF(bien_the_san_pham.ten_don_vi, '')
                                )
                            )
                        ELSE ''
                    END
                )
            ) as ten_hien_thi_san_pham
        ");
    }

    public function index(Request $request)
    {
        $doiTraSummarySub = DB::table('doi_tra')
            ->selectRaw('id_hoa_don, COUNT(*) as so_lan_doi_tra')
            ->whereNull('deleted_at')
            ->groupBy('id_hoa_don');

        $query = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->leftJoin('ca_lam_viec', 'hoa_don.id_ca_lam_viec', '=', 'ca_lam_viec.id')
            ->leftJoinSub($doiTraSummarySub, 'doi_tra_tong_hop', function ($join) {
                $join->on('hoa_don.id', '=', 'doi_tra_tong_hop.id_hoa_don');
            })
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'nguoi_dung.ho_ten as ten_nhan_vien',
                'ca_lam_viec.ten_ca as ten_ca',
                DB::raw('COALESCE(doi_tra_tong_hop.so_lan_doi_tra, 0) as so_lan_doi_tra')
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

        if ($request->filled('tu_ngay') || $request->filled('den_ngay')) {
            $startDate = $request->filled('tu_ngay')
                ? Carbon::parse($request->tu_ngay)->startOfDay()
                : Carbon::parse($request->den_ngay)->startOfDay();

            $endDate = $request->filled('den_ngay')
                ? Carbon::parse($request->den_ngay)->endOfDay()
                : Carbon::parse($request->tu_ngay)->endOfDay();

            if ($startDate->gt($endDate)) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            $query->whereBetween('hoa_don.created_at', [$startDate, $endDate]);
        } elseif ($request->filled('ngay')) {
            $query->whereDate('hoa_don.created_at', $request->ngay);
        } else {
            $today = Carbon::now()->toDateString();
            $request->merge(['ngay' => $today]);
            $query->whereDate('hoa_don.created_at', $today);
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

        $phuongThucThanhToans = DB::table('hoa_don')
            ->whereNotNull('phuong_thuc_thanh_toan')
            ->distinct()
            ->orderBy('phuong_thuc_thanh_toan')
            ->pluck('phuong_thuc_thanh_toan');

        return view('admin_xem_truoc.hoa-don', compact('hoaDons', 'caLamViecs', 'phuongThucThanhToans'));
    }

    public function show($id, DoiTraService $doiTraService)
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
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                $this->tenHienThiBienTheSelect()
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        $diemTichDiems = DB::table('lich_su_tich_diem')
            ->where('id_hoa_don', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        // =====================================================
        // KHUYẾN MÃI ĐÃ ÁP DỤNG (đồng bộ với bán hàng)
        // =====================================================
        $khuyenMaiDaApDung = DB::table('hoa_don_khuyen_mai')
            ->join('khuyen_mai', 'hoa_don_khuyen_mai.id_khuyen_mai', '=', 'khuyen_mai.id')
            ->where('hoa_don_khuyen_mai.id_hoa_don', $id)
            ->select(
                'hoa_don_khuyen_mai.id_khuyen_mai',
                'hoa_don_khuyen_mai.tien_giam',
                'hoa_don_khuyen_mai.loai_ap_dung',
                'khuyen_mai.ten_chuong_trinh',
                'khuyen_mai.loai_giam_gia',
                'khuyen_mai.gia_tri_giam'
            )
            ->get();

        $giamSanPham = $khuyenMaiDaApDung
            ->where('loai_ap_dung', 'san_pham')
            ->sum('tien_giam');

        $giamHoaDon = $khuyenMaiDaApDung
            ->where('loai_ap_dung', 'hoa_don')
            ->sum('tien_giam');

        $returnSummary = $doiTraService->getInvoiceReturnSummary((int) $id);
        $lichSuDoiTra = $returnSummary['lichSuDoiTra'];
        $doiTraMoiNhat = session('last_doi_tra_id')
            ? $lichSuDoiTra->firstWhere('id', (int) session('last_doi_tra_id'))
            : null;
        $tongHopDoiTra = $returnSummary['tongHopDoiTra'];
        $chiTietTheoBienThe = $returnSummary['chiTietTheoBienThe'];
        // =====================================================
// TỔNG TIỀN HOÀN THỰC TẾ TỪ TRẢ HÀNG
// =====================================================

$tongTienHoanThucTe = 0;

foreach ($lichSuDoiTra as $doiTra) {

    $loaiDoiTra = $doiTra->Loai
        ?? $doiTra->loai
        ?? null;

    // Chỉ trả hàng mới làm giảm doanh thu
    if ($loaiDoiTra !== 'tra_hang') {
        continue;
    }

    foreach ($doiTra->chiTietDoiTras ?? [] as $chiTietDoiTra) {

        $tongTienHoanThucTe += (float) (
            $chiTietDoiTra->thanh_tien ?? 0
        );
    }
}


// =====================================================
// TỔNG KẾT THANH TOÁN
// =====================================================

// Tổng tiền hàng trước giảm giá
$tamTinh = (float) ($hoaDon->tong_tien_hang ?? 0);

// Tổng giảm giá
$giamGia = (float) ($hoaDon->tien_giam_gia ?? 0);

// Khách cần trả theo hóa đơn
$khachCanTra = (float) ($hoaDon->khach_can_tra ?? 0);

// Doanh thu thực tế sau khi hoàn tiền
$doanhThuRong = max(
    0,
    $khachCanTra - $tongTienHoanThucTe
);

        foreach ($chiTiet as $item) {
            $returnItem = $chiTietTheoBienThe->get($item->id_bien_the_san_pham);
            $item->tong_da_tra = (int) ($returnItem->tong_tra_hang ?? 0);
            $item->tong_da_doi = (int) ($returnItem->tong_doi_hang ?? 0);
            $item->tong_da_doi_tra = (int) ($returnItem->tong_doi_tra ?? 0);
        }

        $this->ganThuocTinhBienTheChoChiTiet($chiTiet);

        return view('admin_xem_truoc.hoa-don-chi-tiet', compact(
    'hoaDon',
    'chiTiet',
    'diemTichDiems',
    'lichSuDoiTra',
    'doiTraMoiNhat',
    'tongHopDoiTra',
    'khuyenMaiDaApDung',
    'giamSanPham',
    'giamHoaDon',
    'tongTienHoanThucTe',
    'tamTinh',
    'giamGia',
    'khachCanTra',
    'doanhThuRong'
));
    }

    public function chiTietDoiTra($id, DoiTraService $doiTraService)
    {
        $data = $doiTraService->getInvoiceReturnHistoryPageData((int) $id);

        return view('admin_xem_truoc.hoa-don-chi-tiet-doi-tra', $data);
    }

    public function showModal($id, DoiTraService $doiTraService)
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
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                $this->tenHienThiBienTheSelect()
            )
            ->where('chi_tiet_hoa_don.id_hoa_don', $id)
            ->get();

        $diemTichDiems = DB::table('lich_su_tich_diem')
            ->where('id_hoa_don', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $khuyenMaiDaApDung = DB::table('hoa_don_khuyen_mai')
            ->join('khuyen_mai', 'hoa_don_khuyen_mai.id_khuyen_mai', '=', 'khuyen_mai.id')
            ->where('hoa_don_khuyen_mai.id_hoa_don', $id)
            ->select(
                'hoa_don_khuyen_mai.id_khuyen_mai',
                'hoa_don_khuyen_mai.tien_giam',
                'hoa_don_khuyen_mai.loai_ap_dung',
                'khuyen_mai.ten_chuong_trinh',
                'khuyen_mai.loai_giam_gia',
                'khuyen_mai.gia_tri_giam'
            )
            ->get();

        $giamSanPham = $khuyenMaiDaApDung
            ->where('loai_ap_dung', 'san_pham')
            ->sum('tien_giam');

        $giamHoaDon = $khuyenMaiDaApDung
            ->where('loai_ap_dung', 'hoa_don')
            ->sum('tien_giam');

        $returnSummary = $doiTraService->getInvoiceReturnSummary((int) $id);
        $chiTietTheoBienThe = $returnSummary['chiTietTheoBienThe'];

        foreach ($chiTiet as $item) {
            $returnItem = $chiTietTheoBienThe->get($item->id_bien_the_san_pham);
            $item->tong_da_tra = (int) ($returnItem->tong_tra_hang ?? 0);
            $item->tong_da_doi = (int) ($returnItem->tong_doi_hang ?? 0);
            $item->tong_da_doi_tra = (int) ($returnItem->tong_doi_tra ?? 0);
        }

        $lichSuDoiTra = $returnSummary['lichSuDoiTra'];
        $tongHopDoiTra = $returnSummary['tongHopDoiTra'];

        $this->ganThuocTinhBienTheChoChiTiet($chiTiet);

        return view('admin_xem_truoc.partials.hoa-don-modal-content', compact(
            'hoaDon',
            'chiTiet',
            'diemTichDiems',
            'lichSuDoiTra',
            'tongHopDoiTra',
            'khuyenMaiDaApDung',
            'giamSanPham',
            'giamHoaDon'
        ));
    }

    private function ganThuocTinhBienTheChoChiTiet($chiTiet): void
    {
        if ($chiTiet->isEmpty()) {
            return;
        }

        $variantIds = $chiTiet->pluck('id_bien_the_san_pham')->filter()->unique()->all();

        $thuocTinhRows = DB::table('bien_the_san_pham')
            ->whereIn('id', $variantIds)
            ->select('id', 'thuoc_tinh_ids')
            ->get()
            ->keyBy('id');

        $allAttrIds = $thuocTinhRows->pluck('thuoc_tinh_ids')
            ->filter()
            ->flatten()
            ->map(fn ($x) => (int) $x)
            ->unique()
            ->all();

        if (empty($allAttrIds)) {
            foreach ($chiTiet as $item) {
                $item->thuoc_tinh_hien_thi = [];
            }
            return;
        }

        $attrMap = DB::table('thuoc_tinh_san_pham')
            ->whereIn('id', $allAttrIds)
            ->select('id', 'ten_thuoc_tinh', 'thuoc_tinh_cha_id')
            ->get()
            ->keyBy('id');

        foreach ($chiTiet as $item) {
            $row = $thuocTinhRows->get($item->id_bien_the_san_pham);
            $rawIds = $row->thuoc_tinh_ids ?? [];
            $labels = [];

            foreach ((array) $rawIds as $aid) {
                $aid = (int) $aid;
                if (! isset($attrMap[$aid])) {
                    continue;
                }
                $attr = $attrMap[$aid];
                $parentId = $attr->thuoc_tinh_cha_id ? (int) $attr->thuoc_tinh_cha_id : null;
                if ($parentId && isset($attrMap[$parentId])) {
                    $labels[] = $attrMap[$parentId]->ten_thuoc_tinh . ': ' . $attr->ten_thuoc_tinh;
                } else {
                    $labels[] = $attr->ten_thuoc_tinh;
                }
            }

            $item->thuoc_tinh_hien_thi = array_values(array_filter($labels, fn ($v) => $v !== null && $v !== ''));
        }
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
                DB::table('bien_the_san_pham')
                    ->where('id', $item->id_bien_the_san_pham)
                    ->increment('so_luong_ton', $item->so_luong);
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

    public function searchProduct(Request $request, KiemKhoService $kiemKhoService)
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
                'bien_the_san_pham.so_luong_ton',
                $this->tenHienThiBienTheSelect()
            )
            ->where('bien_the_san_pham.trang_thai', 1);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('san_pham.ten_san_pham', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ma_vach', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ma_hang', 'like', "%{$keyword}%");
            });
        }

        $items = $query->limit(20)->get();
        // Check tung variant co bi khoa boi phieu kiem kho nao khong
        foreach ($items as $item) {
            $phieuKhoa = $kiemKhoService->phieuDangKhoaBienThe((int) $item->id);
            $item->dang_bi_khoa_kiem_kho = $phieuKhoa !== null;
            $item->phieu_kiem_kho_khoa = $phieuKhoa?->ma_kiem_kho;
        }

        return response()->json($items);
    }

    public function formDoiTra($id, DoiTraService $doiTraService)
    {
        $data = $doiTraService->getInvoiceReturnData((int) $id);
        $hoaDon = $data['hoaDon'];
        $chiTiet = $data['chiTiet'];
        $danhSachNguoiBan = $doiTraService->getEligibleSalesUsers();

        if (in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'], true)) {
            return back()->with('error', 'Hóa đơn này không thể đổi/trả hàng.');
        }

        return view('admin_xem_truoc.hoa-don-doi-tra', compact('hoaDon', 'chiTiet', 'danhSachNguoiBan'));
    }

    public function xuLyDoiTra(XuLyDoiTraRequest $request, $id, DoiTraService $doiTraService)
    {
        $processedTokens = session()->get('processed_doi_tra_tokens', []);
        $requestToken = $request->string('request_token')->toString();

        if (in_array($requestToken, $processedTokens, true)) {
            return back()->with('error', 'Yêu cầu đổi/trả này đã được xử lý trước đó.');
        }

        $hoaDon = HoaDon::query()->findOrFail($id);
        $doiTra = $doiTraService->process($hoaDon, $request->validated(), Auth::user());

        $processedTokens[] = $requestToken;
        session()->put('processed_doi_tra_tokens', array_slice(array_unique($processedTokens), -20));

        $thongBao = $doiTra->Loai === 'tra_hang'
            ? 'Đã xử lý trả hàng thành công.'
            : 'Đã xử lý đổi hàng lỗi thành công.';

        return redirect()->route('admin.hoa-don.show', $id)
            ->with('success', $thongBao)
            ->with('last_doi_tra_id', $doiTra->id);
    }
}
