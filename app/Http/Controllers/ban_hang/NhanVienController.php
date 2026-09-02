<?php

namespace App\Http\Controllers\ban_hang;

use App\Http\Controllers\Controller;
use App\Http\Requests\BanHang\XuLyDoiTraRequest;
use App\Http\Requests\DoiMatKhauRequest;
use App\Models\ChiaCaLamViec;
use App\Models\GiaoDich;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\CaLamViec;
use App\Models\ChiTietLoHang;
use App\Models\DanhMucSanPham;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\KhachHang;
use App\Services\DoiTraService;
use App\Services\KiemKhoService;
use App\Services\PayOSService;
use Illuminate\Validation\ValidationException;


class NhanVienController extends Controller
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

    private function timCaLamViecHienTai(): ?CaLamViec
{
    $bayGio = Carbon::now();

    $danhSachCa = CaLamViec::query()
        ->whereNull('deleted_at')
        ->get();

    foreach ($danhSachCa as $ca) {
        $ngayHomNay = $bayGio->toDateString();

        $gioBatDauHomNay = Carbon::parse(
            $ngayHomNay . ' ' . $ca->gio_bat_dau
        );

        $gioKetThucHomNay = Carbon::parse(
            $ngayHomNay . ' ' . $ca->gio_ket_thuc
        );

        /*
         * Ca trong cùng ngày:
         * SA1: 07:00 - 12:00
         * CH1: 12:00 - 19:00
         * TO2: 01:00 - 07:00
         */
        if ($gioKetThucHomNay->greaterThan($gioBatDauHomNay)) {
            if (
                $bayGio->greaterThanOrEqualTo($gioBatDauHomNay)
                && $bayGio->lessThan($gioKetThucHomNay)
            ) {
                return $ca;
            }

            continue;
        }

        /*
         * Ca qua đêm:
         * TO1: 19:00 hôm nay - 01:00 ngày mai
         */

        $batDauHomNay = $gioBatDauHomNay->copy();

        $ketThucNgayMai = $gioKetThucHomNay
            ->copy()
            ->addDay();

        if (
            $bayGio->greaterThanOrEqualTo($batDauHomNay)
            && $bayGio->lessThan($ketThucNgayMai)
        ) {
            return $ca;
        }

        /*
         * Trường hợp sau 00:00:
         * 19:00 hôm qua - 01:00 hôm nay
         */
        $batDauHomQua = $gioBatDauHomNay
            ->copy()
            ->subDay();

        $ketThucHomNay = $gioKetThucHomNay->copy();

        if (
            $bayGio->greaterThanOrEqualTo($batDauHomQua)
            && $bayGio->lessThan($ketThucHomNay)
        ) {
            return $ca;
        }
    }

    return null;
}
    public function index()
    {
        return view('ban_hang.dashboard');
    }

   public function banHang()
{
    $nguoiDung = Auth::user();

    if (!$nguoiDung) {
        return redirect()
            ->route('admin.login')
            ->with('error', 'Vui lòng đăng nhập.');
    }

    /*
     * Không kiểm tra chấm công nữa.
     * Chỉ xác định ca làm việc theo thời gian hiện tại.
     */
    $caHienTai = $this->timCaLamViecHienTai();

    $danhSachDanhMuc = DanhMucSanPham::query()
        ->where('trang_thai', 1)
        ->orderBy('id', 'asc')
        ->get();

    return view('ban_hang.pos_moi', compact(
        'nguoiDung',
        'caHienTai',
        'danhSachDanhMuc'
    ));
}

public function banHangMoi()
{
    $nguoiDung = Auth::user();

    if (!$nguoiDung) {
        return redirect()
            ->route('admin.login')
            ->with('error', 'Vui lòng đăng nhập.');
    }

    $caHienTai = $this->timCaLamViecHienTai();

    $danhSachDanhMuc = DanhMucSanPham::query()
        ->where('trang_thai', 1)
        ->orderBy('id', 'asc')
        ->get();

    return view('ban_hang.pos_moi', compact(
        'nguoiDung',
        'caHienTai',
        'danhSachDanhMuc'
    ));
}

private function danhSachDonChoThanhToanPayOS()
    {
        // Lấy tất cả hoá đơn đang chờ thanh toán, kèm giao dịch PayOS còn `cho_xac_nhan`
        $hoaDons = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->where('hoa_don.trang_thai', 'Chờ thanh toán')
        ->select(
            'hoa_don.id',
            'hoa_don.khach_can_tra',
            'hoa_don.created_at',
            'khach_hang.ten_khach_hang',
            'khach_hang.so_dien_thoai'
        )
        ->orderByDesc('hoa_don.id')
        ->limit(50)
        ->get();

    $ids = $hoaDons->pluck('id')->all();

    // Map id_hoa_don => giao dịch PayOS mới nhất còn `cho_xac_nhan`
    $gdMap = [];
    if (!empty($ids)) {
        $gds = DB::table('giao_dich')
            ->whereIn('id_hoa_don', $ids)
            ->where('phuong_thuc', 'payos')
            ->where('trang_thai', 'cho_xac_nhan')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($gds as $gd) {
            $dl = is_string($gd->du_lieu_phan_hoi)
                ? json_decode($gd->du_lieu_phan_hoi, true)
                : (array) $gd->du_lieu_phan_hoi;
            
            if (!isset($gdMap[$gd->id_hoa_don])) {
                // Nếu API có expiredAt thì dùng, không thì tính = created_at + 15 phút
                $expiredAt = $dl['expiredAt'] ?? (strtotime($gd->created_at) + (15 * 60));
                
                $gdMap[$gd->id_hoa_don] = [
                    'giao_dich_id' => $gd->id,
                    'ma_tham_chieu' => $gd->ma_tham_chieu,
                    'checkout_url' => $dl['checkout_url'] ?? null,
                    'qr_code' => $dl['qr_code'] ?? null,
                    'so_tien' => $gd->so_tien,
                    'expiredAt' => $expiredAt,
                ];
            }
        }
    }

    return $hoaDons->map(function ($hd) use ($gdMap) {
        $gd = $gdMap[$hd->id] ?? null;

        $currentTime = time();
        $isExpired = false;
        if ($gd && isset($gd['expiredAt'])) {
            $isExpired = $currentTime > $gd['expiredAt'];
        }
        
        return [
            'hoa_don_id' => (int) $hd->id,
            'ma_hoa_don' => '#' . $hd->id,
            'khach_can_tra' => (float) $hd->khach_can_tra,
            'created_at' => $hd->created_at,
            'ten_khach_hang' => $hd->ten_khach_hang,
            'so_dien_thoai' => $hd->so_dien_thoai,
            'has_payos' => (bool) $gd,
            'checkout_url' => $gd['checkout_url'] ?? null,
            'qr_code' => $gd['qr_code'] ?? null,
            'giao_dich_id' => $gd['giao_dich_id'] ?? null,
            'ma_tham_chieu' => $gd['ma_tham_chieu'] ?? null,
            'expiredAt' => $gd['expiredAt'] ?? null,
            'is_expired' => $isExpired,
        ];
    })->filter(function ($item) {
        // Loại bỏ các hóa đơn có QR đã hết hạn
        return !$item['is_expired'];
    })->values();
}

public function donChoThanhToan(): View
{
    $hoaDons = $this->danhSachDonChoThanhToanPayOS();

    return view('ban_hang.payos.don-cho-thanh-toan', [
        'hoaDons' => $hoaDons,
        'tongHoaDon' => $hoaDons->count(),
        'tongTien' => $hoaDons->sum('khach_can_tra'),
        'soHoaDonCoQR' => $hoaDons->where('has_payos', true)->count(),
        'soHoaDonChuaQR' => $hoaDons->where('has_payos', false)->count(),
    ]);
}

