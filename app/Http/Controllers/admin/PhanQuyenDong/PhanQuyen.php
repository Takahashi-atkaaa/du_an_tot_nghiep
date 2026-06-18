<?php

namespace App\Http\Controllers\admin\PhanQuyenDong;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\Quyen;
use App\Models\VaiTro;
use Illuminate\Http\Request;

class PhanQuyen extends Controller
{
// trang phân quyền
    public function phanQuyen($id_vai_tro){
        $quyens = Quyen::all();
        $vaiTro = VaiTro::find($id_vai_tro);
        $quyen_thuoc_vai_tro = $vaiTro->quyens()->pluck('id_quyen')->toArray();
        return view('admin_xem_truoc.phan-quyen', compact('vaiTro', 'quyens', 'quyen_thuoc_vai_tro'));
    }

// xử lý cập nhật phân quyền
    public function capNhatPhanQuyen(Request $request,  $id_vai_tro){
        $quyens = $request->input('quyens');
        $vaiTro = VaiTro::find($id_vai_tro);
        $vaiTro->quyens()->sync($quyens);
        return redirect()->back()->with('success', 'Đã cập nhật phân quyền.');
    }
}
