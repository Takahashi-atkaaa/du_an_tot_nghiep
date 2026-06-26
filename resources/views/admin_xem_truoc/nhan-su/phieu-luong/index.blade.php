@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Phiếu lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-receipt me-2"></i>Phiếu lương</h4>
    <a href="{{ route('phieu-luong.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tạo phiếu lương
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nhân viên</th>
                    <th>Bảng lương</th>
                    <th>Lương tạm tính</th>
                    <th>Tổng giờ</th>
                    <th>Tổng lương</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phieuLuongs as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->nguoiDung->ho_ten ?? 'N/A' }}</td>
                    <td>{{ $item->bangLuong->ten_bang_luong ?? 'N/A' }}</td>
                    <td>{{ number_format($item->luong_tam_tinh, 0) }} đ</td>
                    <td>{{ $item->tong_gio_lam_thuc_te }}h</td>
                    <td>{{ number_format($item->tong_luong, 0) }} đ</td>
                    <td>{{ $item->trang_thai }}</td>
                    <td class="text-end">
                        <a href="{{ route('phieu-luong.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('phieu-luong.destroy', $item) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Chưa có phiếu lương nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $phieuLuongs->links() }}
    </div>
</div>
@endsection
