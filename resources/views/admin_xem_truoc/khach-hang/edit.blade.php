@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cập nhật khách hàng - SmartMart')

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
        <h4 class="fw-bold mb-1">Cập nhật khách hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('khach-hang.index') }}">Khách hàng</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('khach-hang.show', $khachHang) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-2"></i>Xem chi tiết
        </a>
        <a href="{{ route('khach-hang.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<div class="card soft-panel">
    <div class="card-body p-4 p-lg-5">
        <form method="POST" action="{{ route('khach-hang.update', $khachHang) }}">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="ten_khach_hang" value="{{ old('ten_khach_hang', $khachHang->ten_khach_hang) }}" class="form-control @error('ten_khach_hang') is-invalid @enderror">
                            @error('ten_khach_hang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="so_dien_thoai" value="{{ old('so_dien_thoai', $khachHang->so_dien_thoai) }}" class="form-control @error('so_dien_thoai') is-invalid @enderror">
                            @error('so_dien_thoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" value="{{ old('email', $khachHang->email) }}" class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="trang_thai" class="form-select @error('trang_thai') is-invalid @enderror">
                                <option value="1" @selected(old('trang_thai', (string) $khachHang->trang_thai) === '1')>Đang hoạt động</option>
                                <option value="0" @selected(old('trang_thai', (string) $khachHang->trang_thai) === '0')>Ngừng hoạt động</option>
                            </select>
                            @error('trang_thai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ</label>
                            <textarea name="dia_chi" rows="3" class="form-control @error('dia_chi') is-invalid @enderror">{{ old('dia_chi', $khachHang->dia_chi) }}</textarea>
                            @error('dia_chi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Điểm tích lũy</label>
                            <input type="text" class="form-control" value="{{ number_format($khachHang->diem_tich_luy ?? 0) }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tổng chi tiêu</label>
                            <input type="text" class="form-control" value="{{ number_format($khachHang->tong_chi_tieu ?? 0, 0, ',', '.') }} đ" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Công nợ <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="cong_no" value="{{ old('cong_no', $khachHang->cong_no) }}" class="form-control @error('cong_no') is-invalid @enderror">
                            @error('cong_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <textarea name="phi_chu" rows="4" class="form-control @error('phi_chu') is-invalid @enderror">{{ old('phi_chu', $khachHang->phi_chu) }}</textarea>
                            @error('phi_chu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="p-4 rounded-4 bg-light h-100">
                        <h6 class="fw-bold mb-3">Thông tin nhanh</h6>
                        <div class="mb-2"><span class="text-muted">Mã khách hàng:</span> <strong>KH{{ str_pad((string) $khachHang->id, 4, '0', STR_PAD_LEFT) }}</strong></div>
                        <div class="mb-2"><span class="text-muted">Điểm tích lũy hiện tại:</span> <strong>{{ number_format($khachHang->diem_tich_luy ?? 0) }}</strong></div>
                        <div class="mb-2"><span class="text-muted">Tổng chi tiêu:</span> <strong>{{ number_format($khachHang->tong_chi_tieu ?? 0, 0, ',', '.') }} đ</strong></div>
                        <div class="mb-0"><span class="text-muted">Công nợ hiện tại:</span> <strong class="text-danger">{{ number_format($khachHang->cong_no ?? 0, 0, ',', '.') }} đ</strong></div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('khach-hang.index') }}" class="btn btn-light px-4">Hủy</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>Cập nhật khách hàng
                </button>
            </div>
        </form>
    </div>
</div>
@endsection