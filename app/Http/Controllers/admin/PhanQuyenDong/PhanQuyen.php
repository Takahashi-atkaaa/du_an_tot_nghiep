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
        // #region agent log
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/SmartMart/.cursor/debug-c60244.log', json_encode(['sessionId'=>'c60244','location'=>'PhanQuyen.php:12','message'=>'phanQuyen entry','data'=>['id_vai_tro'=>$id_vai_tro,'type'=>gettype($id_vai_tro)],'timestamp'=>round(microtime(true)*1000),'hypothesisId'=>'C'])."\n", FILE_APPEND);
        // #endregion
        
        $quyens = Quyen::all();
        $vaiTro = VaiTro::findOrFail($id_vai_tro);
        
        // #region agent log
        file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/SmartMart/.cursor/debug-c60244.log', json_encode(['sessionId'=>'c60244','location'=>'PhanQuyen.php:18','message'=>'vaiTro loaded','data'=>['vai_tro_id'=>$vaiTro->id,'vai_tro_ten'=>$vaiTro->ten_vai_tro],'timestamp'=>round(microtime(true)*1000),'hypothesisId'=>'C'])."\n", FILE_APPEND);
        // #endregion
        
        $quyen_thuoc_vai_tro = $vaiTro->quyens()->pluck('id_quyen')->toArray();
        
        // #region agent log
        $nhanSuQuyen = Quyen::where('ma_quyen', 'quan_ly_nhan_su')->first(); file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/SmartMart/.cursor/debug-c60244.log', json_encode(['sessionId'=>'c60244','location'=>'PhanQuyen.php:24','message'=>'permissions data','data'=>['quyen_thuoc_vai_tro'=>$quyen_thuoc_vai_tro,'nhan_su_id'=>$nhanSuQuyen?->id,'in_array_check'=>in_array($nhanSuQuyen?->id, $quyen_thuoc_vai_tro),'total_quyens'=>$quyens->count()],'timestamp'=>round(microtime(true)*1000),'hypothesisId'=>'A,B,D,E'])."\n", FILE_APPEND);
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