public function doiPhuongThucThanhToanDonCho(Request $request, int $id, PayOSService $payOSService)
{
    $request->validate([
        'phuong_thuc_thanh_toan' => 'required|in:cash',
        'tien_khach_dua' => 'required|numeric|min:0',
    ]);

    $hoaDon = DB::table('hoa_don')
        ->where('id', $id)
        ->where('trang_thai', 'Chờ thanh toán')
        ->first();

    if (!$hoaDon) {
        return back()->with('error', 'Không tìm thấy hóa đơn đang chờ thanh toán.');
    }

    $phuongThucThanhToan = 'Tiền mặt';
    $tienKhachDua = (float) $request->input('tien_khach_dua');
    $khachCanTra = (float) $hoaDon->khach_can_tra;

    if ($tienKhachDua < $khachCanTra) {
        return back()
            ->withInput()
            ->with('error', 'Tiền khách đưa phải lớn hơn hoặc bằng số tiền cần thanh toán.');
    }

    $giaoDich = GiaoDich::query()
        ->where('id_hoa_don', $hoaDon->id)
        ->where('phuong_thuc', 'payos')
        ->where('trang_thai', 'cho_xac_nhan')
        ->orderByDesc('id')
        ->first();

    if ($giaoDich && !empty($giaoDich->ma_tham_chieu)) {
        $canProceedWithoutCancel = false;

        try {
            $paymentInfo = $payOSService->getPaymentInfo((string) $giaoDich->ma_tham_chieu);
            $paymentStatus = data_get($paymentInfo, 'status');
            $paymentStatusValue = $paymentStatus instanceof \BackedEnum
                ? $paymentStatus->value
                : strtoupper((string) $paymentStatus);

            if ($paymentStatusValue === 'PAID') {
                return back()->with('error', 'QR này đã được thanh toán rồi, không thể đổi phương thức.');
            }

            if (in_array($paymentStatusValue, ['CANCELLED', 'FAILED', 'EXPIRED', 'UNDERPAID'], true)) {
                $canProceedWithoutCancel = true;
            }
        } catch (\Throwable $e) {
            $expiredAt = data_get($giaoDich->du_lieu_phan_hoi, 'expiredAt');
            $canProceedWithoutCancel = $expiredAt && time() > (int) $expiredAt;
        }

        if (!$canProceedWithoutCancel) {
            try {
                $payOSService->cancelPaymentLink(
                    (string) $giaoDich->ma_tham_chieu,
                    'Đổi phương thức thanh toán từ QR đang chờ'
                );
            } catch (\Throwable $e) {
                $expiredAt = data_get($giaoDich->du_lieu_phan_hoi, 'expiredAt');

                if (!($expiredAt && time() > (int) $expiredAt)) {
                    return back()->with('error', 'Không thể hủy QR PayOS: ' . $e->getMessage());
                }
            }
        }
    }

    try {
        $tienThua = $this->hoanTatHoaDonDonChoThanhToan((int) $hoaDon->id, $phuongThucThanhToan, $tienKhachDua, $giaoDich);
    } catch (\Throwable $e) {
        return back()->with('error', 'Không thể hoàn tất hóa đơn: ' . $e->getMessage());
    }

    return redirect()
        ->route('nhan-vien.ban-hang.don-cho-thanh-toan')
        ->with('success', 'Đã đổi hóa đơn #' . $hoaDon->id . ' sang tiền mặt. Tiền thừa: ' . number_format($tienThua, 0, ',', '.') . 'đ');
}

