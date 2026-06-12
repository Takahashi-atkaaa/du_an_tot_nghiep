@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sửa sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/san-pham') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Sửa sản phẩm</li>
            </ol>
        </nav>
    </div>
    <a href="{{ url('admin/san-pham') }}" class="btn btn-secondary">Quay lại</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ url('admin/san-pham/'.$sanPham->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mã sản phẩm</label>
                    <input type="text" class="form-control" value="{{ $sanPham->ma_hang }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Barcode</label>
                    <input type="text" name="ma_vach" class="form-control" value="{{ old('ma_vach', $sanPham->ma_vach) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="ten_san_pham" class="form-control" value="{{ old('ten_san_pham', $sanPham->ten_san_pham) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_danh_muc" required>
                        <option value="" disabled>Chọn danh mục</option>
                        @foreach($danhMucs as $danhMuc)
                            <option value="{{ $danhMuc->id }}" {{ (old('id_danh_muc', $sanPham->id_danh_muc) == $danhMuc->id) ? 'selected' : '' }}>{{ $danhMuc->ten_danh_muc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Thương hiệu</label>
                    <input type="text" name="thuong_hieu" class="form-control" value="{{ old('thuong_hieu', $sanPham->thuong_hieu) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Đơn vị cơ bản <span class="text-danger">*</span></label>
                    <input type="text" name="don_vi_co_ban" class="form-control" value="{{ old('don_vi_co_ban', $sanPham->donVi->ten_don_vi ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Thuộc tính</label>
                    <select class="form-select" name="id_thuoc_tinh">
                        <option value="">Chọn thuộc tính</option>
                        @foreach($thuocTinhs as $thuocTinh)
                            <option value="{{ $thuocTinh->id }}" {{ (old('id_thuoc_tinh', $sanPham->id_thuoc_tinh) == $thuocTinh->id) ? 'selected' : '' }}>{{ $thuocTinh->ten_thuoc_tinh }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Giá vốn</label>
                    <div class="input-group">
                        <input type="number" name="gia_von" class="form-control" value="{{ old('gia_von', $sanPham->gia_von) }}" min="0">
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="gia_ban" class="form-control" value="{{ old('gia_ban', $sanPham->gia_ban) }}" min="0" required>
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tồn kho</label>
                    <input type="number" name="so_luong_ton_kho" class="form-control" value="{{ old('so_luong_ton_kho', $sanPham->so_luong_ton_kho) }}" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Định mức tối thiểu</label>
                    <input type="number" name="dinh_muc_toi_thieu" class="form-control" value="{{ old('dinh_muc_toi_thieu', $sanPham->dinh_muc_toi_thieu) }}" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Mô tả</label>
                    <textarea class="form-control" name="mo_ta" rows="4">{{ old('mo_ta', $sanPham->mo_ta) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Hình ảnh</label>
                    <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                    @if($sanPham->hinh_anh)
                        <div class="mt-2">
                            <img src="{{ asset($sanPham->hinh_anh) }}" alt="Hình sản phẩm" style="max-width: 200px; height: auto;" class="rounded">
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ url('admin/san-pham') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
