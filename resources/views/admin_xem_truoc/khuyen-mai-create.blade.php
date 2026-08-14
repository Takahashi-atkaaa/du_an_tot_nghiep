@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Tạo khuyến mãi')

@section('content')

<div class="container-fluid">

    {{-- TIÊU ĐỀ --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                Tạo khuyến mãi mới
            </h4>

            <div class="text-muted">
                Admin / Khuyến mãi / Tạo mới
            </div>
        </div>

        <a href="{{ route('khuyen-mai.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Quay lại
        </a>
    </div>


    {{-- FORM --}}
    <form action="{{ route('khuyen-mai.store') }}"
          method="POST"
          id="promotionForm">

        @csrf

        <div class="card">
            <div class="card-body p-4">

                {{-- TÊN --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tên khuyến mãi
                        <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           name="ten_chuong_trinh"
                           class="form-control"
                           placeholder="Nhập tên khuyến mãi">
                </div>


                <div class="row">

                    {{-- LOẠI --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">
                            Loại khuyến mãi
                            <span class="text-danger">*</span>
                        </label>

                        <select name="loai_giam_gia"
                                class="form-select">

                            <option value="">
                                Chọn loại
                            </option>

                            <option value="percent">
                                Giảm theo %
                            </option>

                            <option value="amount">
                                Giảm số tiền
                            </option>

                            <option value="bogo">
                                Mua 1 tặng 1
                            </option>

                        </select>
                    </div>


                    {{-- GIÁ TRỊ --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Giá trị giảm
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <input type="number"
                                   name="gia_tri_giam"
                                   class="form-control"
                                   value="0"
                                   min="0">

                            <span class="input-group-text">
                                % / đ
                            </span>

                        </div>
                    </div>

                </div>


                <div class="row">

                    {{-- NGÀY BẮT ĐẦU --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Ngày bắt đầu
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="ngay_bat_dau"
                               class="form-control">

                    </div>


                    {{-- NGÀY KẾT THÚC --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Ngày kết thúc
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="ngay_ket_thuc"
                               class="form-control">

                    </div>

                </div>


                <div class="row">

                    {{-- SL TỐI THIỂU --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Số lượng sản phẩm tối thiểu
                        </label>

                        <input type="number"
                               name="so_luong_sp_toi_thieu"
                               class="form-control"
                               value="0"
                               min="0">

                    </div>


                    {{-- ĐƠN HÀNG TỐI THIỂU --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">
                            Đơn hàng tối thiểu
                        </label>

                        <div class="input-group">

                            <input type="number"
                                   name="don_hang_toi_thieu"
                                   class="form-control"
                                   value="0"
                                   min="0">

                            <span class="input-group-text">đ</span>

                        </div>

                    </div>

                </div>


                {{-- GIẢM TỐI ĐA --}}
                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Giảm tối đa
                    </label>

                    <div class="input-group">

                        <input type="number"
                               name="giam_toi_da"
                               class="form-control"
                               value="0"
                               min="0">

                        <span class="input-group-text">đ</span>

                    </div>

                </div>


                {{-- ======================= --}}
                {{-- PHẠM VI ÁP DỤNG --}}
                {{-- ======================= --}}

                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Phạm vi áp dụng
                    </label>

                    <select class="form-select"
                            name="pham_vi"
                            id="promotionScope">

                        <option value="hoa_don">
                            Toàn hóa đơn
                        </option>

                        <option value="san_pham">
                            Sản phẩm / biến thể cụ thể
                        </option>

                    </select>

                </div>


                {{-- ======================= --}}
                {{-- SẢN PHẨM ÁP DỤNG --}}
                {{-- ======================= --}}

                <div id="productScopeBox"
                     class="border rounded p-3 mb-4"
                     style="display:none;">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <h6 class="fw-bold mb-1">
                                Sản phẩm áp dụng
                            </h6>

                            <small class="text-muted"
                                   id="selectedText">
                                Chưa chọn sản phẩm
                            </small>
                        </div>


                        <button type="button"
                                class="btn btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#selectProductModal">

                            <i class="fas fa-plus me-1"></i>
                            Chọn sản phẩm

                        </button>

                    </div>


                    {{-- Sản phẩm đã chọn --}}
                    <div id="selectedProductList"
                         class="mt-3">
                    </div>

                </div>


                {{-- MÔ TẢ --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Mô tả
                    </label>

                    <textarea name="ghi_chu"
                              class="form-control"
                              rows="4"
                              placeholder="Nhập mô tả khuyến mãi..."></textarea>

                </div>


                {{-- TRẠNG THÁI --}}
                <div class="form-check mb-4">

                    <input type="checkbox"
                           name="trang_thai"
                           value="1"
                           class="form-check-input"
                           id="trangThai"
                           checked>

                    <label class="form-check-label"
                           for="trangThai">
                        Kích hoạt ngay sau khi tạo
                    </label>

                </div>


                {{-- BUTTON --}}
                <div class="d-flex justify-content-end gap-2">

                    <a href="{{ route('khuyen-mai.index') }}"
                       class="btn btn-secondary">
                        Hủy
                    </a>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>
                        Lưu khuyến mãi

                    </button>

                </div>

            </div>
        </div>

    </form>

</div>


{{-- ======================================= --}}
{{-- POPUP CHỌN SẢN PHẨM --}}
{{-- ======================================= --}}

<div class="modal fade"
     id="selectProductModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fas fa-box me-2"></i>
                    Chọn sản phẩm áp dụng
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                {{-- TÌM KIẾM --}}
                <div class="mb-3">

                    <input type="text"
                           class="form-control"
                           id="productSearch"
                           placeholder="Tìm tên sản phẩm, biến thể, mã hàng...">

                </div>


                {{-- CHỌN TẤT CẢ --}}
                <div class="border-bottom pb-3 mb-3">

                    <div class="form-check">

                        <input type="checkbox"
                               class="form-check-input"
                               id="selectAllProducts">

                        <label class="form-check-label fw-semibold"
                               for="selectAllProducts">
                            Chọn tất cả sản phẩm
                        </label>

                    </div>

                </div>


                {{-- DANH SÁCH --}}
                <div id="productList">

                    @forelse($sanPhams as $sanPham)

                        <div class="product-item border rounded p-3 mb-2">

                            {{-- SẢN PHẨM CHA --}}
                            <div class="form-check">

                                <input type="checkbox"
                                       class="form-check-input product-checkbox"
                                       name="id_san_phams[]"
                                       value="{{ $sanPham->id }}"
                                       form="promotionForm"
                                       data-name="{{ $sanPham->ten_san_pham }}"
                                       id="product_{{ $sanPham->id }}">

                                <label class="form-check-label fw-bold"
                                       for="product_{{ $sanPham->id }}">

                                    <i class="fas fa-box text-primary me-1"></i>

                                    {{ $sanPham->ten_san_pham }}

                                    <small class="text-muted">
                                        (Tất cả biến thể)
                                    </small>

                                </label>

                            </div>


                            {{-- BIẾN THỂ --}}
                            @if(isset($bienThes[$sanPham->id]))

                                <div class="ms-4 mt-2">

                                    @foreach($bienThes[$sanPham->id] as $bienThe)

                                        <div class="form-check py-1">

                                            <input type="checkbox"
                                                   class="form-check-input variant-checkbox"
                                                   name="id_bien_thes[]"
                                                   value="{{ $bienThe->id }}"
                                                   form="promotionForm"
                                                   data-product="{{ $sanPham->id }}"
                                                   data-name="{{ $sanPham->ten_san_pham }} - {{ $bienThe->ten_bien_the }}"
                                                   id="variant_{{ $bienThe->id }}">

                                            <label class="form-check-label w-100"
                                                   for="variant_{{ $bienThe->id }}">

                                                {{ $bienThe->ten_bien_the }}

                                                @if($bienThe->ma_hang)

                                                    <span class="text-muted">
                                                        - {{ $bienThe->ma_hang }}
                                                    </span>

                                                @endif


                                                <span class="float-end text-success">

                                                    {{ number_format(
                                                        $bienThe->gia_ban ?? 0,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}đ

                                                </span>

                                            </label>

                                        </div>

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    @empty

                        <div class="text-center text-muted py-4">
                            Không có sản phẩm.
                        </div>

                    @endforelse

                </div>

            </div>


            <div class="modal-footer">

                <span class="me-auto text-muted"
                      id="selectedCount">
                    0 mục đã chọn
                </span>

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Đóng
                </button>

                <button type="button"
                        class="btn btn-primary"
                        id="confirmProducts"
                        data-bs-dismiss="modal">

                    Xác nhận

                </button>

            </div>

        </div>

    </div>

</div>

@endsection
@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const scope =
        document.getElementById('promotionScope');

    const productBox =
        document.getElementById('productScopeBox');

    const search =
        document.getElementById('productSearch');

    const selectAll =
        document.getElementById('selectAllProducts');

    const selectedCount =
        document.getElementById('selectedCount');

    const selectedText =
        document.getElementById('selectedText');

    const selectedList =
        document.getElementById('selectedProductList');


    // ========================================
    // 1. HIỆN / ẨN CHỌN SẢN PHẨM
    // ========================================

    function changeScope() {

        if (scope.value === 'san_pham') {
            productBox.style.display = 'block';
        } else {
            productBox.style.display = 'none';
        }

    }

    scope.addEventListener('change', changeScope);

    changeScope();


    // ========================================
    // 2. TÌM SẢN PHẨM
    // ========================================

    search.addEventListener('input', function () {

        const keyword =
            this.value.trim().toLowerCase();

        document
            .querySelectorAll('.product-item')
            .forEach(item => {

                const text =
                    item.innerText.toLowerCase();

                item.style.display =
                    text.includes(keyword)
                        ? ''
                        : 'none';

            });

    });


    // ========================================
    // 3. CHỌN SẢN PHẨM CHA
    // ========================================

    document
        .querySelectorAll('.product-checkbox')
        .forEach(product => {

            product.addEventListener('change', function () {

                const productId = this.value;

                const variants =
                    document.querySelectorAll(
                        `.variant-checkbox[data-product="${productId}"]`
                    );

                if (this.checked) {

                    variants.forEach(variant => {
                        variant.checked = false;
                        variant.disabled = true;
                    });

                } else {

                    variants.forEach(variant => {
                        variant.disabled = false;
                    });

                }

                updateCount();

            });

        });


    // ========================================
    // 4. CHỌN BIẾN THỂ
    // ========================================

    document
        .querySelectorAll('.variant-checkbox')
        .forEach(variant => {

            variant.addEventListener('change', function () {

                const productId =
                    this.dataset.product;

                if (this.checked) {

                    const parent =
                        document.getElementById(
                            'product_' + productId
                        );

                    if (parent) {
                        parent.checked = false;
                    }

                }

                updateCount();

            });

        });


    // ========================================
    // 5. CHỌN TẤT CẢ
    // ========================================

    selectAll.addEventListener('change', function () {

        const checked = this.checked;

        document
            .querySelectorAll('.product-checkbox')
            .forEach(product => {

                product.checked = checked;

            });


        document
            .querySelectorAll('.variant-checkbox')
            .forEach(variant => {

                variant.checked = false;
                variant.disabled = checked;

            });

        updateCount();

    });


    // ========================================
    // 6. ĐẾM SỐ ĐÃ CHỌN
    // ========================================

    function updateCount() {

        const products =
            document.querySelectorAll(
                '.product-checkbox:checked'
            );

        const variants =
            document.querySelectorAll(
                '.variant-checkbox:checked'
            );

        const total =
            products.length + variants.length;

        selectedCount.textContent =
            total + ' mục đã chọn';

    }


    // ========================================
    // 7. XÁC NHẬN
    // ========================================

    document
        .getElementById('confirmProducts')
        .addEventListener('click', function () {

            const products =
                Array.from(
                    document.querySelectorAll(
                        '.product-checkbox:checked'
                    )
                );

            const variants =
                Array.from(
                    document.querySelectorAll(
                        '.variant-checkbox:checked'
                    )
                );


            const total =
                products.length +
                variants.length;


            if (total === 0) {

                selectedText.textContent =
                    'Chưa chọn sản phẩm';

                selectedList.innerHTML = '';

                return;
            }


            selectedText.textContent =
                `Đã chọn ${total} mục`;


            let html = `
                <div class="d-flex flex-wrap gap-2">
            `;


            products.forEach(item => {

                html += `
                    <span class="badge bg-primary">
                        ${item.dataset.name}
                        (Tất cả biến thể)
                    </span>
                `;

            });


            variants.forEach(item => {

                html += `
                    <span class="badge bg-success">
                        ${item.dataset.name}
                    </span>
                `;

            });


            html += '</div>';

            selectedList.innerHTML = html;

        });

});
</script>

@endsection