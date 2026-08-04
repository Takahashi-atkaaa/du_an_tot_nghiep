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
    $bayGio = Carbon::now();
    $today = $bayGio->toDateString();

    $danhSachCaHomNay = ChiaCaLamViec::with([
            'caLamViec',
            'diemDanh',
        ])
        ->where('id_nguoi_dung', $nguoiDung->id)
        ->whereDate('ngay', $today)
        ->whereNull('deleted_at')
        ->get();

    $caHomNay = $danhSachCaHomNay->first(function ($chiaCa) {
        return $chiaCa->diemDanh
            && $chiaCa->diemDanh->gio_vao
            && !$chiaCa->diemDanh->gio_tan_ca;
    });

    if (!$caHomNay) {
        $caHomNay = $danhSachCaHomNay->first(function ($chiaCa) use ($bayGio) {
            if (!$chiaCa->caLamViec) {
                return false;
            }

            $ngayLamViec = Carbon::parse($chiaCa->ngay)
                ->toDateString();

            $gioBatDau = Carbon::parse(
                $ngayLamViec . ' ' .
                $chiaCa->caLamViec->gio_bat_dau
            );

            $gioKetThuc = Carbon::parse(
                $ngayLamViec . ' ' .
                $chiaCa->caLamViec->gio_ket_thuc
            );

            if ($gioKetThuc->lessThanOrEqualTo($gioBatDau)) {
                $gioKetThuc->addDay();
            }

            return $bayGio->betweenIncluded(
                $gioBatDau->copy()->subMinutes(5),
                $gioKetThuc
            );
        });
    }

    if (!$caHomNay) {
        $caHomNay = $danhSachCaHomNay
            ->filter(function ($chiaCa) use ($bayGio) {
                if (!$chiaCa->caLamViec) {
                    return false;
                }

                $gioBatDau = Carbon::parse(
                    Carbon::parse($chiaCa->ngay)->toDateString()
                    . ' '
                    . $chiaCa->caLamViec->gio_bat_dau
                );

                return $gioBatDau->greaterThan($bayGio);
            })
            ->sortBy(function ($chiaCa) {
                return Carbon::parse(
                    Carbon::parse($chiaCa->ngay)->toDateString()
                    . ' '
                    . $chiaCa->caLamViec->gio_bat_dau
                )->timestamp;
            })
            ->first();
    }
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

        return view('nhan_vien.cham-cong', compact(
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
        'id_chia_ca' => [
            'required',
            'integer',
            'exists:chia_ca_lam_viec,id',
        ],
    ]);

    $chiaCa = ChiaCaLamViec::with('caLamViec')
        ->find($request->id_chia_ca);

    if (!$chiaCa || !$chiaCa->caLamViec) {
        return redirect()
            ->back()
            ->with('warning', 'Không tìm thấy thông tin ca làm việc.');
    }

    // Không được điểm danh ca của người khác
    if ((int) $chiaCa->id_nguoi_dung !== (int) auth()->id()) {
        abort(403, 'Ca làm việc này không thuộc về bạn.');
    }

    /*
     * Tìm đúng bản điểm danh đang mở.
     * Không sử dụng $chiaCa->diemDanh vì có thể lấy bản cũ.
     */
    $diemDanhDangMo = DiemDanh::query()
        ->where('id_chia_ca_lam_viec', $chiaCa->id)
        ->whereNotNull('gio_vao')
        ->whereNull('gio_tan_ca')
        ->whereNull('deleted_at')
        ->orderByDesc('id')
        ->first();

    /*
     * Nếu đã bắt đầu ca rồi thì chỉ khôi phục session
     * và chuyển sang bán hàng, không tạo dòng mới.
     */
    if ($diemDanhDangMo) {
        session([
            'id_ca_lam_viec' => $chiaCa->id_ca_lam_viec,
            'id_chia_ca_lam_viec' => $chiaCa->id,
            'id_diem_danh' => $diemDanhDangMo->id,
            'ten_ca_lam_viec' => $chiaCa->caLamViec->ten_ca,
        ]);

        return redirect()
            ->route('nhan-vien.ban-hang')
            ->with('success', 'Bạn đang trong ca làm việc.');
    }

    $gioVao = Carbon::now();

    // Chỉ lấy phần ngày, tránh lỗi bị ghép hai lần thời gian
    $ngayLamViec = Carbon::parse($chiaCa->ngay)
        ->toDateString();

    $gioBatDau = Carbon::parse(
        $ngayLamViec . ' ' .
        $chiaCa->caLamViec->gio_bat_dau
    );

    $gioKetThuc = Carbon::parse(
        $ngayLamViec . ' ' .
        $chiaCa->caLamViec->gio_ket_thuc
    );

    // Xử lý ca qua đêm, ví dụ TO1: 19:00 - 01:00
    if ($gioKetThuc->lessThanOrEqualTo($gioBatDau)) {
        $gioKetThuc->addDay();
    }

    $thoiGianChoPhepVaoSom = 5;

    $thoiDiemSomNhat = $gioBatDau
        ->copy()
        ->subMinutes($thoiGianChoPhepVaoSom);

    if ($gioVao->lessThan($thoiDiemSomNhat)) {
        return redirect()
            ->back()
            ->with(
                'warning',
                'Chưa đến giờ vào ca! Bạn chỉ có thể điểm danh trước 5 phút.'
            );
    }

    if ($gioVao->greaterThan($gioKetThuc)) {
        return redirect()
            ->back()
            ->with(
                'warning',
                'Ca làm việc này đã kết thúc.'
            );
    }

    $soPhutTreToiDa = (int) (
        $chiaCa->caLamViec
            ->so_phut_di_lam_tre_toi_da ?? 0
    );

    $soPhutDiMuon = $gioVao->greaterThan($gioBatDau)
        ? $gioBatDau->diffInMinutes($gioVao)
        : 0;

    if ($soPhutDiMuon > $soPhutTreToiDa) {
        $trangThaiVaoLam = 'Đi muộn';
    } else {
        $trangThaiVaoLam = 'Đúng giờ';
        $soPhutDiMuon = 0;
    }

    $diemDanh = DiemDanh::create([
        'id_chia_ca_lam_viec' => $chiaCa->id,
        'gio_vao' => $gioVao,
        'gio_tan_ca' => null,
        'trang_thai_vao_lam' => $trangThaiVaoLam,
        'so_gio_di_lam_muon' => $soPhutDiMuon,
        'so_gio_lam_them' => 0,
    ]);

    session([
        'id_ca_lam_viec' => $chiaCa->id_ca_lam_viec,
        'id_chia_ca_lam_viec' => $chiaCa->id,
        'id_diem_danh' => $diemDanh->id,
        'ten_ca_lam_viec' => $chiaCa->caLamViec->ten_ca,
    ]);

    return redirect()
        ->route('nhan-vien.ban-hang')
        ->with(
            'success',
            'Bắt đầu ca làm việc thành công.'
        );
}

    public function ketThucCa(Request $request): RedirectResponse
{
    $request->validate([
        'id_diem_danh' => [
            'required',
            'integer',
            'exists:diem_danh,id',
        ],
    ]);

    $diemDanh = DiemDanh::with([
        'chiaCaLamViec.caLamViec',
    ])->find($request->id_diem_danh);

    if (
        !$diemDanh ||
        !$diemDanh->chiaCaLamViec ||
        !$diemDanh->chiaCaLamViec->caLamViec
    ) {
        return redirect()
            ->back()
            ->with('warning', 'Không tìm thấy thông tin ca làm việc.');
    }

    /*
     * Không cho người khác kết thúc ca của nhân viên khác.
     */
    if (
        (int) $diemDanh->chiaCaLamViec->id_nguoi_dung
        !== (int) auth()->id()
    ) {
        abort(403, 'Bạn không thể kết thúc ca của người khác.');
    }

    if ($diemDanh->gio_tan_ca) {
        return redirect()
            ->back()
            ->with('warning', 'Bạn đã kết thúc ca làm việc rồi.');
    }

    $gioTanCa = Carbon::now();

    $ngayPhanCa = $diemDanh->chiaCaLamViec->ngay;
    $caLamViec = $diemDanh->chiaCaLamViec->caLamViec;

    $gioBatDau = Carbon::parse(
        $ngayPhanCa . ' ' . $caLamViec->gio_bat_dau
    );

    $gioKetThuc = Carbon::parse(
        $ngayPhanCa . ' ' . $caLamViec->gio_ket_thuc
    );

    if ($gioKetThuc->lessThanOrEqualTo($gioBatDau)) {
        $gioKetThuc->addDay();
    }

    if ($gioTanCa->lessThan($gioKetThuc)) {
        $trangThaiTanCa = 'Về sớm';
        $soGioLamThem = 0;
    } elseif ($gioTanCa->greaterThan($gioKetThuc)) {
        $trangThaiTanCa = 'Tăng ca';

        $soGioLamThem = round(
            $gioKetThuc->diffInMinutes($gioTanCa) / 60,
            2
        );
    } else {
        $trangThaiTanCa = 'Đúng giờ';
        $soGioLamThem = 0;
    }

    $diemDanh->update([
        'gio_tan_ca' => $gioTanCa,
        'trang_thai_tan_ca' => $trangThaiTanCa,
        'so_gio_lam_them' => $soGioLamThem,
    ]);

    /*
     * Kết thúc ca thì xóa thông tin ca khỏi session.
     */
    session()->forget([
        'id_ca_lam_viec',
        'id_chia_ca_lam_viec',
        'id_diem_danh',
        'ten_ca_lam_viec',
    ]);

    return redirect()
        ->route('nhan-vien.cham-cong')
        ->with('success', 'Đã kết thúc ca làm việc thành công.');
}
}
