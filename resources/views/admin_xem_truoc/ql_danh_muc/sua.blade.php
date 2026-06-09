@extends('admin_xem_truoc.layouts.admin')


@section('title', 'Danh mục sản phẩm chỉnh sửa- SmartMart')

@section('content')
<div class="container">

    <h2>Cập nhật danh mục sản phẩm</h2>

    <form action="{{ route('danh_muc.update', $danhmuc->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Tên danh mục --}}
        <div class="mb-3">
            <label>Tên danh mục</label>
            <input type="text"
                   name="ten_danh_muc"
                   class="form-control"
                   value="{{ old('ten_danh_muc', $danhmuc->ten_danh_muc) }}">
        </div>
        @error('ten_danh_muc')
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror

        {{-- Trạng thái --}}
        <div class="mb-3">
            <label>Trạng thái</label>
            <select name="trang_thai" class="form-control">
                <option value="1" {{ $danhmuc->trang_thai ? 'selected' : '' }}>Hoạt động</option>
                <option value="0" {{ !$danhmuc->trang_thai ? 'selected' : '' }}>Ngưng hoạt động</option>
            </select>
        </div>

        {{-- Màu sắc --}}
        <div class="mb-3">
            <label>Màu sắc</label>
            <input type="color"
                   name="mau_sac"
                   class="form-control form-control-color"
                   value="{{ old('mau_sac', $danhmuc->mau_sac) }}">
        </div>
        @error('mau_sac')
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror

        {{-- Icon --}}
        <div class="mb-3">
            @if($danhmuc->icon)
                <div class="mt-2">
                    <p>Icon hiện tại:</p>
                   <i class="fa-solid fa-{{ $danhmuc->icon ?? 'box' }} fa-2x"
                    style="color: {{ $danhmuc->mau_sac ?? '#0d6efd' }}"></i>
                </div>
            @endif
        </div>


        {{-- Icon --}}
        <div class="mb-3">
            <label class="form-label">Icon mới</label>
            <select name="icon" class="form-select">
                <option value="box">📦 Thực phẩm khô</option>
                <option value="bread-slice">🍞 Bánh mì / đồ ăn</option>
                <option value="bowl-food">🍲 Đồ ăn / mì / phở</option>
                <option value="seedling">🌱 Ngũ cốc / nông sản</option>
                <option value="bottle-water">🥤 Nước uống</option>
                <option value="glass-water">💧 Nước lọc</option>
                <option value="mug-hot">☕ Cà phê / trà</option>
                <option value="martini-glass">🍸 Nước giải khát</option>
                <option value="apple-whole">🍎 Trái cây</option>
                <option value="carrot">🥕 Rau củ</option>
                <option value="lemon">🍋 Thực phẩm tươi</option>
                <option value="leaf">🥬 Rau xanh</option>
                <option value="soap">🧼 Đồ vệ sinh</option>
                <option value="pump-soap">🧴 Sữa tắm / dầu gội</option>
                <option value="toothbrush">🪥 Đồ cá nhân</option>
                <option value="spray-can">🧽 Tẩy rửa</option>
                <option value="house">🏠 Gia dụng</option>
                <option value="utensils">🍴 Dụng cụ bếp</option>
                <option value="bucket">🪣 Đồ dùng nhà</option>
                <option value="broom">🧹 Dụng cụ vệ sinh</option>
                <option value="bolt">⚡ Điện / thiết bị</option>
                <option value="plug">🔌 Ổ cắm / dây điện</option>
                <option value="battery-full">🔋 Pin</option>
                <option value="lightbulb">💡 Bóng đèn</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Cập nhật
        </button>

        <a href="{{ route('danh_muc.index') }}" class="btn btn-secondary">
            Quay lại
        </a>

    </form>

</div>
@endsection