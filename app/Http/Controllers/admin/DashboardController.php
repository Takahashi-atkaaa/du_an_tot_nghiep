<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $quickFilter = $request->get('bo_loc');

if ($quickFilter === '3_ngay') {

    $selectedStartDate = Carbon::today()
        ->subDays(2)
        ->toDateString();

    $selectedEndDate = Carbon::today()
        ->toDateString();

} elseif ($quickFilter === '7_ngay') {

    $selectedStartDate = Carbon::today()
        ->subDays(6)
        ->toDateString();

    $selectedEndDate = Carbon::today()
        ->toDateString();

} elseif ($quickFilter === 'thang') {

    $selectedStartDate = Carbon::today()
        ->startOfMonth()
        ->toDateString();

    $selectedEndDate = Carbon::today()
        ->endOfMonth()
        ->toDateString();

} elseif ($quickFilter === 'nam') {

    $selectedStartDate = Carbon::today()
        ->startOfYear()
        ->toDateString();

    $selectedEndDate = Carbon::today()
        ->endOfYear()
        ->toDateString();

} else {

    // Lọc thủ công từ ngày → đến ngày
    $selectedStartDate = $request->filled('tu_ngay')
        ? Carbon::parse($request->tu_ngay)->toDateString()
        : Carbon::today()->toDateString();

    $selectedEndDate = $request->filled('den_ngay')
        ? Carbon::parse($request->den_ngay)->toDateString()
        : Carbon::today()->toDateString();
}
        $rangeStart = Carbon::parse($selectedStartDate)->startOfDay();
        $rangeEnd = Carbon::parse($selectedEndDate)->endOfDay();

        if ($rangeStart->gt($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
        }

        $ordersQuery = DB::table('hoa_don')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd]);

        $completedOrdersQuery = (clone $ordersQuery)
            ->where('trang_thai', 'Hoàn thành');

        $dailyRevenue = (clone $completedOrdersQuery)
            ->sum('khach_can_tra');

        $totalOrders = (clone $ordersQuery)->count();
        $completedOrders = (clone $completedOrdersQuery)->count();
        $cancelledOrders = (clone $ordersQuery)
            ->where('trang_thai', 'Đã hủy')
            ->count();

        $productsSold = DB::table('chi_tiet_hoa_don')
            ->join('hoa_don', 'chi_tiet_hoa_don.id_hoa_don', '=', 'hoa_don.id')
            ->whereBetween('hoa_don.created_at', [$rangeStart, $rangeEnd])
            ->where('hoa_don.trang_thai', 'Hoàn thành')
            ->sum('chi_tiet_hoa_don.so_luong');

        $uniqueCustomerCount = (clone $completedOrdersQuery)
    ->whereNotNull('id_khach_hang')
    ->distinct('id_khach_hang')
    ->count('id_khach_hang');

        $newCustomerCount = DB::table('hoa_don')
            ->join('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->whereBetween('hoa_don.created_at', [$rangeStart, $rangeEnd])
            ->whereBetween('khach_hang.created_at', [$rangeStart, $rangeEnd])
            ->distinct('khach_hang.id')
            ->count('khach_hang.id');

      $discountTotal = (clone $completedOrdersQuery)
    ->sum('tien_giam_gia');

// Điểm thưởng kiếm được:
// Chỉ cộng đúng cột diem_thu_duoc của các hóa đơn đã hoàn thành.
$pointsEarned = (clone $completedOrdersQuery)
    ->sum('diem_thu_duoc');

$pointsUsed = (clone $completedOrdersQuery)
    ->sum('diem_su_dung');

    $averageOrderValue = $completedOrders > 0
    ? round($dailyRevenue / $completedOrders)
    : 0;

      

  $paymentRows = (clone $completedOrdersQuery)
    ->selectRaw("
        CASE
            WHEN LOWER(TRIM(phuong_thuc_thanh_toan)) IN ('cash', 'tien_mat', 'tiền mặt')
                THEN 'Tiền mặt'
            WHEN LOWER(TRIM(phuong_thuc_thanh_toan)) IN ('payos', 'transfer', 'chuyen_khoan', 'chuyển khoản')
                THEN 'PayOS'
            ELSE NULL
        END as method
    ")
    ->selectRaw('SUM(khach_can_tra) as revenue')
    ->whereNotNull('phuong_thuc_thanh_toan')
    ->groupBy('method')
    ->get();

$paymentBreakdown = [
    'Tiền mặt' => 0,
    'PayOS' => 0,
];

foreach ($paymentRows as $row) {
    if ($row->method !== null && array_key_exists($row->method, $paymentBreakdown)) {
        $paymentBreakdown[$row->method] = (float) $row->revenue;
    }
}

       /*
|--------------------------------------------------------------------------
| BIỂU ĐỒ DOANH THU
|--------------------------------------------------------------------------
| 1 ngày        → theo giờ
| 3 / 7 ngày    → theo ngày
| Tháng         → theo ngày
| Năm           → theo tháng
*/

$startDate = Carbon::parse($selectedStartDate);
$endDate = Carbon::parse($selectedEndDate);

$numberOfDays = $startDate->diffInDays($endDate) + 1;

$chartLabels = [];
$chartData = [];
$chartTitle = 'Doanh thu';

if ($quickFilter === 'nam') {

    // =========================
    // NĂM → THEO THÁNG
    // =========================

    $monthlyRows = (clone $completedOrdersQuery)
        ->selectRaw('MONTH(created_at) as month')
        ->selectRaw('SUM(khach_can_tra) as revenue')
        ->groupByRaw('MONTH(created_at)')
        ->orderByRaw('MONTH(created_at)')
        ->get()
        ->keyBy('month');

    for ($month = 1; $month <= 12; $month++) {

        $chartLabels[] = 'Tháng ' . $month;

        $chartData[] = isset($monthlyRows[$month])
            ? (float) $monthlyRows[$month]->revenue
            : 0;
    }

    $chartTitle = 'Doanh thu theo tháng';

} elseif ($numberOfDays === 1) {

    // =========================
    // 1 NGÀY → THEO GIỜ
    // =========================

    $hourlyRows = (clone $completedOrdersQuery)
        ->selectRaw('HOUR(created_at) as hour')
        ->selectRaw('SUM(khach_can_tra) as revenue')
        ->groupByRaw('HOUR(created_at)')
        ->orderByRaw('HOUR(created_at)')
        ->get()
        ->keyBy('hour');

    for ($hour = 0; $hour < 24; $hour++) {

        $chartLabels[] = sprintf('%02d:00', $hour);

        $chartData[] = isset($hourlyRows[$hour])
            ? (float) $hourlyRows[$hour]->revenue
            : 0;
    }

    $chartTitle = 'Doanh thu theo giờ';

} else {

    // =========================
    // 3 NGÀY / 7 NGÀY / THÁNG
    // → THEO NGÀY
    // =========================

    $dailyRows = (clone $completedOrdersQuery)
        ->selectRaw('DATE(created_at) as report_date')
        ->selectRaw('SUM(khach_can_tra) as revenue')
        ->groupByRaw('DATE(created_at)')
        ->orderByRaw('DATE(created_at)')
        ->get()
        ->keyBy('report_date');

    $currentDate = $startDate->copy();

    while ($currentDate->lte($endDate)) {

        $dateKey = $currentDate->toDateString();

        $chartLabels[] = $currentDate->format('d/m');

        $chartData[] = isset($dailyRows[$dateKey])
            ? (float) $dailyRows[$dateKey]->revenue
            : 0;

        $currentDate->addDay();
    }

    $chartTitle = 'Doanh thu theo ngày';
}

        $topProductsSold = DB::table('chi_tiet_hoa_don')
            ->join('hoa_don', 'chi_tiet_hoa_don.id_hoa_don', '=', 'hoa_don.id')
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->whereBetween('hoa_don.created_at', [$rangeStart, $rangeEnd])
            ->where('hoa_don.trang_thai', 'Hoàn thành')
            ->groupBy('san_pham.id', 'san_pham.ten_san_pham')
            ->select(
                'san_pham.id',
                'san_pham.ten_san_pham',
                DB::raw('SUM(chi_tiet_hoa_don.so_luong) as total_quantity'),
                DB::raw('SUM(chi_tiet_hoa_don.thanh_tien) as total_revenue')
            )
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        $topProductsSlow = DB::table('chi_tiet_hoa_don')
            ->join('hoa_don', 'chi_tiet_hoa_don.id_hoa_don', '=', 'hoa_don.id')
            ->join('bien_the_san_pham', 'chi_tiet_hoa_don.id_bien_the_san_pham', '=', 'bien_the_san_pham.id')
            ->join('san_pham', 'bien_the_san_pham.product_id', '=', 'san_pham.id')
            ->whereBetween('hoa_don.created_at', [$rangeStart, $rangeEnd])
            ->where('hoa_don.trang_thai', 'Hoàn thành')
            ->groupBy('san_pham.id', 'san_pham.ten_san_pham')
            ->select(
                'san_pham.id',
                'san_pham.ten_san_pham',
                DB::raw('SUM(chi_tiet_hoa_don.so_luong) as total_quantity'),
                DB::raw('SUM(chi_tiet_hoa_don.thanh_tien) as total_revenue')
            )
            ->orderBy('total_quantity', 'asc')
            ->limit(10)
            ->get();

        $topCustomers = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->whereBetween('hoa_don.created_at', [$rangeStart, $rangeEnd])
            ->where('hoa_don.trang_thai', 'Hoàn thành')
            ->groupBy('hoa_don.id_khach_hang', 'khach_hang.ten_khach_hang')
            ->select(
                'hoa_don.id_khach_hang as customer_id',
                DB::raw("COALESCE(khach_hang.ten_khach_hang, 'Khách lẻ') as ten_khach_hang"),
                DB::raw('SUM(hoa_don.khach_can_tra) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $staffPerformance = DB::table('hoa_don')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->whereBetween('hoa_don.created_at', [$rangeStart, $rangeEnd])
            ->where('hoa_don.trang_thai', 'Hoàn thành')
            ->groupBy('hoa_don.id_nguoi_dung', 'nguoi_dung.ho_ten')
            ->select(
                'hoa_don.id_nguoi_dung as staff_id',
                DB::raw("COALESCE(nguoi_dung.ho_ten, 'Chưa phân công') as staff_name"),
                DB::raw('SUM(hoa_don.khach_can_tra) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $dailyOrders = DB::table('hoa_don')
            ->leftJoin('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->leftJoin('nguoi_dung', 'hoa_don.id_nguoi_dung', '=', 'nguoi_dung.id')
            ->leftJoin('ca_lam_viec', 'hoa_don.id_ca_lam_viec', '=', 'ca_lam_viec.id')
            ->select(
                'hoa_don.*',
                'khach_hang.ten_khach_hang',
                'nguoi_dung.ho_ten as ten_nhan_vien',
                'ca_lam_viec.ten_ca as ten_ca'
            )
            ->whereBetween('hoa_don.created_at', [$rangeStart, $rangeEnd])
            ->orderByDesc('hoa_don.created_at')
            ->paginate(10)
            ->withQueryString();

        $rangeLabel = $rangeStart->toDateString() === $rangeEnd->toDateString()
            ? Carbon::parse($rangeStart)->format('d/m/Y')
            : Carbon::parse($rangeStart)->format('d/m/Y') . ' - ' . Carbon::parse($rangeEnd)->format('d/m/Y');

        $dailyStats = [
            'range_label' => $rangeLabel,
            'revenue' => (float) $dailyRevenue,
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'products_sold' => (int) $productsSold,
            'customers' => (int) $uniqueCustomerCount,
            'new_customers' => (int) $newCustomerCount,
            'discount_total' => (float) $discountTotal,
            'points_earned' => (int) $pointsEarned,
            'points_used' => (int) $pointsUsed,
            'average_order_value' => (float) $averageOrderValue,
        ];

       return view('admin_xem_truoc.dashboard', compact(
    'selectedStartDate',
    'selectedEndDate',
    'quickFilter',
    'dailyStats',
    'paymentBreakdown',

    // Biểu đồ
    'chartLabels',
    'chartData',
    'chartTitle',

    'topProductsSold',
    'topProductsSlow',
    'topCustomers',
    'staffPerformance',
    'dailyOrders',
));
    }
}