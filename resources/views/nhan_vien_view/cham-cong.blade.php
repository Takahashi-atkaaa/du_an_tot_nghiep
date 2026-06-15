@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Chấm công')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Chấm công</h1>
    <div>
        <span class="me-3"><strong>Tháng:</strong> 06/2026</span>
        <select class="form-select d-inline-block" style="width:auto;">
            <option>Tháng này</option>
            <option>Tháng trước</option>
        </select>
    </div>
</div>

{{-- Thống kê chấm công --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-stat green">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <h3 class="mb-0">18</h3>
                    <small class="text-muted">Ngày đi làm</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat blue">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="mb-0">2</h3>
                    <small class="text-muted">Đi muộn</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat orange">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-moon"></i>
                </div>
                <div>
                    <h3 class="mb-0">3</h3>
                    <small class="text-muted">Nghỉ phép</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat purple">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div>
                    <h3 class="mb-0">0</h3>
                    <small class="text-muted">Nghỉ không phép</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bảng chấm công --}}
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Chi tiết chấm công tháng 6/2026</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-nv mb-0">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Tổng giờ</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>01/06/2026</td>
                        <td>07:30</td>
                        <td>18:00</td>
                        <td>9.5h</td>
                        <td><span class="status-badge status-success">Đúng giờ</span></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>02/06/2026</td>
                        <td>07:35</td>
                        <td>18:00</td>
                        <td>9.4h</td>
                        <td><span class="status-badge status-warning">Đi muộn</span></td>
                        <td>Kẹt xe</td>
                    </tr>
                    <tr>
                        <td>03/06/2026</td>
                        <td>07:30</td>
                        <td>18:00</td>
                        <td>9.5h</td>
                        <td><span class="status-badge status-success">Đúng giờ</span></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>04/06/2026</td>
                        <td>07:30</td>
                        <td>18:00</td>
                        <td>9.5h</td>
                        <td><span class="status-badge status-success">Đúng giờ</span></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>05/06/2026</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td><span class="status-badge" style="background:#e3f2fd;color:#1565c0;">Nghỉ phép</span></td>
                        <td>Phép năm</td>
                    </tr>
                    <tr>
                        <td>06/06/2026</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td><span class="badge bg-secondary">Nghỉ tuần</span></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>07/06/2026</td>
                        <td>07:32</td>
                        <td>18:05</td>
                        <td>9.6h</td>
                        <td><span class="status-badge status-warning">Đi muộn</span></td>
                        <td>Trời mưa</td>
                    </tr>
                    <tr>
                        <td>08/06/2026</td>
                        <td>07:30</td>
                        <td>18:00</td>
                        <td>9.5h</td>
                        <td><span class="status-badge status-success">Đúng giờ</span></td>
                        <td>-</td>
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
