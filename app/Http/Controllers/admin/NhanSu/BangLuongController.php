<?php

namespace App\Http\Controllers\admin\NhanSu;

use App\Http\Controllers\Controller;
use App\Models\BangLuong;
use App\Models\ChiaCaLamViec;
use App\Models\DiemDanh;
use App\Models\PhieuLuong;
use App\Models\ThietLapLuong;
use App\Http\Requests\BangLuong\StoreBangLuongRequest;
use App\Http\Requests\BangLuong\UpdateBangLuongRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BangLuongController extends Controller
{
    public function index(Request $request): View
    {
        $query = BangLuong::orderBy('created_at', 'desc');

        if ($request->filled('ky_lam_viec')) {
            $query->where('ky_lam_viec', 'like', '%' . $request->ky_lam_viec . '%');
        }

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $bangLuongs = $query->paginate(10);

        return view('admin_xem_truoc.nhan-su.bang-luong.index', compact('bangLuongs'));
    }

    public function create(): View
    {
        return view('admin_xem_truoc.nhan-su.bang-luong.create', [
            'bangLuong' => new BangLuong(),
        ]);
    }

    public function store(StoreBangLuongRequest $request): RedirectResponse
    {
        $bangLuong = BangLuong::create($request->validated());

        return redirect()
            ->route('bang-luong.show', $bangLuong)
            ->with('info', 'Bảng lương đã được tạo. Vui lòng nhấn "Tính lương" để sinh phiếu lương cho nhân viên.');
    }

    public function show(BangLuong $bangLuong): View
    {
        $bangLuong->load('phieuLuongs.nguoiDung');

        return view('admin_xem_truoc.nhan-su.bang-luong.show', compact('bangLuong'));
    }

    public function edit(BangLuong $bangLuong): View
    {
        return view('admin_xem_truoc.nhan-su.bang-luong.edit', compact('bangLuong'));
    }

    public function update(UpdateBangLuongRequest $request, BangLuong $bangLuong): RedirectResponse
    {
        $bangLuong->update($request->validated());

        return redirect()
            ->route('bang-luong.index')
            ->with('success', 'Đã cập nhật bảng lương.');
    }

    public function destroy(BangLuong $bangLuong): RedirectResponse
    {
        PhieuLuong::where('id_bang_luong', $bangLuong->id)->delete();
        $bangLuong->delete();

        return redirect()
            ->route('bang-luong.index')
            ->with('success', 'Đã xóa bảng lương.');
    }

    public function tinhLuong(BangLuong $bangLuong): RedirectResponse
    {
        $kyLamViec = $bangLuong->ky_lam_viec;
        $parts = explode('/', $kyLamViec);
        $thang = (int) $parts[0];
        $nam = (int) $parts[1];
        $startOfMonth = Carbon::create($nam, $thang, 1)->startOfMonth();
        $endOfMonth = Carbon::create($nam, $thang, 1)->endOfMonth();

        PhieuLuong::where('id_bang_luong', $bangLuong->id)->delete();

        $thietLapLuongs = ThietLapLuong::with('nguoiDung')->get();

        if ($thietLapLuongs->isEmpty()) {
            return redirect()
                ->route('bang-luong.show', $bangLuong)
                ->with('error', 'Chưa có thiết lập lương cho nhân viên nào. Vui lòng thêm thiết lập lương trước.');
        }

        $tongLuong = 0;
        $soNhanVien = 0;

        foreach ($thietLapLuongs as $thietLap) {
            $idNguoiDung = $thietLap->id_nguoi_dung;

            $soGioDiMuon = DiemDanh::whereHas('chiaCaLamViec', function ($q) use ($idNguoiDung) {
                $q->where('id_nguoi_dung', $idNguoiDung);
            })
                ->whereBetween('gio_vao', [$startOfMonth, $endOfMonth])
                ->sum('so_gio_di_lam_muon');

            $soGioTangCa = DiemDanh::whereHas('chiaCaLamViec', function ($q) use ($idNguoiDung) {
                $q->where('id_nguoi_dung', $idNguoiDung);
            })
                ->whereBetween('gio_vao', [$startOfMonth, $endOfMonth])
                ->sum('so_gio_lam_them');

            $chiCaLamViecs = ChiaCaLamViec::where('id_nguoi_dung', $idNguoiDung)
                ->whereBetween('ngay', [$startOfMonth, $endOfMonth])
                ->with('caLamViec')
                ->get();

            $tongGioLam = 0;
            foreach ($chiCaLamViecs as $chiaCa) {
                if ($chiaCa->caLamViec) {
                    $gioBatDau = Carbon::parse($chiaCa->caLamViec->gio_bat_dau);
                    $gioKetThuc = Carbon::parse($chiaCa->caLamViec->gio_ket_thuc);
                    $tongGioLam += $gioBatDau->diffInMinutes($gioKetThuc) / 60;
                }
            }

            $luongCoBan = (float) $thietLap->luong_theo_gio * $tongGioLam;
            $phuCap = (float) ($thietLap->phu_cap ?? 0);
            $thuong = (float) ($thietLap->thuong ?? 0);
            $phatDiMuon = (float) ($thietLap->phat_di_muon ?? 0) * $soGioDiMuon;
            $phatBoCa = (float) ($thietLap->phat_bo_ca ?? 0);
            $luongTangCa = (float) ($thietLap->luong_tang_ca ?? 0) * $soGioTangCa;

            $tongLuongNhanVien = $luongCoBan + $phuCap + $thuong - $phatDiMuon - $phatBoCa + $luongTangCa;
            $luongTamTinh = $luongCoBan + $phuCap;

            PhieuLuong::create([
                'id_nguoi_dung' => $idNguoiDung,
                'id_bang_luong' => $bangLuong->id,
                'luong_tam_tinh' => $luongTamTinh,
                'trang_thai' => 'Chưa chi',
                'tong_gio_lam_thuc_te' => $tongGioLam,
                'tong_luong' => $tongLuongNhanVien,
            ]);

            $tongLuong += $tongLuongNhanVien;
            $soNhanVien++;
        }

        $bangLuong->update([
            'tong_luong_tat_ca_nhan_vien' => $tongLuong,
            'so_nhan_vien' => $soNhanVien,
        ]);

        return redirect()
            ->route('bang-luong.show', $bangLuong)
            ->with('success', "Đã tính lương cho {$soNhanVien} nhân viên. Tổng lương: " . number_format($tongLuong) . 'đ');
    }
}
