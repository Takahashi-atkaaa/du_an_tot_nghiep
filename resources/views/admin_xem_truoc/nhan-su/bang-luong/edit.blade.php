@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa bảng lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-edit me-2"></i>Sửa bảng lương</h4>
    <a href="{{ route('bang-luong.index') }}" class="btn btn-secondary">Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('bang-luong.update', $bangLuong) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Tên bảng lương <span class="text-danger">*</span></label>
                    <input type="text" name="ten_bang_luong" class="form-control" required value="{{ $bangLuong->ten_bang_luong }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kỳ làm việc <span class="text-danger">*</span></label>
                    <input type="text" name="ky_lam_viec" class="form-control" required value="{{ $bangLuong->ky_lam_viec }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tổng lương</label>
                    <input type="number" name="tong_luong_tat_ca_nhan_vien" class="form-control" min="0" value="{{ $bangLuong->tong_luong_tat_ca_nhan_vien }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Số nhân viên</label>
                    <input type="number" name="so_nhan_vien" class="form-control" min="0" value="{{ $bangLuong->so_nhan_vien }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="nhap" @selected($bangLuong->trang_thai == 'nhap')>Nháp</option>
                        <option value="da_chot" @selected($bangLuong->trang_thai == 'da_chot')>Đã chốt</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                <a href="{{ route('bang-luong.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
