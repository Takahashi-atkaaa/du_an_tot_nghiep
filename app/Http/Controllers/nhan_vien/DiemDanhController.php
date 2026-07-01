<?php

namespace App\Http\Controllers\nhan_vien;

use App\Http\Controllers\Controller;
use App\Models\DiemDanh;
use App\Models\ChiaCaLamViec;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiemDanhController extends Controller
{
    public function index(Request $request): View
    {
        $nguoiDung = auth()->user();
        $today = Carbon::today()->toDateString();

        $caHomNay = ChiaCaLamViec::with(['caLamViec', 'diemDanh'])
            ->where('id_nguoi_dung', $nguoiDung->id)
            ->where('ngay', $today)
            ->first();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $tongBuoi = DiemDanh::whereHas('chiaCaLamViec', function ($q) use ($nguoiDung) {
            $q->where('id_nguoi_dung', $nguoiDung->id);
        })
            ->whereMonth('gio_vao', $currentMonth)
            ->whereYear('gio_vao', $currentYear)
            ->count();

        $diMuon = DiemDanh::whereHas('chiaCaLamViec', function ($q) use ($nguoiDung) {
            $q->where('id_nguoi_dung', $nguoiDung->id);
        })
            ->where('trang_thai_vao_lam', 'Đi muộn')
            ->whereMonth('gio_vao', $currentMonth)
            ->whereYear('gio_vao', $currentYear)
            ->count();

        $veSom = DiemDanh::whereHas('chiaCaLamViec', function ($q) use ($nguoiDung) {
            $q->where('id_nguoi_dung', $nguoiDung->id);
        })
            ->where('trang_thai_tan_ca', 'Về sớm')
            ->whereMonth('gio_vao', $currentMonth)
            ->whereYear('gio_vao', $currentYear)
            ->count();

        $tangCa = DiemDanh::whereHas('chiaCaLamViec', function ($q) use ($nguoiDung) {
            $q->where('id_nguoi_dung', $nguoiDung->id);
        })
            ->where('trang_thai_tan_ca', 'Tăng ca')
            ->whereMonth('gio_vao', $currentMonth)
            ->whereYear('gio_vao', $currentYear)
            ->count();

        $lichSuDiemDanh = DiemDanh::with(['chiaCaLamViec.caLamViec'])
            ->whereHas('chiaCaLamViec', function ($q) use ($nguoiDung) {
                $q->where('id_nguoi_dung', $nguoiDung->id);
            })
            ->orderByDesc('gio_vao')
            ->paginate(10);

        return view('nhan_vien_view.cham-cong', compact(
            'caHomNay',
            'tongBuoi',
            'diMuon',
            'veSom',
            'tangCa',
            'lichSuDiemDanh',
            'currentMonth',
            'currentYear'
        ));
    }

    public function vaoCa(Request $request): RedirectResponse
    {
        $request->validate([
            'id_chia_ca' => 'required|integer|exists:chia_ca_lam_viec,id',
        ]);

        
        $chiaCa = ChiaCaLamViec::with('caLamViec')->find($request->id_chia_ca);

        if ($chiaCa->diemDanh) {
            return redirect()
                ->back()
                ->with('warning', 'Bạn đã điểm danh vào ca này rồi.');
        }

        $gioVao = Carbon::now();
        $gioBatDau = $chiaCa->caLamViec->gio_bat_dau;
        $soPhutTreToiDa = $chiaCa->caLamViec->so_phut_di_lam_tre_toi_da ?? 0;

        $gioBatDauParts = explode(':', $gioBatDau);
        $gioVaoParts = [$gioVao->hour, $gioVao->minute];

        $phutBatDau = (int) $gioBatDauParts[0] * 60 + (int) $gioBatDauParts[1];
        $phutVao = $gioVaoParts[0] * 60 + $gioVaoParts[1];
        $chenhLechPhut = $phutVao - $phutBatDau;

        $thoiGianChoPhepVaoSom = 5; // phút

        if ($chenhLechPhut < -$thoiGianChoPhepVaoSom) {
            return redirect()
                ->back()
                ->with('warning', 'Chưa đến giờ vào ca! Bạn chỉ có thể điểm danh trước 5 phút so với giờ bắt đầu ca.');
        }

        if ($chenhLechPhut > $soPhutTreToiDa) {
            $trangThaiVaoLam = 'Đi muộn';
            $soGioDiLamMuon = max(0, $chenhLechPhut);
        } else {
            $trangThaiVaoLam = 'Đúng giờ';
            $soGioDiLamMuon = 0;
        }

        DiemDanh::create([
            'id_chia_ca_lam_viec' => $chiaCa->id,
            'gio_vao' => $gioVao,
            'trang_thai_vao_lam' => $trangThaiVaoLam,
            'so_gio_di_lam_muon' => $soGioDiLamMuon,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Đã điểm danh vào ca thành công.');
    }

    public function ketThucCa(Request $request): RedirectResponse
    {
        $request->validate([
            'id_diem_danh' => 'required|integer|exists:diem_danh,id',
        ]);

        $diemDanh = DiemDanh::with(['chiaCaLamViec.caLamViec'])->find($request->id_diem_danh);

        if ($diemDanh->gio_tan_ca) {
            return redirect()
                ->back()
                ->with('warning', 'Bạn đã kết thúc ca làm việc rồi.');
        }

        $gioTanCa = Carbon::now();
        $gioKetThuc = $diemDanh->chiaCaLamViec->caLamViec->gio_ket_thuc;

        $gioKetThucParts = explode(':', $gioKetThuc);
        $gioTanCaParts = [$gioTanCa->hour, $gioTanCa->minute];

        $phutKetThuc = (int) $gioKetThucParts[0] * 60 + (int) $gioKetThucParts[1];
        $phutTanCa = $gioTanCaParts[0] * 60 + $gioTanCaParts[1];
        $chenhLechPhut = $phutTanCa - $phutKetThuc;

        if ($chenhLechPhut < 0) {
            $trangThaiTanCa = 'Về sớm';
            $soGioLamThem = 0;
        } elseif ($chenhLechPhut > 0) {
            $trangThaiTanCa = 'Tăng ca';
            $soGioLamThem = round($chenhLechPhut / 60, 2);
        } else {
            $trangThaiTanCa = 'Đúng giờ';
            $soGioLamThem = 0;
        }

        $diemDanh->update([
            'gio_tan_ca' => $gioTanCa,
            'trang_thai_tan_ca' => $trangThaiTanCa,
            'so_gio_lam_them' => $soGioLamThem,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Đã kết thúc ca làm việc thành công.');
    }
}
