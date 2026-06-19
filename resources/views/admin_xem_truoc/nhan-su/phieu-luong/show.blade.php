@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Phiếu lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Phiếu lương nhân viên</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('phieu-luong.index') }}">Phiếu lương</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('phieu-luong.edit', $phieuLuong) }}" class="btn btn-outline-primary">
            <i class="fas fa-edit me-2"></i>Chỉnh sửa
        </a>
        <a href="{{ route('phieu-luong.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

<div class="card table-admin mb-4" id="phieu-luong-print">
    <div class="card-body">
        <div class="text-center border-bottom pb-4 mb-4">
            <h3 class="fw-bold text-primary">PHIẾU LƯƠNG</h3>
            <h5 class="mb-1">{{ $phieuLuong->bangLuong->ten_bang_luong ?? 'N/A' }}</h5>
            <p class="mb-0 text-muted">Kỳ: {{ $phieuLuong->bangLuong->ky_lam_viec ?? 'N/A' }}</p>
            <p class="mb-0 text-muted">Ngày lập: {{ $phieuLuong->created_at->format('d/m/Y') }}</p>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <h6 class="text-uppercase text-muted small fw-bold">Thông tin nhân viên</h6>
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Họ tên</td>
                        <td class="fw-semibold">{{ $phieuLuong->nguoiDung->ho_ten ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>{{ $phieuLuong->nguoiDung->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">SĐT</td>
                        <td>{{ $phieuLuong->nguoiDung->sdt ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-uppercase text-muted small fw-bold">Thông tin lương</h6>
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 160px;">Tổng giờ làm thực tế</td>
                        <td class="fw-semibold">{{ number_format($phieuLuong->tong_gio_lam_thuc_te, 1, ',', '.') }} giờ</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lương tạm tính</td>
                        <td>{{ number_format($phieuLuong->luong_tam_tinh, 0, ',', '.') }}đ</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Trạng thái</td>
                        <td>
                            @if($phieuLuong->trang_thai == 'Chưa chi')
                                <span class="badge bg-warning text-dark">{{ $phieuLuong->trang_thai }}</span>
                            @elseif($phieuLuong->trang_thai == 'Đã chi một phần')
                                <span class="badge bg-info">{{ $phieuLuong->trang_thai }}</span>
                            @else
                                <span class="badge bg-success">{{ $phieuLuong->trang_thai }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-6">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Nội dung</th>
                            <th class="text-end">Số tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Lương tạm tính</td>
                            <td class="text-end">{{ number_format($phieuLuong->luong_tam_tinh, 0, ',', '.') }}đ</td>
                        </tr>
                        <tr class="table-success">
                            <td class="fw-bold">TỔNG LƯƠNG THỰC NHẬN</td>
                            <td class="text-end fw-bold text-success">{{ number_format($phieuLuong->tong_luong, 0, ',', '.') }}đ</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
