@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Khách hàng - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Khách hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Khách hàng</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="fas fa-plus me-2"></i>Thêm khách hàng
    </button>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-users"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng khách hàng</h6>
                    <h3 class="mb-0">1,245</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-user-check"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Khách VIP</h6>
                    <h3 class="mb-0">89</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-gem"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Điểm tích lũy</h6>
                    <h3 class="mb-0">12,580,000</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-birthday-cake"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Sinh nhật tuần này</h6>
                    <h3 class="mb-0">12</h3>
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
                    <input type="text" class="form-control" placeholder="Tìm kiếm khách hàng...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả hạng</option>
                    <option>VIP</option>
                    <option>Bạc</option>
                    <option>Đồng</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả trạng thái</option>
                    <option>Hoạt động</option>
                    <option>Khóa</option>
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

<!-- Customers Table -->
<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã KH</th>
                        <th>Khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Hạng</th>
                        <th>Điểm tích lũy</th>
                        <th>Tổng chi tiêu</th>
                        <th style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>KH001</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-weight: 600;">NVM</div>
                                <div>
                                    <strong>Nguyễn Văn Minh</strong>
                                    <br><small class="text-muted">12/03/1990</small>
                                </div>
                            </div>
                        </td>
                        <td>0901234567</td>
                        <td>minhnv@email.com</td>
                        <td><span class="badge bg-warning text-dark"><i class="fas fa-gem me-1"></i>VIP</span></td>
                        <td><strong class="text-primary">125,000</strong></td>
                        <td><strong>8,500,000 đ</strong></td>
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
                        <td><strong>KH002</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-weight: 600;">TTL</div>
                                <div>
                                    <strong>Trần Thị Lan</strong>
                                    <br><small class="text-muted">25/08/1995</small>
                                </div>
                            </div>
                        </td>
                        <td>0912345678</td>
                        <td>lantt@email.com</td>
                        <td><span class="badge bg-secondary"><i class="fas fa-medal me-1"></i>Bạc</span></td>
                        <td><strong class="text-primary">45,000</strong></td>
                        <td><strong>3,200,000 đ</strong></td>
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
                        <td><strong>KH003</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-weight: 600;">LHN</div>
                                <div>
                                    <strong>Lê Hoàng Nam</strong>
                                    <br><small class="text-muted">18/11/1988</small>
                                </div>
                            </div>
                        </td>
                        <td>0932123456</td>
                        <td>namlh@email.com</td>
                        <td><span class="badge bg-danger"><i class="fas fa-crown me-1"></i>VIP</span></td>
                        <td><strong class="text-primary">280,000</strong></td>
                        <td><strong>15,800,000 đ</strong></td>
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
                        <td><strong>KH004</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-weight: 600;">PTH</div>
                                <div>
                                    <strong>Phạm Thu Hà</strong>
                                    <br><small class="text-muted">05/06/1992</small>
                                </div>
                            </div>
                        </td>
                        <td>0988776655</td>
                        <td>hapt@email.com</td>
                        <td><span class="badge bg-dark"><i class="fas fa-circle me-1"></i>Đồng</span></td>
                        <td><strong class="text-primary">12,000</strong></td>
                        <td><strong>850,000 đ</strong></td>
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
                <span class="text-muted">Hiển thị 1-4 of 1,245 khách hàng</span>
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

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Thêm khách hàng mới</h5>
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
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" placeholder="0xxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="email@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select class="form-select">
                                <option selected>Chọn giới tính</option>
                                <option>Nam</option>
                                <option>Nữ</option>
                                <option>Khác</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hạng thẻ</label>
                            <select class="form-select">
                                <option>Đồng</option>
                                <option>Bạc</option>
                                <option>VIP</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <textarea class="form-control" rows="2" placeholder="Địa chỉ..."></textarea>
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
