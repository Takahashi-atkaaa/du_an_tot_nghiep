<?php

namespace App\Http\Controllers\admin\KhoHang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NhaCungCap;
use Illuminate\Support\Facades\DB;

class NhaCungCapController extends Controller
{
    // Danh sách nhà cung cấp
    public function index(Request $request)
    {
        $q = $request->query('q');

        $query = NhaCungCap::query();

        // Tìm kiếm
        if (!empty($q)) {
            $query->where(function ($w) use ($q) {
                $w->where('ten_nha_cung_cap', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('created_at', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin_xem_truoc.nha-cung-cap.index', compact('items', 'q'));
    }

    // Thêm nhà cung cấp
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

    // Lấy dữ liệu để sửa
    public function edit($id)
    {
        $item = NhaCungCap::findOrFail($id);

        return response()->json($item);
    }

    // Cập nhật nhà cung cấp
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
            return response()->json([
                'success' => true,
                'item' => $item
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật nhà cung cấp thành công');
    }

    // Xóa mềm nhà cung cấp
    public function destroy($id)
    {
        $item = NhaCungCap::findOrFail($id);

        $item->delete();

        return redirect()->back()->with('success', 'Xóa nhà cung cấp thành công');
    }

    // Lịch sử giao dịch nhà cung cấp
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
            ->groupBy(
                'phieu.id',
                'phieu.loai_phieu',
                'phieu.created_at',
                'phieu.ghi_chu'
            );

        // Lọc từ ngày
        if ($request->filled('tu_ngay')) {
            $query->whereDate('phieu.created_at', '>=', $request->tu_ngay);
        }

        // Lọc đến ngày
        if ($request->filled('den_ngay')) {
            $query->whereDate('phieu.created_at', '<=', $request->den_ngay);
        }

        $phieuNhap = $query->orderBy('phieu.created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Tổng số phiếu nhập
        $tongSoPhieu = DB::table('phieu')
            ->where('id_nha_cung_cap', $id)
            ->where('loai_phieu', 'Nhập hàng')
            ->count();

        // Tổng giá trị nhập hàng
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

    /**
     * Display trash (soft-deleted) suppliers
     */
    public function trash(Request $request)
    {
        $items = NhaCungCap::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin_xem_truoc.nha-cung-cap.trash', compact('items'));
    }

    /**
     * Restore a single supplier from trash
     */
    public function restore($id)
    {
        $item = NhaCungCap::onlyTrashed()->where('id', $id)->first();
        if (!$item) {
            return redirect()->back()->with('error', 'Không tìm thấy nhà cung cấp trong thùng rác');
        }

        $item->restore();

        return redirect()->back()->with('success', 'Khôi phục nhà cung cấp thành công');
    }

    /**
     * Force delete a single supplier from trash
     */
    public function forceDelete($id)
    {
        $item = NhaCungCap::onlyTrashed()->where('id', $id)->first();
        if (!$item) {
            return redirect()->back()->with('error', 'Không tìm thấy nhà cung cấp trong thùng rác');
        }

        $item->forceDelete();

        return redirect()->back()->with('success', 'Đã xóa vĩnh viễn nhà cung cấp');
    }

    /**
     * Bulk restore suppliers from trash
     */
    public function bulkRestore(Request $request)
    {
        $raw = $request->input('ids');
        $ids = [];
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) $ids = $decoded;
        } elseif (is_array($raw)) {
            $ids = $raw;
        }

        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Không có mục hợp lệ để khôi phục');
        }

        $items = NhaCungCap::onlyTrashed()->whereIn('id', $ids)->get();
        foreach ($items as $it) {
            $it->restore();
        }

        return redirect()->back()->with('success', 'Khôi phục các nhà cung cấp đã chọn thành công');
    }

    /**
     * Bulk force delete suppliers from trash
     */
    public function bulkForceDelete(Request $request)
    {
        $raw = $request->input('ids');
        $ids = [];
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) $ids = $decoded;
        } elseif (is_array($raw)) {
            $ids = $raw;
        }

        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Không có mục hợp lệ để xóa');
        }

        $items = NhaCungCap::onlyTrashed()->whereIn('id', $ids)->get();
        foreach ($items as $it) {
            $it->forceDelete();
        }

        return redirect()->back()->with('success', 'Đã xóa vĩnh viễn các nhà cung cấp đã chọn');
    }
}