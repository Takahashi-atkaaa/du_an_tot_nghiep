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

                    <h6 class="text-muted mb-1">Doanh thu tháng hiện tại </h6>

                    <h3 class="mb-0">

                        {{ number_format($doanhThuThang,0,',','.') }} đ

                    </h3>

                 

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

                    <h6 class="text-muted mb-1">Doanh thu tổng năm</h6>

                    <h3 class="mb-0">

                        {{ number_format($doanhThuNam,0,',','.') }} đ

                    </h3>

                    

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

                    <h3 class="mb-0">{{ number_format($tongSanPhamTonKho, 0, ',', '.') }} sản phẩm</h3>

                   

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

</div>



<!-- Doanh thu 12 tháng -->

    <div class="card table-admin mb-4">



        <div class="card-header bg-white py-3">

            <h5 class="mb-0 fw-bold">

                Doanh thu 12 tháng

            </h5>

        </div>



        <div class="card-body">

            <canvas id="chart12Thang" height="120"></canvas>

        </div>



    </div>









<div class="row">

    <!-- Top sản phẩm bán chạy -->

    <div class="col-xl-6  mb-4">



        <div class="card table-admin">



            <div class="card-header bg-white py-3">

                <h5 class="mb-0 fw-bold">

                    Top 5 sản phẩm bán chạy

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





    {{-- Top sản phẩm bán chậm --}}

    <div class="col-xl-6  mb-4">

        <div class="card table-admin">   

            <div class="card-header bg-white py-3">

                <h5 class="mb-0 fw-bold">

                    Top 5 sản phẩm bán chậm

                </h5>

            </div>



            <div class="card-body p-0">



                <table class="table table-hover">

                    <tr>

                        <th>Tên sản phẩm</th>

                        <th>Số lượng bán</th>

                        <th>Tồn kho</th>

                        <th>Doanh thu</th>

                    </tr>



                    @forelse($sanPhamBanCham as $spc)

                        <tr>

                            <td>{{ $spc->ten_san_pham }}</td>

                            <td>{{ number_format($spc->tong_ban) }}</td>

                            <td>{{ number_format($spc->ton_kho) }}</td>

                            <td>

                                <span class="badge bg-success fs-6">

                                    {{ number_format($spc->doanh_thu,0,',','.') }} đ

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4" class="text-center text-muted py-4">Chưa có dữ liệu sản phẩm.</td>

                        </tr>

                    @endforelse

                </table>

                </div>

            </div> 

        </div>

    </div>
</div>

<!-- Recent Orders -->

<div class="row">

    <div class="col-lg-6 mb-4">

        <div class="card table-admin">

            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold">Đơn hàng gần đây</h5>

                <a href="{{ url('admin/hoa-don') }}" class="btn btn-sm btn-primary">

                    Xem tất cả <i class="fas fa-arrow-right ms-1"></i>

                </a>

            </div>



            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>

                            <tr>

                                <th>Khách hàng</th>

                                <th>Ngày tạo</th>

                                <th>Tổng tiền</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($donHangGanDay as $dh)

                                <tr>

                                    <td>{{ $dh->ten_khach_hang }}</td>

                                    <td>{{ \Carbon\Carbon::parse($dh->created_at)->format('d/m/Y H:i') }}</td>

                                    <td><strong>{{ number_format($dh->khach_can_tra,0,',','.') }} đ</strong></td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="3" class="text-center text-muted py-4">

                                        Chưa có dữ liệu đơn hàng.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>



    <div class="col-lg-6 mb-4">

        <div class="card table-admin">

            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">

                <h5 class="mb-0 fw-bold">Khách hàng thân thiết</h5>

                <a href="{{ url('admin/khach-hang') }}" class="btn btn-sm btn-primary">

                    Xem tất cả <i class="fas fa-arrow-right ms-1"></i>

                </a>

            </div>



            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>

                            <tr>

                                <th>Khách hàng</th>

                                <th>Tổng mua</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($khachHangThanThiet as $kh)

                                <tr>

                                    <td>{{ $kh->ten_khach_hang }}</td>

                                    <td><strong>{{ number_format($kh->tong_mua,0,',','.') }} đ</strong></td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="2" class="text-center text-muted py-4">

                                        Chưa có dữ liệu khách hàng.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

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
// ===========================

// Biểu đồ doanh thu theo năm

// ===========================
const chartNam = document.getElementById('chartNam');
if (chartNam) {
    const labelsNam = @json($doanhThuTheoNam->pluck('nam'));
    const dataNam = @json($doanhThuTheoNam->pluck('doanh_thu'));
    new Chart(chartNam, {
        type: 'bar',
        data: {
            labels: labelsNam,
            datasets: [{
                label: 'Doanh thu theo năm',
                data: dataNam,
                backgroundColor: '#fd7e14',
                borderColor: '#fd7e14',
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


</script>