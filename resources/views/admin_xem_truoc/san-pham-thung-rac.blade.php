@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thùng rác - Sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thùng rác - Sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/san-pham') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Thùng rác</li>
            </ol>
        </nav>
    </div>
    <a href="{{ url('admin/san-pham') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card table-admin">
    <div class="card-body">
        <form action="{{ url('admin/san-pham/trash') }}" method="GET" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control" name="keyword" value="{{ $keyword ?? '' }}" placeholder="Tìm kiếm sản phẩm đã xóa...">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-search me-2"></i>Tìm kiếm
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá bán</th>
                        <th>Đã xóa lúc</th>
                        <th style="width: 200px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trashed as $item)
                        <tr>
                            <td>
                                @if($item->hinh_anh)
                                    <img src="{{ asset($item->hinh_anh) }}" class="rounded" alt="{{ $item->ten_san_pham }}" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <img src="https://via.placeholder.com/50" class="rounded" alt="No image">
                                @endif
                            </td>
                            <td>{{ $item->ma_hang ?? $item->ma_vach }}</td>
                            <td>{{ $item->ten_san_pham }}</td>
                            <td>{{ $item->danhMuc->ten_danh_muc ?? '-' }}</td>
                            <td>{{ number_format($item->gia_ban, 0, ',', '.') }} đ</td>
                            <td>{{ $item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <form action="{{ url('admin/san-pham/'.$item->id.'/restore') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Khôi phục sản phẩm này?')">
                                        <i class="fas fa-undo me-1"></i>Khôi phục
                                    </button>
                                </form>
                                <form action="{{ url('admin/san-pham/'.$item->id.'/force') }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa vĩnh viễn sản phẩm này? Hành động này không thể hoàn tác!')">
                                        <i class="fas fa-trash-alt me-1"></i>Xóa vĩnh viễn
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-trash fa-2x mb-2 d-block text-muted"></i>
                                Thùng rác trống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trashed->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $trashed->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
