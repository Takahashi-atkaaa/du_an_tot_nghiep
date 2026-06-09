@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Khuyến mãi - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Khuyến mãi</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Khuyến mãi</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromotionModal">
        <i class="fas fa-plus me-2"></i>Tạo khuyến mãi
    </button>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-gift"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng khuyến mãi</h6>
                    <h3 class="mb-0">15</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-play-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đang hoạt động</h6>
                    <h3 class="mb-0">8</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-clock"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Sắp diễn ra</h6>
                    <h3 class="mb-0">3</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-pause-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đã kết thúc</h6>
                    <h3 class="mb-0">4</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card table-admin mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="Tìm khuyến mãi...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả loại</option>
                    <option>Giảm %</option>
                    <option>Giảm tiền</option>
                    <option>Buy 1 Get 1</option>
                    <option>Quà tặng</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả trạng thái</option>
                    <option>Đang hoạt động</option>
                    <option>Sắp diễn ra</option>
                    <option>Đã kết thúc</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Promotions Grid -->
<div class="row g-4">
    <!-- Promotion 1 -->
    <div class="col-xl-4 col-lg-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="badge bg-danger"><i class="fas fa-percent me-1"></i>Giảm 20%</span>
                <span class="status-badge status-active">Đang hoạt động</span>
            </div>
            <div class="card-body">
                <h5 class="card-title">Summer Sale 2026</h5>
                <p class="text-muted small mb-2">Giảm 20% cho tất cả sản phẩm thực phẩm</p>
                <div class="d-flex align-items-center text-muted small mb-2">
                    <i class="fas fa-calendar me-2"></i>
                    <span>01/06/2026 - 30/06/2026</span>
                </div>
                <div class="d-flex align-items-center text-muted small">
                    <i class="fas fa-shopping-bag me-2"></i>
                    <span>156 sản phẩm áp dụng</span>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-success"><i class="fas fa-chart-line me-1"></i>Đã bán: 1,245 sản phẩm</small>
                    <div>
                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Promotion 2 -->
    <div class="col-xl-4 col-lg-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="badge bg-warning text-dark"><i class="fas fa-coins me-1"></i>Giảm 50,000đ</span>
                <span class="status-badge status-active">Đang hoạt động</span>
            </div>
            <div class="card-body">
                <h5 class="card-title">Giảm phí vận chuyển</h5>
                <p class="text-muted small mb-2">Miễn phí vận chuyển cho đơn từ 200,000đ</p>
                <div class="d-flex align-items-center text-muted small mb-2">
                    <i class="fas fa-calendar me-2"></i>
                    <span>15/05/2026 - 15/07/2026</span>
                </div>
                <div class="d-flex align-items-center text-muted small">
                    <i class="fas fa-users me-2"></i>
                    <span>Áp dụng tất cả khách hàng</span>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-success"><i class="fas fa-chart-line me-1"></i>Đã sử dụng: 856 lần</small>
                    <div>
                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Promotion 3 -->
    <div class="col-xl-4 col-lg-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="badge bg-info"><i class="fas fa-gift me-1"></i>Buy 1 Get 1</span>
                <span class="status-badge status-pending">Sắp diễn ra</span>
            </div>
            <div class="card-body">
                <h5 class="card-title">Happy Hour</h5>
                <p class="text-muted small mb-2">Mua 1 tặng 1 nước ngọt các loại</p>
                <div class="d-flex align-items-center text-muted small mb-2">
                    <i class="fas fa-calendar me-2"></i>
                    <span>20/06/2026 - 25/06/2026</span>
                </div>
                <div class="d-flex align-items-center text-muted small">
                    <i class="fas fa-box me-2"></i>
                    <span>5 sản phẩm áp dụng</span>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="fas fa-clock me-1"></i>Còn 11 ngày</small>
                    <div>
                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Promotion 4 -->
    <div class="col-xl-4 col-lg-6">
        <div class="card table-admin h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="badge bg-success"><i class="fas fa-tag me-1"></i>Giảm 10%</span>
                <span class="status-badge status-inactive">Đã kết thúc</span>
            </div>
            <div class="card-body">
                <h5 class="card-title">Women's Day</h5>
                <p class="text-muted small mb-2">Giảm 10% cho khách hàng nữ</p>
                <div class="d-flex align-items-center text-muted small mb-2">
                    <i class="fas fa-calendar me-2"></i>
                    <span>08/03/2026 - 08/03/2026</span>
                </div>
                <div class="d-flex align-items-center text-muted small">
                    <i class="fas fa-users me-2"></i>
                    <span>245 khách hàng đã sử dụng</span>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-success"><i class="fas fa-chart-line me-1"></i>Doanh thu: 12,500,000đ</small>
                    <div>
                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-copy"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add New Card -->
    <div class="col-xl-4 col-lg-6">
        <div class="card table-admin h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center py-5">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-plus fa-2x text-primary"></i>
                </div>
                <h5 class="mb-2">Tạo khuyến mãi mới</h5>
                <p class="text-muted mb-3">Thêm chương trình khuyến mãi mới</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromotionModal">
                    <i class="fas fa-plus me-2"></i>Tạo mới
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Promotion Modal -->
<div class="modal fade" id="addPromotionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-gift me-2"></i>Tạo khuyến mãi mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Nhập tên khuyến mãi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loại khuyến mãi <span class="text-danger">*</span></label>
                            <select class="form-select">
                                <option selected disabled>Chọn loại</option>
                                <option>Giảm %</option>
                                <option>Giảm tiền</option>
                                <option>Buy 1 Get 1</option>
                                <option>Quà tặng</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" placeholder="0">
                                <span class="input-group-text">% / đ</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Áp dụng cho</label>
                            <select class="form-select">
                                <option>Tất cả sản phẩm</option>
                                <option selected>Danh mục cụ thể</option>
                                <option>Sản phẩm cụ thể</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Đơn hàng tối thiểu</label>
                            <div class="input-group">
                                <input type="number" class="form-control" placeholder="0">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" rows="3" placeholder="Mô tả chi tiết..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActive">
                                <label class="form-check-label" for="isActive">
                                    Kích hoạt ngay sau khi tạo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
