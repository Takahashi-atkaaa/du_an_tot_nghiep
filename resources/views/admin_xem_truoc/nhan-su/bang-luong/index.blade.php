@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Bảng lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Bảng lương</h4>
    <a href="{{ route('bang-luong.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tạo bảng lương
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
                    <th>Tên bảng lương</th>
                    <th>Kỳ làm việc</th>
                    <th>Số nhân viên</th>
                    <th>Tổng lương</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bangLuongs as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->ten_bang_luong }}</td>
                    <td>{{ $item->ky_lam_viec }}</td>
                    <td>{{ $item->so_nhan_vien }}</td>
                    <td>{{ number_format($item->tong_luong_tat_ca_nhan_vien, 0) }} đ</td>
                    <td><span class="badge bg-{{ $item->trang_thai == 'da_chot' ? 'success' : 'secondary' }}">{{ $item->trang_thai }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('bang-luong.show', $item) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('bang-luong.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('bang-luong.destroy', $item) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Chưa có bảng lương nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $bangLuongs->links() }}
    </div>
</div>
@endsection
