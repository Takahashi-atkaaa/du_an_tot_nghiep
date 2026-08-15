@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Dashboard - SmartMart')

@section('content')

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-primary mb-0">
            <i class="fas fa-chart-line me-2"></i>
                        Thống kê
        </h2>
        <small class="text-muted">Tổng quan hoạt động kinh doanh</small>
    </div>
    <div class="text-end">
        <span class="badge bg-primary fs-6 px-3 py-2">
            <i class="fas fa-calendar"></i>
            {{ now()->format('d/m/Y') }}
        </span>
    </div>
</div>

<div class="card table-admin mb-4">
    <div class="card-body py-3">

        <form method="GET" action="{{ url('admin/dashboard') }}">

            {{-- BỘ LỌC NHANH --}}
            <div class="mb-3">

                <label class="form-label fw-semibold mb-2">
                    <i class="fas fa-bolt text-warning me-1"></i>
                    Bộ lọc nhanh
                </label>

                <div class="d-flex flex-wrap gap-2">

                    {{-- 3 ngày --}}
                    <button
                        type="submit"
                        name="bo_loc"
                        value="3_ngay"
                        class="btn {{ ($quickFilter ?? '') === '3_ngay'
                            ? 'btn-primary'
                            : 'btn-outline-primary' }}"
                    >
                        <i class="fas fa-calendar-day me-1"></i>
                        3 ngày
                    </button>

                    {{-- 7 ngày --}}
                    <button
                        type="submit"
                        name="bo_loc"
                        value="7_ngay"
                        class="btn {{ ($quickFilter ?? '') === '7_ngay'
                            ? 'btn-primary'
                            : 'btn-outline-primary' }}"
                    >
                        <i class="fas fa-calendar-week me-1"></i>
                        7 ngày
                    </button>

                    {{-- Tháng --}}
                    <button
                        type="submit"
                        name="bo_loc"
                        value="thang"
                        class="btn {{ ($quickFilter ?? '') === 'thang'
                            ? 'btn-primary'
                            : 'btn-outline-primary' }}"
                    >
                        <i class="fas fa-calendar-alt me-1"></i>
                        Tháng này
                    </button>

                    {{-- Năm --}}
                    <button
                        type="submit"
                        name="bo_loc"
                        value="nam"
                        class="btn {{ ($quickFilter ?? '') === 'nam'
                            ? 'btn-primary'
                            : 'btn-outline-primary' }}"
                    >
                        <i class="fas fa-calendar me-1"></i>
                        Năm nay
                    </button>

                    {{-- Hôm nay --}}
                    <a
                        href="{{ url('admin/dashboard') }}"
                        class="btn btn-outline-secondary"
                    >
                        <i class="fas fa-sync-alt me-1"></i>
                        Hôm nay
                    </a>

                </div>
            </div>


            {{-- LỌC THEO KHOẢNG NGÀY --}}
            <div class="row g-3 align-items-end">

                {{-- Từ ngày --}}
                <div class="col-12 col-md-3">

                    <label class="form-label fw-semibold mb-2">
                        Từ ngày
                    </label>

                    <input
                        type="date"
                        name="tu_ngay"
                        class="form-control"
                        value="{{ $selectedStartDate ?? now()->toDateString() }}"
                    >

                </div>


                {{-- Đến ngày --}}
                <div class="col-12 col-md-3">

                    <label class="form-label fw-semibold mb-2">
                        Đến ngày
                    </label>

                    <input
                        type="date"
                        name="den_ngay"
                        class="form-control"
                        value="{{ $selectedEndDate ?? now()->toDateString() }}"
                    >

                </div>


                {{-- Nút lọc --}}
                <div class="col-12 col-md-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        <i class="fas fa-filter me-2"></i>
                        Lọc
                    </button>

                </div>


                {{-- Thông tin khoảng thời gian --}}
                <div class="col-12 col-md-4">

                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">

                        <div class="text-muted small">

                            Thống kê:

                            <strong class="text-dark">
                                {{ $dailyStats['range_label'] ?? now()->format('d/m/Y') }}
                            </strong>

                        </div>

                        <a
                            href="{{ url('admin/hoa-don?tu_ngay=' . $selectedStartDate . '&den_ngay=' . $selectedEndDate) }}"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            <i class="fas fa-file-invoice me-1"></i>
                            Xem đơn hàng
                        </a>

                    </div>

                </div>

            </div>

        </form>

    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-wallet"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Doanh thu</h6>
                    <h3 class="mb-0">{{ number_format($dailyStats['revenue'] ?? 0, 0, ',', '.') }} đ</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng đơn</h6>
                    <h3 class="mb-0">{{ number_format($dailyStats['total_orders'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Hoàn thành</h6>
                    <h3 class="mb-0">{{ number_format($dailyStats['completed_orders'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-times-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đã hủy</h6>
                    <h3 class="mb-0">{{ number_format($dailyStats['cancelled_orders'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat purple h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-boxes"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Sản phẩm đã bán</h6>
                    <h3 class="mb-0">{{ number_format($dailyStats['products_sold'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat cyan h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-user-friends"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Khách hàng</h6>
                    <h3 class="mb-0">{{ number_format($dailyStats['customers'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat info h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-user-plus"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Khách hàng mới</h6>
                    <h3 class="mb-0">{{ number_format($dailyStats['new_customers'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat gray h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-calculator"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Giá trị đơn TB</h6>
                    <h3 class="mb-0">{{ number_format($dailyStats['average_order_value'] ?? 0, 0, ',', '.') }} đ</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Tổng giảm giá</h5>
            </div>
            <div class="card-body">
                <h3>{{ number_format($dailyStats['discount_total'] ?? 0, 0, ',', '.') }} đ</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Điểm thưởng kiếm được</h5>
            </div>
            <div class="card-body">
                <h3>{{ number_format($dailyStats['points_earned'] ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Điểm đã dùng</h5>
            </div>
            <div class="card-body">
                <h3>{{ number_format($dailyStats['points_used'] ?? 0) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
<h5 class="mb-0 fw-bold">
    {{ $chartTitle ?? 'Doanh thu' }}
</h5>            </div>
            <div class="card-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Phương thức thanh toán</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($paymentBreakdown as $method => $amount)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div>
                                <strong>{{ $method }}</strong>
                                <div class="text-muted small">Doanh thu</div>
                            </div>
                            <span class="badge bg-success fs-6">{{ number_format($amount, 0, ',', '.') }} đ</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>



<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Top sản phẩm bán chạy</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($topProductsSold as $product)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $product->ten_san_pham }}</h6>
                                    <small class="text-muted">Số lượng: {{ number_format($product->total_quantity) }}</small>
                                </div>
                                <span class="badge bg-success fs-6">{{ number_format($product->total_revenue, 0, ',', '.') }} đ</span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">Chưa có dữ liệu sản phẩm bán chạy.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Top sản phẩm bán chậm</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProductsSlow as $product)
                                <tr>
                                    <td>{{ $product->ten_san_pham }}</td>
                                    <td>{{ number_format($product->total_quantity) }}</td>
                                    <td>{{ number_format($product->total_revenue, 0, ',', '.') }} đ</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu sản phẩm bán chậm.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Khách hàng tiêu biểu</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Khách hàng</th>
                                <th>Đơn</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->ten_khach_hang }}</td>
                                    <td>{{ number_format($customer->order_count) }}</td>
                                    <td>{{ number_format($customer->total_revenue, 0, ',', '.') }} đ</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu khách hàng.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Hiệu suất nhân viên</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Đơn</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffPerformance as $staff)
                                <tr>
                                    <td>{{ $staff->staff_name }}</td>
                                    <td>{{ number_format($staff->order_count) }}</td>
                                    <td>{{ number_format($staff->total_revenue, 0, ',', '.') }} đ</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu nhân viên.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card table-admin mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Danh sách đơn hàng trong khoảng thời gian</h5>
        <a href="{{ url('admin/hoa-don?tu_ngay=' . $selectedStartDate . '&den_ngay=' . $selectedEndDate) }}" class="btn btn-sm btn-primary">Xem tất cả</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Khách hàng</th>
                        <th>Nhân viên</th>
                        <th>Ca</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyOrders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->ten_khach_hang ?? 'Khách lẻ' }}</td>
                            <td>{{ $order->ten_nhan_vien ?? 'Không có' }}</td>
                            <td>{{ $order->ten_ca ?? 'Không có' }}</td>
                            <td>{{ number_format($order->khach_can_tra ?? 0, 0, ',', '.') }} đ</td>
                            <td>{{ $order->trang_thai }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu đơn hàng.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $dailyOrders->links() }}
    </div>
</div>

@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const chartElement = document.getElementById('revenueChart');

    if (!chartElement) {
        return;
    }

    new Chart(chartElement, {
        type: 'line',

        data: {
            labels: @json($chartLabels ?? []),

            datasets: [{
                label: @json($chartTitle ?? 'Doanh thu'),

                data: @json($chartData ?? []),

                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.15)',

                borderWidth: 2,
                fill: true,
                tension: 0.3,

                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: true
                },

                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('vi-VN')
                                .format(context.raw) + ' đ';
                        }
                    }
                }
            },

            scales: {
                y: {
                    beginAtZero: true,

                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN')
                                .format(value) + ' đ';
                        }
                    }
                }
            }
        }
    });
});
</script>