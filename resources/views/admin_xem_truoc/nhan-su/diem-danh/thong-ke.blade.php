@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thống kê điểm danh - SmartMart')

<style>
    .stat-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }
    .icon-primary  { background: #e7f0ff; color: #2563eb; }
    .icon-success  { background: #eaf7ef; color: #198754; }
    .icon-warning  { background: #fff4df; color: #f59e0b; }
    .icon-danger   { background: #fdecec; color: #dc3545; }
    .icon-info     { background: #e0f4ff; color: #0dcaf0; }
</style>

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thống kê điểm danh</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('diem-danh.index') }}">Điểm danh</a></li>
                <li class="breadcrumb-item active">Thống kê</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('diem-danh.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Quay lại
    </a>
</div>

{{-- Filter tháng/năm --}}
<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('diem-danh.thong-ke') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Tháng</label>
                <input type="month" name="thang" value="{{ $thang }}" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Lọc</button>
            </div>
        </form>
    </div>
</div>

{{-- Tổng quan tháng --}}
<div class="row g-3 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon icon-primary mx-auto mb-2"><i class="fas fa-calendar-check"></i></div>
                <div class="text-muted small">Tổng buổi</div>
                <h3 class="mb-0">{{ $tongBuoi }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon icon-danger mx-auto mb-2"><i class="fas fa-user-clock"></i></div>
                <div class="text-muted small">Đi muộn</div>
                <h3 class="mb-0 text-danger">{{ $diMuon }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon icon-warning mx-auto mb-2"><i class="fas fa-user-minus"></i></div>
                <div class="text-muted small">Về sớm</div>
                <h3 class="mb-0 text-warning">{{ $veSom }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon icon-info mx-auto mb-2"><i class="fas fa-moon"></i></div>
                <div class="text-muted small">Tăng ca</div>
                <h3 class="mb-0 text-info">{{ $tangCa }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon icon-success mx-auto mb-2"><i class="fas fa-clock"></i></div>
                <div class="text-muted small">Giờ tăng ca</div>
                <h3 class="mb-0 text-success">{{ $tongGioLamThem }}h</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="stat-icon icon-danger mx-auto mb-2"><i class="fas fa-stopwatch"></i></div>
                <div class="text-muted small">Phút đi muộn</div>
                <h3 class="mb-0 text-danger">{{ $tongPhutDiMuon }}p</h3>
            </div>
        </div>
    </div>
</div>

{{-- Tổng quan % --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Tỷ lệ đi muộn</div>
                <h4 class="mb-0 {{ $tongBuoi > 0 && ($diMuon / $tongBuoi) > 0.2 ? 'text-danger' : 'text-success' }}">
                    {{ $tongBuoi > 0 ? round(($diMuon / $tongBuoi) * 100, 1) : 0 }}%
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Tỷ lệ về sớm</div>
                <h4 class="mb-0 {{ $tongBuoi > 0 && ($veSom / $tongBuoi) > 0.2 ? 'text-warning' : 'text-success' }}">
                    {{ $tongBuoi > 0 ? round(($veSom / $tongBuoi) * 100, 1) : 0 }}%
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Tỷ lệ tăng ca</div>
                <h4 class="mb-0 text-success">
                    {{ $tongBuoi > 0 ? round(($tangCa / $tongBuoi) * 100, 1) : 0 }}%
                </h4>
            </div>
        </div>
    </div>
</div>

{{-- Bảng theo nhân viên --}}
<div class="card table-admin">
    <div class="card-header">
        <i class="fas fa-users me-2"></i>Chi tiết theo nhân viên
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Nhân viên</th>
                        <th class="text-center">Tổng buổi</th>
                        <th class="text-center">Đi muộn</th>
                        <th class="text-center">Về sớm</th>
                        <th class="text-center">Tăng ca</th>
                        <th class="text-center">Giờ tăng ca</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($theoNhanVien as $nv)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $nv['ho_ten'] }}</td>
                            <td class="text-center">{{ $nv['tong_buoi'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $nv['di_muon'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $nv['ve_som'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $nv['tang_ca'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-success fw-semibold">+{{ $nv['tong_gio_lam_them'] }}h</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">Không có dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
