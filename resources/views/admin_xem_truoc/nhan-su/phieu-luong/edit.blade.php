@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa phiếu lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-edit me-2"></i>Sửa phiếu lương</h4>
    <a href="{{ route('phieu-luong.index') }}" class="btn btn-secondary">Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('phieu-luong.update', $phieuLuong) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nhân viên</label>
                    <input type="text" class="form-control" value="{{ $phieuLuong->nguoiDung->ho_ten ?? 'N/A' }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bảng lương</label>
                    <input type="text" class="form-control" value="{{ $phieuLuong->bangLuong->ten_bang_luong ?? 'N/A' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lương tạm tính <span class="text-danger">*</span></label>
                    <input type="number" name="luong_tam_tinh" class="form-control" min="0" required value="{{ $phieuLuong->luong_tam_tinh }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tổng giờ làm thực tế</label>
                    <input type="number" name="tong_gio_lam_thuc_te" class="form-control" min="0" step="0.01" value="{{ $phieuLuong->tong_gio_lam_thuc_te }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tổng lương</label>
                    <input type="number" name="tong_luong" class="form-control" min="0" value="{{ $phieuLuong->tong_luong }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="cho_duyet" @selected($phieuLuong->trang_thai == 'cho_duyet')>Chờ duyệt</option>
                        <option value="da_duyet" @selected($phieuLuong->trang_thai == 'da_duyet')>Đã duyệt</option>
                        <option value="da_chi" @selected($phieuLuong->trang_thai == 'da_chi')>Đã chi</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                <a href="{{ route('phieu-luong.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
