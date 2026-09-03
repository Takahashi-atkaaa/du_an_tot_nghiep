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
        // // #region agent log
      
        // #endregion
        
        $quyens = Quyen::all();
        $vaiTro = VaiTro::findOrFail($id_vai_tro);
        
        // #region agent log
        // #endregion
        
        $quyen_thuoc_vai_tro = $vaiTro->quyens()->pluck('id_quyen')->toArray();
        
        // #region agent log
        // #endregion
        
        return view('admin_xem_truoc.phan-quyen', compact('vaiTro', 'quyens', 'quyen_thuoc_vai_tro'));
    }

// xử lý cập nhật phân quyền
    public function capNhatPhanQuyen(Request $request,  $id_vai_tro){
        $vaiTro = VaiTro::findOrFail($id_vai_tro);

        // Quyền Admin không được thay đổi qua màn hình phân quyền.
        abort_if((int) $vaiTro->id === 1, 403, 'Không được thay đổi quyền của Admin.');

        $quyens = $request->validate([
            'quyens' => ['nullable', 'array'],
            'quyens.*' => ['integer', 'exists:quyen,id'],
        ])['quyens'] ?? [];

        $vaiTro->quyens()->sync($quyens);
        return redirect()->back()->with('success', 'Đã cập nhật phân quyền.');
    }
}
