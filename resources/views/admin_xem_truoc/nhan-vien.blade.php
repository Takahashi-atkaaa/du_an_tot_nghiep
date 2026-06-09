@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Nhân viên - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Nhân viên</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Nhân viên</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
        <i class="fas fa-plus me-2"></i>Thêm nhân viên
    </button>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-users"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng nhân viên</h6>
                    <h3 class="mb-0">24 người</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-user-check"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đang làm việc</h6>
                    <h3 class="mb-0">18 người</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-clock"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Nghỉ phép</h6>
                    <h3 class="mb-0">3 người</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-user-times"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đã nghỉ việc</h6>
                    <h3 class="mb-0">3 người</h3>
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
                    <input type="text" class="form-control" placeholder="Tìm kiếm nhân viên...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả chức vụ</option>
                    <option>Quản lý</option>
                    <option>Trưởng ca</option>
                    <option>Nhân viên</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả ca làm</option>
                    <option>Ca sáng</option>
                    <option>Ca chiều</option>
                    <option>Ca đêm</option>
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

<!-- Employees Table -->
<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Mã NV</th>
                        <th>Họ tên</th>
                        <th>Chức vụ</th>
                        <th>Số điện thoại</th>
                        <th>Ca làm</th>
                        <th>Trạng thái</th>
                        <th style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-weight: 600;">
                                NVA
                            </div>
                        </td>
                        <td><strong>NV001</strong></td>
                        <td>
                            <strong>Nguyễn Văn Admin</strong>
                            <br><small class="text-muted">admin@smartmart.vn</small>
                        </td>
                        <td><span class="badge bg-primary">Quản lý</span></td>
                        <td>0912345678</td>
                        <td><span class="text-muted">-</span></td>
                        <td><span class="status-badge status-active">Hoạt động</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-weight: 600;">
                                TTN
                            </div>
                        </td>
                        <td><strong>NV002</strong></td>
                        <td>
                            <strong>Trần Thị Thu Ngân</strong>
                            <br><small class="text-muted">thungan@smartmart.vn</small>
                        </td>
                        <td><span class="badge bg-success">Trưởng ca</span></td>
                        <td>0987654321</td>
                        <td><span class="badge bg-warning text-dark">Ca sáng</span></td>
                        <td><span class="status-badge status-active">Hoạt động</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-weight: 600;">
                                LVB
                            </div>
                        </td>
                        <td><strong>NV003</strong></td>
                        <td>
                            <strong>Lê Văn Bảo</strong>
                            <br><small class="text-muted">baohang@smartmart.vn</small>
                        </td>
                        <td><span class="badge bg-secondary">Nhân viên</span></td>
                        <td>0977112233</td>
                        <td><span class="badge bg-warning text-dark">Ca chiều</span></td>
                        <td><span class="status-badge status-active">Hoạt động</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-weight: 600;">
                                PTH
                            </div>
                        </td>
                        <td><strong>NV004</strong></td>
                        <td>
                            <strong>Phạm Thị Hồng</strong>
                            <br><small class="text-muted">hong@smartmart.vn</small>
                        </td>
                        <td><span class="badge bg-secondary">Nhân viên</span></td>
                        <td>0966887766</td>
                        <td><span class="badge bg-dark">Ca đêm</span></td>
                        <td><span class="status-badge status-pending">Nghỉ phép</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted">Hiển thị 1-4 of 24 nhân viên</span>
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Thêm nhân viên mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Nhập họ tên">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" placeholder="email@smartmart.vn">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" placeholder="0xxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chức vụ <span class="text-danger">*</span></label>
                            <select class="form-select">
                                <option selected disabled>Chọn chức vụ</option>
                                <option>Quản lý</option>
                                <option>Trưởng ca</option>
                                <option>Nhân viên</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ca làm việc</label>
                            <select class="form-select">
                                <option>Ca sáng</option>
                                <option>Ca chiều</option>
                                <option>Ca đêm</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" placeholder="Nhập mật khẩu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" placeholder="Nhập lại mật khẩu">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <textarea class="form-control" rows="2" placeholder="Địa chỉ liên hệ..."></textarea>
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
