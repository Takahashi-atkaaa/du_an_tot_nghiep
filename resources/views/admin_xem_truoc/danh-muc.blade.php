@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Danh mục - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Danh mục</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Danh mục</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus me-2"></i>Thêm danh mục
    </button>
</div>

<!-- Categories Grid -->
<div class="row g-4">
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-utensils fa-2x text-primary"></i>
                </div>
                <h5 class="mb-1">Thực phẩm</h5>
                <p class="text-muted mb-2">156 sản phẩm</p>
                <span class="status-badge status-active">Đang hoạt động</span>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                        <i class="fas fa-edit me-1"></i>Sửa
                    </button>
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-glass-water fa-2x text-success"></i>
                </div>
                <h5 class="mb-1">Đồ uống</h5>
                <p class="text-muted mb-2">98 sản phẩm</p>
                <span class="status-badge status-active">Đang hoạt động</span>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Sửa
                    </button>
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-cookie fa-2x text-warning"></i>
                </div>
                <h5 class="mb-1">Bánh kẹo</h5>
                <p class="text-muted mb-2">124 sản phẩm</p>
                <span class="status-badge status-active">Đang hoạt động</span>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Sửa
                    </button>
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-bowl-food fa-2x text-info"></i>
                </div>
                <h5 class="mb-1">Mì gói</h5>
                <p class="text-muted mb-2">67 sản phẩm</p>
                <span class="status-badge status-active">Đang hoạt động</span>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Sửa
                    </button>
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-apple-whole fa-2x text-danger"></i>
                </div>
                <h5 class="mb-1">Trái cây</h5>
                <p class="text-muted mb-2">45 sản phẩm</p>
                <span class="status-badge status-active">Đang hoạt động</span>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Sửa
                    </button>
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-candy-cane fa-2x text-secondary"></i>
                </div>
                <h5 class="mb-1">Sữa & Bơ</h5>
                <p class="text-muted mb-2">78 sản phẩm</p>
                <span class="status-badge status-inactive">Tạm ngưng</span>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Sửa
                    </button>
                    <button class="btn btn-sm btn-outline-success">
                        <i class="fas fa-play me-1"></i>Kích hoạt
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-dark bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-soap fa-2x text-dark"></i>
                </div>
                <h5 class="mb-1">Hóa phẩm</h5>
                <p class="text-muted mb-2">89 sản phẩm</p>
                <span class="status-badge status-active">Đang hoạt động</span>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>Sửa
                    </button>
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card table-admin h-100">
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-plus fa-2x text-primary"></i>
                </div>
                <h5 class="mb-1 text-muted">Thêm mới</h5>
                <p class="text-muted mb-2">Danh mục mới</p>
                <button class="btn btn-outline-primary mt-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus me-1"></i>Thêm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Nhập tên danh mục">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select class="form-select">
                            <option value="utensils">Thực phẩm</option>
                            <option value="glass-water">Đồ uống</option>
                            <option value="cookie">Bánh kẹo</option>
                            <option value="bowl-food">Mì gói</option>
                            <option value="apple-whole">Trái cây</option>
                            <option value="candy-cane">Sữa & Bơ</option>
                            <option value="soap">Hóa phẩm</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <input type="color" class="form-control form-control-color" value="#0d6efd">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" rows="3" placeholder="Mô tả danh mục..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select">
                            <option value="1">Đang hoạt động</option>
                            <option value="0">Tạm ngưng</option>
                        </select>
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
