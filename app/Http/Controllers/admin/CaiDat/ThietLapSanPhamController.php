<?php

namespace App\Http\Controllers\admin\CaiDat;

use App\Http\Controllers\Controller;
use App\Models\DonViSanPham;
use App\Models\ThuocTinhSanPham;
use Illuminate\Http\Request;

class ThietLapSanPhamController extends Controller
{
    public function index()
    {
        $donVis = DonViSanPham::orderBy('id', 'asc')->get();
        $thuocTinhs = ThuocTinhSanPham::orderBy('id', 'asc')->get();

        return view('admin_xem_truoc.cai-dat-san-pham', compact('donVis', 'thuocTinhs'));
    }

    public function storeDonVi(Request $request)
    {
        $data = $request->validate([
            'ten_don_vi' => 'required|string|max:255',
            'so_luong_san_pham_trong_don_vi' => 'nullable|integer|min:1',
        ]);

        DonViSanPham::create(array_merge($data, ['so_luong_san_pham_trong_don_vi' => $data['so_luong_san_pham_trong_don_vi'] ?? 1, 'trang_thai' => true]));

        return redirect()->back()->with('success', 'Đã thêm đơn vị thành công');
    }

    public function storeThuocTinh(Request $request)
    {
        $data = $request->validate([
            'ten_thuoc_tinh' => 'required|string|max:255',
        ]);

        ThuocTinhSanPham::create(array_merge($data, ['trang_thai' => true]));

        return redirect()->back()->with('success', 'Đã thêm thuộc tính thành công');
    }

    public function destroyDonVi($id)
    {
        $item = DonViSanPham::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Đã xóa đơn vị');
    }

    public function updateDonVi(Request $request, $id)
    {
        $data = $request->validate([
            'ten_don_vi' => 'required|string|max:255',
            'so_luong_san_pham_trong_don_vi' => 'nullable|integer|min:1',
        ]);

        $item = DonViSanPham::findOrFail($id);
        $item->update([
            'ten_don_vi' => $data['ten_don_vi'],
            'so_luong_san_pham_trong_don_vi' => $data['so_luong_san_pham_trong_don_vi'] ?? 1,
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật đơn vị.');
    }

    public function destroyThuocTinh($id)
    {
        $item = ThuocTinhSanPham::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Đã xóa thuộc tính');
    }

    public function updateThuocTinh(Request $request, $id)
    {
        $data = $request->validate([
            'ten_thuoc_tinh' => 'required|string|max:255',
        ]);

        $item = ThuocTinhSanPham::findOrFail($id);
        $item->update(['ten_thuoc_tinh' => $data['ten_thuoc_tinh']]);

        return redirect()->back()->with('success', 'Đã cập nhật thuộc tính.');
    }
}
