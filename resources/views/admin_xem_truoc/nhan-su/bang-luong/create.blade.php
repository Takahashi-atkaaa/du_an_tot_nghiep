@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Tạo bảng lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tạo bảng lương</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('bang-luong.index') }}">Bảng lương</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('bang-luong.index') }}" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="card table-admin">
    <div class="card-body">
        <form method="POST" action="{{ route('bang-luong.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Tên bảng lương <span class="text-danger">*</span></label>
                    <input type="text" name="ten_bang_luong" value="{{ old('ten_bang_luong') }}" class="form-control @error('ten_bang_luong') is-invalid @enderror" placeholder="VD: Bảng lương tháng 06/2026">
                    @error('ten_bang_luong')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kỳ làm việc <span class="text-danger">*</span></label>
                    <input type="text" name="ky_lam_viec" value="{{ old('ky_lam_viec') }}" class="form-control @error('ky_lam_viec') is-invalid @enderror" placeholder="VD: 06/2026">
                    <small class="text-muted">Nhập theo định dạng: MM/YYYY</small>
                    @error('ky_lam_viec')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                Sau khi tạo bảng lương, hãy nhấn <strong>"Tính lương"</strong> trên trang chi tiết bảng lương để hệ thống tự động sinh phiếu lương cho tất cả nhân viên có thiết lập lương.
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('bang-luong.index') }}" class="btn btn-light">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Tạo bảng lương
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
