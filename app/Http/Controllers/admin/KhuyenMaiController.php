<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KhuyenMai;
use Carbon\Carbon;

class KhuyenMaiController extends Controller
{
    public function index(Request $request)
    {
        $query = KhuyenMai::query();

        // Search
        if ($q = $request->query('q')) {
            $query->where(function($qbuilder) use ($q) {
                $qbuilder->where('ten_chuong_trinh', 'like', "%{$q}%")
                         ->orWhere('ghi_chu', 'like', "%{$q}%");
            });
        }

        // Filter by type
        if ($type = $request->query('loai')) {
            $query->where('loai_giam_gia', $type);
        }

        // Filter by status: active, upcoming, ended
        $now = Carbon::now();
        if ($status = $request->query('trang_thai')) {
            if ($status === 'active') {
                $query->where('trang_thai', true)
                      ->where('ngay_bat_dau', '<=', $now)
                      ->where('ngay_ket_thuc', '>=', $now);
            } elseif ($status === 'upcoming') {
                $query->where('ngay_bat_dau', '>', $now);
            } elseif ($status === 'ended') {
                $query->where(function($qb) use ($now) {
                    $qb->where('ngay_ket_thuc', '<', $now)
                       ->orWhere('trang_thai', false);
                });
            }
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        // Counts for stats
        $total = KhuyenMai::count();
        $active = KhuyenMai::where('trang_thai', true)
            ->where('ngay_bat_dau', '<=', $now)
            ->where('ngay_ket_thuc', '>=', $now)
            ->count();
        $upcoming = KhuyenMai::where('ngay_bat_dau', '>', $now)->count();
        $ended = KhuyenMai::where('ngay_ket_thuc', '<', $now)->orWhere('trang_thai', false)->count();

        return view('admin_xem_truoc.khuyen-mai', compact('items', 'total', 'active', 'upcoming', 'ended'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ten_chuong_trinh' => 'required|string|max:255',
            'loai_giam_gia' => 'required|string|max:50',
            'gia_tri_giam' => 'required|numeric',
            'giam_toi_da' => 'nullable|numeric',
            'so_luong_sp_toi_thieu' => 'nullable|integer',
            'don_hang_toi_thieu' => 'nullable|numeric',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_bat_dau',
            'trang_thai' => 'sometimes|boolean',
            'ghi_chu' => 'nullable|string',
        ]);

        $promo = KhuyenMai::create($data);

        return redirect()->back()->with('success', 'Tạo chương trình khuyến mãi thành công');
    }

    // Delete promotion (soft-delete)
    public function destroy($id)
    {
        $promo = KhuyenMai::findOrFail($id);
        $promo->delete();
        return redirect()->back()->with('success', 'Xóa chương trình khuyến mãi thành công');
    }

    public function edit($id)
    {
        $promo = KhuyenMai::findOrFail($id);
        return view('admin_xem_truoc.khuyen-mai-edit', compact('promo'));
    }

    public function update(Request $request, $id)
    {
        $promo = KhuyenMai::findOrFail($id);

        $data = $request->validate([
            'ten_chuong_trinh' => 'required|string|max:255',
            'loai_giam_gia' => 'required|string|max:50',
            'gia_tri_giam' => 'required|numeric',
            'giam_toi_da' => 'nullable|numeric',
            'so_luong_sp_toi_thieu' => 'nullable|integer',
            'don_hang_toi_thieu' => 'nullable|numeric',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_bat_dau',
            'trang_thai' => 'sometimes|boolean',
            'ghi_chu' => 'nullable|string',
        ]);

        $promo->update($data);

        return redirect()->route('khuyen-mai.edit', $promo->id)->with('success', 'Cập nhật chương trình khuyến mãi thành công');
    }

    // Toggle promotion active state
    public function toggle($id)
    {
        $promo = KhuyenMai::findOrFail($id);
        $now = Carbon::now();
        $start = $promo->ngay_bat_dau;
        $end = $promo->ngay_ket_thuc;

        // Only allow toggle when both start and end are set and now is between them
        if (! $start || ! $end || ! $now->between($start, $end)) {
            return redirect()->back()->with('error', 'Chỉ có thể bật/tắt chương trình đang trong thời gian áp dụng.');
        }

        $promo->trang_thai = ! $promo->trang_thai;
        $promo->save();

        $msg = $promo->trang_thai ? 'Kích hoạt chương trình khuyến mãi thành công' : 'Tắt chương trình khuyến mãi thành công';
        return redirect()->back()->with('success', $msg);
    }

    // AJAX toggle endpoint
    public function ajaxToggle(Request $request, $id)
    {
        $promo = KhuyenMai::findOrFail($id);
        $now = Carbon::now();
        $start = $promo->ngay_bat_dau;
        $end = $promo->ngay_ket_thuc;

        if (! $start || ! $end || ! $now->between($start, $end)) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể bật/tắt chương trình đang trong thời gian áp dụng.'
            ], 422);
        }

        $promo->trang_thai = ! $promo->trang_thai;
        $promo->save();

        return response()->json([
            'success' => true,
            'trang_thai' => (bool) $promo->trang_thai,
            'message' => $promo->trang_thai ? 'Đã kích hoạt' : 'Đã tắt',
        ]);
    }
}
