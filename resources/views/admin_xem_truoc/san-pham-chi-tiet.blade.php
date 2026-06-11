@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chi tiết sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/san-pham') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active">{{ $sanPham->ten_san_pham }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ url('admin/san-pham') }}" class="btn btn-secondary">Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                @if($sanPham->hinh_anh)
                    <img src="{{ asset($sanPham->hinh_anh) }}" class="img-fluid rounded" alt="{{ $sanPham->ten_san_pham }}">
                @else
                    <img src="https://via.placeholder.com/300" class="img-fluid rounded" alt="No image">
                @endif
            </div>
            <div class="col-md-8">
                <h3>{{ $sanPham->ten_san_pham }} <small class="text-muted">({{ $sanPham->ma_vach }})</small></h3>
                <p class="mb-1"><strong>Danh mục:</strong> {{ $sanPham->danhMuc->ten_danh_muc ?? '-' }}</p>
                <p class="mb-1"><strong>Đơn vị:</strong> {{ $sanPham->donVi->ten_don_vi ?? '-' }}</p>
                <p class="mb-1"><strong>Thuộc tính:</strong> {{ $sanPham->thuocTinh->ten_thuoc_tinh ?? '-' }}</p>
                <p class="mb-1"><strong>Giá bán:</strong> {{ number_format($sanPham->gia_ban, 0, ',', '.') }} đ</p>
                <p class="mb-1"><strong>Tồn kho:</strong> {{ $sanPham->so_luong_ton_kho }}</p>
                <p class="mb-1"><strong>Mô tả:</strong></p>
                <div class="border p-3">{!! nl2br(e($sanPham->mo_ta)) !!}</div>
            </div>
        </div>
    </div>
</div>

@endsection
