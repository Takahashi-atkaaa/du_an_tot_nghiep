<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\CaLamViec;
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
