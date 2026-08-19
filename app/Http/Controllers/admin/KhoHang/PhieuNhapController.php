<?php

namespace App\Http\Controllers\Admin\KhoHang;

use App\Http\Controllers\Controller;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhieuNhapController extends Controller
{
    /**
     * Hiển thị trang tạo phiếu nhập (dạng trang riêng thay vì modal).
     * Truyền danh sách nhà cung cấp để chọn trong form.
     */
    public function create(Request $request): View
    {
        $nhaCungCaps = NhaCungCap::query()
            ->orderBy('ten_nha_cung_cap')
            ->get();

        return view('admin_xem_truoc.kho-hang.phieu-nhap.create', compact('nhaCungCaps'));
    }
}
