<?php

namespace App\Http\Controllers\nhan_vien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestNhanVienController extends Controller
{
    public function index()
    {
        return view('nhan_vien_view.dashboard');
    }

    public function banHang()
    {
        return view('nhan_vien_view.pos');
    }

    public function hoaDon()
    {
        return view('nhan_vien_view.hoa-don.index');
    }

    public function sanPham()
    {
        return view('nhan_vien_view.san-pham.index');
    }

    public function khachHang()
    {
        return view('nhan_vien_view.khach-hang.index');
    }

    public function lichLamViec()
    {
        return view('nhan_vien_view.lich-lam-viec');
    }

    public function chamCong()
    {
        return view('nhan_vien_view.cham-cong');
    }

    public function hoSo()
    {
        return view('nhan_vien_view.ho-so');
    }
}
