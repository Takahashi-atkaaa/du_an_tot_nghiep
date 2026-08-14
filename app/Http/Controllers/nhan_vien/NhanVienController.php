<?php

namespace App\Http\Controllers\nhan_vien;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoiMatKhauRequest;
use App\Models\ChiaCaLamViec;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Services\NhanSu\NhanSuStatService;
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

   public function hoaDon(Request $request)
{
    $query = DB::table('hoa_don')
        ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
        ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
        ->leftJoin('ca_lam_viec', 'hoa_don.id_ca_lam_viec', '=', 'ca_lam_viec.id')
        ->select(
    'hoa_don.*',
    'khach_hang.ten_khach_hang',
    'nguoi_dung.ho_ten as ten_nhan_vien',
    'ca_lam_viec.ten_ca',
    'ca_lam_viec.gio_bat_dau',
    'ca_lam_viec.gio_ket_thuc'
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
    }

    if ($request->filled('trang_thai')) {
        $query->where('hoa_don.trang_thai', $request->trang_thai);
    }

    $hoaDons = $query->paginate(10)->withQueryString();

    $caLamViecs = DB::table('ca_lam_viec')
    ->orderBy('gio_bat_dau')
    ->get();
    return view('nhan_vien_view.hoa-don.index', compact('hoaDons', 'caLamViecs'));
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

        return view('nhan_vien.lich-lam-viec.xem-tuan', [
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

    public function hoSo()
    {
        $nguoiDung = auth()->user();
        $nguoiDung->load('vaiTro');

        $caGanNhat = NhanSuStatService::getCaGanNhat($nguoiDung->id);
        $caHomNay  = NhanSuStatService::getCaHomNay($nguoiDung->id);
        $hoaDonThang = NhanSuStatService::getThongKeHoaDonThang($nguoiDung->id);

        return view('nhan_vien.ho-so', [
            'nguoiDung' => $nguoiDung,
            'caGanNhat' => $caGanNhat,
            'caHomNay' => $caHomNay,
            'tongHoaDonThang' => $hoaDonThang['tong_hoa_don'],
            'tongDoanhThuThang' => $hoaDonThang['tong_doanh_thu'],
            'ngayVaoLam' => $nguoiDung->created_at,
        ]);
    }

    public function doiMatKhau(DoiMatKhauRequest $request)
    {
        $user = $request->user();
        $user->mat_khau = $request->mat_khau_moi;
        $user->save();

        return back()->with('success', 'Đổi mật khẩu thành công');
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
}
