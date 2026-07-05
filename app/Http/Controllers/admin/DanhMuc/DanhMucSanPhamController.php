<?php
 
namespace App\Http\Controllers\admin\DanhMuc;
 
use App\Http\Controllers\Controller;
use App\Http\Requests\DanhMuc\StoreCreateRequest;
use App\Http\Requests\DanhMuc\UpdateDanhMucRequest;
use App\Models\DanhMucSanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 
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

        // Đếm cả sản phẩm chưa xóa và đã xóa mềm để báo lỗi chính xác.
        $activeCount = $danh_muc->sanPhams()->count();
        $trashedCount = $danh_muc->sanPhams()->onlyTrashed()->count();

        if ($activeCount > 0) {
            return redirect()
                ->route('danh_muc.index')
                ->with('error', "Không thể xóa danh mục \"{$danh_muc->ten_danh_muc}\" vì đang có {$activeCount} sản phẩm liên kết. Vui lòng chuyển các sản phẩm sang danh mục khác trước khi xóa.");
        }

        // Có sản phẩm đã xóa mềm nhưng vẫn liên kết FK → cũng không thể xóa cứng
        if ($trashedCount > 0) {
            return redirect()
                ->route('danh_muc.index')
                ->with('error', "Không thể xóa danh mục \"{$danh_muc->ten_danh_muc}\" vì vẫn còn {$trashedCount} sản phẩm đã xóa mềm liên kết. Vui lòng xóa vĩnh viễn hoặc khôi phục chúng trước.");
        }

        // 3. Nếu không có sản phẩm nào thì tiến hành xóa
        DB::transaction(function () use ($danh_muc) {
            $danh_muc->delete();
        });

        return redirect()
            ->route('danh_muc.index')
            ->with('success', "Danh mục \"{$danh_muc->ten_danh_muc}\" đã được xóa thành công.");
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