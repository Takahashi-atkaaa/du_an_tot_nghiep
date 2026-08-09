<?php

namespace App\Http\Controllers\Admin\KiemKho;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Render Blade cho các trang kiểm kho.
 * Logic API nằm ở KiemKhoApiController.
 */
class KiemKhoController extends Controller
{
    /**
     * GET /admin/kho-hang/kiem-kho
     * Trang tạo phiếu kiểm kho (UX KiotViet)
     */
    public function index(Request $request)
    {
        return view('admin_xem_truoc.kiem_kho.create');
    }

    /**
     * GET /admin/kho-hang/kiem-kho/lich-su
     * Danh sách phiếu kiểm kho đã tạo
     */
    public function history(Request $request)
    {
        return view('admin_xem_truoc.kiem_kho.history');
    }

    /**
     * GET /admin/kho-hang/kiem-kho/thung-rac
     * Trang thùng rác - liệt kê các phiếu đã soft delete
     */
    public function trash(Request $request)
    {
        return view('admin_xem_truoc.kiem_kho.thung-rac');
    }

    /**
     * GET /admin/kho-hang/kiem-kho/{id}
     * Chi tiết 1 phiếu (read-only)
     */
    public function show(Request $request, int $id)
    {
        return view('admin_xem_truoc.kiem_kho.show', ['id' => $id]);
    }
}
