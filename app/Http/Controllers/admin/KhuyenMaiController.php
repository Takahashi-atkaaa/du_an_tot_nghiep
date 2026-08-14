<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KhuyenMai;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class KhuyenMaiController extends Controller
{
    // Danh sách khuyến mãi
    public function index(Request $request)
    {
        $query = KhuyenMai::query();

        // Tìm kiếm
        if ($q = $request->query('q')) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('ten_chuong_trinh', 'like', "%{$q}%")
                    ->orWhere('ghi_chu', 'like', "%{$q}%");
            });
        }

        // Lọc theo loại
        if ($type = $request->query('loai')) {
            $query->where('loai_giam_gia', $type);
        }

        // Lọc theo trạng thái
        $now = Carbon::now();

        if ($status = $request->query('trang_thai')) {

            if ($status === 'active') {
                $query->where('trang_thai', true)
                    ->where('ngay_bat_dau', '<=', $now)
                    ->where('ngay_ket_thuc', '>=', $now);
            }

            elseif ($status === 'upcoming') {
                $query->where('ngay_bat_dau', '>', $now);
            }

            elseif ($status === 'ended') {
                $query->where(function ($qb) use ($now) {
                    $qb->where('ngay_ket_thuc', '<', $now)
                        ->orWhere('trang_thai', false);
                });
            }
        }

        $items = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Thống kê
        $total = KhuyenMai::count();

        $active = KhuyenMai::where('trang_thai', true)
            ->where('ngay_bat_dau', '<=', $now)
            ->where('ngay_ket_thuc', '>=', $now)
            ->count();

        $upcoming = KhuyenMai::where('ngay_bat_dau', '>', $now)->count();

        $ended = KhuyenMai::where('ngay_ket_thuc', '<', $now)
            ->orWhere('trang_thai', false)
            ->count();
    $sanPhams = DB::table('san_pham')
    ->whereNull('deleted_at')
    ->orderBy('ten_san_pham')
    ->select(
        'id',
        'ten_san_pham'
    )
    ->get();
    $bienThes = DB::table('bien_the_san_pham')
    ->whereNull('deleted_at')
    ->orderBy('product_id')
    ->select(
        'id',
        'product_id',
        'ten_bien_the',
        'ma_hang',
        'gia_ban'
    )
    ->get()
    ->groupBy('product_id');
        return view(
            'admin_xem_truoc.khuyen-mai',
             compact(
    'items',
    'total',
    'active',
    'upcoming',
    'ended',
    'sanPhams',
    'bienThes'
)
        );
        
    }

    // Thêm khuyến mãi
   public function store(Request $request)
{
    $data = $request->validate([
        'ten_chuong_trinh' =>
            'required|string|max:255',

        'loai_giam_gia' =>
            'required|string|max:50',

        'gia_tri_giam' =>
            'required|numeric|min:0',

        'giam_toi_da' =>
            'nullable|numeric|min:0',

        'so_luong_sp_toi_thieu' =>
            'nullable|integer|min:0',

        'don_hang_toi_thieu' =>
            'nullable|numeric|min:0',

        'ngay_bat_dau' =>
            'nullable|date',

        'ngay_ket_thuc' =>
            'nullable|date|after_or_equal:ngay_bat_dau',

        'trang_thai' =>
            'sometimes|boolean',

        'ghi_chu' =>
            'nullable|string',

        'id_san_phams' => [
            'nullable',
            'array',
        ],

        'id_san_phams.*' => [
            'integer',
            'exists:san_pham,id',
        ],

        'id_bien_thes' => [
            'nullable',
            'array',
        ],

        'id_bien_thes.*' => [
            'integer',
            'exists:bien_the_san_pham,id',
        ],
    ]);

    $idSanPhams =
        $data['id_san_phams'] ?? [];

    $idBienThes =
        $data['id_bien_thes'] ?? [];

    unset(
        $data['id_san_phams'],
        $data['id_bien_thes']
    );

    if (
        empty($idSanPhams) &&
        empty($idBienThes)
    ) {
        return redirect()
            ->back()
            ->withInput()
            ->withErrors([
                'id_san_phams' =>
                    'Bạn phải chọn ít nhất một sản phẩm hoặc biến thể.'
            ]);
    }

    $data['trang_thai'] =
        $request->boolean('trang_thai');

    DB::transaction(function () use (
        $data,
        $idSanPhams,
        $idBienThes
    ) {

        $khuyenMai =
            KhuyenMai::create($data);

        foreach (
            array_unique($idSanPhams)
            as $idSanPham
        ) {

            DB::table(
                'khuyen_mai_san_pham'
            )->insert([
                'id_khuyen_mai' =>
                    $khuyenMai->id,

                'id_san_pham' =>
                    $idSanPham,

                'id_bien_the_san_pham' =>
                    null,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (
            array_unique($idBienThes)
            as $idBienThe
        ) {

            $bienThe =
                DB::table('bien_the_san_pham')
                    ->where('id', $idBienThe)
                    ->first();

            if (!$bienThe) {
                continue;
            }

            if (
                in_array(
                    (int) $bienThe->product_id,
                    array_map(
                        'intval',
                        $idSanPhams
                    ),
                    true
                )
            ) {
                continue;
            }

            DB::table(
                'khuyen_mai_san_pham'
            )->insert([
                'id_khuyen_mai' =>
                    $khuyenMai->id,

                'id_san_pham' =>
                    $bienThe->product_id,

                'id_bien_the_san_pham' =>
                    $bienThe->id,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    });

    return redirect()
        ->back()
        ->with(
            'success',
            'Tạo chương trình khuyến mãi thành công'
        );
}

    // Xóa mềm khuyến mãi
    public function destroy($id)
    {
        $promo = KhuyenMai::findOrFail($id);

        $promo->trang_thai = false;
        $promo->save();

        $promo->delete();

        return redirect()->back()
            ->with('success', 'Xóa chương trình khuyến mãi thành công');
    }

    // Form sửa khuyến mãi
  public function edit($id)
{
    $promo = KhuyenMai::findOrFail($id);

    $sanPhams = DB::table('san_pham')
        ->whereNull('deleted_at')
        ->orderBy('ten_san_pham')
        ->select(
            'id',
            'ten_san_pham'
        )
        ->get();

    $idSanPhamsDaChon = DB::table('khuyen_mai_san_pham')
        ->where('id_khuyen_mai', $promo->id)
        ->pluck('id_san_pham')
        ->map(fn ($id) => (int) $id)
        ->all();

    return view(
        'admin_xem_truoc.khuyen-mai-edit',
        compact(
            'promo',
            'sanPhams',
            'idSanPhamsDaChon'
        )
    );
}

    // Cập nhật khuyến mãi
    public function update(Request $request, $id)
{
    $promo = KhuyenMai::findOrFail($id);

    $data = $request->validate([
        'ten_chuong_trinh' => 'required|string|max:255',
        'loai_giam_gia' => 'required|string|max:50',
        'gia_tri_giam' => 'required|numeric|min:0',
        'giam_toi_da' => 'nullable|numeric|min:0',
        'so_luong_sp_toi_thieu' => 'nullable|integer|min:0',
        'don_hang_toi_thieu' => 'nullable|numeric|min:0',
        'ngay_bat_dau' => 'nullable|date',
        'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_bat_dau',
        'trang_thai' => 'sometimes|boolean',
        'ghi_chu' => 'nullable|string',

        'id_san_phams' => [
            'required',
            'array',
            'min:1',
        ],

        'id_san_phams.*' => [
            'integer',
            'exists:san_pham,id',
        ],
    ]);

    $idSanPhams = $data['id_san_phams'];

    unset($data['id_san_phams']);

    $data['trang_thai'] = $request->boolean('trang_thai');

    DB::transaction(function () use (
        $promo,
        $data,
        $idSanPhams
    ) {
        $promo->update($data);

        DB::table('khuyen_mai_san_pham')
            ->where('id_khuyen_mai', $promo->id)
            ->delete();

        $rows = collect($idSanPhams)
            ->unique()
            ->map(function ($idSanPham) use ($promo) {
                return [
                    'id_khuyen_mai' => $promo->id,
                    'id_san_pham' => $idSanPham,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        DB::table('khuyen_mai_san_pham')->insert($rows);
    });

    return redirect()
        ->route('khuyen-mai.edit', $promo->id)
        ->with(
            'success',
            'Cập nhật chương trình khuyến mãi thành công'
        );
}
    // Bật / Tắt khuyến mãi
    public function toggle($id)
    {
        $promo = KhuyenMai::findOrFail($id);

        $now = Carbon::now();
        $start = $promo->ngay_bat_dau;
        $end = $promo->ngay_ket_thuc;

        if (!$start || !$end || !$now->between($start, $end)) {
            return redirect()->back()
                ->with('error', 'Chỉ có thể bật/tắt chương trình đang trong thời gian áp dụng.');
        }

        $promo->trang_thai = !$promo->trang_thai;
        $promo->save();

        $msg = $promo->trang_thai
            ? 'Kích hoạt chương trình khuyến mãi thành công'
            : 'Tắt chương trình khuyến mãi thành công';

        return redirect()->back()->with('success', $msg);
    }

    // Bật / Tắt bằng AJAX
    public function ajaxToggle(Request $request, $id)
    {
        $promo = KhuyenMai::findOrFail($id);

        $now = Carbon::now();
        $start = $promo->ngay_bat_dau;
        $end = $promo->ngay_ket_thuc;

        if (!$start || !$end || !$now->between($start, $end)) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể bật/tắt chương trình đang trong thời gian áp dụng.'
            ], 422);
        }

        $promo->trang_thai = !$promo->trang_thai;
        $promo->save();

        return response()->json([
            'success' => true,
            'trang_thai' => (bool) $promo->trang_thai,
            'message' => $promo->trang_thai ? 'Đã kích hoạt' : 'Đã tắt',
        ]);
    }

    // Thùng rác khuyến mãi
    public function trash(Request $request)
    {
        $items = KhuyenMai::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(12);

        return view('admin_xem_truoc.khuyen-mai-trash', compact('items'));
    }

    // Khôi phục khuyến mãi
    public function restore($id)
    {
        $promo = KhuyenMai::onlyTrashed()->findOrFail($id);

        $promo->restore();

        return redirect()->back()
            ->with('success', 'Khôi phục chương trình khuyến mãi thành công');
    }

    // Xóa vĩnh viễn
    public function forceDelete($id)
    {
        $promo = KhuyenMai::onlyTrashed()->findOrFail($id);

        $promo->forceDelete();

        return redirect()->back()
            ->with('success', 'Đã xóa vĩnh viễn chương trình khuyến mãi');
    }
}