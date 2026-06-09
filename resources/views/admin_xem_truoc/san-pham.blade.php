@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Sản phẩm - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Sản phẩm</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus me-2"></i>Thêm sản phẩm
    </button>
</div>

<!-- Filter & Search -->
<div class="card table-admin mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" placeholder="Tìm kiếm sản phẩm...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả danh mục</option>
                    <option>Thực phẩm</option>
                    <option>Đồ uống</option>
                    <option>Bánh kẹo</option>
                    <option>Mì gói</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Tất cả trạng thái</option>
                    <option>Đang bán</option>
                    <option>Hết hàng</option>
                    <option>Ngừng bán</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" class="form-check-input">
                        </th>
                        <th>Ảnh</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <img src="https://via.placeholder.com/50" class="rounded" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><strong>SP001</strong></td>
                        <td>
                            <strong>Sữa tươi Vinamilk 180ml</strong>
                            <br><small class="text-muted">Barcode: 8934567890123</small>
                        </td>
                        <td><span class="badge bg-secondary">Thực phẩm</span></td>
                        <td><strong>8,500 đ</strong></td>
                        <td>
                            <span class="text-success">250</span>
                        </td>
                        <td><span class="status-badge status-active">Đang bán</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" title="Sửa" data-bs-toggle="modal" data-bs-target="#editProductModal">
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
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <img src="https://via.placeholder.com/50" class="rounded" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><strong>SP002</strong></td>
                        <td>
                            <strong>Bánh Oreo 133g</strong>
                            <br><small class="text-muted">Barcode: 8934567890124</small>
                        </td>
                        <td><span class="badge bg-secondary">Bánh kẹo</span></td>
                        <td><strong>22,000 đ</strong></td>
                        <td>
                            <span class="text-success">180</span>
                        </td>
                        <td><span class="status-badge status-active">Đang bán</span></td>
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
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <img src="https://via.placeholder.com/50" class="rounded" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><strong>SP003</strong></td>
                        <td>
                            <strong>Mì Hảo Tấm gói</strong>
                            <br><small class="text-muted">Barcode: 8934567890125</small>
                        </td>
                        <td><span class="badge bg-secondary">Mì gói</span></td>
                        <td><strong>7,000 đ</strong></td>
                        <td>
                            <span class="text-warning">45</span>
                        </td>
                        <td><span class="status-badge status-active">Đang bán</span></td>
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
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <img src="https://via.placeholder.com/50" class="rounded" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><strong>SP004</strong></td>
                        <td>
                            <strong>Nước ngọt Coca Cola 330ml</strong>
                            <br><small class="text-muted">Barcode: 8934567890126</small>
                        </td>
                        <td><span class="badge bg-secondary">Đồ uống</span></td>
                        <td><strong>12,000 đ</strong></td>
                        <td>
                            <span class="text-danger">0</span>
                        </td>
                        <td><span class="status-badge status-inactive">Hết hàng</span></td>
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
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <img src="https://via.placeholder.com/50" class="rounded" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td><strong>SP005</strong></td>
                        <td>
                            <strong>Cà phê G7 3in1</strong>
                            <br><small class="text-muted">Barcode: 8934567890127</small>
                        </td>
                        <td><span class="badge bg-secondary">Đồ uống</span></td>
                        <td><strong>35,000 đ</strong></td>
                        <td>
                            <span class="text-success">120</span>
                        </td>
                        <td><span class="status-badge status-active">Đang bán</span></td>
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
                <span class="text-muted">Hiển thị 1-5 of 156 sản phẩm</span>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã sản phẩm</label>
                            <input type="text" class="form-control" placeholder="Auto generate" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" class="form-control" placeholder="8934567890123">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Nhập tên sản phẩm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select">
                                <option selected disabled>Chọn danh mục</option>
                                <option>Thực phẩm</option>
                                <option>Đồ uống</option>
                                <option>Bánh kẹo</option>
                                <option>Mì gói</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Đơn vị tính</label>
                            <select class="form-select">
                                <option>Cái</option>
                                <option>Gói</option>
                                <option>Chai</option>
                                <option>Hộp</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giá nhập</label>
                            <div class="input-group">
                                <input type="number" class="form-control" placeholder="0">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" placeholder="0">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tồn kho</label>
                            <input type="number" class="form-control" placeholder="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" rows="3" placeholder="Mô tả sản phẩm..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" accept="image/*">
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