private function hoanTatHoaDonDonChoThanhToan(int $hoaDonId, string $phuongThucThanhToan, float $tienKhachDua, ?GiaoDich $giaoDich = null): float
{
    return DB::transaction(function () use ($hoaDonId, $phuongThucThanhToan, $tienKhachDua, $giaoDich) {
        $hoaDon = DB::table('hoa_don')
            ->where('id', $hoaDonId)
            ->lockForUpdate()
            ->first();

        if (!$hoaDon || $hoaDon->trang_thai !== 'Chờ thanh toán') {
            return 0.0;
        }

        $khachCanTra = (float) $hoaDon->khach_can_tra;
        if ($tienKhachDua < $khachCanTra) {
            throw ValidationException::withMessages([
                'tien_khach_dua' => 'Tiền khách đưa phải lớn hơn hoặc bằng số tiền cần thanh toán.',
            ]);
        }

        $tienThua = max(0, round($tienKhachDua - $khachCanTra, 2));

        $chiTiets = DB::table('chi_tiet_hoa_don')
            ->where('id_hoa_don', $hoaDon->id)
            ->get();

        foreach ($chiTiets as $ct) {
            $variantId = (int) $ct->id_bien_the_san_pham;
            $soLuongBan = (int) $ct->so_luong;

            $remaining = $soLuongBan;
            $loList = ChiTietLoHang::where('variant_id', $variantId)
                ->where('so_luong_ton', '>', 0)
                ->orderBy('han_su_dung', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($loList as $lo) {
                if ($remaining <= 0) {
                    break;
                }

                $truTuLo = (int) min($remaining, $lo->so_luong_ton);
                $lo->so_luong_ton -= $truTuLo;
                $lo->save();
                $remaining -= $truTuLo;
            }
        }

        $diemSuDung = (int) DB::table('lich_su_tich_diem')
            ->where('id_hoa_don', $hoaDon->id)
            ->where('loai_bien_dong', 'tru')
            ->sum('so_diem');

        $diemThuDuoc = (int) floor(((float) $hoaDon->khach_can_tra) / 10000);

        if ($hoaDon->id_khach_hang) {
            DB::table('khach_hang')
                ->where('id', $hoaDon->id_khach_hang)
                ->increment('tong_chi_tieu', $hoaDon->khach_can_tra);

            if ($diemThuDuoc > 0) {
                DB::table('khach_hang')
                    ->where('id', $hoaDon->id_khach_hang)
                    ->increment('diem_tich_luy', $diemThuDuoc);

                DB::table('lich_su_tich_diem')->insert([
                    'id_khach_hang' => $hoaDon->id_khach_hang,
                    'id_hoa_don' => $hoaDon->id,
                    'loai_bien_dong' => 'cong',
                    'so_diem' => $diemThuDuoc,
                    'ly_do' => 'Tích điểm từ hóa đơn (đổi từ QR đang chờ)',
                    'created_at' => now(),
                ]);
            }
        }

        if ($giaoDich && $giaoDich->trang_thai === 'cho_xac_nhan') {
            DB::table('giao_dich')
                ->where('id', $giaoDich->id)
                ->update([
                    'trang_thai' => 'that_bai',
                    'ma_phan_hoi' => 'PM_CASH',
                    'trang_thai_doi_tac' => 'CANCELLED',
                    'du_lieu_phan_hoi' => json_encode(array_merge($giaoDich->du_lieu_phan_hoi ?? [], [
                        'manual_change' => [
                            'new_payment_method' => $phuongThucThanhToan,
                            'changed_at' => now()->toDateTimeString(),
                        ],
                    ]), JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
        }

        DB::table('hoa_don')
            ->where('id', $hoaDon->id)
            ->update([
                'phuong_thuc_thanh_toan' => $phuongThucThanhToan,
                'tien_khach_dua' => $tienKhachDua,
                'tien_thua' => $tienThua,
                'trang_thai' => 'Hoàn thành',
                'diem_su_dung' => $diemSuDung,
                'diem_thu_duoc' => $diemThuDuoc,
                'updated_at' => now(),
            ]);
        return $tienThua;
    });
}

    public function hoaDon(Request $request)
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
    'ca_lam_viec.ten_ca',
    'ca_lam_viec.gio_bat_dau',
    'ca_lam_viec.gio_ket_thuc',
    DB::raw('COALESCE(doi_tra_tong_hop.so_lan_doi_tra, 0) as so_lan_doi_tra')
)
            ->orderByDesc('hoa_don.id');

        if ($request->filled('q')) {
            $query->where('hoa_don.id', preg_replace('/[^0-9]/', '', $request->q));
        }
        if ($request->filled('id_ca_lam_viec')) {
            $query->where('hoa_don.id_ca_lam_viec', $request->id_ca_lam_viec);
        }

        if ($request->filled('ngay')) {
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

        $hoaDons = $query->paginate(10)->withQueryString();

        $caLamViecs = DB::table('ca_lam_viec')
            ->orderBy('gio_bat_dau')
            ->get();

        $phuongThucThanhToans = DB::table('hoa_don')
            ->whereNotNull('phuong_thuc_thanh_toan')
            ->distinct()
            ->orderBy('phuong_thuc_thanh_toan')
            ->pluck('phuong_thuc_thanh_toan');

        return view('ban_hang.hoa-don.index', compact('hoaDons', 'caLamViecs', 'phuongThucThanhToans'));
    }

    public function sanPham()
    {
        // Kiểm tra quyền truy cập
        if (!auth()->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = auth()->user();
        
        // Trả về view danh sách sản phẩm cho POS
        $keyword = request()->input('keyword');
        $danhMucId = request()->input('danh_muc');
        $trangThai = request()->filled('trang_thai') ? request()->boolean('trang_thai') : null;

        $danhMucs = \App\Models\DanhMucSanPham::query()->orderBy('ten_danh_muc')->get();

        $sanPhams = \App\Models\SanPham::with(['danhMuc', 'variants'])
            ->withSum('variants', 'so_luong_ton')
            ->whereNull('deleted_at')
            ->whereHas('variants', fn($q) => $q->whereNull('deleted_at'))
            ->when($keyword, fn($q) => $q
                ->where(fn($inner) => $inner
                    ->whereRaw('LOWER(ten_san_pham) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                    ->orWhereHas('variants', fn($v) => $v
                        ->whereNull('deleted_at')
                        ->whereRaw('LOWER(ma_vach) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                        ->orWhereRaw('LOWER(ma_hang) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                        ->orWhereRaw('LOWER(ten_bien_the) LIKE ?', ["%".mb_strtolower($keyword)."%"])
                    )
                )
            )
            ->when($danhMucId, fn($q) => $q->where('id_danh_muc', $danhMucId))
            ->when(!is_null($trangThai), fn($q) => $q->where('trang_thai', $trangThai))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Transform collection để thêm các thuộc tính hiển thị
        $sanPhams->getCollection()->transform(function ($sp) {
            $firstVariant = $sp->variants->first();

            $sp->hinh_anh_hien_thi = $firstVariant?->hinh_anh ?? $sp->hinh_anh;
            $sp->don_vi_tinh_hien_thi = $firstVariant?->ten_don_vi ?? 'Cái';
            $sp->gia_ban_hien_thi = $firstVariant?->gia_ban ?? 0;
            $sp->tong_ton_kho_hien_thi = $sp->variants_sum_so_luong_ton ?? 0;
            $sp->trang_thai_kho_hien_thi = $sp->tong_ton_kho_hien_thi > 0 ? 'Còn hàng' : 'Hết hàng';

            return $sp;
        });

        return view('ban_hang.san-pham.index', [
            'sanPhams' => $sanPhams,
            'danhMucs' => $danhMucs,
            'tuKhoa' => $keyword,
            'danhMucId' => $danhMucId,
            'trangThai' => request()->input('trang_thai'),
        ]);
    }
    public function lichLamViec(Request $request): View
    {
        return $this->lichSuCaLam($request);
    }

    public function lichSuCaLam(Request $request): View
    {
        $nguoiDung = $this->resolvePreviewEmployee($request);

        $query = ChiaCaLamViec::query()
            ->with('caLamViec')
            ->where('id_nguoi_dung', $nguoiDung->id);

        if ($request->filled('tu_ngay')) {
            $query->whereDate('ngay', '>=', $request->input('tu_ngay'));
        }

        if ($request->filled('den_ngay')) {
            $query->whereDate('ngay', '<=', $request->input('den_ngay'));
        }

        $lichSuCaLam = (clone $query)
            ->orderByDesc('ngay')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $tongSoCa = (clone $query)->count();
        $tongSoNgayLam = (clone $query)
            ->distinct('ngay')
            ->count('ngay');
        $caGanNhat = (clone $query)
            ->orderByDesc('ngay')
            ->orderByDesc('id')
            ->first();
        $caDauTien = (clone $query)
            ->orderBy('ngay')
            ->orderBy('id')
            ->first();

        return view('nhan_vien_view.lich-lam-viec.lich-su', [
            'nguoiDung' => $nguoiDung,
            'lichSuCaLam' => $lichSuCaLam,
            'tongSoCa' => $tongSoCa,
            'tongSoNgayLam' => $tongSoNgayLam,
            'caGanNhat' => $caGanNhat,
            'caDauTien' => $caDauTien,
            'boLoc' => [
                'tu_ngay' => $request->input('tu_ngay'),
                'den_ngay' => $request->input('den_ngay'),
            ],
        ]);
    }


    private function calculateShiftMinutes(string $gioBatDau, string $gioKetThuc): int
    {
        $batDau = Carbon::createFromFormat('H:i:s', substr($gioBatDau, 0, 8));
        $ketThuc = Carbon::createFromFormat('H:i:s', substr($gioKetThuc, 0, 8));

        if ($ketThuc->lessThanOrEqualTo($batDau)) {
            $ketThuc->addDay();
        }

        return $batDau->diffInMinutes($ketThuc);
    }

    private function formatHoursFromMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . ' giờ';
        }

        return $hours . ' giờ ' . $remainingMinutes . ' phút';
    }

    private function displayRole(?string $vaiTro): string
    {
        $normalized = Str::of((string) $vaiTro)->lower()->ascii()->value();

        return match ($normalized) {
            'truong ca' => 'Trưởng ca',
            'admin' => 'Admin',
            default => 'Nhân viên',
        };
    }

    private function isAdminRole(?string $vaiTro): bool
    {
        return Str::of((string) $vaiTro)->lower()->ascii()->value() === 'admin';
    }
 public function laySanPham(Request $request)
{
    $query = DB::table('bien_the_san_pham')
        ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
        ->where('san_pham.trang_thai', 1)
        ->where('bien_the_san_pham.trang_thai', 1)
        ->whereNull('san_pham.deleted_at')
        ->whereNull('bien_the_san_pham.deleted_at')
        ->where('bien_the_san_pham.so_luong_ton', '>', 0);

        if ($request->filled('id_danh_muc') && $request->id_danh_muc !== 'all') {
            $query->where('san_pham.id_danh_muc', $request->id_danh_muc);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('san_pham.ten_san_pham', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ma_hang', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ma_vach', 'like', "%{$keyword}%")
                    ->orWhere('bien_the_san_pham.ten_bien_the', 'like', "%{$keyword}%")
                    ->orWhereExists(function ($unitQuery) use ($keyword) {
                        $unitQuery->selectRaw('1')
                            ->from('don_vi_quy_doi')
                            ->whereColumn('don_vi_quy_doi.variant_id', 'bien_the_san_pham.id')
                            ->whereNull('don_vi_quy_doi.deleted_at')
                            ->where(function ($unit) use ($keyword) {
                                $unit->where('don_vi_quy_doi.ten_don_vi', 'like', "%{$keyword}%")
                                    ->orWhere('don_vi_quy_doi.ma_vach', 'like', "%{$keyword}%")
                                    ->orWhere('don_vi_quy_doi.ma_hang', 'like', "%{$keyword}%");
                            });
                    });
            });
        }

        $products = $query->select(
            'bien_the_san_pham.id',
            'san_pham.id as id_san_pham',
            'san_pham.id_danh_muc',
            'san_pham.ten_san_pham',
            'bien_the_san_pham.ten_bien_the',
            'bien_the_san_pham.thuoc_tinh_ids',
            'bien_the_san_pham.la_don_vi',
            'bien_the_san_pham.ten_don_vi',
            'bien_the_san_pham.ma_hang',
            'bien_the_san_pham.ma_vach',
            'bien_the_san_pham.gia_ban',
            'bien_the_san_pham.so_luong_ton as so_luong_ton_kho',
            'bien_the_san_pham.hinh_anh'
        )
            ->orderByDesc('bien_the_san_pham.id')
            ->get();

        $variantIds = $products->pluck('id')->map(fn ($id) => (int) $id)->all();
        $unitsByVariant = DB::table('don_vi_quy_doi')
            ->whereIn('variant_id', $variantIds)
            ->whereNull('deleted_at')
            ->select(
                'id',
                'variant_id',
                'ten_don_vi',
                'so_luong_san_pham_trong_don_vi',
                'gia_ban_quy_doi',
                'ma_hang',
                'ma_vach',
                'hinh_anh'
            )
            ->orderBy('so_luong_san_pham_trong_don_vi')
            ->get()
            ->groupBy('variant_id');

        $attributeIds = [];
        foreach ($products as $product) {
            $rawIds = json_decode((string) ($product->thuoc_tinh_ids ?? ''), true);
            if (!is_array($rawIds)) {
                $rawIds = explode(',', (string) ($product->thuoc_tinh_ids ?? ''));
            }
            foreach ($rawIds as $id) {
                if (is_numeric($id) && (int) $id > 0) {
                    $attributeIds[] = (int) $id;
                }
            }
        }

        $attributeRows = collect();
        if (!empty($attributeIds)) {
            $attributeRows = DB::table('thuoc_tinh_san_pham as value')
                ->leftJoin('thuoc_tinh_san_pham as parent', 'parent.id', '=', 'value.thuoc_tinh_cha_id')
                ->whereIn('value.id', array_unique($attributeIds))
                ->select(
                    'value.id',
                    'value.ten_thuoc_tinh',
                    'parent.ten_thuoc_tinh as ten_thuoc_tinh_cha'
                )
                ->get()
                ->keyBy('id');
        }

        $products = $products->map(function ($product) use ($unitsByVariant, $attributeRows) {
            $product->ten_san_pham_goc = trim((string) ($product->ten_san_pham ?? ''));
            $product->ten_san_pham = trim(($product->ten_san_pham ?? '') . ' ' . ($product->ten_bien_the ?? ''));
            $product->ten_san_pham = preg_replace('/\s+/', ' ', $product->ten_san_pham);
            $product->ten_san_pham = trim($product->ten_san_pham);

            $product->ma_hang = $product->ma_hang ?? null;
            $product->ma_vach = $product->ma_vach ?? null;
            $product->la_don_vi = (bool) ($product->la_don_vi ?? false);
            $product->ten_don_vi = $product->ten_don_vi ?? null;
            $product->gia_ban = (float) ($product->gia_ban ?? 0);
            $product->so_luong_ton_kho = (int) ($product->so_luong_ton_kho ?? 0);
            $product->hinh_anh = $product->hinh_anh ?? null;

            $product->don_vi_quy_doi = ($unitsByVariant->get($product->id) ?? collect())
                ->map(function ($unit) use ($product) {
                    $factor = (float) ($unit->so_luong_san_pham_trong_don_vi ?: 1);
                    return [
                        'id' => (int) $unit->id,
                        'ten_don_vi' => trim((string) $unit->ten_don_vi),
                        'so_luong_san_pham_trong_don_vi' => $factor,
                        'gia_ban_quy_doi' => (float) ($unit->gia_ban_quy_doi ?? 0),
                        'so_luong_ton_kho' => $factor > 0 ? (int) floor($product->so_luong_ton_kho / $factor) : 0,
                        'ma_hang' => $unit->ma_hang ?? null,
                        'ma_vach' => $unit->ma_vach ?? null,
                        'hinh_anh' => $unit->hinh_anh ?? null,
                    ];
                })
                ->values()
                ->all();

            $rawIds = json_decode((string) ($product->thuoc_tinh_ids ?? ''), true);
            if (!is_array($rawIds)) {
                $rawIds = explode(',', (string) ($product->thuoc_tinh_ids ?? ''));
            }
            $attributeLabels = [];
            foreach ($rawIds as $id) {
                $attribute = is_numeric($id) ? $attributeRows->get((int) $id) : null;
                if (!$attribute) {
                    continue;
                }
                $groupName = trim((string) ($attribute->ten_thuoc_tinh_cha ?: 'Thuộc tính'));
                $attributeLabels[$groupName] = trim((string) $attribute->ten_thuoc_tinh);
            }
            $product->thuoc_tinh_hien_thi = $attributeLabels;

            return $product;
        });

        return response()->json($products);
    }
    public function layDanhMuc()
    {
        return response()->json(
            DanhMucSanPham::query()
                ->where('trang_thai', 1)
                ->select('id', 'ten_danh_muc')
                ->orderBy('id', 'asc')
                ->get()
        );
    }
    public function thanhToan(Request $request, KiemKhoService $kiemKhoService)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:bien_the_san_pham,id',
            'cart.*.qty' => 'required|integer|min:1',
            'cart.*.id_don_vi_quy_doi' => 'nullable|integer|exists:don_vi_quy_doi,id',
            'tien_khach_dua' => 'required|numeric|min:0',
            'phuong_thuc_thanh_toan' => 'required|string',
            'id_khach_hang' => 'nullable|integer|exists:khach_hang,id',
            // Người bán: id_nguoi_ban phải tồn tại trong bảng nguoi_dung (đang hoạt động, không xoá mềm).
            // Nếu không gửi, mặc định dùng người đang đăng nhập (Auth::id()).
            'id_nguoi_ban' => 'nullable|integer|exists:nguoi_dung,id',
            'id_khuyen_mai' => 'nullable|integer|exists:khuyen_mai,id',
            'diem_su_dung' => 'nullable|integer|min:0',
        ]);

        // CHAN: kiem tra tung variant trong cart co bi khoa boi phieu kiem kho khong
        foreach ($request->cart as $item) {
            $phieuKhoa = $kiemKhoService->phieuDangKhoaBienThe((int) $item['id']);
            if ($phieuKhoa) {
                return response()->json([
                    'success' => false,
                    'message' => "Sản phẩm ID {$item['id']} đang bị khoá bởi phiếu kiểm kho {$phieuKhoa->ma_kiem_kho}. Không thể bán.",
                ], 422);
            }
        }

        // Xác định id người bán:
        //  - Ưu tiên id_nguoi_ban từ request (do dropdown POS chọn)
        //  - Nếu không có, fallback về người đang đăng nhập
        $idNguoiBan = $request->input('id_nguoi_ban') ?: Auth::id();

        // Đảm bảo người bán tồn tại, đang hoạt động và không bị xoá mềm
        // (rule 'exists' của Laravel chỉ kiểm tra id tồn tại, không lọc theo trang_thai/deleted_at)
        $nguoiBanHopLe = DB::table('nguoi_dung')
            ->whereNull('deleted_at')
            ->where('trang_thai', 1)
            ->where('id', $idNguoiBan)
            ->exists();

        if (!$nguoiBanHopLe) {
            return response()->json([
                'success' => false,
                'message' => 'Người bán không hợp lệ hoặc đã ngừng hoạt động.',
            ], 422);
        }

        return DB::transaction(function () use ($request, $idNguoiBan) {

    $caHienTai = $this->timCaLamViecHienTai();

    if (!$caHienTai) {
        return response()->json([
            'success' => false,
            'message' => 'Không xác định được ca làm việc hiện tại.'
        ], 422);
    }

    $tongTienHang = 0;
    $items = [];

        foreach ($request->cart as $item) {
            $bienThe = DB::table('bien_the_san_pham')
                ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
                ->where('bien_the_san_pham.id', $item['id'])
                ->whereNull('bien_the_san_pham.deleted_at')
                ->whereNull('san_pham.deleted_at')
                ->select(
                    'bien_the_san_pham.*',
                    'san_pham.ten_san_pham'
                )
                ->lockForUpdate()
                ->first();

            if (!$bienThe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm.'
                ], 422);
            }

            $soLuongBan = (int) $item['qty'];
            $donViQuyDoi = null;
            $heSoQuyDoi = 1.0;
            $giaBanDonVi = (float) $bienThe->gia_ban;

            if (!empty($item['id_don_vi_quy_doi'])) {
                $donViQuyDoi = DB::table('don_vi_quy_doi')
                    ->where('id', (int) $item['id_don_vi_quy_doi'])
                    ->where('variant_id', $bienThe->id)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$donViQuyDoi) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Đơn vị quy đổi không thuộc sản phẩm đã chọn.',
                    ], 422);
                }

                $heSoQuyDoi = (float) ($donViQuyDoi->so_luong_san_pham_trong_don_vi ?: 1);
                $giaBanDonVi = (float) ($donViQuyDoi->gia_ban_quy_doi ?? 0);
                $tonTheoDonVi = $heSoQuyDoi > 0
                    ? (int) floor((float) $bienThe->so_luong_ton / $heSoQuyDoi)
                    : 0;

                if ($soLuongBan > $tonTheoDonVi) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sản phẩm "' . $bienThe->ten_san_pham . '" không đủ tồn kho theo đơn vị ' . $donViQuyDoi->ten_don_vi . '.',
                    ], 422);
                }
            } elseif ((int) $bienThe->so_luong_ton < $soLuongBan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm "' . $bienThe->ten_san_pham . '" không đủ tồn kho.'
                ], 422);
            }

            // Kho luôn quản lý theo đơn vị cơ bản; hóa đơn tính theo đơn vị khách chọn.
            $soLuongTonCanTru = max(1, (int) ceil($soLuongBan * $heSoQuyDoi));
            $thanhTien = $giaBanDonVi * $soLuongBan;
            $giaBanTheoDonViCoBan = $soLuongTonCanTru > 0
                ? $thanhTien / $soLuongTonCanTru
                : 0;
            $tongTienHang += $thanhTien;

            $items[] = [
                'bien_the' => $bienThe,
                'so_luong' => $soLuongTonCanTru,
                'so_luong_ban' => $soLuongBan,
                'gia_ban' => $giaBanTheoDonViCoBan,
                'gia_ban_don_vi' => $giaBanDonVi,
                'he_so_quy_doi' => $heSoQuyDoi,
                'ten_don_vi' => $donViQuyDoi?->ten_don_vi ?? $bienThe->ten_don_vi,
                'thanh_tien' => $thanhTien,
            ];
        }

            $tienGiamGia = 0;
            $diemSuDung = (int)($request->diem_su_dung ?? 0);
            $khuyenMaiDaApDung = [];
            // ===============================
