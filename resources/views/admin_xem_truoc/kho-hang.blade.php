@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Kho hàng - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Kho hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Kho hàng</li>
            </ol>
        </nav>
    </div>
    <div class="btn-group">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-arrow-down me-2"></i>Nhập kho
        </button>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="fas fa-arrow-up me-2"></i>Xuất kho
        </button>
    </div>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-boxes-stacked"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng sản phẩm</h6>
                    <h3 class="mb-0">1,245</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Còn hàng</h6>
                    <h3 class="mb-0">1,180</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Sắp hết</h6>
                    <h3 class="mb-0">45</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-times-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Hết hàng</h6>
                    <h3 class="mb-0">20</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="#"><i class="fas fa-boxes-stacked me-2"></i>Tồn kho</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#"><i class="fas fa-arrow-down me-2"></i>Phiếu nhập</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#"><i class="fas fa-arrow-up me-2"></i>Phiếu xuất</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ url('admin/kho-hang/nha-cung-cap') }}"><i class="fas fa-truck me-2"></i>Nhà cung cấp</a>
    </li>
</ul>

<!-- Filter -->
<div class="card table-admin mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="Tìm sản phẩm...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả trạng thái</option>
                    <option>Còn hàng</option>
                    <option>Sắp hết</option>
                    <option>Hết hàng</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả kho</option>
                    <option>Kho chính</option>
                    <option>Kho phụ</option>
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

<!-- Inventory Table -->
<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Tồn kho</th>
                        <th>Tối thiểu</th>
                        <th>Giá nhập</th>
                        <th>Giá bán</th>
                        <th>Trạng thái</th>
                        <th style="width: 100px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>SP001</strong></td>
                        <td>
                            <strong>Sữa tươi Vinamilk 180ml</strong>
                            <br><small class="text-muted">Barcode: 8934567890123</small>
                        </td>
                        <td><span class="badge bg-secondary">Thực phẩm</span></td>
                        <td><strong>250</strong></td>
                        <td>50</td>
                        <td>6,500 đ</td>
                        <td><strong>8,500 đ</strong></td>
                        <td><span class="status-badge status-active">Còn hàng</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Nhập thêm">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" title="Lịch sử">
                                <i class="fas fa-history"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>SP002</strong></td>
                        <td>
                            <strong>Bánh Oreo 133g</strong>
                            <br><small class="text-muted">Barcode: 8934567890124</small>
                        </td>
                        <td><span class="badge bg-secondary">Bánh kẹo</span></td>
                        <td><strong>180</strong></td>
                        <td>30</td>
                        <td>18,000 đ</td>
                        <td><strong>22,000 đ</strong></td>
                        <td><span class="status-badge status-active">Còn hàng</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Nhập thêm">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" title="Lịch sử">
                                <i class="fas fa-history"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>SP003</strong></td>
                        <td>
                            <strong>Mì Hảo Tấm gói</strong>
                            <br><small class="text-muted">Barcode: 8934567890125</small>
                        </td>
                        <td><span class="badge bg-secondary">Mì gói</span></td>
                        <td><strong class="text-warning">45</strong></td>
                        <td>50</td>
                        <td>5,500 đ</td>
                        <td><strong>7,000 đ</strong></td>
                        <td><span class="status-badge status-pending">Sắp hết</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Nhập gấp">
                                <i class="fas fa-exclamation"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" title="Lịch sử">
                                <i class="fas fa-history"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>SP004</strong></td>
                        <td>
                            <strong>Nước ngọt Coca Cola 330ml</strong>
                            <br><small class="text-muted">Barcode: 8934567890126</small>
                        </td>
                        <td><span class="badge bg-secondary">Đồ uống</span></td>
                        <td><strong class="text-danger">0</strong></td>
                        <td>30</td>
                        <td>9,000 đ</td>
                        <td><strong>12,000 đ</strong></td>
                        <td><span class="status-badge status-inactive">Hết hàng</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Nhập gấp">
                                <i class="fas fa-exclamation"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" title="Lịch sử">
                                <i class="fas fa-history"></i>
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
                <span class="text-muted">Hiển thị 1-4 of 1,245 sản phẩm</span>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-arrow-down me-2"></i>Nhập kho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã phiếu nhập</label>
                            <input type="text" class="form-control" value="PN001" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày nhập</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" class="form-select" required>
                              <option value="">Chọn nhà cung cấp</option>
                             @foreach($nhaCungCaps as $ncc)
                            <option value="{{ $ncc->id }}">
                                 {{ $ncc->ten_nha_cung_cap }}
                             </option>
                         @endforeach
                         </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Người nhận</label>
                            <input type="text" class="form-control" placeholder="Tên người nhận">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <hr>
                    <h6>Danh sách sản phẩm</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>SL nhập</th>
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
                                    </select>
                                </td>
                                <td><input type="number" class="form-control form-control-sm" value="100" style="width: 80px;"></td>
                                <td><input type="number" class="form-control form-control-sm" value="6500" style="width: 100px;"></td>
                                <td>650,000 đ</td>
                                <td><button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-1"></i>Thêm sản phẩm</button>
                    
                    <div class="text-end mt-3">
                        <h5>Tổng tiền: <span class="text-primary">0 đ</span></h5>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Lưu phiếu nhập
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
