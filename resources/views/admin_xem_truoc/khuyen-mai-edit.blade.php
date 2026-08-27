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

                {{-- Tên khuyến mãi --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Tên khuyến mãi
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="ten_chuong_trinh"
                        value="{{ old('ten_chuong_trinh', $promo->ten_chuong_trinh) }}"
                        class="form-control @error('ten_chuong_trinh') is-invalid @enderror"
                        placeholder="Nhập tên khuyến mãi"
                        required
                    >

                    @error('ten_chuong_trinh')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>


                {{-- Loại khuyến mãi --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Loại khuyến mãi
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="loai_giam_gia"
                        id="loai_giam_gia"
                        class="form-select @error('loai_giam_gia') is-invalid @enderror"
                        required
                    >
                        <option value="">
                            Chọn loại
                        </option>

                        <option value="percent"
                            {{ old('loai_giam_gia', $promo->loai_giam_gia) == 'percent' ? 'selected' : '' }}>
                            Giảm %
                        </option>

                        <option value="amount"
                            {{ old('loai_giam_gia', $promo->loai_giam_gia) == 'amount' ? 'selected' : '' }}>
                            Giảm tiền
                        </option>

                        <option value="bogo"
                            {{ old('loai_giam_gia', $promo->loai_giam_gia) == 'bogo' ? 'selected' : '' }}>
                            Mua 1 tặng 1
                        </option>

                        <option value="gift"
                            {{ old('loai_giam_gia', $promo->loai_giam_gia) == 'gift' ? 'selected' : '' }}>
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
                            type="number"
                            name="gia_tri_giam"
                            id="gia_tri_giam"
                            step="0.01"
                            min="0"
                            value="{{ old('gia_tri_giam', $promo->gia_tri_giam) }}"
                            class="form-control @error('gia_tri_giam') is-invalid @enderror"
                            required
                        >

                        <span class="input-group-text"
                              id="donViGiam">
                            % / đ
                        </span>

                        @error('gia_tri_giam')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>
                </div>


                {{-- Ngày bắt đầu --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Ngày bắt đầu
                        <span class="text-danger">*</span>
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
                        required
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
                        <span class="text-danger">*</span>
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
                        required
                    >

                    @error('ngay_ket_thuc')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>


                {{-- Số lượng sản phẩm tối thiểu --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Số lượng sản phẩm tối thiểu
                    </label>

                    <input
                        type="number"
                        name="so_luong_sp_toi_thieu"
                        min="0"
                        value="{{ old(
                            'so_luong_sp_toi_thieu',
                            $promo->so_luong_sp_toi_thieu
                        ) }}"
                        class="form-control @error('so_luong_sp_toi_thieu') is-invalid @enderror"
                        placeholder="0"
                    >

                    @error('so_luong_sp_toi_thieu')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>


                {{-- Đơn hàng tối thiểu --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Đơn hàng tối thiểu
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            name="don_hang_toi_thieu"
                            step="0.01"
                            min="0"
                            value="{{ old(
                                'don_hang_toi_thieu',
                                $promo->don_hang_toi_thieu
                            ) }}"
                            class="form-control @error('don_hang_toi_thieu') is-invalid @enderror"
                            placeholder="0"
                        >

                        <span class="input-group-text">đ</span>

                        @error('don_hang_toi_thieu')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>
                </div>


                {{-- Giảm tối đa --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Giảm tối đa
                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            name="giam_toi_da"
                            step="0.01"
                            min="0"
                            value="{{ old(
                                'giam_toi_da',
                                $promo->giam_toi_da
                            ) }}"
                            class="form-control @error('giam_toi_da') is-invalid @enderror"
                            placeholder="0"
                        >

                        <span class="input-group-text">đ</span>

                        @error('giam_toi_da')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>
                </div>


                {{-- Phạm vi áp dụng --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Phạm vi áp dụng
                    </label>

                    <select
                        name="pham_vi_ap_dung"
                        id="pham_vi_ap_dung"
                        class="form-select"
                    >
                        <option value="hoa_don"
                            {{ old(
                                'pham_vi_ap_dung',
                                $promo->pham_vi_ap_dung ?? 'hoa_don'
                            ) == 'hoa_don' ? 'selected' : '' }}>
                            Toàn hóa đơn
                        </option>

                        <option value="san_pham"
                            {{ old(
                                'pham_vi_ap_dung',
                                $promo->pham_vi_ap_dung ?? 'hoa_don'
                            ) == 'san_pham' ? 'selected' : '' }}>
                            Sản phẩm / biến thể cụ thể
                        </option>
                    </select>
                </div>


                {{-- Sản phẩm áp dụng --}}
                <div
                    class="col-12"
                    id="productArea"
                >

                    <label class="form-label fw-semibold">
                        Sản phẩm áp dụng
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="searchPromotionProduct"
                        class="form-control mb-2"
                        placeholder="Tìm tên sản phẩm..."
                    >

                    <div
                        id="promotionProductList"
                        class="border rounded p-2"
                        style="max-height:260px; overflow-y:auto;"
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


                {{-- Kích hoạt --}}
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

                        <label
                            class="form-check-label"
                            for="active"
                        >
                            Kích hoạt ngay sau khi cập nhật
                        </label>

                    </div>

                </div>

            </div>


            {{-- Nút --}}
            <div class="d-flex justify-content-end gap-2 mt-4">

                <a
                    href="{{ url('/admin/khuyen-mai') }}"
                    class="btn btn-secondary"
                >
                    Hủy
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-save me-1"></i>
                    Lưu thay đổi
                </button>

            </div>

        </form>

    </div>
</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const loaiGiamGia =
        document.getElementById('loai_giam_gia');

    const giaTriGiam =
        document.getElementById('gia_tri_giam');

    const phamVi =
        document.getElementById('pham_vi_ap_dung');

    const productArea =
        document.getElementById('productArea');

    const searchInput =
        document.getElementById('searchPromotionProduct');


    // ================================
    // Hiển thị % / đ
    // ================================
    function updateUnit() {

        if (!loaiGiamGia || !giaTriGiam) {
            return;
        }

        if (loaiGiamGia.value === 'percent') {

            giaTriGiam.setAttribute('max', '100');

        } else {

            giaTriGiam.removeAttribute('max');

        }
    }


    // ================================
    // Phạm vi áp dụng
    // ================================
    function updateProductArea() {

        if (!phamVi || !productArea) {
            return;
        }

        if (phamVi.value === 'san_pham') {

            productArea.style.display = 'block';

        } else {

            productArea.style.display = 'none';

        }
    }


    // ================================
    // Tìm sản phẩm
    // ================================
    function filterPromotionProducts() {

        if (!searchInput) {
            return;
        }

        const keyword =
            searchInput.value
                .trim()
                .toLowerCase();

        document
            .querySelectorAll('.promotion-product-item')
            .forEach(function (item) {

                const productName =
                    item.dataset.name || '';

                item.style.display =
                    productName.includes(keyword)
                        ? 'flex'
                        : 'none';

            });
    }


    if (loaiGiamGia) {
        loaiGiamGia.addEventListener(
            'change',
            updateUnit
        );
    }


    if (phamVi) {
        phamVi.addEventListener(
            'change',
            updateProductArea
        );
    }


    if (searchInput) {
        searchInput.addEventListener(
            'input',
            filterPromotionProducts
        );
    }


    updateUnit();
    updateProductArea();

});

</script>

@endsection