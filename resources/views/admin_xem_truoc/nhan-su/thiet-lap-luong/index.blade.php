@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thiết lập lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-cog me-2"></i>Thiết lập lương</h4>
    <a href="{{ route('thiet-lap-luong.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Thêm thiết lập
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
                    <th>Lương theo giờ</th>
                    <th>Phụ cấp</th>
                    <th>Thưởng</th>
                    <th>Phạt</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($thietLapLuongs as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->nguoiDung->ho_ten ?? 'N/A' }}</td>
                    <td>{{ number_format($item->luong_theo_gio, 0) }} đ</td>
                    <td>{{ number_format($item->phu_cap, 0) }} đ</td>
                    <td>{{ number_format($item->thuong, 0) }} đ</td>
                    <td>{{ number_format($item->phat_di_muon + $item->phat_bo_ca, 0) }} đ</td>
                    <td class="text-end">
                        <a href="{{ route('thiet-lap-luong.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('thiet-lap-luong.destroy', $item) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Chưa có thiết lập lương nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $thietLapLuongs->links() }}
    </div>
</div>
@endsection
