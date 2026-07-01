@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cập nhật bảng lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Cập nhật bảng lương</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('bang-luong.index') }}">Bảng lương</a></li>
                <li class="breadcrumb-item active">Cập nhật</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('bang-luong.index') }}" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="card table-admin">
    <div class="card-body">
        <form method="POST" action="{{ route('bang-luong.update', $bangLuong) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Tên bảng lương <span class="text-danger">*</span></label>
                    <input type="text" name="ten_bang_luong" value="{{ old('ten_bang_luong', $bangLuong->ten_bang_luong) }}" class="form-control @error('ten_bang_luong') is-invalid @enderror">
                    @error('ten_bang_luong')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input type="date" name="ngay_bat_dau" value="{{ old('ngay_bat_dau', $bangLuong->ngay_bat_dau) }}" class="form-control @error('ngay_bat_dau') is-invalid @enderror">
                    @error('ngay_bat_dau')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                    <input type="date" name="ngay_ket_thuc" value="{{ old('ngay_ket_thuc', $bangLuong->ngay_ket_thuc) }}" class="form-control @error('ngay_ket_thuc') is-invalid @enderror">
                    @error('ngay_ket_thuc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                    <select name="trang_thai" class="form-select @error('trang_thai') is-invalid @enderror">
                        <option value="Chưa chi" {{ old('trang_thai', $bangLuong->trang_thai) == 'Chưa chi' ? 'selected' : '' }}>Chưa chi</option>
                        <option value="Đã chi một phần" {{ old('trang_thai', $bangLuong->trang_thai) == 'Đã chi một phần' ? 'selected' : '' }}>Đã chi một phần</option>
                        <option value="Đã chi" {{ old('trang_thai', $bangLuong->trang_thai) == 'Đã chi' ? 'selected' : '' }}>Đã chi</option>
                    </select>
                    @error('trang_thai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('bang-luong.index') }}" class="btn btn-light">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