// KHUYẾN MÃI SẢN PHẨM TỰ ĐỘNG
// ===============================
// ===============================
// KHUYẾN MÃI SẢN PHẨM TỰ ĐỘNG
// ===============================
foreach ($items as $item) {

    $idSanPham = (int) $item['bien_the']->product_id;
    $idBienThe = (int) $item['bien_the']->id;

    $soLuong = (int) ($item['so_luong_ban'] ?? $item['so_luong']);
    $giaBan = (float) ($item['gia_ban_don_vi'] ?? $item['gia_ban']);
    $thanhTien = (float) $item['thanh_tien'];

    $khuyenMaisSanPham = DB::table('khuyen_mai')
        ->join(
            'khuyen_mai_san_pham',
            'khuyen_mai.id',
            '=',
            'khuyen_mai_san_pham.id_khuyen_mai'
        )
        ->where('khuyen_mai.trang_thai', 1)
        ->where('khuyen_mai.ngay_bat_dau', '<=', now())
        ->where('khuyen_mai.ngay_ket_thuc', '>=', now())
        ->where(function ($q) use ($idSanPham, $idBienThe) {

            $q->where(function ($sub) use ($idSanPham) {
                $sub->where(
                    'khuyen_mai_san_pham.id_san_pham',
                    $idSanPham
                )
                ->whereNull(
                    'khuyen_mai_san_pham.id_bien_the_san_pham'
                );
            })

            ->orWhere(
                'khuyen_mai_san_pham.id_bien_the_san_pham',
                $idBienThe
            );
        })
        ->select('khuyen_mai.*')
        ->get();

    $giamTotNhat = 0;
    $idKhuyenMaiTotNhat = null;

    foreach ($khuyenMaisSanPham as $km) {

        $minQty = (int) ($km->so_luong_sp_toi_thieu ?? 0);

        if ($minQty > 0 && $soLuong < $minQty) {
            continue;
        }

        if (
            $tongTienHang <
            (float) ($km->don_hang_toi_thieu ?? 0)
        ) {
            continue;
        }

        $loai = Str::of((string) $km->loai_giam_gia)
            ->trim()
            ->lower()
            ->ascii()
            ->replace([' ', '-'], '_')
            ->value();

        $giaTri = (float) ($km->gia_tri_giam ?? 0);
        $giamToiDa = (float) ($km->giam_toi_da ?? 0);

        $giam = 0;

        switch ($loai) {

            case 'phan_tram':
            case 'percent':
            case 'percentage':
                $giam = $thanhTien * $giaTri / 100;
                break;

            case 'amount':
            case 'fixed':
            case 'tien_mat':
            case 'so_tien':
            case 'giam_tien':
                $giam = min(
                    $giaTri * $soLuong,
                    $thanhTien
                );
                break;

            case 'bogo':
            case 'mua_1_tang_1':
                $giam =
                    floor($soLuong / 2) *
                    $giaBan;
                break;
        }

        if ($giamToiDa > 0) {
            $giam = min(
                $giam,
                $giamToiDa
            );
        }

        $giam = min(
            max(0, $giam),
            $thanhTien
        );
        $giam = round($giam, 2);

        // Chọn KM giảm nhiều nhất
        if ($giam > $giamTotNhat) {

            $giamTotNhat = $giam;

            $idKhuyenMaiTotNhat =
                (int) $km->id;
        }
    }

    // Lưu lại KM thắng của item này
    if (
        $idKhuyenMaiTotNhat !== null &&
        $giamTotNhat > 0
    ) {

        if (!isset(
            $khuyenMaiDaApDung[$idKhuyenMaiTotNhat]
        )) {

            $khuyenMaiDaApDung[$idKhuyenMaiTotNhat] = [
                'id_khuyen_mai' =>
                    $idKhuyenMaiTotNhat,

                'tien_giam' => 0,

                'loai_ap_dung' =>
                    'san_pham',
            ];
        }

        $khuyenMaiDaApDung[
            $idKhuyenMaiTotNhat
        ]['tien_giam'] +=
            $giamTotNhat;
    }

    $tienGiamGia += $giamTotNhat;
}
            // ==========================================
