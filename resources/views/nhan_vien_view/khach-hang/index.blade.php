@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Khách hàng')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Danh sách Khách hàng</h1>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="row">
            <div class="col-md-5">
                <input type="text" class="form-control" placeholder="Tìm theo tên, SĐT, email...">
            </div>
            <div class="col-md-4">
                <select class="form-select">
                    <option>Tất cả nhóm khách</option>
                    <option>Khách VIP</option>
                    <option>Khách thường</option>
                    <option>Khách mới</option>
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
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Email</th>
                        <th>Địa chỉ</th>
                        <th>Tổng chi tiêu</th>
                        <th>Nhóm</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2" style="width:35px;height:35px;font-size:14px;background:#2e7d32;">NA</div>
                                <strong>Nguyễn Văn A</strong>
                            </div>
                        </td>
                        <td>0901234567</td>
                        <td>nvana@email.com</td>
                        <td>123 Đường A, Q1</td>
                        <td><strong class="text-success">15.500.000đ</strong></td>
                        <td><span class="status-badge status-success">VIP</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2" style="width:35px;height:35px;font-size:14px;background:#1976d2;">TB</div>
                                <strong>Trần Thị B</strong>
                            </div>
                        </td>
                        <td>0902345678</td>
                        <td>ttb@email.com</td>
                        <td>456 Đường B, Q2</td>
                        <td><strong class="text-success">8.200.000đ</strong></td>
                        <td><span class="status-badge status-info">Thường</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2" style="width:35px;height:35px;font-size:14px;background:#e65100;">LC</div>
                                <strong>Lê Văn C</strong>
                            </div>
                        </td>
                        <td>0903456789</td>
                        <td>lvc@email.com</td>
                        <td>789 Đường C, Q3</td>
                        <td><strong class="text-success">3.500.000đ</strong></td>
                        <td><span class="status-badge status-info">Thường</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2" style="width:35px;height:35px;font-size:14px;background:#7b1fa2;">PD</div>
                                <strong>Phạm Thị D</strong>
                            </div>
                        </td>
                        <td>0904567890</td>
                        <td>ptd@email.com</td>
                        <td>321 Đường D, Q4</td>
                        <td><strong class="text-success">25.000.000đ</strong></td>
                        <td><span class="status-badge status-success">VIP</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2" style="width:35px;height:35px;font-size:14px;background:#c62828;">HE</div>
                                <strong>Hoàng Văn E</strong>
                            </div>
                        </td>
                        <td>0905678901</td>
                        <td>hve@email.com</td>
                        <td>654 Đường E, Q5</td>
                        <td><strong class="text-success">1.800.000đ</strong></td>
                        <td><span class="status-badge status-warning">Mới</span></td>
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
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Sau</a></li>
            </ul>
        </nav>
    </div>
</div>
@endsection
