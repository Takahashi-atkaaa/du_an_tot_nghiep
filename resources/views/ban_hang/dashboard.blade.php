@extends('ban_hang.layouts.ban_hang')

@section('title', 'Dashboard - Nhân viên')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <span class="text-muted">Xin chào, {{ Auth::user()->ho_ten }}</span>
</div>

{{-- Thống kê --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-stat green">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dailyOrders ?? 0 }}</h3>
                    <small class="text-muted">Hóa đơn hôm nay</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat blue">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($dailyRevenue ?? 0, 0, ',', '.') }} đ</h3>
                    <small class="text-muted">Doanh thu hôm nay</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat orange">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dailyNewCustomers ?? 0 }}</h3>
                    <small class="text-muted">Khách hàng mới</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat purple">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $dailyHoursWorked ?? 0 }}h</h3>
                    <small class="text-muted">Giờ làm hôm nay</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Lịch làm việc hôm nay --}}
    {{-- <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2 text-success"></i>Lịch làm việc hôm nay</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <span class="badge bg-success p-2"><i class="fas fa-sign-in-alt"></i></span>
                    </div>
                    <div>
                        <strong>Ca sáng</strong>
                        <small class="d-block text-muted">07:30 - 12:00</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-danger p-2"><i class="fas fa-sign-out-alt"></i></span>
                    </div>
                    <div>
                        <strong>Ca chiều</strong>
                        <small class="d-block text-muted">13:30 - 18:00</small>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- Hóa đơn gần đây --}}
    {{-- <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2 text-success"></i>Hóa đơn gần đây</h5>
                <a href="{{ url('nhan-vien/hoa-don') }}" class="btn btn-sm btn-outline-success">Xem tất cả</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-nv mb-0">
                        <thead>
                            <tr>
                                <th>Mã HD</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ as $)
                                <tr>
                                    <td><strong>{{ $invoice->ma_hd }}</strong></td>
                                    <td>{{ $invoice->khach_hang->ho_ten ?? 'Khách lẻ' }}</td>
                                    <td><span class="text-success">{{ number_format($invoice->tong_tien, 0, ',', '.') }}đ</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Chưa có hóa đơn gần đây.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> --}}
</div>
@endsection
