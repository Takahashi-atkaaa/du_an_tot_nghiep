<?php

namespace App\Http\Controllers\Admin\KhoHang;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhieuXuatController extends Controller
{
    /**
     * Hiển thị trang tạo phiếu xuất (dạng trang riêng, không dùng modal).
     * Truyền danh sách nhà cung cấp để chọn trong form khi loại xuất = trả hàng NCC.
     */
    public function create(Request $request): View
    {
        $nhaCungCaps = NhaCungCap::query()
            ->orderBy('ten_nha_cung_cap')
            ->get();

        return view('admin_xem_truoc.kho-hang.phieu-xuat.create', compact('nhaCungCaps'));
    }
}
