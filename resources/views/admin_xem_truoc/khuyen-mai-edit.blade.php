@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chỉnh sửa Khuyến mãi')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            Chỉnh sửa chương trình khuyến mãi
        </h4>

        <div class="text-muted small">
            Cập nhật thông tin và sản phẩm áp dụng
        </div>
    </div>

    <a href="{{ url('/admin/khuyen-mai') }}"
       class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Quay lại
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body p-4">

        <form action="{{ route('khuyen-mai.update', $promo->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="row g-3">

                {{-- Tên chương trình --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Tên chương trình
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="ten_chuong_trinh"
                        value="{{ old('ten_chuong_trinh', $promo->ten_chuong_trinh) }}"
                        class="form-control @error('ten_chuong_trinh') is-invalid @enderror"
                        required
                    >

                    @error('ten_chuong_trinh')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Loại giảm giá --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Loại khuyến mãi
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="loai_giam_gia"
                        class="form-select @error('loai_giam_gia') is-invalid @enderror"
                        required
                    >
                        <option value="percent"
                            {{ old('loai_giam_gia', $promo->loai_giam_gia) === 'percent' ? 'selected' : '' }}>
                            Giảm %
                        </option>

                        <option value="amount"
                            {{ old('loai_giam_gia', $promo->loai_giam_gia) === 'amount' ? 'selected' : '' }}>
                            Giảm tiền
                        </option>

                        <option value="bogo"
                            {{ old('loai_giam_gia', $promo->loai_giam_gia) === 'bogo' ? 'selected' : '' }}>
                            Buy 1 Get 1
                        </option>

                        <option value="gift"
                            {{ old('loai_giam_gia', $promo->loai_giam_gia) === 'gift' ? 'selected' : '' }}>
                            Quà tặng
                        </option>
                    </select>

                    @error('loai_giam_gia')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Giá trị giảm --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Giá trị giảm
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">
                        <input
                            type="text"
                            name="gia_tri_giam"
                            class="form-control money-input @error('gia_tri_giam') is-invalid @enderror"
                            data-money-decimals="2"
                            value="{{ old('gia_tri_giam', $promo->gia_tri_giam ? number_format((float)$promo->gia_tri_giam, 2, ',', '.') : '') }}"
                            inputmode="decimal"
                            required
                        >

                        <span class="input-group-text">
                            % / đ
                        </span>

                        @error('gia_tri_giam')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Giảm tối đa --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Giảm tối đa
                    </label>

                    <div class="input-group">
                        <input
                            type="text"
                            name="giam_toi_da"
                            class="form-control money-input @error('giam_toi_da') is-invalid @enderror"
                            value="{{ old('giam_toi_da', $promo->giam_toi_da ? number_format((float)$promo->giam_toi_da, 0, ',', '.') : '') }}"
                            placeholder="0"
                            inputmode="numeric"
                        >

                        <span class="input-group-text">đ</span>
                    </div>
                </div>

                {{-- Số lượng tối thiểu --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Số lượng sản phẩm tối thiểu
                    </label>

                    <input
                        type="number"
                        min="0"
                        name="so_luong_sp_toi_thieu"
                        value="{{ old('so_luong_sp_toi_thieu', $promo->so_luong_sp_toi_thieu) }}"
                        class="form-control @error('so_luong_sp_toi_thieu') is-invalid @enderror"
                        placeholder="0"
                    >
                </div>

                {{-- Đơn hàng tối thiểu --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Đơn hàng tối thiểu
                    </label>

                    <div class="input-group">
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="don_hang_toi_thieu"
                            value="{{ old('don_hang_toi_thieu', $promo->don_hang_toi_thieu) }}"
                            class="form-control @error('don_hang_toi_thieu') is-invalid @enderror"
                            placeholder="0"
                        >

                        <span class="input-group-text">đ</span>
                    </div>
                </div>

                {{-- Ngày bắt đầu --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Ngày bắt đầu
                    </label>

                    <input
                        type="date"
                        name="ngay_bat_dau"
                        value="{{ old(
                            'ngay_bat_dau',
                            $promo->ngay_bat_dau
                                ? $promo->ngay_bat_dau->format('Y-m-d')
                                : ''
                        ) }}"
                        class="form-control @error('ngay_bat_dau') is-invalid @enderror"
                    >

                    @error('ngay_bat_dau')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Ngày kết thúc --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Ngày kết thúc
                    </label>

                    <input
                        type="date"
                        name="ngay_ket_thuc"
                        value="{{ old(
                            'ngay_ket_thuc',
                            $promo->ngay_ket_thuc
                                ? $promo->ngay_ket_thuc->format('Y-m-d')
                                : ''
                        ) }}"
                        class="form-control @error('ngay_ket_thuc') is-invalid @enderror"
                    >

                    @error('ngay_ket_thuc')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Sản phẩm áp dụng --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Sản phẩm áp dụng
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="searchPromotionProduct"
                        class="form-control mb-2"
                        placeholder="Tìm tên sản phẩm..."
                        oninput="filterPromotionProducts()"
                    >

                    <div
                        id="promotionProductList"
                        class="border rounded p-2"
                        style="max-height: 260px; overflow-y: auto;"
                    >
                        @forelse($sanPhams as $sanPham)
                            <label
                                class="promotion-product-item d-flex align-items-center gap-2 p-2 border-bottom"
                                data-name="{{ mb_strtolower($sanPham->ten_san_pham) }}"
                            >
                                <input
                                    type="checkbox"
                                    name="id_san_phams[]"
                                    value="{{ $sanPham->id }}"
                                    class="form-check-input m-0"
                                    {{
                                        in_array(
                                            (int) $sanPham->id,
                                            array_map(
                                                'intval',
                                                old(
                                                    'id_san_phams',
                                                    $idSanPhamsDaChon ?? []
                                                )
                                            ),
                                            true
                                        )
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span>
                                    {{ $sanPham->ten_san_pham }}
                                </span>
                            </label>
                        @empty
                            <div class="text-muted text-center py-3">
                                Chưa có sản phẩm nào.
                            </div>
                        @endforelse
                    </div>

                    <small class="text-muted">
                        Chọn ít nhất một sản phẩm áp dụng khuyến mãi.
                    </small>

                    @error('id_san_phams')
                        <div class="text-danger mt-1">
                            {{ $message }}
                        </div>
                    @enderror

                    @error('id_san_phams.*')
                        <div class="text-danger mt-1">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Mô tả --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Mô tả
                    </label>

                    <textarea
                        name="ghi_chu"
                        rows="4"
                        class="form-control @error('ghi_chu') is-invalid @enderror"
                    >{{ old('ghi_chu', $promo->ghi_chu) }}</textarea>

                    @error('ghi_chu')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Trạng thái --}}
                <div class="col-12">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            name="trang_thai"
                            value="1"
                            class="form-check-input"
                            id="active"
                            {{ old('trang_thai', $promo->trang_thai) ? 'checked' : '' }}
                        >

                        <label class="form-check-label" for="active">
                            Kích hoạt chương trình
                        </label>
                    </div>
                </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ url('/admin/khuyen-mai') }}"
                   class="btn btn-secondary">
                    Hủy
                </a>

                <button type="submit"
                        class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Lưu thay đổi
                </button>
            </div>

        </form>
    </div>
</div>

<script>
function filterPromotionProducts() {
    const input = document.getElementById(
        'searchPromotionProduct'
    );

    if (!input) {
        return;
    }

    const keyword = input.value
        .trim()
        .toLowerCase();

    document
        .querySelectorAll('.promotion-product-item')
        .forEach(item => {
            const productName =
                item.dataset.name || '';

            item.style.display =
                productName.includes(keyword)
                    ? 'flex'
                    : 'none';
        });
}
</script>

@endsection