@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Dashboard - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dashboard</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Admin</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    <div>
        <span class="text-muted">Ngày: {{ date('d/m/Y') }}</span>
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
                    <h3 class="mb-0">15,680,000 đ</h3>
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
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1">Đơn hàng hôm nay</h6>
                    <h3 class="mb-0">48 đơn</h3>
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
                    <h6 class="text-muted mb-1">Khách hàng mới</h6>
                    <h3 class="mb-0">12 khách</h3>
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
    <!-- Revenue Chart -->
    <div class="col-xl-8">
        <div class="card table-admin">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Doanh thu 7 ngày gần đây</h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary">Ngày</button>
                    <button class="btn btn-sm btn-primary">Tuần</button>
                    <button class="btn btn-sm btn-outline-secondary">Tháng</button>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center py-5" style="background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Biểu đồ doanh thu (Chart.js)</p>
                    <div class="row mt-4 px-4">
                        <div class="col-2">
                            <div class="bg-primary rounded py-3 text-white">
                                <small>T2</small>
                                <h6>12.5M</h6>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="bg-primary rounded py-3 text-white">
                                <small>T3</small>
                                <h6>15.2M</h6>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="bg-primary rounded py-3 text-white">
                                <small>T4</small>
                                <h6>14.8M</h6>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="bg-primary rounded py-3 text-white">
                                <small>T5</small>
                                <h6>18.1M</h6>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="bg-primary rounded py-3 text-white">
                                <small>T6</small>
                                <h6>20.3M</h6>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="bg-primary rounded py-3 text-white">
                                <small>T7</small>
                                <h6>22.5M</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-xl-4">
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Top sản phẩm bán chạy</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex align-items-center py-3">
                        <img src="https://via.placeholder.com/50" class="rounded me-3" alt="Product">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Sữa tươi Vinamilk 180ml</h6>
                            <small class="text-muted">Đã bán 245 cái</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">245</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center py-3">
                        <img src="https://via.placeholder.com/50" class="rounded me-3" alt="Product">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Bánh Oreo 133g</h6>
                            <small class="text-muted">Đã bán 198 cái</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">198</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center py-3">
                        <img src="https://via.placeholder.com/50" class="rounded me-3" alt="Product">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Mì Hảo Tấm gói</h6>
                            <small class="text-muted">Đã bán 156 cái</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">156</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center py-3">
                        <img src="https://via.placeholder.com/50" class="rounded me-3" alt="Product">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Nước ngọt Coca Cola 330ml</h6>
                            <small class="text-muted">Đã bán 142 cái</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">142</span>
                    </div>
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
            <div class="card-body p-0">
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
            </div>
        </div>
    </div>
</div>
@endsection
