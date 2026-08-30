<?php

namespace App\Http\Controllers\Admin\KiemKho;

use App\Http\Controllers\Controller;
use App\Http\Requests\KiemKho\HistoryFilterRequest;
use App\Http\Requests\KiemKho\HuyPhieuRequest;
use App\Http\Requests\KiemKho\StoreKiemKhoRequest;
use App\Http\Requests\KiemKho\UpdateKiemKhoRequest;
use App\Models\DanhMucSanPham;
use App\Models\NguoiDung;
use App\Models\PhieuKiemKho;
use App\Services\KiemKhoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KiemKhoController extends Controller
{
    public function index(HistoryFilterRequest $request)
    {
        $query = PhieuKiemKho::with(['nguoiTao', 'nguoiKiem', 'nguoiDuyet'])
            ->orderByDesc('created_at');

        if ($request->filled('ma_phieu')) {
            $query->where('ma_kiem_kho', 'like', '%' . $request->ma_phieu . '%');
        }
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }
        if ($request->filled('tu_ngay')) {
            $query->whereDate('ngay_kiem', '>=', $request->tu_ngay);
        }
        if ($request->filled('den_ngay')) {
            $query->whereDate('ngay_kiem', '<=', $request->den_ngay);
        }
        if ($request->filled('id_nguoi_kiem')) {
            $query->where('id_nguoi_kiem', $request->id_nguoi_kiem);
        }

        $perPage = (int) ($request->per_page ?? 15);
        $phieus = $query->paginate($perPage)->withQueryString();

        $dsTrangThai = [
            'phieu_tam' => 'Phiếu tạm',
            'counting' => 'Đang đếm',
            'cho_duyet' => 'Chờ duyệt',
            'da_duyet' => 'Đã duyệt',
            'hoan_thanh' => 'Hoàn thành',
            'tu_choi' => 'Từ chối',
            'da_huy' => 'Đã hủy',
        ];

        $dsNguoiKiem = NguoiDung::orderBy('ho_ten')->get(['id', 'ho_ten', 'email']);

        return view('admin_xem_truoc.kiem_kho.index', compact('phieus', 'dsTrangThai', 'dsNguoiKiem'));
    }

    public function create()
    {
        $dsNguoiDung = NguoiDung::orderBy('ho_ten')->get(['id', 'ho_ten', 'email']);
        $dsDanhMuc = DanhMucSanPham::orderBy('ten_danh_muc')->get(['id', 'ten_danh_muc']);

        return view('admin_xem_truoc.kiem_kho.create', compact('dsNguoiDung', 'dsDanhMuc'));
    }

    public function store(StoreKiemKhoRequest $request, KiemKhoService $service)
    {
        try {
            $phieu = $service->taoPhieu($request->validated(), Auth::user());

            return redirect()
                ->route('kiem-kho.show', $phieu->id)
                ->with('success', "Đã tạo phiếu kiểm kho {$phieu->ma_kiem_kho}");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $phieu = PhieuKiemKho::with([
            'nguoiTao',
            'nguoiKiem',
            'nguoiDuyet',
            'chiTietKiemKho' => fn ($q) => $q->orderBy('ten_san_pham'),
        ])->findOrFail($id);

        // Lay thong ke
        $thongKe = [
            'tong_so_san_pham' => $phieu->tong_so_san_pham,
            'so_sp_dung' => $phieu->so_sp_dung,
            'so_sp_thieu' => $phieu->so_sp_thieu,
            'so_sp_thua' => $phieu->so_sp_thua,
            'so_sp_chua_dem' => $phieu->tong_so_san_pham - $phieu->so_sp_dung - $phieu->so_sp_thieu - $phieu->so_sp_thua,
            'tong_sl_lech' => $phieu->tong_sl_lech,
            'tong_gia_tri_lech' => $phieu->tong_gia_tri_lech,
        ];

        return view('admin_xem_truoc.kiem_kho.show', compact('phieu', 'thongKe'));
    }

    public function dem($id)
    {
        $phieu = PhieuKiemKho::with([
            'nguoiTao',
            'nguoiKiem',
            'chiTietKiemKho' => fn ($q) => $q->orderBy('ten_san_pham'),
        ])->findOrFail($id);

        $thongKe = [
            'tong_so_san_pham' => (int) ($phieu->tong_so_san_pham ?? 0),
            'so_sp_dung' => (int) ($phieu->so_sp_dung ?? 0),
            'so_sp_thieu' => (int) ($phieu->so_sp_thieu ?? 0),
            'so_sp_thua' => (int) ($phieu->so_sp_thua ?? 0),
            'so_sp_chua_dem' => (int) ($phieu->tong_so_san_pham ?? 0) - (int) ($phieu->so_sp_dung ?? 0) - (int) ($phieu->so_sp_thieu ?? 0) - (int) ($phieu->so_sp_thua ?? 0),
            'tong_sl_lech' => (int) ($phieu->tong_sl_lech ?? 0),
        ];

        $items = [];
        foreach ($phieu->chiTietKiemKho as $ct) {
            $items[$ct->id] = [
                'so_luong_he_thong' => (int) $ct->so_luong_he_thong,
                'so_luong_thuc_te' => $ct->so_luong_thuc_te !== null ? (int) $ct->so_luong_thuc_te : null,
                'ly_do' => $ct->ly_do ?? '',
                'chenh_lech' => (int) $ct->so_luong_lech,
                'da_dem' => $ct->so_luong_thuc_te !== null,
                'edited' => false,
            ];
        }
        $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);

        return view('admin_xem_truoc.kiem_kho.dem', compact('phieu', 'thongKe', 'itemsJson'));
    }

    public function edit($id)
    {
        $phieu = PhieuKiemKho::findOrFail($id);

        if (!$phieu->co_the_sua) {
            return back()->with('error', 'Chỉ sửa được phiếu ở trạng thái "Phiếu tạm".');
        }

        $dsNguoiDung = NguoiDung::orderBy('ho_ten')->get(['id', 'ho_ten', 'email']);
        $dsDanhMuc = DanhMucSanPham::orderBy('ten_danh_muc')->get(['id', 'ten_danh_muc']);

        return view('admin_xem_truoc.kiem_kho.edit', compact('phieu', 'dsNguoiDung', 'dsDanhMuc'));
    }

    public function update(UpdateKiemKhoRequest $request, $id, KiemKhoService $service)
    {
        try {
            $service->updatePhieu($id, $request->validated());
            return redirect()->route('kiem-kho.show', $id)->with('success', 'Đã cập nhật phiếu.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $phieu = PhieuKiemKho::findOrFail($id);
        $phieu->delete();

        return redirect()->route('kiem-kho.index')->with('success', 'Đã chuyển phiếu vào thùng rác.');
    }

    public function trash(Request $request)
    {
        $phieus = PhieuKiemKho::onlyTrashed()
            ->with(['nguoiTao', 'nguoiKiem'])
            ->orderByDesc('deleted_at')
            ->paginate(15);

        return view('admin_xem_truoc.kiem_kho.trash', compact('phieus'));
    }

    public function restore($id, KiemKhoService $service)
    {
        try {
            $service->khoiPhuc($id);
            return redirect()->route('kiem-kho.trash')->with('success', 'Đã khôi phục phiếu.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        $phieu = PhieuKiemKho::withTrashed()->findOrFail($id);

        // Xoa cascade khoa_kiem_kho
        \App\Models\KhoaKiemKho::where('id_phieu_kiem_kho', $phieu->id)->delete();
        $phieu->forceDelete();

        return redirect()->route('kiem-kho.trash')->with('success', 'Đã xóa vĩnh viễn phiếu.');
    }

    public function print($id)
    {
        $phieu = PhieuKiemKho::with([
            'nguoiTao',
            'nguoiKiem',
            'nguoiDuyet',
            'chiTietKiemKho' => fn ($q) => $q->orderBy('ten_san_pham'),
        ])->findOrFail($id);

        return view('admin_xem_truoc.kiem_kho.print', compact('phieu'));
    }

    public function baoCao(Request $request)
    {
        $tuNgay = $request->tu_ngay;
        $denNgay = $request->den_ngay;

        $query = PhieuKiemKho::where('trang_thai', 'hoan_thanh');
        if ($tuNgay) {
            $query->whereDate('hoan_thanh_luc', '>=', $tuNgay);
        }
        if ($denNgay) {
            $query->whereDate('hoan_thanh_luc', '<=', $denNgay);
        }
        $phieus = $query->with(['nguoiKiem'])->orderByDesc('hoan_thanh_luc')->get();

        $tongHop = [
            'tong_phieu' => $phieus->count(),
            'tong_san_pham_kiem' => (int) $phieus->sum('tong_so_san_pham'),
            'tong_sp_thieu' => (int) $phieus->sum('so_sp_thieu'),
            'tong_sp_thua' => (int) $phieus->sum('so_sp_thua'),
            'tong_sp_dung' => (int) $phieus->sum('so_sp_dung'),
            'tong_sl_thieu' => abs((int) $phieus->where('tong_sl_lech', '<', 0)->sum('tong_sl_lech')),
            'tong_sl_thua' => (int) $phieus->where('tong_sl_lech', '>', 0)->sum('tong_sl_lech'),
            'tong_gia_tri_lech' => (float) $phieus->sum('tong_gia_tri_lech'),
        ];

        return view('admin_xem_truoc.kiem_kho.bao-cao', compact('phieus', 'tongHop', 'tuNgay', 'denNgay'));
    }
}