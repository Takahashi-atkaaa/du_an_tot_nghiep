<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\CanhBao;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | DOANH THU HÔM NAY
        |--------------------------------------------------------------------------
        */
        $doanhThuNgay = DB::table('hoa_don')
            ->whereDate('created_at', Carbon::today())
            ->where('trang_thai', 'Hoàn thành')
            ->sum('tong_tien_hang');


        /*
        |--------------------------------------------------------------------------
        | DOANH THU THÁNG
        |--------------------------------------------------------------------------
        */

        $doanhThuThang = DB::table('hoa_don')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('trang_thai', 'Hoàn thành')
            ->sum('tong_tien_hang');



        /*
        |--------------------------------------------------------------------------
        | DOANH THU NĂM
        |--------------------------------------------------------------------------
        */

        $doanhThuNam = DB::table('hoa_don')
            ->whereYear('created_at', Carbon::now()->year)
            ->where('trang_thai', 'Hoàn thành')
            ->sum('tong_tien_hang');


        $doanhThuTheoNam = DB::table('hoa_don')
            ->selectRaw('YEAR(created_at) as nam')
            ->selectRaw('SUM(tong_tien_hang) as doanh_thu')
            ->where('trang_thai', 'Hoàn thành') // sửa theo dữ liệu của bạn
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'))
            ->get();
        /*
        |--------------------------------------------------------------------------
        | BIỂU ĐỒ DOANH THU 7 NGÀY
        |--------------------------------------------------------------------------
        */

        $doanhThu7Ngay = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = Carbon::today()->subDays($i);

            $tong = DB::table('hoa_don')
                ->whereDate('created_at', $date)
                ->where('trang_thai', 'Hoàn thành')
                ->sum('tong_tien_hang');

            $doanhThu7Ngay[] = [

                'ngay' => $date->format('d/m'),

                'tong' => $tong

            ];
        }



        /*
        |--------------------------------------------------------------------------
        | BIỂU ĐỒ DOANH THU 12 THÁNG
        |--------------------------------------------------------------------------
        */

        $doanhThu12Thang = [];

        for ($i = 1; $i <= 12; $i++) {

            $tong = DB::table('hoa_don')
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', Carbon::now()->year)
                ->where('trang_thai', 'Hoàn thành')
                ->sum('tong_tien_hang');

            $doanhThu12Thang[] = [

                'thang' => 'Tháng '.$i,

                'tong' => $tong

            ];
        }



        /*
        |--------------------------------------------------------------------------
        | TOP 10 SẢN PHẨM BÁN CHẠY
        |--------------------------------------------------------------------------
        */

        $topSanPham = DB::table('chi_tiet_hoa_don')
            ->join(
                'san_pham',
                'chi_tiet_hoa_don.id_san_pham',
                '=',
                'san_pham.id'
            )

            ->select(

                'san_pham.id',

                'san_pham.ten_san_pham',

                DB::raw('SUM(chi_tiet_hoa_don.so_luong) as tong_ban'),

                DB::raw('SUM(chi_tiet_hoa_don.thanh_tien) as doanh_thu')

            )

            ->groupBy(

                'san_pham.id',

                'san_pham.ten_san_pham'

            )

            ->orderByDesc('tong_ban')

            ->limit(5)

            ->get();

        //Các sản phẩm bán chậm 
        $sanPhamBanCham = DB::table('chi_tiet_hoa_don')
            ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
            ->select('san_pham.id', 'san_pham.ten_san_pham', 
                DB::raw('SUM(chi_tiet_hoa_don.so_luong) as tong_ban'), 
                DB::raw('SUM(chi_tiet_hoa_don.thanh_tien) as doanh_thu'))
            ->groupBy('san_pham.id', 'san_pham.ten_san_pham')
            ->orderBy('tong_ban', 'asc')
            ->limit(5)
            ->get();

        foreach ($sanPhamBanCham as $sp) {
            $sp->ton_kho = DB::table('chi_tiet_phieu')
                ->where('id_san_pham', $sp->id)
                ->sum('so_luong_con_lai');
        }


        //Tổng sản phẩm tồn kho
        $tongSanPhamTonKho = DB::table('chi_tiet_phieu')
             ->sum('so_luong');

        //khách hàng thân thiết
        $khachHangThanThiet = DB::table('hoa_don')
             ->join ('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
             ->select('khach_hang.id', 'khach_hang.ten_khach_hang', 
                   DB::raw('SUM(hoa_don.khach_can_tra) as tong_mua'))
            ->groupBy('khach_hang.id', 'khach_hang.ten_khach_hang')
            ->orderByDesc('tong_mua')   
            ->limit(5)
            ->get();

        //Đơn hàng gần đây
        $donHangGanDay = DB::table('hoa_don')
            ->join('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->select('hoa_don.*', 'khach_hang.ten_khach_hang')
            ->orderByDesc('hoa_don.created_at')
            ->limit(5)
            ->get();

        //Hoat dong dang nhan (cho panel KiotViet)
        $soHoatDongDangNhan = CanhBao::where('da_doc', false)->count();

        $hoatDongGanDay = CanhBao::with('nguoiDungThucHien')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(function ($cb) {
                $cb->thoi_gian_tuongdoi = $cb->created_at
                    ? $cb->created_at->diffForHumans(['parts' => 1, 'short' => true])
                    : '';
                return $cb;
            });

        return view(
            'admin_xem_truoc.dashboard',
            compact(

                'doanhThuNgay',

                'doanhThuThang',

                'doanhThuNam',

                'doanhThuTheoNam',

                'doanhThu7Ngay',

                'doanhThu12Thang',

                'topSanPham',

                'tongSanPhamTonKho',

                'khachHangThanThiet',
                'sanPhamBanCham',
                'donHangGanDay',
                'soHoatDongDangNhan',
                'hoatDongGanDay'

            )
        );
    }
}