// VOUCHER / KHUYẾN MÃI ĐƯỢC CHỌN TẠI POS
// ==========================================

$tienGiamVoucher = 0;
$idVoucherApDung = null;

if ($request->id_khuyen_mai) {

    $khuyenMai = DB::table('khuyen_mai')
        ->where('id', $request->id_khuyen_mai)
        ->where('trang_thai', 1)
        ->where('ngay_bat_dau', '<=', now())
        ->where('ngay_ket_thuc', '>=', now())
        ->first();

    if ($khuyenMai) {

        /*
         * Kiểm tra khuyến mãi này có gắn sản phẩm không.
         *
         * Không có dòng nào:
         * => voucher toàn hóa đơn.
         *
         * Có dòng:
         * => khuyến mãi sản phẩm.
         */
        $phamViKhuyenMai = DB::table('khuyen_mai_san_pham')
            ->where('id_khuyen_mai', $khuyenMai->id)
            ->get();


        // ======================================
        // CHỈ XỬ LÝ VOUCHER TOÀN HÓA ĐƠN Ở ĐÂY
        // ======================================

        if ($phamViKhuyenMai->isEmpty()) {

            $tongSoLuong = collect($items)
                ->sum('so_luong');

            // Voucher hóa đơn được áp sau khuyến mãi sản phẩm.
            $giaTriDuDieuKien = max(0, $tongTienHang - $tienGiamGia);

            $donHangToiThieu =
                (float) ($khuyenMai->don_hang_toi_thieu ?? 0);

            $soLuongToiThieu =
                (int) ($khuyenMai->so_luong_sp_toi_thieu ?? 0);


            /*
             * Kiểm tra điều kiện đơn hàng.
             */
            if (
                $tongTienHang >= $donHangToiThieu &&
                $tongSoLuong >= $soLuongToiThieu
            ) {

                $loaiGiamGia = Str::of(
                    (string) $khuyenMai->loai_giam_gia
                )
                    ->trim()
                    ->lower()
                    ->ascii()
                    ->replace([' ', '-'], '_')
                    ->value();


                $giaTriGiam =
                    (float) ($khuyenMai->gia_tri_giam ?? 0);

                $giamToiDa =
                    (float) ($khuyenMai->giam_toi_da ?? 0);


                switch ($loaiGiamGia) {

                    /*
                     * Giảm phần trăm toàn hóa đơn.
                     */
                    case 'phan_tram':
                    case 'percent':
                    case 'percentage':

                        $tienGiamVoucher =
                            $giaTriDuDieuKien
                            * $giaTriGiam
                            / 100;

                        break;


                    /*
                     * Giảm số tiền cố định toàn hóa đơn.
                     *
                     * Ví dụ:
                     * đơn từ 300k giảm 50k
                     */
                    case 'amount':
                    case 'fixed':
                    case 'tien_mat':
                    case 'so_tien':
                    case 'giam_tien':

                        $tienGiamVoucher =
                            $giaTriGiam;

                        break;


                    /*
                     * Nếu vẫn muốn cho BOGO ở cấp hóa đơn.
                     */
                    case 'bogo':
                    case 'mua_1_tang_1':

                        foreach ($items as $item) {

                            $freeQty =
                                floor(
                                    $item['so_luong'] / 2
                                );

                            $tienGiamVoucher +=
                                $freeQty
                                * $item['gia_ban'];
                        }

                        break;


                    default:

                        return response()->json([
                            'success' => false,
                            'message' =>
                                'Loại giảm giá không hợp lệ: '
                                . $khuyenMai->loai_giam_gia
                        ], 422);
                }


                /*
                 * Giảm tối đa.
                 */
                if ($giamToiDa > 0) {

                    $tienGiamVoucher = min(
                        $tienGiamVoucher,
                        $giamToiDa
                    );
                }


                /*
                 * Không giảm quá tổng hóa đơn.
                 */
                $tienGiamVoucher = min(
                    max(0, $tienGiamVoucher),
                    $giaTriDuDieuKien
                );

                $tienGiamVoucher = round($tienGiamVoucher, 2);
            }
        }
    }
}


