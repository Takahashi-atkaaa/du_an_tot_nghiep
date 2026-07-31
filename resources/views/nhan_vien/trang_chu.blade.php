@extends('nhan_vien.layouts.nhan_vien')

@section('title', 'Trang chủ')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h3 class="fw-bold">Xin chào, Nhân viên 👋</h3>
        <p class="text-muted">
            Chúc bạn có một ngày làm việc hiệu quả!
        </p>
    </div>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                    <h5>Lịch làm việc</h5>
                    <p class="text-muted">Xem lịch làm việc trong tuần.</p>
                    <a href="{{ url('lich-lam-viec/tuan') }}" class="btn btn-success">
                        Xem lịch
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                    <h5>Chấm công</h5>
                    <p class="text-muted">Thực hiện chấm công vào ca làm việc.</p>
                    <a href="{{ url('cham-cong') }}" class="btn btn-primary">
                        Chấm công
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-user fa-3x text-warning mb-3"></i>
                    <h5>Hồ sơ cá nhân</h5>
                    <p class="text-muted">Cập nhật thông tin cá nhân của bạn.</p>
                    <a href="{{ url('nhan-vien/ho-so') }}" class="btn btn-warning text-white">
                        Xem hồ sơ
                    </a>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-bullhorn me-2"></i>
                Thông báo
            </h5>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li>📌 Kiểm tra lịch làm việc mỗi ngày.</li>
                <li>📌 Chấm công đúng giờ khi bắt đầu ca làm.</li>
                <li>📌 Liên hệ quản lý nếu cần đổi ca hoặc nghỉ phép.</li>
            </ul>
        </div>
    </div>

</div>
@endsection