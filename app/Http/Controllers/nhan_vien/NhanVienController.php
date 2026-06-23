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

    public function hoaDon()
    {
        return view('nhan_vien_view.hoa-don.index');
    }

    public function sanPham()
    {
        return view('nhan_vien_view.san-pham.index');
    }

    public function khachHang()
    {
        return view('nhan_vien_view.khach-hang.index');
    }

    public function lichLamViec(Request $request)
    {
        return $this->lichLamViecTuan($request);
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

        $tienGiamGia = 0;
        $khachCanTra = $tongTienHang - $tienGiamGia;
        $tienKhachDua = $request->tien_khach_dua;
        $tienThua = max(0, $tienKhachDua - $khachCanTra);

        if ($request->phuong_thuc_thanh_toan === 'cash' && $tienKhachDua < $khachCanTra) {
            return response()->json([
                'success' => false,
                'message' => 'Tiền khách đưa chưa đủ.'
            ], 422);
        }

        $hoaDonId = DB::table('hoa_don')->insertGetId([
            'id_nguoi_dung' => 1,
            'id_khach_hang' => null,
            'id_ca_lam_viec' => null,
            'id_khuyen_mai' => null,
            'tong_tien_hang' => $tongTienHang,
            'tien_giam_gia' => $tienGiamGia,
            'khach_can_tra' => $khachCanTra,
            'tien_khach_dua' => $tienKhachDua,
            'tien_thua' => $tienThua,
            'phuong_thuc_thanh_toan' => $request->phuong_thuc_thanh_toan,
            'trang_thai' => 'Hoàn thành',
            'diem_su_dung' => 0,
            'diem_thu_duoc' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
}
