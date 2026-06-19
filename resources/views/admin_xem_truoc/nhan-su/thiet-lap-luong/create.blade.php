@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thêm thiết lập lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thêm thiết lập lương</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('thiet-lap-luong.index') }}">Thiết lập lương</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('thiet-lap-luong.index') }}" class="btn btn-outline-secondary">Quay lại</a>
</div>

<div class="card table-admin">
    <div class="card-body">
        <form method="POST" action="{{ route('thiet-lap-luong.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Nhân viên <span class="text-danger">*</span></label>
                    <select name="id_nguoi_dung" class="form-select @error('id_nguoi_dung') is-invalid @enderror">
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($nhanViens as $nv)
                            <option value="{{ $nv->id }}" {{ old('id_nguoi_dung') == $nv->id ? 'selected' : '' }}>{{ $nv->ho_ten }} ({{ $nv->email }})</option>
                        @endforeach
                    </select>
                    @error('id_nguoi_dung')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Lương theo giờ <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="luong_theo_gio" value="{{ old('luong_theo_gio') }}" class="form-control @error('luong_theo_gio') is-invalid @enderror" placeholder="VD: 30000">
                    @error('luong_theo_gio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Lương tăng ca (giờ)</label>
                    <input type="number" step="0.01" min="0" name="luong_tang_ca" value="{{ old('luong_tang_ca', 0) }}" class="form-control" placeholder="VD: 45000">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Phụ cấp</label>
                    <input type="number" step="0.01" min="0" name="phu_cap" value="{{ old('phu_cap', 0) }}" class="form-control" placeholder="VD: 500000">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Thưởng</label>
                    <input type="number" step="0.01" min="0" name="thuong" value="{{ old('thuong', 0) }}" class="form-control" placeholder="VD: 200000">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Phạt đi muộn (mỗi giờ)</label>
                    <input type="number" step="0.01" min="0" name="phat_di_muon" value="{{ old('phat_di_muon', 0) }}" class="form-control" placeholder="VD: 20000">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phạt bỏ ca</label>
                    <input type="number" step="0.01" min="0" name="phat_bo_ca" value="{{ old('phat_bo_ca', 0) }}" class="form-control" placeholder="VD: 100000">
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('thiet-lap-luong.index') }}" class="btn btn-light">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Lưu thiết lập lương
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