/*
 * $tienGiamGia phía trên đã chứa
 * khuyến mãi sản phẩm tự động.
 *
 * Bây giờ cộng thêm voucher hóa đơn.
 */
// Ghi nhận voucher toàn hóa đơn
if (
    $request->id_khuyen_mai &&
    $tienGiamVoucher > 0
) {

    $idVoucherApDung =
        (int) $request->id_khuyen_mai;

    $khuyenMaiDaApDung[$idVoucherApDung] = [
        'id_khuyen_mai' =>
            $idVoucherApDung,

        'tien_giam' =>
            $tienGiamVoucher,

        'loai_ap_dung' =>
            'hoa_don',
    ];
}
$tienGiamGia += $tienGiamVoucher;

            $khachHang = null;

            if ($request->id_khach_hang) {
                $khachHang = KhachHang::lockForUpdate()->find($request->id_khach_hang);

                if (!$khachHang) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy khách hàng.'
                    ], 422);
                }

                $maxUsePoint = floor(max(0, $tongTienHang - $tienGiamGia) / 100);

                $diemSuDung = min(
                    $diemSuDung,
                    (int)$khachHang->diem_tich_luy,
                    (int)$maxUsePoint
                );

                $tienGiamGia += $diemSuDung * 100;
            }

            $khachCanTra = max(0, $tongTienHang - $tienGiamGia);
            $tienKhachDua = (float)$request->tien_khach_dua;
            $tienThua = max(0, $tienKhachDua - $khachCanTra);
            $diemThuDuoc = floor($khachCanTra / 10000);

            $phuongThucMap = [
                'cash' => 'Tiền mặt',
                'transfer' => 'Chuyển khoản',
                'tien_mat' => 'Tiền mặt',
                'chuyen_khoan' => 'Chuyển khoản',
                'payos' => 'PayOS',
            ];

            $phuongThucThanhToan = $phuongThucMap[$request->phuong_thuc_thanh_toan]
                ?? $request->phuong_thuc_thanh_toan;

            if (
                in_array($request->phuong_thuc_thanh_toan, ['cash', 'tien_mat']) &&
                $tienKhachDua < $khachCanTra
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tiền khách đưa chưa đủ.'
                ], 422);
            }

            $isPayOS = $request->phuong_thuc_thanh_toan === 'payos';

            $hoaDonId = DB::table('hoa_don')->insertGetId([
                'id_nguoi_dung' => $idNguoiBan,
                'id_khach_hang' => $request->id_khach_hang,
                'id_ca_lam_viec' => $caHienTai->id,
                'id_khuyen_mai' => $idVoucherApDung,
                'tong_tien_hang' => $tongTienHang,
                'tien_giam_gia' => $tienGiamGia,
                'khach_can_tra' => $khachCanTra,
                'tien_khach_dua' => $isPayOS ? $khachCanTra : $tienKhachDua,
                'tien_thua' => $isPayOS ? 0 : $tienThua,
                'phuong_thuc_thanh_toan' => $phuongThucThanhToan,
                'trang_thai' => $isPayOS ? 'Chờ thanh toán' : 'Hoàn thành',
                'diem_su_dung' => $isPayOS ? 0 : $diemSuDung,
                'diem_thu_duoc' => $isPayOS ? 0 : $diemThuDuoc,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Lưu các khuyến mãi đã áp dụng vào hóa đơn
foreach ($khuyenMaiDaApDung as $kmDaDung) {

    DB::table('hoa_don_khuyen_mai')->insert([
        'id_hoa_don' => $hoaDonId,
        'id_khuyen_mai' => $kmDaDung['id_khuyen_mai'],
        'tien_giam' => $kmDaDung['tien_giam'],
        'loai_ap_dung' => $kmDaDung['loai_ap_dung'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

            if ($khachHang) {
                if ($isPayOS) {
                    if ($diemSuDung > 0) {
                        DB::table('khach_hang')
                            ->where('id', $khachHang->id)
                            ->update([
                                'diem_tich_luy' => $khachHang->diem_tich_luy - $diemSuDung,
                                'updated_at' => now(),
                            ]);

                        DB::table('lich_su_tich_diem')->insert([
                            'id_khach_hang' => $khachHang->id,
                            'id_hoa_don' => $hoaDonId,
                            'loai_bien_dong' => 'tru',
                            'so_diem' => $diemSuDung,
                            'ly_do' => 'Sử dụng điểm thanh toán (PayOS - đang chờ)',
                            'created_at' => now(),
                        ]);
                    }
                } else {
                    $diemMoi = $khachHang->diem_tich_luy - $diemSuDung + $diemThuDuoc;

                    DB::table('khach_hang')
                        ->where('id', $khachHang->id)
                        ->update([
                            'diem_tich_luy' => $diemMoi,
                            'tong_chi_tieu' => $khachHang->tong_chi_tieu + $khachCanTra,
                            'updated_at' => now(),
                        ]);

                    if ($diemSuDung > 0) {
                        DB::table('lich_su_tich_diem')->insert([
                            'id_khach_hang' => $khachHang->id,
                            'id_hoa_don' => $hoaDonId,
                            'loai_bien_dong' => 'tru',
                            'so_diem' => $diemSuDung,
                            'ly_do' => 'Sử dụng điểm thanh toán',
                            'created_at' => now(),
                        ]);
                    }

                    if ($diemThuDuoc > 0) {
                        DB::table('lich_su_tich_diem')->insert([
                            'id_khach_hang' => $khachHang->id,
                            'id_hoa_don' => $hoaDonId,
                            'loai_bien_dong' => 'cong',
                            'so_diem' => $diemThuDuoc,
                            'ly_do' => 'Tích điểm từ hóa đơn',
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            foreach ($items as $item) {
                $variantId = (int) $item['bien_the']->id;
                $soLuongBan = (int) $item['so_luong'];

                DB::table('chi_tiet_hoa_don')->insert([
                    'id_hoa_don' => $hoaDonId,
                    'id_bien_the_san_pham' => $item['bien_the']->id,
                    'so_luong' => $item['so_luong'],
                    'gia_ban' => $item['gia_ban'],
                    'thanh_tien' => $item['thanh_tien'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (!$isPayOS) {
                    // FIFO (FEFO): trừ từ lô có HSD gần nhất trước (sắp hết hạn bán trước),
                    // đồng thời ưu tiên lô nhập trước (id nhỏ hơn) khi cùng HSD.
                    // ChiTietLoHangObserver::updated() tự đồng bộ tổng tồn bien_the_san_pham.
                    $remaining = $soLuongBan;
                    $loList = ChiTietLoHang::where('variant_id', $variantId)
                        ->where('so_luong_ton', '>', 0)
                        ->orderBy('han_su_dung', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    foreach ($loList as $lo) {
                        if ($remaining <= 0) {
                            break;
                        }
                        $truTuLo = (int) min($remaining, $lo->so_luong_ton);
                        $lo->so_luong_ton -= $truTuLo;
                        $lo->save();
                        $remaining -= $truTuLo;
                    }

                    if ($remaining > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sản phẩm "' . $item['bien_the']->ten_san_pham . '" không đủ tồn kho theo lô.',
                        ], 422);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => $isPayOS ? 'Đang tạo link thanh toán PayOS.' : 'Thanh toán thành công.',
                'hoa_don_id' => $hoaDonId,
                'redirect_to_payos' => $isPayOS,
            ]);
        });
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

   public function chiTietHoaDon($id, DoiTraService $doiTraService)
{
    // =====================================================
    // THÔNG TIN HÓA ĐƠN
    // =====================================================
    $hoaDon = DB::table('hoa_don')
        ->leftJoin(
            'khach_hang',
            'hoa_don.id_khach_hang',
            '=',
            'khach_hang.id'
        )
        ->leftJoin(
            'nguoi_dung',
            'hoa_don.id_nguoi_dung',
            '=',
            'nguoi_dung.id'
        )
        ->leftJoin(
            'ca_lam_viec',
            'hoa_don.id_ca_lam_viec',
            '=',
            'ca_lam_viec.id'
        )
        ->select(
            'hoa_don.*',
            'khach_hang.ten_khach_hang',
            'nguoi_dung.ho_ten as ten_nhan_vien',
            'ca_lam_viec.ten_ca',
            'ca_lam_viec.gio_bat_dau',
            'ca_lam_viec.gio_ket_thuc'
        )
        ->where('hoa_don.id', $id)
        ->first();

    abort_if(!$hoaDon, 404);

    // =====================================================
    // CHI TIẾT SẢN PHẨM + BIẾN THỂ
    // =====================================================
    $chiTiet = DB::table('chi_tiet_hoa_don')
        ->join(
            'bien_the_san_pham',
            'chi_tiet_hoa_don.id_bien_the_san_pham',
            '=',
            'bien_the_san_pham.id'
        )
        ->join(
            'san_pham',
            'bien_the_san_pham.product_id',
            '=',
            'san_pham.id'
        )
        ->select(
            'chi_tiet_hoa_don.*',

            'san_pham.id as id_san_pham',
            'san_pham.ten_san_pham',

            'bien_the_san_pham.id as id_bien_the',
            'bien_the_san_pham.ten_bien_the',
            'bien_the_san_pham.ten_don_vi',
            'bien_the_san_pham.ma_vach',

            $this->tenHienThiBienTheSelect()
        )
        ->where(
            'chi_tiet_hoa_don.id_hoa_don',
            $id
        )
        ->get();


    // =====================================================
    // KHUYẾN MÃI ĐÃ ÁP DỤNG
    // =====================================================
    $khuyenMaiDaApDung = DB::table('hoa_don_khuyen_mai')
        ->join(
            'khuyen_mai',
            'hoa_don_khuyen_mai.id_khuyen_mai',
            '=',
            'khuyen_mai.id'
        )
        ->where(
            'hoa_don_khuyen_mai.id_hoa_don',
            $id
        )
        ->select(
            'hoa_don_khuyen_mai.id_khuyen_mai',
            'hoa_don_khuyen_mai.tien_giam',
            'hoa_don_khuyen_mai.loai_ap_dung',

            'khuyen_mai.ten_chuong_trinh',
            'khuyen_mai.loai_giam_gia',
            'khuyen_mai.gia_tri_giam'
        )
        ->get();


    // =====================================================
    // TÁCH TIỀN GIẢM SẢN PHẨM / HÓA ĐƠN
    // =====================================================
    $giamSanPham = $khuyenMaiDaApDung
        ->where('loai_ap_dung', 'san_pham')
        ->sum('tien_giam');

    $giamHoaDon = $khuyenMaiDaApDung
        ->where('loai_ap_dung', 'hoa_don')
        ->sum('tien_giam');
// =====================================================
// TÍNH GIÁ BÁN SAU KHUYẾN MÃI SẢN PHẨM
// =====================================================
$khuyenMaiSanPham = $khuyenMaiDaApDung
    ->where('loai_ap_dung', 'san_pham');

$phamViKhuyenMai = collect();

if ($khuyenMaiSanPham->isNotEmpty()) {
    $phamViKhuyenMai = DB::table('khuyen_mai_san_pham')
        ->whereIn(
            'id_khuyen_mai',
            $khuyenMaiSanPham
                ->pluck('id_khuyen_mai')
                ->all()
        )
        ->get();
}

foreach ($chiTiet as $item) {

    // Mặc định chưa có KM thì giá sau giảm = giá bán gốc
    $item->gia_sau_giam = (float) $item->gia_ban;

    $giaBan = (float) $item->gia_ban;
    $soLuong = max(1, (int) $item->so_luong);

    $giamTotNhat = 0;

    foreach ($khuyenMaiSanPham as $km) {

        // Kiểm tra KM có áp dụng đúng sản phẩm / biến thể không
        $apDung = $phamViKhuyenMai
            ->where(
                'id_khuyen_mai',
                $km->id_khuyen_mai
            )
            ->contains(function ($row) use ($item) {

                if (
                    (int) $row->id_san_pham !==
                    (int) $item->id_san_pham
                ) {
                    return false;
                }

                // NULL = áp dụng cho tất cả biến thể của sản phẩm
                return
                    is_null($row->id_bien_the_san_pham)
                    ||
                    (int) $row->id_bien_the_san_pham ===
                    (int) $item->id_bien_the;
            });

        if (!$apDung) {
            continue;
        }

        $loai = Str::of(
            (string) $km->loai_giam_gia
        )
            ->trim()
            ->lower()
            ->ascii()
            ->replace([' ', '-'], '_')
            ->value();

        $giaTri = (float) ($km->gia_tri_giam ?? 0);

        $giam = 0;

        switch ($loai) {

            case 'phan_tram':
            case 'percent':
            case 'percentage':
                $giam = $giaBan * $giaTri / 100;
                break;

            case 'amount':
            case 'fixed':
            case 'tien_mat':
            case 'so_tien':
            case 'giam_tien':
                $giam = min(
                    $giaTri,
                    $giaBan
                );
                break;

            case 'bogo':
            case 'mua_1_tang_1':
                // BOGO không có 1 giá cố định cho từng SP,
                // nên giữ giá gốc ở cột Giá bán.
                $giam = 0;
                break;
        }

        if ($giam > $giamTotNhat) {
            $giamTotNhat = $giam;
        }
    }

    $item->gia_sau_giam = max(
        0,
        $giaBan - $giamTotNhat
    );
}

    // =====================================================
    // ĐỔI / TRẢ
    // =====================================================
    $returnSummary =
        $doiTraService->getInvoiceReturnSummary((int) $id);

    $lichSuDoiTra =
        $returnSummary['lichSuDoiTra'];

    $doiTraMoiNhat = session('last_doi_tra_id')
        ? $lichSuDoiTra->firstWhere(
            'id',
            (int) session('last_doi_tra_id')
        )
        : null;

    $tongHopDoiTra =
        $returnSummary['tongHopDoiTra'];

    $chiTietTheoBienThe =
        $returnSummary['chiTietTheoBienThe'];


    foreach ($chiTiet as $item) {

        $returnItem =
            $chiTietTheoBienThe->get(
                $item->id_bien_the_san_pham
            );

        $item->tong_da_tra =
            (int) ($returnItem->tong_tra_hang ?? 0);

        $item->tong_da_doi =
            (int) ($returnItem->tong_doi_hang ?? 0);

        $item->tong_da_doi_tra =
            (int) ($returnItem->tong_doi_tra ?? 0);
    }


    // Gắn thuộc tính biến thể
    $this->ganThuocTinhBienTheChoChiTiet(
        $chiTiet
    );


    // =====================================================
    // TRẢ VIEW
    // =====================================================
    return view(
        'ban_hang.hoa-don.chi-tiet',
        compact(
            'hoaDon',
            'chiTiet',

            'khuyenMaiDaApDung',
            'giamSanPham',
            'giamHoaDon',

            'lichSuDoiTra',
            'doiTraMoiNhat',
            'tongHopDoiTra'
        )
    )->with(
        'auto_print',
        request()->boolean('print')
    );
}

    public function chiTietDoiTra($id, DoiTraService $doiTraService)
    {
        $data = $doiTraService->getInvoiceReturnHistoryPageData((int) $id);

        return view('ban_hang.hoa-don.chi-tiet-doi-tra', $data);
    }

    public function formDoiTra($id, DoiTraService $doiTraService)
    {
        $data = $doiTraService->getInvoiceReturnData((int) $id);
        $hoaDon = $data['hoaDon'];
        $chiTiet = $data['chiTiet'];

        if (in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ'], true)) {
            return back()->with('error', 'Hóa đơn này không thể đổi/trả hàng.');
        }

        return view('ban_hang.hoa-don.doi-tra', compact('hoaDon', 'chiTiet'));

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
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->select(
                'chi_tiet_hoa_don.*',
                'san_pham.ten_san_pham',
                'bien_the_san_pham.ten_bien_the',
                'bien_the_san_pham.ten_don_vi',
                'bien_the_san_pham.ma_vach',
                DB::raw('COALESCE(bien_the_san_pham.gia_ban, chi_tiet_hoa_don.gia_ban) as gia_ban')
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

        return view('ban_hang.hoa-don.doi-tra', compact('hoaDon', 'chiTiet', 'daTra'));
    }

    public function xuLyDoiTraNhanVien(XuLyDoiTraRequest $request, $id)
    {
        $adminController = app()->make(\App\Http\Controllers\admin\BanHang\HoaDonController::class);
        $response = app()->call([$adminController, 'xuLyDoiTra'], ['request' => $request, 'id' => $id]);

        // If admin logic returned a redirect, map to staff routes and preserve flash messages
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $redirect = redirect()->route('nhan-vien.hoa-don.chi-tiet', $id);
            if (session()->has('success')) {
                return $redirect->with('success', session('success'));
            }
            if (session()->has('error')) {
                return $redirect->with('error', session('error'));
            }
            if (session()->has('message')) {
                $redirect->with('message', session('message'));
            }
            if (session()->has('last_doi_tra_id')) {
                $redirect->with('last_doi_tra_id', session('last_doi_tra_id'));
            }
            return $redirect;
        }

        return $response;
    }

    public function inHoaDon($id)
    {
        return redirect()->route('nhan-vien.hoa-don.chi-tiet', ['id' => $id, 'print' => 1]);
    }
    public function huyHoaDon($id)
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
            if ($hoaDon->id_khach_hang && $hoaDon->diem_thu_duoc > 0) {

                DB::table('khach_hang')
                    ->where('id', $hoaDon->id_khach_hang)
                    ->decrement('diem_tich_luy', $hoaDon->diem_thu_duoc);

                DB::table('lich_su_tich_diem')->insert([
                    'id_khach_hang' => $hoaDon->id_khach_hang,
                    'id_hoa_don' => $hoaDon->id,
                    'loai_bien_dong' => 'tru',
                    'so_diem' => $hoaDon->diem_thu_duoc,
                    'ly_do' => 'Hủy hóa đơn',
                    'ngay_tao' => now(),
                ]);
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
    public function layKhachHang(Request $request)
    {
        $query = KhachHang::query()
            ->where('trang_thai', 1);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('ten_khach_hang', 'like', '%' . $request->q . '%')
                    ->orWhere('so_dien_thoai', 'like', '%' . $request->q . '%')
                    ->orWhere('email', 'like', '%' . $request->q . '%');
            });
        }

        return response()->json(
            $query->select(
                'id',
                'ten_khach_hang',
                'so_dien_thoai',
                'email',
                'diem_tich_luy',
                'tong_chi_tieu'
            )
                ->orderBy('ten_khach_hang')
                ->limit(10)
                ->get()
        );
    }

    /**
     * Lấy danh sách người bán cho dropdown ở POS.
     * - Là NguoiDung có thông tin nhân sự (trang_thai = 1, không bị xoá mềm)
     * - Vai trò bất kỳ (Admin, Nhân viên, Trưởng ca, Bán hàng, ...)
     *   → Admin luôn xuất hiện, không cần phân ca
     */
    public function layNhanVienBanHang(): \Illuminate\Http\JsonResponse
    {
        $ds = DB::table('nguoi_dung')
            ->join('vai_tro', 'nguoi_dung.id_vai_tro', '=', 'vai_tro.id')
            ->whereNull('nguoi_dung.deleted_at')
            ->where('nguoi_dung.trang_thai', 1)
            ->orderBy('nguoi_dung.ho_ten')
            ->select(
                'nguoi_dung.id',
                'nguoi_dung.ho_ten',
                'nguoi_dung.email',
                'vai_tro.ten_vai_tro'
            )
            ->get();

        return response()->json($ds);
    }
    public function layKhuyenMai()
{
    $khuyenMais = DB::table('khuyen_mai')
        ->where('trang_thai', 1)
        ->where('ngay_bat_dau', '<=', now())
        ->where('ngay_ket_thuc', '>=', now())
        ->select(
            'id',
            'ten_chuong_trinh',
            'loai_giam_gia',
            'gia_tri_giam',
            'giam_toi_da',
            'so_luong_sp_toi_thieu',
            'don_hang_toi_thieu'
        )
        ->orderByDesc('id')
        ->get();

    foreach ($khuyenMais as $khuyenMai) {

        $phamVi = DB::table('khuyen_mai_san_pham')
            ->where('id_khuyen_mai', $khuyenMai->id)
            ->get();

        /*
         * Có dữ liệu trong khuyen_mai_san_pham
         * => đây là khuyến mãi sản phẩm.
         */
        $khuyenMai->la_khuyen_mai_san_pham =
            $phamVi->isNotEmpty();

        /*
         * id_bien_the_san_pham = NULL
         * => áp dụng toàn bộ biến thể của sản phẩm.
         */
        $khuyenMai->id_san_phams = $phamVi
            ->whereNull('id_bien_the_san_pham')
            ->pluck('id_san_pham')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        /*
         * Khuyến mãi chỉ áp dụng riêng biến thể.
         */
        $khuyenMai->id_bien_thes = $phamVi
            ->whereNotNull('id_bien_the_san_pham')
            ->pluck('id_bien_the_san_pham')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    return response()->json($khuyenMais);
}
}
