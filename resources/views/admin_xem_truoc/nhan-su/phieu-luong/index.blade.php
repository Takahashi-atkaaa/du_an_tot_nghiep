@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Danh sách phiếu lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Danh sách phiếu lương</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item active">Phiếu lương</li>
            </ol>
        </nav>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('phieu-luong.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="id_bang_luong" class="form-select">
                    <option value="">-- Tất cả bảng lương --</option>
                    @foreach($bangLuongs as $bl)
                        <option value="{{ $bl->id }}" {{ request('id_bang_luong') == $bl->id ? 'selected' : '' }}>{{ $bl->ten_bang_luong }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="trang_thai" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Chưa chi" {{ request('trang_thai') == 'Chưa chi' ? 'selected' : '' }}>Chưa chi</option>
                    <option value="Đã chi một phần" {{ request('trang_thai') == 'Đã chi một phần' ? 'selected' : '' }}>Đã chi một phần</option>
                    <option value="Đã chi" {{ request('trang_thai') == 'Đã chi' ? 'selected' : '' }}>Đã chi</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i>Tìm</button>
            </div>
        </form>
    </div>
</div>

<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Bảng lương</th>
                        <th>Kỳ</th>
                        <th>Giờ làm</th>
                        <th>Lương tạm tính</th>
                        <th>Tổng lương</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($phieuLuongs as $pl)
                        <tr>
                            <td class="fw-semibold">{{ $pl->nguoiDung->ho_ten ?? 'N/A' }}</td>
                            <td>{{ $pl->bangLuong->ten_bang_luong ?? 'N/A' }}</td>
                            <td>{{ $pl->bangLuong->ky_lam_viec ?? 'N/A' }}</td>
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
                                <a href="{{ route('phieu-luong.show', $pl) }}" class="btn btn-sm btn-outline-info btn-action" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('phieu-luong.edit', $pl) }}" class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Chưa có phiếu lương nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted">
                Hiển thị {{ $phieuLuongs->firstItem() ?? 0 }}-{{ $phieuLuongs->lastItem() ?? 0 }} của {{ $phieuLuongs->total() }} phiếu lương
            </div>
            {{ $phieuLuongs->links() }}
        </div>
    </div>
</div>
@endsection
