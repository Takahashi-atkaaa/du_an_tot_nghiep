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
    public function create()
{
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
        'admin_xem_truoc.khuyen-mai-create',
        compact('sanPhams', 'bienThes')
    );
}
   public function store(Request $request)
{
    $data = $request->validate([
        'ten_chuong_trinh' =>
            'required|string|max:255',

        'loai_giam_gia' =>
            'required|string|max:50',

        'gia_tri_giam' => [
    'required',
    'numeric',
    'gt:0',

    function ($attribute, $value, $fail) use ($request) {

        $loai = strtolower(
            trim((string) $request->loai_giam_gia)
        );

        if (
            in_array(
                $loai,
                [
                    'phan_tram',
                    'percent',
                    'percentage'
                ],
                true
            )
            && (float) $value > 100
        ) {
            $fail(
                'Giá trị giảm theo phần trăm không được vượt quá 100%.'
            );
        }
    },
],

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

        // Phạm vi áp dụng
        'pham_vi' =>
            'required|in:hoa_don,san_pham',

        // Sản phẩm
        'id_san_phams' => [
            'nullable',
            'array',
        ],

        'id_san_phams.*' => [
            'integer',
            'exists:san_pham,id',
        ],

        // Biến thể
        'id_bien_thes' => [
            'nullable',
            'array',
        ],

        'id_bien_thes.*' => [
            'integer',
            'exists:bien_the_san_pham,id',
        ],
    ]);


    /*
     * ================================
     * LẤY PHẠM VI
     * ================================
     */

    $phamVi = $data['pham_vi'];

    $idSanPhams =
        $data['id_san_phams'] ?? [];

    $idBienThes =
        $data['id_bien_thes'] ?? [];


    /*
     * ================================
     * NẾU LÀ KHUYẾN MÃI SẢN PHẨM
     * THÌ PHẢI CHỌN ÍT NHẤT 1 SP
     * ================================
     */

    if (
        $phamVi === 'san_pham' &&
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


    /*
     * ================================
     * KHÔNG LƯU CÁC FIELD NÀY
     * VÀO BẢNG khuyen_mai
     * ================================
     */

    unset(
        $data['pham_vi'],
        $data['id_san_phams'],
        $data['id_bien_thes']
    );


    /*
     * Checkbox không tick thì request
     * không gửi lên nên dùng boolean()
     */
    $data['trang_thai'] =
        $request->boolean('trang_thai');


    DB::transaction(function () use (
        $data,
        $phamVi,
        $idSanPhams,
        $idBienThes
    ) {

        /*
         * ================================
         * TẠO KHUYẾN MÃI
         * ================================
         */

        $khuyenMai =
            KhuyenMai::create($data);


        /*
         * ================================
         * KHUYẾN MÃI TOÀN HÓA ĐƠN
         * ================================
         *
         * Không thêm dữ liệu vào
         * khuyen_mai_san_pham.
         *
         * Sau này POS dựa vào việc
         * không có sản phẩm liên kết
         * để biết đây là voucher hóa đơn.
         */

        if ($phamVi === 'hoa_don') {
            return;
        }


        /*
         * ================================
         * KHUYẾN MÃI SẢN PHẨM
         * ================================
         *
         * Nếu chọn sản phẩm cha:
         * id_bien_the_san_pham = NULL
         *
         * => áp dụng toàn bộ biến thể
         */

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


        /*
         * ================================
         * BIẾN THỂ ĐƯỢC CHỌN RIÊNG
         * ================================
         */

        foreach (
            array_unique($idBienThes)
            as $idBienThe
        ) {

            $bienThe =
                DB::table(
                    'bien_the_san_pham'
                )
                ->where(
                    'id',
                    $idBienThe
                )
                ->first();


            if (!$bienThe) {
                continue;
            }


            /*
             * Nếu đã chọn cả sản phẩm cha
             * thì không cần lưu biến thể
             * của sản phẩm đó nữa.
             */
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
        ->route('khuyen-mai.index')
        ->with(
            'success',
            'Tạo chương trình khuyến mãi thành công'
        );
}
public function show($id)
{
    $khuyenMai = KhuyenMai::findOrFail($id);

    $sanPhamApDung = DB::table('khuyen_mai_san_pham')
        ->join(
            'san_pham',
            'khuyen_mai_san_pham.id_san_pham',
            '=',
            'san_pham.id'
        )
        ->leftJoin(
            'bien_the_san_pham',
            'khuyen_mai_san_pham.id_bien_the_san_pham',
            '=',
            'bien_the_san_pham.id'
        )
        ->where('khuyen_mai_san_pham.id_khuyen_mai', $id)
        ->select(
            'khuyen_mai_san_pham.id_san_pham',
            'khuyen_mai_san_pham.id_bien_the_san_pham',
            'san_pham.ten_san_pham',
            'bien_the_san_pham.ten_bien_the',
            'bien_the_san_pham.ma_hang',
            'bien_the_san_pham.gia_ban'
        )
        ->get();

    $laKhuyenMaiHoaDon = $sanPhamApDung->isEmpty();

    // ===============================
    // THỐNG KÊ SỬ DỤNG KHUYẾN MÃI
    // ===============================
    $thongKe = DB::table('hoa_don_khuyen_mai')
        ->join(
            'hoa_don',
            'hoa_don_khuyen_mai.id_hoa_don',
            '=',
            'hoa_don.id'
        )
        ->where(
            'hoa_don_khuyen_mai.id_khuyen_mai',
            $id
        )
        ->whereNotIn('hoa_don.trang_thai', [
            'Đã hủy',
            'Hủy',
            'da_huy',
            'huy'
        ])
        ->selectRaw('
            COUNT(DISTINCT hoa_don.id) as so_hoa_don,
            COUNT(*) as so_luot_ap_dung,
            COALESCE(SUM(hoa_don_khuyen_mai.tien_giam), 0) as tong_tien_giam
        ')
        ->first();

    // ===============================
    // TÍNH DOANH THU CHÍNH XÁC
    // ===============================
    $doanhThu = DB::table('hoa_don')
        ->whereIn(
            'id',
            DB::table('hoa_don_khuyen_mai')
                ->where('id_khuyen_mai', $id)
                ->select('id_hoa_don')
        )
        ->whereNotIn('trang_thai', [
            'Đã hủy',
            'Hủy',
            'da_huy',
            'huy'
        ])
        ->sum('khach_can_tra');

    $thongKe->doanh_thu = $doanhThu;

    // Giá trị hóa đơn trung bình
    $thongKe->gia_tri_trung_binh =
        $thongKe->so_hoa_don > 0
            ? $thongKe->doanh_thu / $thongKe->so_hoa_don
            : 0;

    // Tiền giảm trung bình mỗi lượt
    $thongKe->giam_trung_binh =
        $thongKe->so_luot_ap_dung > 0
            ? $thongKe->tong_tien_giam / $thongKe->so_luot_ap_dung
            : 0;

    // ===============================
    // 5 HÓA ĐƠN GẦN NHẤT
    // ===============================
   // ===============================
// DANH SÁCH HÓA ĐƠN + BỘ LỌC
// ===============================
$hoaDonQuery = DB::table('hoa_don_khuyen_mai')
    ->join(
        'hoa_don',
        'hoa_don_khuyen_mai.id_hoa_don',
        '=',
        'hoa_don.id'
    )
    ->where(
        'hoa_don_khuyen_mai.id_khuyen_mai',
        $id
    )
    ->whereNotIn('hoa_don.trang_thai', [
        'Đã hủy',
        'Hủy',
        'da_huy',
        'huy'
    ]);

// Lọc từ ngày
if (request('tu_ngay')) {
    $hoaDonQuery->whereDate(
        'hoa_don.created_at',
        '>=',
        request('tu_ngay')
    );
}

// Lọc đến ngày
if (request('den_ngay')) {
    $hoaDonQuery->whereDate(
        'hoa_don.created_at',
        '<=',
        request('den_ngay')
    );
}

// Lọc theo loại áp dụng
if (request('loai_ap_dung')) {
    $hoaDonQuery->where(
        'hoa_don_khuyen_mai.loai_ap_dung',
        request('loai_ap_dung')
    );
}

// Phân trang 10 hóa đơn / trang
$hoaDonGanDay = $hoaDonQuery
    ->select(
        'hoa_don.id',
        'hoa_don.khach_can_tra',
        'hoa_don.created_at',
        'hoa_don_khuyen_mai.tien_giam',
        'hoa_don_khuyen_mai.loai_ap_dung'
    )
    ->orderByDesc('hoa_don.created_at')
    ->paginate(10)
    ->withQueryString();;

    return view(
        'admin_xem_truoc.khuyen-mai-show',
        compact(
            'khuyenMai',
            'sanPhamApDung',
            'laKhuyenMaiHoaDon',
            'thongKe',
            'hoaDonGanDay'
        )
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


    $phamViDaChon = DB::table(
        'khuyen_mai_san_pham'
    )
        ->where(
            'id_khuyen_mai',
            $promo->id
        )
        ->get();


    /*
     * Không có dòng sản phẩm
     * => KM hóa đơn.
     */
    $phamVi = $phamViDaChon->isEmpty()
        ? 'hoa_don'
        : 'san_pham';


    /*
     * Sản phẩm được chọn toàn bộ biến thể
     */
    $idSanPhamsDaChon = $phamViDaChon
        ->whereNull(
            'id_bien_the_san_pham'
        )
        ->pluck('id_san_pham')
        ->map(
            fn ($id) => (int) $id
        )
        ->values()
        ->all();


    /*
     * Các biến thể được chọn riêng
     */
    $idBienThesDaChon = $phamViDaChon
        ->whereNotNull(
            'id_bien_the_san_pham'
        )
        ->pluck(
            'id_bien_the_san_pham'
        )
        ->map(
            fn ($id) => (int) $id
        )
        ->values()
        ->all();


    return view(
        'admin_xem_truoc.khuyen-mai-edit',
        compact(
            'promo',
            'sanPhams',
            'bienThes',
            'phamVi',
            'idSanPhamsDaChon',
            'idBienThesDaChon'
        )
    );
}

    // Cập nhật khuyến mãi
    public function update(Request $request, $id)
{
    $promo = KhuyenMai::findOrFail($id);

    /*
    |--------------------------------------------------------------------------
    | XÁC ĐỊNH PHẠM VI KHUYẾN MÃI
    |--------------------------------------------------------------------------
    | Nếu form edit có gửi pham_vi thì dùng pham_vi.
    | Nếu form cũ chưa có pham_vi:
    | - Có dòng trong khuyen_mai_san_pham => KM sản phẩm
    | - Không có dòng => KM hóa đơn
    */
    $phamViHienTai = DB::table('khuyen_mai_san_pham')
        ->where('id_khuyen_mai', $promo->id)
        ->exists()
            ? 'san_pham'
            : 'hoa_don';

    $phamVi = $request->input(
        'pham_vi',
        $phamViHienTai
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATE CƠ BẢN
    |--------------------------------------------------------------------------
    */
    $data = $request->validate(
        [
            'ten_chuong_trinh' => [
                'required',
                'string',
                'max:255',
            ],

            'loai_giam_gia' => [
                'required',
                'string',
                'max:50',
            ],

            'gia_tri_giam' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'giam_toi_da' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'so_luong_sp_toi_thieu' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'don_hang_toi_thieu' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'ngay_bat_dau' => [
                'nullable',
                'date',
            ],

            'ngay_ket_thuc' => [
                'nullable',
                'date',
                'after_or_equal:ngay_bat_dau',
            ],

            'trang_thai' => [
                'sometimes',
                'boolean',
            ],

            'ghi_chu' => [
                'nullable',
                'string',
            ],

            'pham_vi' => [
                'nullable',
                'in:hoa_don,san_pham',
            ],

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
        ],
        [
            'ten_chuong_trinh.required' =>
                'Vui lòng nhập tên chương trình.',

            'loai_giam_gia.required' =>
                'Vui lòng chọn loại khuyến mãi.',

            'gia_tri_giam.required' =>
                'Vui lòng nhập giá trị giảm.',

            'gia_tri_giam.numeric' =>
                'Giá trị giảm phải là số.',

            'gia_tri_giam.gt' =>
                'Giá trị giảm phải lớn hơn 0.',

            'giam_toi_da.numeric' =>
                'Giảm tối đa phải là số.',

            'giam_toi_da.min' =>
                'Giảm tối đa không được âm.',

            'so_luong_sp_toi_thieu.integer' =>
                'Số lượng sản phẩm tối thiểu phải là số nguyên.',

            'so_luong_sp_toi_thieu.min' =>
                'Số lượng sản phẩm tối thiểu không được âm.',

            'don_hang_toi_thieu.numeric' =>
                'Đơn hàng tối thiểu phải là số.',

            'don_hang_toi_thieu.min' =>
                'Đơn hàng tối thiểu không được âm.',

            'ngay_ket_thuc.after_or_equal' =>
                'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | KIỂM TRA GIẢM %
    |--------------------------------------------------------------------------
    */
    $loaiGiamGia = strtolower(
        trim((string) $request->loai_giam_gia)
    );

    if (
        in_array(
            $loaiGiamGia,
            [
                'phan_tram',
                'percent',
                'percentage',
            ],
            true
        )
        &&
        (float) $request->gia_tri_giam > 100
    ) {
        return redirect()
            ->back()
            ->withInput()
            ->withErrors([
                'gia_tri_giam' =>
                    'Giá trị giảm theo phần trăm không được vượt quá 100%.',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | LẤY SẢN PHẨM / BIẾN THỂ
    |--------------------------------------------------------------------------
    */
    $idSanPhams = $data['id_san_phams'] ?? [];

    $idBienThes = $data['id_bien_thes'] ?? [];


    /*
    |--------------------------------------------------------------------------
    | KM SẢN PHẨM PHẢI CHỌN ÍT NHẤT 1 SP / BIẾN THỂ
    |--------------------------------------------------------------------------
    */
    if (
        $phamVi === 'san_pham'
        &&
        empty($idSanPhams)
        &&
        empty($idBienThes)
    ) {
        return redirect()
            ->back()
            ->withInput()
            ->withErrors([
                'id_san_phams' =>
                    'Bạn phải chọn ít nhất một sản phẩm hoặc biến thể.',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | KHÔNG LƯU CÁC FIELD PHỤ VÀO BẢNG khuyen_mai
    |--------------------------------------------------------------------------
    */
    unset(
        $data['pham_vi'],
        $data['id_san_phams'],
        $data['id_bien_thes']
    );

    $data['trang_thai'] =
        $request->boolean('trang_thai');


    /*
    |--------------------------------------------------------------------------
    | CẬP NHẬT
    |--------------------------------------------------------------------------
    */
    DB::transaction(function () use (
        $promo,
        $data,
        $phamVi,
        $idSanPhams,
        $idBienThes
    ) {

        // Cập nhật thông tin chương trình
        $promo->update($data);


        /*
        |--------------------------------------------------------------------------
        | XÓA PHẠM VI CŨ
        |--------------------------------------------------------------------------
        */
        DB::table('khuyen_mai_san_pham')
            ->where(
                'id_khuyen_mai',
                $promo->id
            )
            ->delete();


        /*
        |--------------------------------------------------------------------------
        | NẾU LÀ KM HÓA ĐƠN
        |--------------------------------------------------------------------------
        | Không lưu dòng nào vào khuyen_mai_san_pham.
        */
        if ($phamVi === 'hoa_don') {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SẢN PHẨM CHA
        |--------------------------------------------------------------------------
        | id_bien_the_san_pham = NULL
        | => áp dụng tất cả biến thể của sản phẩm.
        */
        foreach (
            array_unique($idSanPhams)
            as $idSanPham
        ) {

            DB::table('khuyen_mai_san_pham')
                ->insert([
                    'id_khuyen_mai' =>
                        $promo->id,

                    'id_san_pham' =>
                        $idSanPham,

                    'id_bien_the_san_pham' =>
                        null,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | BIẾN THỂ CHỌN RIÊNG
        |--------------------------------------------------------------------------
        */
        foreach (
            array_unique($idBienThes)
            as $idBienThe
        ) {

            $bienThe = DB::table(
                'bien_the_san_pham'
            )
                ->where(
                    'id',
                    $idBienThe
                )
                ->first();

            if (!$bienThe) {
                continue;
            }


            /*
             * Nếu đã chọn sản phẩm cha thì
             * không cần lưu thêm biến thể.
             */
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


            DB::table('khuyen_mai_san_pham')
                ->insert([
                    'id_khuyen_mai' =>
                        $promo->id,

                    'id_san_pham' =>
                        $bienThe->product_id,

                    'id_bien_the_san_pham' =>
                        $bienThe->id,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);
        }
    });


    return redirect()
        ->route(
            'khuyen-mai.edit',
            $promo->id
        )
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