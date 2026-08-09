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
        $selectedDate = $request->filled('ngay')
            ? Carbon::parse($request->ngay)->toDateString()
            : Carbon::today()->toDateString();

        $dayStart = Carbon::parse($selectedDate)->startOfDay();
        $dayEnd = Carbon::parse($selectedDate)->endOfDay();

        $ordersQuery = DB::table('hoa_don')
            ->whereBetween('created_at', [$dayStart, $dayEnd]);

        $completedOrdersQuery = (clone $ordersQuery)
            ->where('trang_thai', 'Hoàn thành');

        $dailyRevenue = (clone $completedOrdersQuery)
            ->sum('tong_tien_hang');

        $totalOrders = (clone $ordersQuery)->count();
        $completedOrders = (clone $completedOrdersQuery)->count();
        $cancelledOrders = (clone $ordersQuery)
            ->where('trang_thai', 'Đã hủy')
            ->count();

        $productsSold = DB::table('chi_tiet_hoa_don')
            ->join('hoa_don', 'chi_tiet_hoa_don.id_hoa_don', '=', 'hoa_don.id')
            ->whereBetween('hoa_don.created_at', [$dayStart, $dayEnd])
            ->where('hoa_don.trang_thai', 'Hoàn thành')
            ->sum('chi_tiet_hoa_don.so_luong');

        $uniqueCustomerCount = (clone $ordersQuery)
            ->whereNotNull('id_khach_hang')
            ->distinct('id_khach_hang')
            ->count('id_khach_hang');

        $newCustomerCount = DB::table('hoa_don')
            ->join('khach_hang', 'hoa_don.id_khach_hang', '=', 'khach_hang.id')
            ->whereBetween('hoa_don.created_at', [$dayStart, $dayEnd])
            ->whereDate('khach_hang.created_at', $selectedDate)
            ->distinct('khach_hang.id')
            ->count('khach_hang.id');

        $discountTotal = (clone $ordersQuery)
            ->sum('tien_giam_gia');

        $pointsEarned = (clone $ordersQuery)
            ->sum('diem_thu_duoc');

        $pointsUsed = (clone $ordersQuery)
            ->sum('diem_su_dung');

        $averageOrderValue = $completedOrders > 0
            ? round($dailyRevenue / $completedOrders)
            : 0;

        $last3DaysStats = [];
        for ($i = 2; $i >= 0; $i--) {
            $date = Carbon::parse($selectedDate)->subDays($i)->toDateString();
            $dayQuery = DB::table('hoa_don')->whereDate('created_at', $date);
            $last3DaysStats[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->format('d/m/Y'),
                'revenue' => (float) $dayQuery->where('trang_thai', 'Hoàn thành')->sum('tong_tien_hang'),
                'orders' => $dayQuery->count(),
                'completed' => $dayQuery->where('trang_thai', 'Hoàn thành')->count(),
                'cancelled' => $dayQuery->where('trang_thai', 'Đã hủy')->count(),
            ];
        }

        $paymentRows = (clone $completedOrdersQuery)
            ->selectRaw("CASE
                WHEN phuong_thuc_thanh_toan IN ('cash','tien_mat','Tiền mặt') THEN 'Tiền mặt'
                WHEN phuong_thuc_thanh_toan IN ('transfer','chuyen_khoan','Chuyển khoản') THEN 'Chuyển khoản'
                ELSE COALESCE(phuong_thuc_thanh_toan, 'Khác')
            END as method")
            ->selectRaw('SUM(tong_tien_hang) as revenue')
            ->groupBy('method')
            ->orderBy('method')
            ->get();

        $paymentBreakdown = [
            'Tiền mặt' => 0,
            'Chuyển khoản' => 0,
            'Khác' => 0,
        ];

        foreach ($paymentRows as $row) {
            $paymentBreakdown[$row->method] = (float) $row->revenue;
        }

        $hourlyRows = (clone $completedOrdersQuery)
            ->selectRaw('HOUR(created_at) as hour')
            ->selectRaw('SUM(tong_tien_hang) as revenue')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hourlyRevenue = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyRevenue[$hour] = 0;
        }

        foreach ($hourlyRows as $row) {
            $hourlyRevenue[(int) $row->hour] = (float) $row->revenue;
        }

        $hourLabels = array_map(function ($hour) {
            return sprintf('%02d:00', $hour);
        }, range(0, 23));

        $topProductsSold = DB::table('chi_tiet_hoa_don')
            ->join('hoa_don', 'chi_tiet_hoa_don.id_hoa_don', '=', 'hoa_don.id')
            ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
            ->whereBetween('hoa_don.created_at', [$dayStart, $dayEnd])
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
            ->join('san_pham', 'chi_tiet_hoa_don.id_san_pham', '=', 'san_pham.id')
            ->whereBetween('hoa_don.created_at', [$dayStart, $dayEnd])
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
            ->whereBetween('hoa_don.created_at', [$dayStart, $dayEnd])
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
            ->whereBetween('hoa_don.created_at', [$dayStart, $dayEnd])
            ->where('hoa_don.trang_thai', 'Hoàn thành')
            ->groupBy('hoa_don.id_nguoi_dung', 'nguoi_dung.ho_ten')
            ->select(
                'hoa_don.id_nguoi_dung as staff_id',
                DB::raw("COALESCE(nguoi_dung.ho_ten, 'Chưa phân công') as staff_name"),
                DB::raw('SUM(hoa_don.tong_tien_hang) as total_revenue'),
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
            ->whereBetween('hoa_don.created_at', [$dayStart, $dayEnd])
            ->orderByDesc('hoa_don.created_at')
            ->paginate(10)
            ->withQueryString();

        $dailyStats = [
            'date' => Carbon::parse($selectedDate)->format('d/m/Y'),
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
            'selectedDate',
            'dailyStats',
            'paymentBreakdown',
            'hourLabels',
            'hourlyRevenue',
            'topProductsSold',
            'topProductsSlow',
            'topCustomers',
            'staffPerformance',
            'dailyOrders',
            'last3DaysStats'
        ));
    }
}