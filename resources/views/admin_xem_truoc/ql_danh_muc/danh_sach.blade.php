@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Danh mục sản phẩm - SmartMart')

@section('content')
<div class="row g-4">

    <div class="col-12" style="text-align: right;">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalThemDanhMuc">
            Thêm danh mục mới
        </button>
    </div>

    {{-- Đoạn code hiển thị lỗi cũ của bạn --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- CHÈN THÊM ĐOẠN NÀY ĐỂ HIỂN THỊ THÔNG BÁO THÀNH CÔNG --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Thành công!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Thất bại!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    @endif

    {{-- //modol thêm danh mục --}}
    <div class="modal fade" id="modalThemDanhMuc" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('danh_muc.store') }}" method="POST" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Thêm danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- Tên danh mục --}}
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục</label>
                        <input type="text" name="ten_danh_muc" class="form-control" required>
                    </div>

                    {{-- Icon --}}
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
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

                    {{-- Màu sắc --}}
                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <input type="color" name="mau_sac" class="form-control form-control-color" value="#0d6efd">
                    </div>

                    {{-- Trạn thái --}}
                     <div class="mb-3">   
                        <select name="trang_thai" class="form-select">
                            <option value="1">Đang hoạt động</option>
                            <option value="0">Tạm ngưng</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Lưu
                    </button>
                </div>

            </form>
        </div>
    </div>
    
    @foreach($danh_muc_sp as $item)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card table-admin h-100">

                <div class="card-body text-center">

                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                    style="width: 80px; height: 80px; background-color: {{ $item->mau_sac ?? '#0d6efd' }}20;">

                    <i class="fa-solid fa-{{ $item->icon ?? 'box' }} fa-2x"
                    style="color: {{ $item->mau_sac ?? '#0d6efd' }}"></i>
                </div>

                    <h5 class="mb-1">{{ $item->ten_danh_muc }}</h5>

                    <p class="text-muted mb-2">
                        {{ $item->san_phams_count }} sản phẩm
                    </p>

                    <a href="{{ route('danh_muc.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-edit"></i>
                    </a>
                    <form action="{{ route('danh_muc.destroy', $item->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                    <br>

                    @if(isset($item->trang_thai))
                        @if($item->trang_thai == 1)
                            <span class="status-badge status-active">Đang hoạt động</span>
                        @else
                            <span class="status-badge status-inactive">Tạm ngưng</span>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection