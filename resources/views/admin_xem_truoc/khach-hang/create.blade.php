@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thêm khách hàng - SmartMart')

@section('content')
<style>
    .soft-panel {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 0.75rem 2rem rgba(15, 23, 42, 0.08);
    }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thêm khách hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('khach-hang.index') }}">Khách hàng</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('khach-hang.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>
</div>

<div class="card soft-panel">
    <div class="card-body p-4 p-lg-5">
        <form method="POST" action="{{ route('khach-hang.store') }}">
            @csrf
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="ten_khach_hang" value="{{ old('ten_khach_hang', $khachHang->ten_khach_hang) }}" class="form-control @error('ten_khach_hang') is-invalid @enderror" placeholder="Nhập họ và tên">
                            @error('ten_khach_hang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="so_dien_thoai" value="{{ old('so_dien_thoai', $khachHang->so_dien_thoai) }}" class="form-control @error('so_dien_thoai') is-invalid @enderror" placeholder="0912345678">
                            @error('so_dien_thoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" value="{{ old('email', $khachHang->email) }}" class="form-control @error('email') is-invalid @enderror" placeholder="email@smartmart.vn">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="trang_thai" class="form-select @error('trang_thai') is-invalid @enderror">
                                <option value="1" @selected(old('trang_thai', '1') == '1')>Đang hoạt động</option>
                                <option value="0" @selected(old('trang_thai') === '0')>Ngừng hoạt động</option>
                            </select>
                            @error('trang_thai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ</label>
                            <textarea name="dia_chi" rows="3" class="form-control @error('dia_chi') is-invalid @enderror" placeholder="Nhập địa chỉ liên hệ">{{ old('dia_chi', $khachHang->dia_chi) }}</textarea>
                            @error('dia_chi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <textarea name="phi_chu" rows="4" class="form-control @error('phi_chu') is-invalid @enderror" placeholder="Nhập ghi chú nếu có">{{ old('phi_chu', $khachHang->phi_chu) }}</textarea>
                            @error('phi_chu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="p-4 rounded-4 bg-light h-100">
                        <h6 class="fw-bold mb-3">Lưu ý nhanh</h6>
                        <ul class="text-muted small mb-0 ps-3">
                            <li>Họ tên và số điện thoại là thông tin bắt buộc.</li>
                            <li>Email chỉ cần đúng định dạng, có thể để trống.</li>
                            <li>Điểm tích lũy và tổng chi tiêu sẽ được hệ thống tính sau.</li>
                            <li>Trạng thái dùng để bật/tắt hiển thị khách hàng.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('khach-hang.index') }}" class="btn btn-light px-4">Hủy</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>Lưu khách hàng
                </button>
            </div>
        </form>
    </div>
</div>
@endsection