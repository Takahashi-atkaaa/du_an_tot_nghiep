@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Danh sách thiết lập lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Danh sách thiết lập lương</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/nhan-su') }}">Nhân sự</a></li>
                <li class="breadcrumb-item active">Thiết lập lương</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('thiet-lap-luong.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Thêm thiết lập lương
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('thiet-lap-luong.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm theo tên, email nhân viên...">
            </div>
            <div class="col-md-3">
                <select name="id_nguoi_dung" class="form-select">
                    <option value="">-- Tất cả nhân viên --</option>
                    @foreach($nhanViens as $nv)
                        <option value="{{ $nv->id }}" {{ request('id_nguoi_dung') == $nv->id ? 'selected' : '' }}>{{ $nv->ho_ten }}</option>
                    @endforeach
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
                        <th>Lương giờ</th>
                        <th>Phụ cấp</th>
                        <th>Thưởng</th>
                        <th>Phạt đi muộn</th>
                        <th>Phạt bỏ ca</th>
                        <th>Lương tăng ca</th>
                        <th class="text-end" style="width: 160px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thietLapLuongs as $item)
                        <tr>
                            <td>{{ $item->nguoiDung->ho_ten ?? 'N/A' }}</td>
                            <td>{{ number_format($item->luong_theo_gio, 0, ',', '.') }}đ/h</td>
                            <td>{{ number_format($item->phu_cap, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($item->thuong, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($item->phat_di_muon, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($item->phat_bo_ca, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($item->luong_tang_ca, 0, ',', '.') }}đ/h</td>
                            <td class="text-end">
                                <a href="{{ route('thiet-lap-luong.show', $item) }}" class="btn btn-sm btn-outline-info btn-action" title="Xem">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('thiet-lap-luong.edit', $item) }}" class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('thiet-lap-luong.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa thiết lập lương này?')">
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
                            <td colspan="8" class="text-center text-muted py-5">Chưa có thiết lập lương nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted">
                Hiển thị {{ $thietLapLuongs->firstItem() ?? 0 }}-{{ $thietLapLuongs->lastItem() ?? 0 }} của {{ $thietLapLuongs->total() }} thiết lập
            </div>
            {{ $thietLapLuongs->links() }}
        </div>
    </div>
</div>
@endsection
