<?php

namespace App\Http\Controllers\admin\KhoHang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NhaCungCap;
use Illuminate\Support\Facades\DB;
class NhaCungCapController extends Controller
{
    /**
     * Display a listing of suppliers with optional search.
     */
    public function index(Request $request)
    {
        $q = $request->query('q');

        $query = NhaCungCap::query();

        if (!empty($q)) {
            $query->where(function($w) use ($q) {
                $w->where('ten_nha_cung_cap', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('so_dien_thoai', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('created_at', 'asc')->paginate(15)->withQueryString();

        return view('admin_xem_truoc.nha-cung-cap.index', compact('items', 'q'));
    }

    /**
     * Store a newly created supplier in storage.
     */
  public function store(Request $request)
{
    $data = $request->validate([
        'ten_nha_cung_cap' => 'required|string|max:255|unique:nha_cung_cap,ten_nha_cung_cap',
        'email' => 'nullable|email|max:255',
        'so_dien_thoai' => 'required|string|max:50',
        'nguoi_dai_dien' => 'nullable|string|max:255',
    ], [
        'ten_nha_cung_cap.required' => 'Vui lòng nhập tên nhà cung cấp.',
        'ten_nha_cung_cap.unique' => 'Tên nhà cung cấp này đã tồn tại.',
        'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
    ]);

    NhaCungCap::create($data);

    return redirect()->back()->with('success', 'Thêm nhà cung cấp thành công');
}

    /**
     * Show the form for editing the specified supplier. Returns JSON for AJAX.
     */
    public function edit($id)
    {
        $item = NhaCungCap::findOrFail($id);
        return response()->json($item);
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'ten_nha_cung_cap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'so_dien_thoai' => 'required|string|max:50',
            'nguoi_dai_dien' => 'nullable|string|max:255',
        ]);

        $item = NhaCungCap::findOrFail($id);
        $item->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->back()->with('success', 'Cập nhật nhà cung cấp thành công');
    }

    /**
     * Remove the specified supplier (soft delete).
     */
    public function destroy($id)
    {
        $item = NhaCungCap::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Xóa nhà cung cấp thành công');
    }
    public function lichSuGiaoDich(Request $request, $id)
{
    $nhaCungCap = NhaCungCap::findOrFail($id);

    $query = DB::table('phieu')
        ->leftJoin('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
        ->where('phieu.id_nha_cung_cap', $id)
        ->where('phieu.loai_phieu', 'Nhập hàng')
        ->select(
            'phieu.id',
            'phieu.loai_phieu',
            'phieu.created_at',
            'phieu.ghi_chu',
            DB::raw('COALESCE(SUM(chi_tiet_phieu.so_luong * chi_tiet_phieu.gia_nhap), 0) as tong_gia_tri'),
            DB::raw('COALESCE(SUM(chi_tiet_phieu.so_luong), 0) as tong_so_luong')
        )
        ->groupBy('phieu.id', 'phieu.loai_phieu', 'phieu.created_at', 'phieu.ghi_chu');

    if ($request->filled('tu_ngay')) {
        $query->whereDate('phieu.created_at', '>=', $request->tu_ngay);
    }

    if ($request->filled('den_ngay')) {
        $query->whereDate('phieu.created_at', '<=', $request->den_ngay);
    }

    $phieuNhap = $query->orderBy('phieu.created_at', 'desc')
        ->paginate(10)
        ->withQueryString();

    $tongSoPhieu = DB::table('phieu')
        ->where('id_nha_cung_cap', $id)
        ->where('loai_phieu', 'Nhập hàng')
        ->count();

    $tongGiaTri = DB::table('phieu')
        ->join('chi_tiet_phieu', 'phieu.id', '=', 'chi_tiet_phieu.id_phieu')
        ->where('phieu.id_nha_cung_cap', $id)
        ->where('phieu.loai_phieu', 'Nhập hàng')
        ->sum(DB::raw('chi_tiet_phieu.so_luong * chi_tiet_phieu.gia_nhap'));

    return view('admin_xem_truoc.nha-cung-cap.lich-su-giao-dich', compact(
        'nhaCungCap',
        'phieuNhap',
        'tongSoPhieu',
        'tongGiaTri'
    ));
}
}
