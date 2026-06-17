@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết khách hàng - SmartMart')

@section('content')
<style>
    .soft-panel {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 0.75rem 2rem rgba(15, 23, 42, 0.08);
    }
    .customer-avatar-lg {
        width: 96px;
        height: 96px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(135deg, #0d6efd, #20c997);
        box-shadow: 0 0.75rem 1.5rem rgba(13, 110, 253, 0.25);
    }
    .timeline-dot-up { background: #198754; }
    .timeline-dot-down { background: #dc3545; }
</style>

@php
    $initials = collect(explode(' ', trim($khachHang->ten_khach_hang)))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chi tiết khách hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('khach-hang.index') }}">Khách hàng</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('khach-hang.edit', $khachHang) }}" class="btn btn-primary">
            <i class="fas fa-pen me-2"></i>Chỉnh sửa
        </a>
        <a href="{{ route('khach-hang.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card soft-panel h-100">
            <div class="card-body text-center p-4">
                <div class="customer-avatar-lg mx-auto mb-3">{{ $initials ?: 'KH' }}</div>
                <h5 class="fw-bold mb-1">{{ $khachHang->ten_khach_hang }}</h5>
                <div class="text-muted mb-3">Mã KH{{ str_pad((string) $khachHang->id, 4, '0', STR_PAD_LEFT) }}</div>
                <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                    <span class="badge bg-success px-3 py-2">{{ $khachHang->trang_thai ? 'Hoạt động' : 'Ngừng hoạt động' }}</span>
                    <span class="badge bg-info px-3 py-2">{{ $khachHang->diem_tich_luy ?? 0 }} điểm</span>
                </div>
                <div class="text-start border-top pt-3">
                    <div class="mb-2"><i class="fas fa-phone text-primary me-2"></i>{{ $khachHang->so_dien_thoai }}</div>
                    <div class="mb-2"><i class="fas fa-envelope text-primary me-2"></i>{{ $khachHang->email ?: '-' }}</div>
                    <div class="mb-2"><i class="fas fa-location-dot text-primary me-2"></i>{{ $khachHang->dia_chi ?: '-' }}</div>
                    <div class="mb-0"><i class="fas fa-note-sticky text-primary me-2"></i>{{ $khachHang->phi_chu ?: 'Không có ghi chú' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card card-stat blue soft-panel h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon me-3"><i class="fas fa-gem"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Điểm tích lũy hiện tại</h6>
                            <h3 class="mb-0">{{ number_format($khachHang->diem_tich_luy ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat green soft-panel h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon me-3"><i class="fas fa-wallet"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Tổng chi tiêu</h6>
                            <h3 class="mb-0">{{ number_format($khachHang->tong_chi_tieu ?? 0, 0, ',', '.') }} đ</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat red soft-panel h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon me-3"><i class="fas fa-sack-dollar"></i></div>
                        <div>
                            <h6 class="text-muted mb-1">Công nợ</h6>
                            <h3 class="mb-0">{{ number_format($khachHang->cong_no ?? 0, 0, ',', '.') }} đ</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card soft-panel mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Thống kê giao dịch</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small mb-1">Tổng số hóa đơn</div>
                            <div class="fs-3 fw-bold">{{ number_format($tongSoHoaDon) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small mb-1">Tổng tiền đã mua</div>
                            <div class="fs-3 fw-bold text-success">{{ number_format($tongTienDaMua, 0, ',', '.') }} đ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card soft-panel" id="lich-su-tich-diem">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fw-bold">Lịch sử tích điểm</h5>
                <span class="badge bg-secondary">{{ $lichSuTichDiems->total() }} giao dịch</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày</th>
                                <th>Hóa đơn</th>
                                <th>Loại biến động</th>
                                <th>Số điểm</th>
                                <th>Lý do</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lichSuTichDiems as $lichSu)
                                @php
                                    $isTăng = str_contains(mb_strtolower($lichSu->loai_bien_dong), 'tăng') || $lichSu->so_diem >= 0;
                                @endphp
                                <tr>
                                    <td>{{ optional($lichSu->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($lichSu->hoaDon)
                                            <strong>#HD{{ str_pad((string) $lichSu->hoaDon->id, 4, '0', STR_PAD_LEFT) }}</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $isTăng ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                            {{ $lichSu->loai_bien_dong }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold {{ $isTăng ? 'text-success' : 'text-danger' }}">
                                            {{ $isTăng ? '+' : '-' }}{{ number_format(abs($lichSu->so_diem)) }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $lichSu->ly_do ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="py-4">
                                            <i class="fas fa-star fa-2x text-muted mb-3"></i>
                                            <h6 class="fw-semibold mb-1">Chưa có lịch sử tích điểm</h6>
                                            <p class="text-muted mb-0">Dữ liệu mẫu sẽ hiển thị khi chưa có giao dịch tích điểm.</p>
                                        </div>
                                        <div class="table-responsive mt-3">
                                            <table class="table table-bordered align-middle mb-0">
                                                <tbody>
                                                    <tr>
                                                        <td>12/06/2026 09:20</td>
                                                        <td>#HD0001</td>
                                                        <td><span class="badge bg-success px-3 py-2">Tăng điểm</span></td>
                                                        <td><span class="fw-bold text-success">+120</span></td>
                                                        <td>Mua hàng hóa đơn thành công</td>
                                                    </tr>
                                                    <tr>
                                                        <td>10/06/2026 16:45</td>
                                                        <td>#HD0002</td>
                                                        <td><span class="badge bg-danger px-3 py-2">Giảm điểm</span></td>
                                                        <td><span class="fw-bold text-danger">-50</span></td>
                                                        <td>Đổi quà bằng điểm tích lũy</td>
                                                    </tr>
                                                    <tr>
                                                        <td>08/06/2026 11:10</td>
                                                        <td>#HD0003</td>
                                                        <td><span class="badge bg-success px-3 py-2">Tăng điểm</span></td>
                                                        <td><span class="fw-bold text-success">+80</span></td>
                                                        <td>Thanh toán đơn hàng</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $lichSuTichDiems->links() }}
            </div>
        </div>
    </div>
</div>
@endsection