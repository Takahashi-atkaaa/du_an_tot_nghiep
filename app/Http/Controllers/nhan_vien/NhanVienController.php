<?php

namespace App\Http\Controllers\nhan_vien;

use App\Http\Controllers\Controller;
use App\Models\ChiaCaLamViec;
use App\Models\NguoiDung;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
}
