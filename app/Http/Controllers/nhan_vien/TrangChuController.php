<?php

namespace App\Http\Controllers\nhan_vien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrangChuController extends Controller
{
    public function trang_chu(){
        
        return view('nhan_vien.trang_chu');
    }
}
