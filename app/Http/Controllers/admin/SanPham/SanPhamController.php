<?php

namespace App\Http\Controllers\admin\SanPham;

use App\Http\Controllers\Controller;
use App\Http\Requests\SanPham\UpdateSanPhamRequest;
use App\Http\Requests\SanPham\ImportSanPhamRequest;
use App\Http\Requests\SanPham\ThemSanPhamRequest;
use App\Models\DanhMucSanPham;
use App\Models\Product;
use App\Models\BienTheSanPham;
use App\Models\DonViQuyDoi;
use App\Models\SanPham;
use App\Models\ThuocTinhSanPham;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SanPhamController extends Controller
{
    public function index(Request $request)
    {
        $danhSachSanPham = SanPham::with('danhMuc', 'bienTheSanPhams')->get();
        return view('admin_xem_truoc.san-pham.index', compact('danhSachSanPham'));
    }


    public function create()
{
    $danhMucs = DanhMucSanPham::where('trang_thai',1)->get();

    return view('admin_xem_truoc.san-pham.them-san-pham',compact('danhMucs'));
}


public function store(ThemSanPhamRequest $request)
{

    DB::beginTransaction();

    try {

        $tenAnh = null;

        if ($request->hasFile('hinh_anh')) {

            $tenAnh = time() . '.' . $request->file('hinh_anh')->extension();

            $request->file('hinh_anh')
                ->move(public_path('uploads/san_pham'), $tenAnh);
        }

        //==========================
        // Tạo sản phẩm
        //==========================

        $sanPham = SanPham::create([

            'id_danh_muc' => $request->id_danh_muc,

            'ten_san_pham' => $request->ten_san_pham,

            'gia_ban' => $request->gia_ban,

            'dinh_muc_toi_thieu' => $request->dinh_muc_toi_thieu,

            'mo_ta' => $request->mo_ta,

            'hinh_anh' => $tenAnh,

            'trang_thai' => 1,

        ]);

        //==========================
        // Kiểm tra có biến thể không
        //==========================

        if (!empty($request->bien_the)) {

    foreach ($request->bien_the as $bienThe) {

        BienTheSanPham::create([
            'id_san_pham' => $sanPham->id,
            'ten_bien_the'  => $bienThe['ten_bien_the'],
            'gia_bien_the' => $bienThe['gia_bien_the'] ?? 0,
            'he_so_quy_doi' => $bienThe['he_so_quy_doi'] ?? 1,
            'trang_thai' => $bienThe['trang_thai'] ?? 1,
        ]);
    }

} else {

    BienTheSanPham::create([
        'id_san_pham' => $sanPham->id,
        'ten_bien_the' => 'Default',
        'gia_bien_the' => 0,
        'he_so_quy_doi' => 1,
        'trang_thai' => 1,
    ]);

}
        DB::commit();

        return redirect()
            ->route('san-pham.index')
            ->with('success', 'Thêm sản phẩm thành công.');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}


// public function edit($id)
// {
//     $sanPham = SanPham::with('bienTheSanPhams')
//                     ->findOrFail($id);

//     $danhMucs = DanhMucSanPham::where(
//         'trang_thai',
//         1
//     )->get();

//     return view(
//         'admin_xem_truoc.san-pham.sua-san-pham',
//         compact(
//             'sanPham',
//             'danhMucs'
//         )
//     );
// }





// public function update(
//     StoreSanPhamRequest $request,
//     $id
// )
// {

//     DB::beginTransaction();

//     try{

//         $sanPham = SanPham::findOrFail($id);

//         $tenAnh = $sanPham->hinh_anh;

//         if($request->hasFile('hinh_anh')){

//             $tenAnh=time().'.'.$request->hinh_anh->extension();

//             $request->hinh_anh->move(
//                 public_path('uploads/san_pham'),
//                 $tenAnh
//             );

//         }

//         $sanPham->update([

//             'id_danh_muc'=>$request->id_danh_muc,

//             'ten_san_pham'=>$request->ten_san_pham,

//             'gia_ban'=>$request->gia_ban,

//             'dinh_muc_toi_thieu'=>$request->dinh_muc_toi_thieu,

//             'mo_ta'=>$request->mo_ta,

//             'hinh_anh'=>$tenAnh,

//         ]);

//         //Xóa biến thể cũ

//         BienTheSanPham::where(
//             'id_san_pham',
//             $sanPham->id
//         )->delete();

//         //Nếu không có biến thể

//         if(empty($request->bien_the)){

//             BienTheSanPham::create([

//                 'id_san_pham'=>$sanPham->id,

//                 'ten_bien_the'=>'Default',

//                 'he_so_quy_doi'=>1,

//                 'trang_thai'=>1

//             ]);

//         }else{

//             foreach($request->bien_the as $bienThe){

//                 if(empty($bienThe['ten_bien_the'])){

//                     continue;

//                 }

//                 BienTheSanPham::create([

//                     'id_san_pham'=>$sanPham->id,

//                     'ten_bien_the'=>$bienThe['ten_bien_the'],

//                     'he_so_quy_doi'=>$bienThe['he_so_quy_doi'],

//                     'trang_thai'=>$bienThe['trang_thai']

//                 ]);

//             }

//         }

//         DB::commit();

//         return redirect()
//                 ->route('san-pham.index')
//                 ->with(
//                     'success',
//                     'Cập nhật thành công.'
//                 );

//     }catch(\Exception $e){

//         DB::rollBack();

//         return back()
//             ->withInput()
//             ->with(
//                 'error',
//                 $e->getMessage()
//             );

//     }

// }
}
