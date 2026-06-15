@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Hóa đơn')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Danh sách Hóa đơn</h1>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="row">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Tìm theo mã hóa đơn...">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control">
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option>Tất cả trạng thái</option>
                    <option>Hoàn thành</option>
                    <option>Đang xử lý</option>
                    <option>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-success w-100"><i class="fas fa-search me-2"></i>Tìm</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-nv mb-0">
                <thead>
                    <tr>
                        <th>Mã HD</th>
                        <th>Ngày tạo</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>#HD0001</strong></td>
                        <td>16/06/2026 08:30</td>
                        <td>Nguyễn Văn A</td>
                        <td><strong>350.000đ</strong></td>
                        <td><span class="status-badge status-success">Hoàn thành</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>#HD0002</strong></td>
                        <td>16/06/2026 09:15</td>
                        <td>Trần Thị B</td>
                        <td><strong>520.000đ</strong></td>
                        <td><span class="status-badge status-success">Hoàn thành</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>#HD0003</strong></td>
                        <td>16/06/2026 10:00</td>
                        <td>Lê Văn C</td>
                        <td><strong>180.000đ</strong></td>
                        <td><span class="status-badge status-warning">Đang xử lý</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>#HD0004</strong></td>
                        <td>16/06/2026 11:30</td>
                        <td>Khách lẻ</td>
                        <td><strong>75.000đ</strong></td>
                        <td><span class="status-badge status-success">Hoàn thành</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>#HD0005</strong></td>
                        <td>16/06/2026 12:00</td>
                        <td>Phạm Thị D</td>
                        <td><strong>890.000đ</strong></td>
                        <td><span class="status-badge status-info">Chờ thanh toán</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>#HD0006</strong></td>
                        <td>15/06/2026 14:20</td>
                        <td>Hoàng Văn E</td>
                        <td><strong>1.250.000đ</strong></td>
                        <td><span class="status-badge status-success">Hoàn thành</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                        </td>
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
