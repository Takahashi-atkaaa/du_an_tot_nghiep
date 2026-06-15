@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Khuyến mãi - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Khuyến mãi</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Khuyến mãi</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromotionModal">
        <i class="fas fa-plus me-2"></i>Tạo khuyến mãi
    </button>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-gift"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng khuyến mãi</h6>
                    <h3 class="mb-0">{{ $total ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-play-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đang hoạt động</h6>
                    <h3 class="mb-0">{{ $active ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-clock"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Sắp diễn ra</h6>
                    <h3 class="mb-0">{{ $upcoming ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-pause-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đã kết thúc</h6>
                    <h3 class="mb-0">{{ $ended ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card table-admin mb-4">
    <div class="card-body">
        <form action="{{ url('/admin/khuyen-mai') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm khuyến mãi...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="loai">
                        <option value="" {{ request('loai') == '' ? 'selected' : '' }}>Tất cả loại</option>
                        <option value="percent" {{ request('loai') == 'percent' ? 'selected' : '' }}>Giảm %</option>
                        <option value="amount" {{ request('loai') == 'amount' ? 'selected' : '' }}>Giảm tiền</option>
                        <option value="bogo" {{ request('loai') == 'bogo' ? 'selected' : '' }}>Buy 1 Get 1</option>
                        <option value="gift" {{ request('loai') == 'gift' ? 'selected' : '' }}>Quà tặng</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="trang_thai">
                        <option value="" {{ request('trang_thai') == '' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="active" {{ request('trang_thai') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="upcoming" {{ request('trang_thai') == 'upcoming' ? 'selected' : '' }}>Sắp diễn ra</option>
                        <option value="ended" {{ request('trang_thai') == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Promotions Grid -->
<div class="card table-admin mb-4">
    <div class="card-body">
        <div class="row g-4">
            @if(isset($items) && $items->count())
                @foreach($items as $promo)
                    @php
                        $now = \Carbon\Carbon::now();
                        $start = $promo->ngay_bat_dau;
                        $end = $promo->ngay_ket_thuc;

                        if ($promo->trang_thai && $start && $end && $now->between($start, $end)) {
                            $statusText = 'Đang hoạt động';
                            $statusClass = 'status-active';
                        } elseif ($start && $now->lt($start)) {
                            $statusText = 'Sắp diễn ra';
                            $statusClass = 'status-pending';
                        } else {
                            $statusText = 'Đã kết thúc';
                            $statusClass = 'status-inactive';
                        }

                        if ($promo->loai_giam_gia === 'percent') {
                            $badge = 'Giảm ' . rtrim(rtrim(number_format($promo->gia_tri_giam, 2, ',', '.'), '0'), ',') . '%';
                        } elseif ($promo->loai_giam_gia === 'amount') {
                            $badge = 'Giảm ' . number_format($promo->gia_tri_giam, 0, ',', '.') . 'đ';
                        } else {
                            $badge = ucfirst($promo->loai_giam_gia);
                        }
                    @endphp

                    <div class="col-xl-4 col-lg-6">
                        <div class="card table-admin h-100">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                @if($promo->loai_giam_gia === 'percent')
                                    <span class="badge bg-primary"><i class="fas fa-percent me-1"></i>Giảm {{ rtrim(rtrim(number_format($promo->gia_tri_giam, 2, ',', '.'), '0'), ',') }}%</span>
                                @elseif($promo->loai_giam_gia === 'amount')
                                    <span class="badge bg-success"><i class="fas fa-coins me-1"></i>Giảm {{ number_format($promo->gia_tri_giam, 0, ',', '.') }}đ</span>
                                @elseif($promo->loai_giam_gia === 'bogo')
                                    <span class="badge bg-info text-dark"><i class="fas fa-gifts me-1"></i>Buy 1 Get 1</span>
                                @elseif($promo->loai_giam_gia === 'gift')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-gift me-1"></i>Quà tặng</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-tag me-1"></i>{{ ucfirst($promo->loai_giam_gia) }}</span>
                                @endif
                                <span class="status-badge {{ $statusClass }}" id="promo-status-{{ $promo->id }}">{{ $statusText }}</span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $promo->ten_chuong_trinh }}</h5>
                                <p class="text-muted small mb-2">{{ $promo->ghi_chu ?? '-' }}</p>
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="fas fa-calendar me-2"></i>
                                    <span>{{ $promo->ngay_bat_dau ? $promo->ngay_bat_dau->format('d/m/Y') : '-' }} - {{ $promo->ngay_ket_thuc ? $promo->ngay_ket_thuc->format('d/m/Y') : '-' }}</span>
                                </div>
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="fas fa-box me-2"></i>
                                    <span>{{ $promo->so_luong_sp_toi_thieu ? $promo->so_luong_sp_toi_thieu . ' sp tối thiểu' : 'Áp dụng cho tất cả sản phẩm' }}</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-success"><i class="fas fa-chart-line me-1"></i>—</small>
                                    <div>
                                        <a href="{{ route('khuyen-mai.edit', $promo->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('khuyen-mai.destroy', $promo->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Xác nhận xóa chương trình khuyến mãi này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                        {{-- Fallback for no-JS: keep a small POST form (hidden) --}}
                                        <form action="{{ route('khuyen-mai.toggle', $promo->id) }}" method="POST" style="display:inline-block; margin-left:6px;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Bật/Tắt">
                                                @if($promo->trang_thai)
                                                    <i class="fas fa-toggle-on text-success"></i>
                                                @else
                                                    <i class="fas fa-toggle-off text-secondary"></i>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Add New Card --}}
                <div class="col-xl-4 col-lg-6">
                    <div class="card table-admin h-100">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center py-5">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-plus fa-2x text-primary"></i>
                            </div>
                            <h5 class="mb-2">Tạo khuyến mãi mới</h5>
                            <p class="text-muted mb-3">Thêm chương trình khuyến mãi mới</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromotionModal">
                                <i class="fas fa-plus me-2"></i>Tạo mới
                            </button>
                        </div>
                    </div>
                </div>

            @else
                <div class="col-12">
                    <div class="card table-admin">
                        <div class="card-body text-center">
                            <p class="mb-0">Chưa có chương trình khuyến mãi nào. Nhấn "Tạo khuyến mãi" để thêm mới.</p>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addPromotionModal">Tạo khuyến mãi mới</button>
                        </div>
                    </div>
                </div>

                {{-- Add New Card when no items --}}
                <div class="col-xl-4 col-lg-6">
                    <div class="card table-admin h-100">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center py-5">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-plus fa-2x text-primary"></i>
                            </div>
                            <h5 class="mb-2">Tạo khuyến mãi mới</h5>
                            <p class="text-muted mb-3">Thêm chương trình khuyến mãi mới</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromotionModal">
                                <i class="fas fa-plus me-2"></i>Tạo mới
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if(isset($items) && method_exists($items, 'links'))
            <div class="mt-4 d-flex justify-content-center">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Promotion Modal -->
<div class="modal fade" id="addPromotionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-gift me-2"></i>Tạo khuyến mãi mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/khuyen-mai') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                            <input type="text" name="ten_chuong_trinh" class="form-control" placeholder="Nhập tên khuyến mãi" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loại khuyến mãi <span class="text-danger">*</span></label>
                            <select class="form-select" name="loai_giam_gia" required>
                                <option value="" selected disabled>Chọn loại</option>
                                <option value="percent">Giảm %</option>
                                <option value="amount">Giảm tiền</option>
                                <option value="bogo">Buy 1 Get 1</option>
                                <option value="gift">Quà tặng</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="gia_tri_giam" class="form-control" placeholder="0" required>
                                <span class="input-group-text">% / đ</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="date" name="ngay_bat_dau" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="ngay_ket_thuc" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Áp dụng cho</label>
                            <select class="form-select" name="ap_dung_cho">
                                <option value="all">Tất cả sản phẩm</option>
                                <option value="category">Danh mục cụ thể</option>
                                <option value="product">Sản phẩm cụ thể</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Đơn hàng tối thiểu</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="don_hang_toi_thieu" class="form-control" placeholder="0">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="ghi_chu" rows="3" placeholder="Mô tả chi tiết..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActive" name="trang_thai" value="1">
                                <label class="form-check-label" for="isActive">
                                    Kích hoạt ngay sau khi tạo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
