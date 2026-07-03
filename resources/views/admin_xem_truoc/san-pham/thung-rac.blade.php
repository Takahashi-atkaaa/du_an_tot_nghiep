@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thùng rác - Sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-trash-alt me-2 text-danger"></i>Thùng rác - Sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/san-pham') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Thùng rác</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/san-pham') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ url('admin/san-pham/trash') }}" method="GET" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text"
                           class="form-control"
                           name="keyword"
                           value="{{ $keyword ?? '' }}"
                           placeholder="Tìm kiếm sản phẩm đã xóa...">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-search me-1"></i>Tìm kiếm
                </button>
            </div>
            <div class="col-md-2">
                @if(!empty($keyword))
                    <a href="{{ url('admin/san-pham/trash') }}" class="btn btn-outline-danger w-100">
                        <i class="fas fa-times me-1"></i>Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold"><i class="fas fa-trash-restore me-2 text-secondary"></i>Danh sách đã xóa</h5>
        </div>
        <span class="badge bg-secondary">{{ $trashed->total() }} mục</span>
    </div>
    <div class="card-body p-0">
        @if($trashed->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-uppercase small fw-semibold" style="width:60px;">Ảnh</th>
                            <th class="text-uppercase small fw-semibold">Tên sản phẩm</th>
                            <th class="text-uppercase small fw-semibold">Tên biến thể</th>
                            <th class="text-uppercase small fw-semibold text-end">Giá bán</th>
                            <th class="text-uppercase small fw-semibold text-center">Tồn kho</th>
                            <th class="text-uppercase small fw-semibold text-center">Đã xóa lúc</th>
                            <th class="text-uppercase small fw-semibold text-center" style="width:160px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trashed as $item)
                            <tr class="align-middle">
                                <td>
                                    @if($item->hinh_anh)
                                        <img src="{{ asset($item->hinh_anh) }}"
                                             alt="{{ $item->ten_bien_the }}"
                                             class="rounded"
                                             style="width:48px;height:48px;object-fit:cover;">
                                    @else
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                             style="width:48px;height:48px;">
                                            <i class="fas fa-box text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold small">
                                        {{ $item->product->ten_san_pham ?? '-' }}
                                    </span>
                                    @if($item->product && $item->product->danhMuc)
                                        <div class="small">
                                            <span class="badge bg-light text-dark border" style="font-size:0.68rem;">
                                                {{ $item->product->danhMuc->ten_danh_muc }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="small">{{ $item->ten_bien_the ?: 'Mặc định' }}</span>
                                    @if($item->ma_vach)
                                        <div class="text-muted" style="font-size:0.72rem;">#{{ $item->ma_vach }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-primary">
                                        {{ number_format((float)$item->gia_ban, 0, ',', '.') }} đ
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        {{ number_format($item->so_luong_ton ?? 0) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($item->deleted_at)
                                        <span class="small text-muted">
                                            {{ \Carbon\Carbon::parse($item->deleted_at)->format('d/m/Y') }}
                                        </span>
                                        <div class="small text-muted">
                                            {{ \Carbon\Carbon::parse($item->deleted_at)->format('H:i') }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ url('admin/san-pham/' . $item->id . '/restore') }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục biến thể này?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success me-1" title="Khôi phục">
                                            <i class="fas fa-trash-restore me-1"></i>Khôi phục
                                        </button>
                                    </form>
                                    <form action="{{ url('admin/san-pham/' . $item->id . '/force') }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN biến thể này? Hành động này không thể hoàn tác.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn">
                                            <i class="fas fa-times-circle me-1"></i>Xóa VV
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Hiển thị {{ $trashed->firstItem() ?? 0 }} - {{ $trashed->lastItem() ?? 0 }} trên {{ $trashed->total() }} mục
                    </span>
                    <nav>
                        {{ $trashed->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            </div>
        @else
            <div class="text-center text-muted py-5">
                <i class="fas fa-trash-alt fa-4x mb-3 opacity-25"></i>
                <h5 class="text-muted">Thùng rác trống</h5>
                <p class="mb-3">Không có sản phẩm nào trong thùng rác.</p>
                <a href="{{ url('admin/san-pham') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                </a>
            </div>
        @endif
    </div>
</div>

<div class="mt-4 p-3 bg-light rounded border">
    <div class="d-flex align-items-start gap-2">
        <i class="fas fa-info-circle text-info mt-1"></i>
        <div class="small text-muted">
            <strong>Lưu ý:</strong>
            <ul class="mb-0 mt-1">
                <li>Các biến thể đã xóa sẽ nằm trong thùng rác và có thể được khôi phục.</li>
                <li>Nhấn <strong>"Khôi phục"</strong> để đưa biến thể trở lại danh sách chính.</li>
                <li>Nhấn <strong>"Xóa vĩnh viễn"</strong> để xóa biến thể và tất cả đơn vị quy đổi vĩnh viễn. Hành động này không thể hoàn tác.</li>
                <li>Sản phẩm sẽ tự động bị xóa vĩnh viễn sau <strong>30 ngày</strong> kể từ ngày xóa.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
<style>
    .table td {
        vertical-align: middle;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
