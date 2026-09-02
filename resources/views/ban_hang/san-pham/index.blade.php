@extends('ban_hang.layouts.ban_hang')

@section('title', 'Tra cứu sản phẩm')

@section('styles')
<style>
    .search-card,
    .product-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(27, 94, 32, 0.08);
    }

    .search-card .card-body,
    .product-card .card-body {
        padding: 1.25rem;
    }

    .product-image {
        width: 48px !important;
        height: 48px !important;
        max-width: 48px !important;
        max-height: 48px !important;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        background: #fff;
        display: block;
    }

    .product-image-placeholder {
        width: 48px !important;
        height: 48px !important;
        max-width: 48px !important;
        max-height: 48px !important;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .search-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .search-actions .btn {
        min-width: 120px;
    }

    .text-description {
        min-width: 220px;
        max-width: 320px;
        white-space: normal;
    }

    .pagination {
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .search-actions .btn {
            width: 100%;
        }

        .text-description {
            min-width: 180px;
            max-width: none;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between flex-wrap align-items-center gap-2 pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-1">Tra cứu sản phẩm</h1>
        <p class="text-muted mb-0">Xem thông tin sản phẩm đang hoạt động bằng tìm kiếm theo tên hoặc thương hiệu.</p>
    </div>
</div>

<div class="card search-card mb-4">
    <div class="card-body">
        <form action="{{ route('nhan-vien.san-pham') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-8">
                    <label for="tu_khoa" class="form-label fw-semibold">Từ khóa tìm kiếm</label>
                    <input
                        type="text"
                        class="form-control"
                        id="tu_khoa"
                        name="tu_khoa"
                        value="{{ $tuKhoa }}"
                        placeholder="Nhập tên sản phẩm hoặc thương hiệu"
                    >
                </div>
                <div class="col-12 col-lg-4">
                    <div class="search-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                        <a href="{{ route('nhan-vien.san-pham') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-rotate-left me-2"></i>Đặt lại
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card product-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-nv mb-0">
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Thương hiệu</th>
                        <th>Danh mục</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                        <th>Đơn vị tính</th>
                        <th>Giá bán</th>
                        <th>Số lượng tồn</th>
                        <th>Trạng thái kho</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sanPhams as $sanPham)
                        <tr>
                            <td>
                                @if ($sanPham->hinh_anh_hien_thi && \App\Models\BienTheSanPham::hasImageFile($sanPham->hinh_anh_hien_thi))
                                    <img
                                        src="{{ \App\Models\BienTheSanPham::resolveImageUrl($sanPham->hinh_anh_hien_thi) }}"
                                        alt="Hình ảnh {{ $sanPham->ten_san_pham }}"
                                        class="product-image"
                                    >
                                @else
                                    <span class="product-image-placeholder">
                                        <i class="fas fa-box"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $sanPham->ten_san_pham }}</td>
                            <td>{{ $sanPham->thuong_hieu ?: 'Không có' }}</td>
                            <td>{{ optional($sanPham->danhMuc)->ten_danh_muc ?: 'Không có danh mục' }}</td>
                            <td class="text-description">{{ $sanPham->mo_ta ?: 'Không có mô tả' }}</td>
                            <td>
                                <span class="status-badge status-success">Đang hoạt động</span>
                            </td>
                            <td>{{ $sanPham->don_vi_tinh_hien_thi ?: 'Không có' }}</td>
                            <td>
                                @if (!is_null($sanPham->gia_ban_hien_thi))
                                    {{ number_format((float) $sanPham->gia_ban_hien_thi, 0, ',', '.') }} đ
                                @else
                                    Không có
                                @endif
                            </td>
                            <td>{{ number_format((int) $sanPham->tong_ton_kho_hien_thi, 0, ',', '.') }}</td>
                            <td>
                                @if ($sanPham->trang_thai_kho_hien_thi === 'Còn hàng')
                                    <span class="status-badge status-success">Còn hàng</span>
                                @else
                                    <span class="status-badge status-warning">Hết hàng</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                Không tìm thấy sản phẩm phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="text-muted small">
                Hiển thị {{ $sanPhams->firstItem() ?? 0 }} - {{ $sanPhams->lastItem() ?? 0 }} trên {{ $sanPhams->total() }} sản phẩm
            </div>
            {{ $sanPhams->links() }}
        </div>
    </div>
</div>
@endsection
