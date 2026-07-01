<?php

namespace App\Http\Controllers\nhan_vien;

use App\Http\Controllers\Controller;
use App\Models\ChiaCaLamViec;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\DanhMucSanPham;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\KhachHang;


class NhanVienController extends Controller
{
    public function index()
    {
        return view('nhan_vien_view.dashboard');
    }

    public function banHang()
    {
        return view('nhan_vien_view.pos');
    }

   public function hoaDon(Request $request)
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
        $query->where('hoa_don.id', preg_replace('/[^0-9]/', '', $request->q));
    }

    if ($request->filled('ngay')) {
        $query->whereDate('hoa_don.created_at', $request->ngay);
    }

    if ($request->filled('trang_thai')) {
        $query->where('hoa_don.trang_thai', $request->trang_thai);
    }

    $hoaDons = $query->paginate(10)->withQueryString();

    return view('nhan_vien_view.hoa-don.index', compact('hoaDons'));
}

    public function sanPham()
    {
        return view('nhan_vien_view.san-pham.index');
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

    public function lichLamViecTuan(Request $request): View
    {
        $weekSource = $request->query('week_start');
        $weekStart = $weekSource
            ? Carbon::parse($weekSource)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);
        $weekDates = collect(range(0, 6))
            ->map(fn (int $dayOffset) => $weekStart->copy()->addDays($dayOffset));

        $nguoiDung = $this->resolvePreviewEmployee($request);

        $lichTheoTuan = ChiaCaLamViec::query()
            ->with('caLamViec')
            ->where('id_nguoi_dung', $nguoiDung->id)
            ->whereBetween('ngay', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('ngay')
            ->orderBy('id_ca_lam_viec')
            ->get();

        $lichTheoNgay = $lichTheoTuan->groupBy(fn ($lich) => Carbon::parse($lich->ngay)->toDateString());

        $ngayDaMoLich = ChiaCaLamViec::query()
            ->whereBetween('ngay', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->selectRaw('DATE(ngay) as ngay')
            ->distinct()
            ->pluck('ngay')
            ->map(fn ($ngay) => Carbon::parse($ngay)->toDateString())
            ->flip();

        $tongSoCa = $lichTheoTuan->count();
        $tongPhutLam = $lichTheoTuan->sum(function ($lich) {
            if (! $lich->caLamViec) {
                return 0;
            }

            return $this->calculateShiftMinutes(
                (string) $lich->caLamViec->gio_bat_dau,
                (string) $lich->caLamViec->gio_ket_thuc
            );
        });

        $vaiTroChinh = $lichTheoTuan->contains(fn ($lich) => ($lich->vai_tro_trong_ca ?? '') === 'truong_ca')
            ? 'Trưởng ca'
            : $this->displayRole(optional($nguoiDung->vaiTro)->ten_vai_tro);

        return view('nhan_vien_view.lich-lam-viec.xem-tuan', [
            'nguoiDung' => $nguoiDung,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'selectedWeekDate' => $weekSource
                ? Carbon::parse($weekSource)->toDateString()
                : $weekStart->toDateString(),
            'weekDates' => $weekDates,
            'lichTheoNgay' => $lichTheoNgay,
            'ngayDaMoLich' => $ngayDaMoLich,
            'tongSoCa' => $tongSoCa,
            'tongGioLam' => $this->formatHoursFromMinutes($tongPhutLam),
            'vaiTroChinh' => $vaiTroChinh,
        ]);
    }

    public function chamCong()
    {
        return view('nhan_vien_view.cham-cong');
    }

    public function hoSo()
    {
        return view('nhan_vien_view.ho-so');
    }

    private function resolvePreviewEmployee(Request $request): NguoiDung
    {
        $authUser = auth()->user();
        if ($authUser instanceof NguoiDung && ! $this->isAdminRole(optional($authUser->vaiTro)->ten_vai_tro)) {
            $authUser->loadMissing('vaiTro');

            return $authUser;
        }

        $requestedUserId = $request->integer('user_id');
        if ($requestedUserId) {
            $nguoiDung = NguoiDung::query()
                ->with('vaiTro')
                ->where('trang_thai', 1)
                ->whereHas('vaiTro', function ($query) {
                    $query->whereIn('ten_vai_tro', ['Nhân viên', 'Trưởng ca']);
                })
                ->find($requestedUserId);

            if ($nguoiDung) {
                return $nguoiDung;
            }
        }

        return NguoiDung::query()
            ->with('vaiTro')
            ->where('trang_thai', 1)
            ->whereHas('vaiTro', function ($query) {
                $query->whereIn('ten_vai_tro', ['Nhân viên', 'Trưởng ca']);
            })
            ->orderBy('ho_ten')
            ->firstOrFail();
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
    $query = SanPham::query()
        ->where('trang_thai', 1)
        ->where('so_luong_ton_kho', '>', 0);

    if ($request->filled('id_danh_muc') && $request->id_danh_muc !== 'all') {
        $query->where('id_danh_muc', $request->id_danh_muc);
    }

    if ($request->filled('q')) {
        $query->where(function ($q) use ($request) {
            $q->where('ten_san_pham', 'like', '%' . $request->q . '%')
              ->orWhere('ma_vach', 'like', '%' . $request->q . '%');
        });
    }

    return response()->json(
        $query->select(
            'id',
            'id_danh_muc',
            'ten_san_pham',
            'ma_vach',
            'gia_ban',
            'so_luong_ton_kho',
            'hinh_anh'
        )->orderBy('id', 'desc')->get()
    );
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
public function thanhToan(Request $request)
{
    $request->validate([
        'cart' => 'required|array|min:1',
        'cart.*.id' => 'required|integer|exists:san_pham,id',
        'cart.*.qty' => 'required|integer|min:1',
        'tien_khach_dua' => 'required|numeric|min:0',
        'phuong_thuc_thanh_toan' => 'required|string',
        'id_khach_hang' => 'nullable|integer|exists:khach_hang,id',
        'tien_giam_gia' => 'nullable|numeric|min:0',
    'id_khuyen_mai' => 'nullable|integer|exists:khuyen_mai,id',
        'diem_su_dung' => 'nullable|integer|min:0',
    ]);

    return DB::transaction(function () use ($request) {
        $tongTienHang = 0;
        $items = [];

        foreach ($request->cart as $item) {
            $sanPham = SanPham::lockForUpdate()->findOrFail($item['id']);

            if ($sanPham->so_luong_ton_kho < $item['qty']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm "' . $sanPham->ten_san_pham . '" không đủ tồn kho.'
                ], 422);
            }

            $thanhTien = $sanPham->gia_ban * $item['qty'];
            $tongTienHang += $thanhTien;

            $items[] = [
                'san_pham' => $sanPham,
                'so_luong' => $item['qty'],
                'gia_ban' => $sanPham->gia_ban,
                'thanh_tien' => $thanhTien,
            ];
        }

        $tienGiamGia = min((float) $request->tien_giam_gia, $tongTienHang);
        $khachCanTra = $tongTienHang - $tienGiamGia;
       $diemSuDung = (int) $request->diem_su_dung;
       // số điểm tối đa có thể dùng
        $maxUsePoint = floor($tongTienHang / 100);

        // không cho dùng quá số điểm đang có
        $diemSuDung = min(
            $diemSuDung,
            $khachHang->diem_tich_luy,
            $maxUsePoint
        );
        $tienGiamGia = 0;

        if ($request->id_khach_hang && $diemSuDung > 0) {

            $khachHang = KhachHang::lockForUpdate()->find($request->id_khach_hang);

            if (!$khachHang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy khách hàng.'
                ], 422);
            }

            if ($diemSuDung > $khachHang->diem_tich_luy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Khách không đủ điểm.'
                ], 422);
            }

            // 1 điểm = 100đ
            $tienGiamGia = $diemSuDung * 100;
        }

        $khachCanTra = max(0, $tongTienHang - $tienGiamGia);
        $tienKhachDua = $request->tien_khach_dua;
        $tienThua = max(0, $tienKhachDua - $khachCanTra);
        // 10.000 VNĐ = 1 điểm
        $diemThuDuoc = floor($khachCanTra / 10000);
        $phuongThucMap = [
        'cash' => 'Tiền mặt',
        'transfer' => 'Chuyển khoản',
         'card' => 'Quẹt thẻ',
         'tien_mat' => 'Tiền mặt',
            'chuyen_khoan' => 'Chuyển khoản',
        ];

        $phuongThucThanhToan = $phuongThucMap[$request->phuong_thuc_thanh_toan]
         ?? $request->phuong_thuc_thanh_toan;

        if ($request->phuong_thuc_thanh_toan === 'cash' && $tienKhachDua < $khachCanTra) {
            return response()->json([
                'success' => false,
                'message' => 'Tiền khách đưa chưa đủ.'
            ], 422);
        }

        $hoaDonId = DB::table('hoa_don')->insertGetId([
            'id_nguoi_dung' => auth()->user()->id,
            'id_khach_hang' => $request->id_khach_hang,
            'id_ca_lam_viec' => null,
            'id_khuyen_mai' => $request->id_khuyen_mai,
            'tong_tien_hang' => $tongTienHang,
            'tien_giam_gia' => $tienGiamGia,
            'khach_can_tra' => $khachCanTra,
            'tien_khach_dua' => $tienKhachDua,
            'tien_thua' => $tienThua,
            'phuong_thuc_thanh_toan' => $phuongThucThanhToan,
            'trang_thai' => 'Hoàn thành',
            'diem_su_dung' => $diemSuDung,
            'diem_thu_duoc' => $diemThuDuoc,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
// Cộng điểm cho khách hàng
if ($request->id_khach_hang) {

    $khachHang = KhachHang::lockForUpdate()
        ->find($request->id_khach_hang);

    if ($khachHang) {

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
        // Lưu lịch sử tích điểm
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
        foreach ($items as $item) {
            DB::table('chi_tiet_hoa_don')->insert([
                'id_hoa_don' => $hoaDonId,
                'id_san_pham' => $item['san_pham']->id,
                'id_chi_tiet_phieu' => null,
                'so_luong' => $item['so_luong'],
                'gia_ban' => $item['gia_ban'],
                'thanh_tien' => $item['thanh_tien'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $item['san_pham']->decrement('so_luong_ton_kho', $item['so_luong']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán thành công.',
            'hoa_don_id' => $hoaDonId,
        ]);
    });
}
public function chiTietHoaDon($id)
{
    $hoaDon = DB::table('hoa_don')
        ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
        ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
        ->select(
            'hoa_don.*',
            'khach_hang.ten_khach_hang',
            'nguoi_dung.ho_ten as ten_nhan_vien'
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

    return view('nhan_vien_view.hoa-don.chi-tiet', compact('hoaDon', 'chiTiet'));
}

public function inHoaDon($id)
{
    return $this->chiTietHoaDon($id);
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
            DB::table('san_pham')
                ->where('id', $item->id_san_pham)
                ->increment('so_luong_ton_kho', $item->so_luong);
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
public function layKhuyenMai()
{
    return response()->json(
        DB::table('khuyen_mai')
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
            ->get()
    );
}
}
