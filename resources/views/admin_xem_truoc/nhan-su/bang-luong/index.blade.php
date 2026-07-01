@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Danh sách bảng lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Danh sách bảng lương</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item active">Bảng lương</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('bang-luong.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tạo bảng lương
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('bang-luong.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="date" name="ngay_bat_dau" value="{{ request('ngay_bat_dau') }}" class="form-control" placeholder="Ngày bắt đầu">
            </div>
            <div class="col-md-3">
                <input type="date" name="ngay_ket_thuc" value="{{ request('ngay_ket_thuc') }}" class="form-control" placeholder="Ngày kết thúc">
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
                        <th>Tên bảng lương</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Số nhân viên</th>
                        <th>Tổng lương</th>
                        <th>Trạng thái</th>
                        <th class="text-end" style="width: 160px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bangLuongs as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->ten_bang_luong }}</td>
                            <td>{{ $item->ngay_bat_dau ? \Carbon\Carbon::parse($item->ngay_bat_dau)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->ngay_ket_thuc ? \Carbon\Carbon::parse($item->ngay_ket_thuc)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $item->so_nhan_vien }} nhân viên</td>
                            <td class="text-primary fw-semibold">{{ number_format($item->tong_luong_tat_ca_nhan_vien, 0, ',', '.') }}đ</td>
                            <td>
                                @if($item->trang_thai == 'Chưa chi')
                                    <span class="badge bg-warning text-dark">{{ $item->trang_thai }}</span>
                                @elseif($item->trang_thai == 'Đã chi một phần')
                                    <span class="badge bg-info">{{ $item->trang_thai }}</span>
                                @else
                                    <span class="badge bg-success">{{ $item->trang_thai }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('bang-luong.show', $item) }}" class="btn btn-sm btn-outline-info btn-action" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('bang-luong.edit', $item) }}" class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('bang-luong.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa bảng lương này? Tất cả phiếu lương liên quan cũng sẽ bị xóa.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">Chưa có bảng lương nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted">
                Hiển thị {{ $bangLuongs->firstItem() ?? 0 }}-{{ $bangLuongs->lastItem() ?? 0 }} của {{ $bangLuongs->total() }} bảng lương
            </div>
            {{ $bangLuongs->links() }}
        </div>
    </div>
</div>
@endsection
