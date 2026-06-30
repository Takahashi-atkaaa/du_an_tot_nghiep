@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Dashboard - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold text-primary mb-0">
            <i class="fas fa-chart-line me-2"></i>
            Dashboard
        </h2>

        <small class="text-muted">
            Tổng quan hoạt động kinh doanh
        </small>

    </div>

    <div class="text-end">

        <span class="badge bg-primary fs-6 px-3 py-2">
            <i class="fas fa-calendar"></i>
            {{ now()->format('d/m/Y') }}
        </span>

    </div>

</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Doanh thu hôm nay</h6>
                    <h3 class="mb-0">
                    {{ number_format($doanhThuNgay,0,',','.') }} đ
                </h3>
                    <small class="text-success">
                        <i class="fas fa-arrow-up"></i> +12.5%
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Doanh thu tháng</h6>
                    <h3 class="mb-0">
                        {{ number_format($doanhThuThang,0,',','.') }} đ
                    </h3>
                    <small class="text-success">
                        <i class="fas fa-arrow-up"></i> +8.2%
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Doanh thu năm</h6>
                    <h3 class="mb-0">
                        {{ number_format($doanhThuNam,0,',','.') }} đ
                    </h3>
                    <small class="text-success">
                        <i class="fas fa-arrow-up"></i> +5.3%
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Sản phẩm tồn kho</h6>
                    <h3 class="mb-0">1,245 cái</h3>
                    <small class="text-danger">
                        <i class="fas fa-arrow-down"></i> -3.1%
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Tables -->
<div class="row g-4">

    <!-- Biểu đồ doanh thu -->
    <div class="col-xl-8">

        <!-- Doanh thu 7 ngày -->
        <div class="card table-admin mb-4">

            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold">
                    Doanh thu 7 ngày gần đây
                </h5>

            

            </div>

            <div class="card-body">
                <canvas id="chart7Ngay" height="120"></canvas>
            </div>

        </div>

        <!-- Doanh thu 12 tháng -->
        <div class="card table-admin">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    Doanh thu 12 tháng
                </h5>
            </div>

            <div class="card-body">
                <canvas id="chart12Thang" height="120"></canvas>
            </div>

        </div>

    </div>
 

            <div class="col-xl-4">

    <div class="card table-admin">

        <div class="card-header bg-white">

            <h5 class="mb-0 fw-bold">
                Doanh thu theo năm
            </h5>

        </div>

        <div class="card-body">

            <canvas id="chartNam"></canvas>

        </div>

    </div>

</div>
    <!-- Top sản phẩm bán chạy -->
    <div class="col-xl-4">

        <div class="card table-admin">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                    Top 10 sản phẩm bán chạy
                </h5>
            </div>

            <div class="card-body p-0">

                <div class="list-group list-group-flush">

                    @forelse($topSanPham as $sp)

                        <div class="list-group-item">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <h6 class="mb-1">
                                        {{ $sp->ten_san_pham }}
                                    </h6>

                                    <small class="text-muted">
                                        Đã bán:
                                        <strong>{{ number_format($sp->tong_ban) }}</strong>
                                    </small>

                                </div>

                                <span class="badge bg-success fs-6">
                                    {{ number_format($sp->doanh_thu,0,',','.') }} đ
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="list-group-item text-center text-muted py-4">
                            Chưa có dữ liệu sản phẩm.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Recent Orders -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card table-admin">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Đơn hàng gần đây</h5>
                <a href="{{ url('admin/hoa-don') }}" class="btn btn-sm btn-primary">
                    Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            {{-- <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mã HD</th>
                                <th>Khách hàng</th>
                                <th>Ngày tạo</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#HD001</strong></td>
                                <td>Nguyễn Văn Minh</td>
                                <td>09/06/2026 14:30</td>
                                <td><strong>320,000 đ</strong></td>
                                <td><span class="status-badge status-active">Hoàn thành</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary btn-action" title="In hóa đơn">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#HD002</strong></td>
                                <td>Trần Thị Lan</td>
                                <td>09/06/2026 13:45</td>
                                <td><strong>185,000 đ</strong></td>
                                <td><span class="status-badge status-pending">Đang xử lý</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary btn-action" title="In hóa đơn">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#HD003</strong></td>
                                <td>Lê Hoàng Nam</td>
                                <td>09/06/2026 12:20</td>
                                <td><strong>450,000 đ</strong></td>
                                <td><span class="status-badge status-active">Hoàn thành</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary btn-action" title="In hóa đơn">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#HD004</strong></td>
                                <td>Phạm Thu Hà</td>
                                <td>09/06/2026 11:00</td>
                                <td><strong>78,000 đ</strong></td>
                                <td><span class="status-badge status-inactive">Đã hủy</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary btn-action" title="In hóa đơn">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#HD005</strong></td>
                                <td>Hoàng Đức Anh</td>
                                <td>09/06/2026 10:30</td>
                                <td><strong>560,000 đ</strong></td>
                                <td><span class="status-badge status-active">Hoàn thành</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary btn-action" title="In hóa đơn">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> --}}
        </div>
    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ===========================
    // Biểu đồ doanh thu 7 ngày
    // ===========================

    const chart7 = document.getElementById('chart7Ngay');

    if (chart7) {

        const labels7 = @json($doanhThu7Ngay ? collect($doanhThu7Ngay)->pluck('ngay') : []);

        const data7 = @json($doanhThu7Ngay ? collect($doanhThu7Ngay)->pluck('tong') : []);

        new Chart(chart7, {

            type: 'line',

            data: {

                labels: labels7,

                datasets: [{

                    label: 'Doanh thu 7 ngày',

                    data: data7,

                    borderColor: '#0d6efd',

                    backgroundColor: 'rgba(13,110,253,0.2)',

                    borderWidth: 2,

                    fill: true,

                    tension: 0.4

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        display: true

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true

                    }

                }

            }

        });

    }


    // ===========================
    // Biểu đồ doanh thu 12 tháng
    // ===========================

    const chart12 = document.getElementById('chart12Thang');

    if (chart12) {

        const labels12 = @json($doanhThu12Thang ? collect($doanhThu12Thang)->pluck('thang') : []);

        const data12 = @json($doanhThu12Thang ? collect($doanhThu12Thang)->pluck('tong') : []);

        new Chart(chart12, {

            type: 'bar',

            data: {

                labels: labels12,

                datasets: [{

                    label: 'Doanh thu 12 tháng',

                    data: data12,

                    backgroundColor: '#198754',

                    borderColor: '#198754',

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        display: true

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true

                    }

                }

            }

        });

    }

});

const labelsNam=@json($doanhThuTheoNam->pluck('nam'));

const dataNam=@json($doanhThuTheoNam->pluck('doanh_thu'));

new Chart(document.getElementById('chartNam'),{

type:'bar',

data:{

labels:labelsNam,

datasets:[{

label:'Doanh thu',

data:dataNam,

backgroundColor:'#fd7e14'

}]

},

options:{

responsive:true,

plugins:{

legend:{

display:true

}

}

}

});

</script>