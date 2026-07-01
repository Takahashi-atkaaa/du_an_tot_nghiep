<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
use App\Models\ChiaCaLamViec;
use App\Models\DiemDanh;
use App\Models\NguoiDung;
use App\Models\VaiTro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiemDanhController extends Controller
{
    public function index(Request $request): View
    {
        $ngay = $request->filled('ngay') ? $request->ngay : now()->format('Y-m-d');

        $query = ChiaCaLamViec::with(['nguoiDung', 'caLamViec', 'diemDanh'])
            ->where('ngay', $ngay)
            ->orderBy('id_ca_lam_viec');

        if ($request->filled('id_ca_lam_viec')) {
            $query->where('id_ca_lam_viec', $request->id_ca_lam_viec);
        }

        if ($request->filled('id_nguoi_dung')) {
            $query->where('id_nguoi_dung', $request->id_nguoi_dung);
        }

        if ($request->filled('trang_thai')) {
            $query->whereHas('diemDanh', function ($q) use ($request) {
                $q->where('trang_thai_vao_lam', $request->trang_thai);
            });
        }

        $danhSachChiaCa = $query->get();
        $caLamViecs = CaLamViec::orderBy('gio_bat_dau')->get();
        $nhanViens = NguoiDung::where('id_vai_tro', VaiTro::where('ten_vai_tro', 'Nhân viên')->first()?->id)
            ->orderBy('ho_ten')
            ->get();

        $tongNhanVien = $danhSachChiaCa->count();
        $daDiemDanh = $danhSachChiaCa->filter(fn($item) => $item->diemDanh)->count();
        $chuaDiemDanh = $tongNhanVien - $daDiemDanh;
        $diMuon = $danhSachChiaCa->filter(fn($item) => $item->diemDanh && $item->diemDanh->trang_thai_vao_lam === 'Đi muộn')->count();

        return view('admin_xem_truoc.nhan-su.diem-danh.index', compact(
            'danhSachChiaCa',
            'caLamViecs',
            'nhanViens',
            'ngay',
            'tongNhanVien',
            'daDiemDanh',
            'chuaDiemDanh',
            'diMuon'
        ));
    }

    public function xacNhanDiemDanh(ChiaCaLamViec $chiaCaLamViec): RedirectResponse
    {
        if ($chiaCaLamViec->diemDanh) {
            return redirect()
                ->back()
                ->with('warning', 'Nhân viên này đã được xác nhận điểm danh rồi.');
        }

        $caLamViec = $chiaCaLamViec->caLamViec;
        $gioVao = now();
        $gioBatDau = $caLamViec->gio_bat_dau;
        $soPhutTreToiDa = $caLamViec->so_phut_di_lam_tre_toi_da ?? 0;

        $gioBatDauParts = explode(':', $gioBatDau);
        $gioVaoParts = [$gioVao->hour, $gioVao->minute, $gioVao->second];

        $phutBatDau = $gioBatDauParts[0] * 60 + $gioBatDauParts[1];
        $phutVao = $gioVaoParts[0] * 60 + $gioVaoParts[1];
        $chenhLechPhut = $phutVao - $phutBatDau;

        if ($chenhLechPhut > $soPhutTreToiDa) {
            $trangThaiVaoLam = 'Đi muộn';
            $soGioDiLamMuon = max(0, $chenhLechPhut);
        } else {
            $trangThaiVaoLam = 'Đúng giờ';
            $soGioDiLamMuon = 0;
        }

        DiemDanh::create([
            'id_chia_ca_lam_viec' => $chiaCaLamViec->id,
            'gio_vao' => $gioVao,
            'trang_thai_vao_lam' => $trangThaiVaoLam,
            'so_gio_di_lam_muon' => $soGioDiLamMuon,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Đã xác nhận điểm danh cho nhân viên.');
    }

    public function ketThucCa(ChiaCaLamViec $chiaCaLamViec): RedirectResponse
    {
        $diemDanh = $chiaCaLamViec->diemDanh;

        if (!$diemDanh) {
            return redirect()
                ->back()
                ->with('warning', 'Chưa xác nhận điểm danh. Vui lòng xác nhận điểm danh trước.');
        }

        if ($diemDanh->gio_tan_ca) {
            return redirect()
                ->back()
                ->with('warning', 'Ca đã được kết thúc rồi.');
        }

        $caLamViec = $chiaCaLamViec->caLamViec;
        $gioTanCa = now();
        $gioKetThuc = $caLamViec->gio_ket_thuc;

        $gioKetThucParts = explode(':', $gioKetThuc);
        $gioTanCaParts = [$gioTanCa->hour, $gioTanCa->minute, $gioTanCa->second];

        $phutKetThuc = $gioKetThucParts[0] * 60 + $gioKetThucParts[1];
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
            ->with('success', 'Đã kết thúc ca cho nhân viên.');
    }

    public function huyDiemDanh(DiemDanh $diemDanh): RedirectResponse
    {
        $diemDanh->delete();

        return redirect()
            ->back()
            ->with('success', 'Đã hủy điểm danh.');
    }

    public function lichSu(Request $request): View
    {
        $query = DiemDanh::with(['chiaCaLamViec.nguoiDung', 'chiaCaLamViec.caLamViec'])
            ->whereHas('chiaCaLamViec', function ($q) {
                $q->whereNotNull('id');
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('tu_ngay')) {
            $query->whereDate('gio_vao', '>=', $request->tu_ngay);
        }

        if ($request->filled('den_ngay')) {
            $query->whereDate('gio_vao', '<=', $request->den_ngay);
        }

        if ($request->filled('id_nguoi_dung')) {
            $query->whereHas('chiaCaLamViec', fn($q) => $q->where('id_nguoi_dung', $request->id_nguoi_dung));
        }

        if ($request->filled('id_ca_lam_viec')) {
            $query->whereHas('chiaCaLamViec', fn($q) => $q->where('id_ca_lam_viec', $request->id_ca_lam_viec));
        }

        if ($request->filled('trang_thai_vao_lam')) {
            $query->where('trang_thai_vao_lam', $request->trang_thai_vao_lam);
        }

        if ($request->filled('trang_thai_tan_ca')) {
            $query->where('trang_thai_tan_ca', $request->trang_thai_tan_ca);
        }

        $diemDanhs = $query->paginate(10);
        $caLamViecs = CaLamViec::orderBy('gio_bat_dau')->get();
        $nhanViens = NguoiDung::where('id_vai_tro', VaiTro::where('ten_vai_tro', 'Nhân viên')->first()?->id)
            ->orderBy('ho_ten')
            ->get();

        return view('admin_xem_truoc.nhan-su.diem-danh.lich-su', compact(
            'diemDanhs',
            'caLamViecs',
            'nhanViens'
        ));
    }

    public function thongKe(Request $request): View
    {
        $thang = $request->filled('thang') ? $request->thang : now()->format('Y-m');
        $nam = $request->filled('nam') ? $request->nam : now()->year;

        $diemDanhs = DiemDanh::with(['chiaCaLamViec.nguoiDung', 'chiaCaLamViec.caLamViec'])
            ->whereYear('gio_vao', $nam)
            ->whereMonth('gio_vao', explode('-', $thang)[1])
            ->get();

        $tongBuoi = $diemDanhs->count();
        $diMuon = $diemDanhs->where('trang_thai_vao_lam', 'Đi muộn')->count();
        $veSom = $diemDanhs->where('trang_thai_tan_ca', 'Về sớm')->count();
        $tangCa = $diemDanhs->where('trang_thai_tan_ca', 'Tăng ca')->count();
        $tongGioLamThem = $diemDanhs->sum('so_gio_lam_them');
        $tongPhutDiMuon = $diemDanhs->sum('so_gio_di_lam_muon');

        $theoNhanVien = $diemDanhs
            ->groupBy(fn($item) => $item->chiaCaLamViec->id_nguoi_dung)
            ->map(fn($group, $idNguoiDung) => [
                'ho_ten' => $group->first()->chiaCaLamViec->nguoiDung->ho_ten ?? 'N/A',
                'tong_buoi' => $group->count(),
                'di_muon' => $group->where('trang_thai_vao_lam', 'Đi muộn')->count(),
                've_som' => $group->where('trang_thai_tan_ca', 'Về sớm')->count(),
                'tang_ca' => $group->where('trang_thai_tan_ca', 'Tăng ca')->count(),
                'tong_gio_lam_them' => $group->sum('so_gio_lam_them'),
            ])
            ->values();

        return view('admin_xem_truoc.nhan-su.diem-danh.thong-ke', compact(
            'thang',
            'nam',
            'tongBuoi',
            'diMuon',
            'veSom',
            'tangCa',
            'tongGioLamThem',
            'tongPhutDiMuon',
            'theoNhanVien'
        ));
    }
}
