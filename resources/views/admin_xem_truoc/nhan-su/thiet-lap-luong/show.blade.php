@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết thiết lập lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chi tiết thiết lập lương</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('thiet-lap-luong.index') }}">Thiết lập lương</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('thiet-lap-luong.edit', $thietLapLuong) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Chỉnh sửa
        </a>
        <a href="{{ route('thiet-lap-luong.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

<div class="card table-admin">
    <div class="card-body">
        <h5 class="mb-3">Thông tin nhân viên</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label text-muted small">Họ tên</label>
                <p class="mb-0 fw-semibold">{{ $thietLapLuong->nguoiDung->ho_ten ?? 'N/A' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Email</label>
                <p class="mb-0">{{ $thietLapLuong->nguoiDung->email ?? 'N/A' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">SĐT</label>
                <p class="mb-0">{{ $thietLapLuong->nguoiDung->sdt ?? 'N/A' }}</p>
            </div>
        </div>

        <h5 class="mb-3">Cấu hình lương</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted small">Lương theo giờ</label>
                <p class="mb-0 fw-semibold text-primary">{{ number_format($thietLapLuong->luong_theo_gio, 0, ',', '.') }}đ / giờ</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Lương tăng ca</label>
                <p class="mb-0">{{ number_format($thietLapLuong->luong_tang_ca, 0, ',', '.') }}đ / giờ</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Phụ cấp</label>
                <p class="mb-0 text-success">{{ number_format($thietLapLuong->phu_cap, 0, ',', '.') }}đ</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Thưởng</label>
                <p class="mb-0 text-success">{{ number_format($thietLapLuong->thuong, 0, ',', '.') }}đ</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Phạt đi muộn (mỗi giờ)</label>
                <p class="mb-0 text-danger">{{ number_format($thietLapLuong->phat_di_muon, 0, ',', '.') }}đ</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Phạt bỏ ca</label>
                <p class="mb-0 text-danger">{{ number_format($thietLapLuong->phat_bo_ca, 0, ',', '.') }}đ</p>
            </div>
        </div>
    </div>
</div>
@endsection
