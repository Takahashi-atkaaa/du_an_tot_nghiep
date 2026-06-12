<?php
 
namespace App\Http\Controllers\Admin\DanhMuc;
 
use App\Http\Controllers\Controller;
use App\Requests\DanhMuc\StoreCreateRequest;
use App\Requests\DanhMuc\UpdateDanhMucRequest;
use App\Models\DanhMucSanPham;
use Illuminate\Http\Request;
 
class DanhMucSanPhamController extends Controller
{
    function index(){
        $danh_muc_sp = DanhMucSanPham::withCount('sanPhams')
                       ->get();
        return view('admin_xem_truoc.ql_danh_muc.danh_sach', compact('danh_muc_sp'));
    }


    function store(StoreCreateRequest $request)
    {
        DanhMucSanPham::create($request->validated());

        return redirect()
            ->route('danh_muc.index')
            ->with('success', 'Danh mục sản phẩm đã được tạo thành công.');
    }




    public function destroy($id)
    {
        // 1. Tìm danh mục sản phẩm theo ID, nếu không thấy sẽ tự trả về lỗi 404
        $danh_muc = DanhMucSanPham::findOrFail($id);

        /* * 2. Kiểm tra xem danh mục này có sản phẩm nào liên kết không.
         * LƯU Ý: Bạn kiểm tra lại trong Model DanhMucSanPham xem hàm relationship 
         * đang viết là 'sanPhams' hay 'san_phams' để điền vào đây cho đúng nhé.
         */
        if ($danh_muc->sanPhams()->exists()) {
            return redirect()
                ->route('danh_muc.index')
                ->with('error', 'Không thể xóa danh mục này vì đang có sản phẩm liên kết với nó!');
        }

        // 3. Nếu không có sản phẩm nào thì tiến hành xóa
        $danh_muc->delete();

        return redirect()
            ->route('danh_muc.index')
            ->with('success', 'Danh mục sản phẩm đã được xóa thành công.');
    }

    // Sửa danh mục sản phẩm
    public function edit($id){
        $danhmuc = DanhMucSanPham::findOrfail($id);
        return view('admin_xem_truoc.ql_danh_muc.sua', compact('danhmuc'));
    }

    // Cập nhập danh mục sản phẩm
    public function update(UpdateDanhMucRequest $request, $id)
    {
        $danhmuc = DanhMucSanPham::findOrFail($id);

        $danhmuc->ten_danh_muc = $request->ten_danh_muc;
        $danhmuc->trang_thai   = $request->trang_thai;
        $danhmuc->mau_sac      = $request->mau_sac;
        $danhmuc->icon         = $request->icon;

        $danhmuc->save();

        return redirect()
            ->route('danh_muc.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }
}   