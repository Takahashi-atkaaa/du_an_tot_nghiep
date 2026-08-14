<?php

namespace App\Http\Controllers\admin\KhoHang;

use App\Http\Controllers\Controller;
use App\Models\HangLoi;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HangLoiController extends Controller
{
    public function index(Request $request)
    {
        $schemaReady = Schema::hasTable('hang_loi');
        $migrationMessage = null;

        if (!$schemaReady) {
            $migrationMessage = 'Bảng hàng lỗi chưa được tạo. Vui lòng chạy migration trước khi sử dụng màn hình này.';
            $hangLois = new LengthAwarePaginator([], 0, 15, 1, [
                'path' => route('admin.hang-loi.index'),
                'pageName' => 'page',
            ]);

            return view('admin_xem_truoc.hang-loi.index', compact('hangLois', 'schemaReady', 'migrationMessage'));
        }

        $query = HangLoi::query()
            ->with([
                'doiTra.nguoiDung.vaiTro',
                'doiTra.hoaDon',
                'bienTheSanPham.product',
                'nguoiDungTieuHuy',
            ])
            ->orderByRaw("CASE WHEN trang_thai = 'cho_tieu_huy' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->string('trang_thai')->toString());
        }

        $hangLois = $query->paginate(15)->withQueryString();
        $hangLois->getCollection()->transform(function (HangLoi $hangLoi) {
            if ($hangLoi->doiTra?->nguoiDung) {
                $hangLoi->doiTra->nguoiDung->setAttribute('ho_ten', $hangLoi->doiTra->nguoiDung->ho_ten_kem_vai_tro);
            }

            return $hangLoi;
        });

        return view('admin_xem_truoc.hang-loi.index', compact('hangLois', 'schemaReady', 'migrationMessage'));
    }

    public function xacNhanTieuHuy(int $id)
    {
        if (!Schema::hasTable('hang_loi')) {
            return back()->with('error', 'Bảng hàng lỗi chưa được tạo. Vui lòng chạy migration trước.');
        }

        DB::transaction(function () use ($id) {
            $hangLoi = HangLoi::query()
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($hangLoi->trang_thai === 'da_tieu_huy') {
                return;
            }

            $hangLoi->update([
                'trang_thai' => 'da_tieu_huy',
                'ngay_tieu_huy' => now(),
                'id_nguoi_dung_tieu_huy' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Đã xác nhận tiêu hủy hàng lỗi.');
    }
}
