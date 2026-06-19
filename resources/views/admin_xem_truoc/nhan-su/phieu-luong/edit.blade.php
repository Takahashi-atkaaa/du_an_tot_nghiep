@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cập nhật phiếu lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Cập nhật phiếu lương</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('phieu-luong.index') }}">Phiếu lương</a></li>
                <li class="breadcrumb-item active">Cập nhật</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('phieu-luong.show', $phieuLuong) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-2"></i>Xem phiếu lương
        </a>
        <a href="{{ route('phieu-luong.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card table-admin">
            <div class="card-header bg-white">
                <h5 class="mb-0">Thông tin nhân viên</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Họ tên</td>
                        <td class="fw-semibold">{{ $phieuLuong->nguoiDung->ho_ten ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Bảng lương</td>
                        <td>{{ $phieuLuong->bangLuong->ten_bang_luong ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kỳ</td>
                        <td>{{ $phieuLuong->bangLuong->ky_lam_viec ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card table-admin">
            <div class="card-body">
                <form method="POST" action="{{ route('phieu-luong.update', $phieuLuong) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tổng giờ làm thực tế</label>
                            <input type="number" step="0.01" min="0" name="tong_gio_lam_thuc_te" value="{{ old('tong_gio_lam_thuc_te', $phieuLuong->tong_gio_lam_thuc_te) }}" class="form-control" placeholder="VD: 176.5">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Lương tạm tính</label>
                            <input type="number" step="0.01" min="0" name="luong_tam_tinh" value="{{ old('luong_tam_tinh', $phieuLuong->luong_tam_tinh) }}" class="form-control" placeholder="VD: 5000000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tổng lương thực nhận</label>
                            <input type="number" step="0.01" min="0" name="tong_luong" value="{{ old('tong_luong', $phieuLuong->tong_luong) }}" class="form-control" placeholder="VD: 5500000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Trạng thái</label>
                            <select name="trang_thai" class="form-select">
                                <option value="Chưa chi" {{ old('trang_thai', $phieuLuong->trang_thai) == 'Chưa chi' ? 'selected' : '' }}>Chưa chi</option>
                                <option value="Đã chi một phần" {{ old('trang_thai', $phieuLuong->trang_thai) == 'Đã chi một phần' ? 'selected' : '' }}>Đã chi một phần</option>
                                <option value="Đã chi" {{ old('trang_thai', $phieuLuong->trang_thai) == 'Đã chi' ? 'selected' : '' }}>Đã chi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('phieu-luong.index') }}" class="btn btn-light">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Cập nhật phiếu lương
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
