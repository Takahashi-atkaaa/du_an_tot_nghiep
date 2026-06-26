@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết bảng lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>{{ $bangLuong->ten_bang_luong }}</h4>
    <a href="{{ route('bang-luong.index') }}" class="btn btn-secondary">Quay lại</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <strong>Kỳ làm việc:</strong> {{ $bangLuong->ky_lam_viec }}
            </div>
            <div class="col-md-3">
                <strong>Số nhân viên:</strong> {{ $bangLuong->so_nhan_vien }}
            </div>
            <div class="col-md-3">
                <strong>Tổng lương:</strong> {{ number_format($bangLuong->tong_luong_tat_ca_nhan_vien, 0) }} đ
            </div>
            <div class="col-md-3">
                <strong>Trạng thái:</strong> {{ $bangLuong->trang_thai }}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nhân viên</th>
                    <th>Lương tạm tính</th>
                    <th>Tổng giờ</th>
                    <th>Tổng lương</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bangLuong->phieuLuongs as $pl)
                <tr>
                    <td>{{ $pl->id }}</td>
                    <td>{{ $pl->nguoiDung->ho_ten ?? 'N/A' }}</td>
                    <td>{{ number_format($pl->luong_tam_tinh, 0) }} đ</td>
                    <td>{{ $pl->tong_gio_lam_thuc_te }}h</td>
                    <td>{{ number_format($pl->tong_luong, 0) }} đ</td>
                    <td>{{ $pl->trang_thai }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Chưa có phiếu lương nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
