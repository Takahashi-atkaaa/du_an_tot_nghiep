@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Hóa đơn - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Hóa đơn</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Hóa đơn</li>
            </ol>
        </nav>
    </div>
    <div class="btn-group">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newInvoiceModal">
            <i class="fas fa-plus me-2"></i>Tạo hóa đơn
        </button>
        <button class="btn btn-outline-secondary">
            <i class="fas fa-file-export me-2"></i>Xuất Excel
        </button>
    </div>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng hóa đơn</h6>
                    <h3 class="mb-0">1,568</h3>
                    <small class="text-muted">Hôm nay: 48</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Hoàn thành</h6>
                    <h3 class="mb-0">1,420</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-clock"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đang xử lý</h6>
                    <h3 class="mb-0">125</h3>
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
                    <h3 class="mb-0">23</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card table-admin mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="Tìm mã hóa đơn...">
                </div>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" placeholder="Từ ngày">
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả trạng thái</option>
                    <option>Hoàn thành</option>
                    <option>Đang xử lý</option>
                    <option>Đã hủy</option>
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

<!-- Invoices Table -->
<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã HD</th>
                        <th>Khách hàng</th>
                        <th>Nhân viên</th>
                        <th>Ngày tạo</th>
                        <th>Tổng tiền</th>
                        <th>Giảm giá</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong class="text-primary">#HD001</strong></td>
                        <td>Nguyễn Văn Minh</td>
                        <td>Trần Thị Thu Ngân</td>
                        <td>09/06/2026 14:30</td>
                        <td><strong>350,000 đ</strong></td>
                        <td><span class="text-danger">-10,000 đ</span></td>
                        <td><strong class="text-success">340,000 đ</strong></td>
                        <td><span class="status-badge status-active">Hoàn thành</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary btn-action" title="In">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Hủy">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong class="text-primary">#HD002</strong></td>
                        <td>Trần Thị Lan</td>
                        <td>Lê Văn Bảo</td>
                        <td>09/06/2026 13:45</td>
                        <td><strong>185,000 đ</strong></td>
                        <td><span class="text-muted">-0 đ</span></td>
                        <td><strong class="text-success">185,000 đ</strong></td>
                        <td><span class="status-badge status-pending">Đang xử lý</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success btn-action" title="Xác nhận">
                                <i class="fas fa-check"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong class="text-primary">#HD003</strong></td>
                        <td>Khách lẻ</td>
                        <td>Phạm Thị Hồng</td>
                        <td>09/06/2026 12:20</td>
                        <td><strong>450,000 đ</strong></td>
                        <td><span class="text-danger">-25,000 đ</span></td>
                        <td><strong class="text-success">425,000 đ</strong></td>
                        <td><span class="status-badge status-active">Hoàn thành</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary btn-action" title="In">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Hủy">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong class="text-primary">#HD004</strong></td>
                        <td>Lê Hoàng Nam</td>
                        <td>Trần Thị Thu Ngân</td>
                        <td>09/06/2026 11:00</td>
                        <td><strong>78,000 đ</strong></td>
                        <td><span class="text-muted">-0 đ</span></td>
                        <td><strong class="text-muted">78,000 đ</strong></td>
                        <td><span class="status-badge status-inactive">Đã hủy</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong class="text-primary">#HD005</strong></td>
                        <td>Phạm Thu Hà</td>
                        <td>Lê Văn Bảo</td>
                        <td>09/06/2026 10:30</td>
                        <td><strong>560,000 đ</strong></td>
                        <td><span class="text-danger">-30,000 đ</span></td>
                        <td><strong class="text-success">530,000 đ</strong></td>
                        <td><span class="status-badge status-active">Hoàn thành</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Xem">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary btn-action" title="In">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Hủy">
                                <i class="fas fa-times"></i>
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
                <span class="text-muted">Hiển thị 1-5 of 1,568 hóa đơn</span>
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

<!-- New Invoice Modal -->
<div class="modal fade" id="newInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Tạo hóa đơn mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Khách hàng</label>
                            <select class="form-select">
                                <option selected>Khách lẻ</option>
                                <option>Nguyễn Văn Minh</option>
                                <option>Trần Thị Lan</option>
                                <option>Lê Hoàng Nam</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nhân viên bán</label>
                            <select class="form-select">
                                <option>Trần Thị Thu Ngân</option>
                                <option>Lê Văn Bảo</option>
                                <option>Phạm Thị Hồng</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <h6>Danh sách sản phẩm</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>SL</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm">
                                        <option>Sữa tươi Vinamilk 180ml</option>
                                        <option>Bánh Oreo 133g</option>
                                        <option>Mì Hảo Tấm gói</option>
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm" value="1" style="width: 60px;"></td>
                                <td>8,500 đ</td>
                                <td>8,500 đ</td>
                                <td><button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-1"></i>Thêm sản phẩm</button>
                    
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2 d-flex justify-content-between">
                                <span>Tạm tính:</span>
                                <strong>0 đ</strong>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span>Giảm giá:</span>
                                <span class="text-danger">- 0 đ</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span>Khách thanh toán:</span>
                                <strong class="text-success">0 đ</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Tạo hóa đơn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
