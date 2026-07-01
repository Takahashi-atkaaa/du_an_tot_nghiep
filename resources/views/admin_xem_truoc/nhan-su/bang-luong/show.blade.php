@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết bảng lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $bangLuong->ten_bang_luong }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item"><a href="{{ route('bang-luong.index') }}">Bảng lương</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('bang-luong.edit', $bangLuong) }}" class="btn btn-outline-primary">
            <i class="fas fa-edit me-2"></i>Chỉnh sửa
        </a>
        <a href="{{ route('bang-luong.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-money-bill-wave"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng lương</h6>
                    <h4 class="mb-0">{{ number_format($bangLuong->tong_luong_tat_ca_nhan_vien, 0, ',', '.') }}đ</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-users"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Số nhân viên</h6>
                    <h4 class="mb-0">{{ $bangLuong->so_nhan_vien }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat yellow h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Ngày làm việc</h6>
                    <h4 class="mb-0">{{ $bangLuong->ngay_bat_dau ? \Carbon\Carbon::parse($bangLuong->ngay_bat_dau)->format('d/m/Y') : '-' }} - {{ $bangLuong->ngay_ket_thuc ? \Carbon\Carbon::parse($bangLuong->ngay_ket_thuc)->format('d/m/Y') : '-' }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat {{ $bangLuong->trang_thai == 'Đã chi' ? 'green' : ($bangLuong->trang_thai == 'Chưa chi' ? 'yellow' : 'blue') }} h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-flag"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Trạng thái</h6>
                    <h4 class="mb-0">{{ $bangLuong->trang_thai }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card table-admin">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách phiếu lương</h5>
        @if($bangLuong->so_nhan_vien == 0)
            <form action="{{ route('bang-luong.tinh-luong', $bangLuong) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-calculator me-2"></i>Tính lương
                </button>
            </form>
        @else
            <form action="{{ route('bang-luong.tinh-luong', $bangLuong) }}" method="POST" class="d-inline" onsubmit="return confirm('Tính lại lương sẽ xóa các phiếu lương cũ. Tiếp tục?')">
                @csrf
                <button type="submit" class="btn btn-outline-success">
                    <i class="fas fa-redo me-2"></i>Tính lại lương
                </button>
            </form>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Email</th>
                        <th>Tổng giờ làm</th>
                        <th>Lương tạm tính</th>
                        <th>Tổng lương</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bangLuong->phieuLuongs as $pl)
                        <tr>
                            <td class="fw-semibold">{{ $pl->nguoiDung->ho_ten ?? 'N/A' }}</td>
                            <td>{{ $pl->nguoiDung->email ?? 'N/A' }}</td>
                            <td>{{ number_format($pl->tong_gio_lam_thuc_te, 1, ',', '.') }}h</td>
                            <td>{{ number_format($pl->luong_tam_tinh, 0, ',', '.') }}đ</td>
                            <td class="text-primary fw-semibold">{{ number_format($pl->tong_luong, 0, ',', '.') }}đ</td>
                            <td>
                                @if($pl->trang_thai == 'Chưa chi')
                                    <span class="badge bg-warning text-dark">{{ $pl->trang_thai }}</span>
                                @elseif($pl->trang_thai == 'Đã chi một phần')
                                    <span class="badge bg-info">{{ $pl->trang_thai }}</span>
                                @else
                                    <span class="badge bg-success">{{ $pl->trang_thai }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('phieu-luong.show', $pl) }}" class="btn btn-sm btn-outline-info btn-action" title="Xem phiếu lương">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('phieu-luong.edit', $pl) }}" class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                Chưa có phiếu lương nào.
                                @if($bangLuong->so_nhan_vien == 0)
                                    Nhấn <strong>"Tính lương"</strong> để sinh phiếu lương.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
