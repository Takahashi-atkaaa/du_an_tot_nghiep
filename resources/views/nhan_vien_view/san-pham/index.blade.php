@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Sản phẩm')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Danh sách Sản phẩm</h1>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="row">
            <div class="col-md-5">
                <input type="text" class="form-control" placeholder="Tìm theo tên, mã sản phẩm...">
            </div>
            <div class="col-md-4">
                <select class="form-select">
                    <option>Tất cả danh mục</option>
                    <option>Thực phẩm</option>
                    <option>Đồ uống</option>
                    <option>Điện tử</option>
                    <option>Quần áo</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-success"><i class="fas fa-search me-2"></i>Tìm</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-nv mb-0">
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><div class="bg-light rounded p-2"><i class="fas fa-coffee fa-2x text-success"></i></div></td>
                        <td><strong>SP001</strong></td>
                        <td>Cà phê sữa</td>
                        <td>Đồ uống</td>
                        <td><span class="text-success fw-bold">35.000đ</span></td>
                        <td>50</td>
                        <td><span class="status-badge status-success">Còn hàng</span></td>
                    </tr>
                    <tr>
                        <td><div class="bg-light rounded p-2"><i class="fas fa-bread-slice fa-2x text-warning"></i></div></td>
                        <td><strong>SP002</strong></td>
                        <td>Bánh mì</td>
                        <td>Thực phẩm</td>
                        <td><span class="text-success fw-bold">25.000đ</span></td>
                        <td>30</td>
                        <td><span class="status-badge status-success">Còn hàng</span></td>
                    </tr>
                    <tr>
                        <td><div class="bg-light rounded p-2"><i class="fas fa-mobile-alt fa-2x text-primary"></i></div></td>
                        <td><strong>SP003</strong></td>
                        <td>Điện thoại X</td>
                        <td>Điện tử</td>
                        <td><span class="text-success fw-bold">5.990.000đ</span></td>
                        <td>15</td>
                        <td><span class="status-badge status-success">Còn hàng</span></td>
                    </tr>
                    <tr>
                        <td><div class="bg-light rounded p-2"><i class="fas fa-water fa-2x text-info"></i></div></td>
                        <td><strong>SP004</strong></td>
                        <td>Nước suối</td>
                        <td>Đồ uống</td>
                        <td><span class="text-success fw-bold">7.000đ</span></td>
                        <td>100</td>
                        <td><span class="status-badge status-success">Còn hàng</span></td>
                    </tr>
                    <tr>
                        <td><div class="bg-light rounded p-2"><i class="fas fa-tshirt fa-2x text-secondary"></i></div></td>
                        <td><strong>SP005</strong></td>
                        <td>Áo thun nam</td>
                        <td>Quần áo</td>
                        <td><span class="text-success fw-bold">199.000đ</span></td>
                        <td><span class="text-danger">3</span></td>
                        <td><span class="status-badge status-warning">Sắp hết</span></td>
                    </tr>
                    <tr>
                        <td><div class="bg-light rounded p-2"><i class="fas fa-laptop fa-2x text-dark"></i></div></td>
                        <td><strong>SP006</strong></td>
                        <td>Laptop Y Pro</td>
                        <td>Điện tử</td>
                        <td><span class="text-success fw-bold">15.990.000đ</span></td>
                        <td>8</td>
                        <td><span class="status-badge status-success">Còn hàng</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <nav>
            <ul class="pagination mb-0 justify-content-end">
                <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">Sau</a></li>
            </ul>
        </nav>
    </div>
</div>
@endsection
