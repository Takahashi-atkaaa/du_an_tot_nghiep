@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa sản phẩm - SmartMart')

@section('content')
@php
    $isParent = $isParent ?? (bool) $sanPham->la_san_pham_cha;
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            @if($isParent)
                Sửa sản phẩm cha
            @else
                Sửa biến thể sản phẩm
            @endif
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/san-pham') }}">Sản phẩm</a></li>
                @if($isParent)
                    <li class="breadcrumb-item active">Sửa: {{ $sanPham->ten_san_pham }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ url('admin/san-pham/'.$sanPham->sanPhamCha?->id.'/edit') }}">
                            {{ $sanPham->sanPhamCha?->ten_san_pham ?? 'Sản phẩm cha' }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Biến thể</li>
                @endif
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

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-info-circle me-1"></i>
            @if($isParent) Thông tin sản phẩm cha @else Thông tin biến thể @endif
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ url('admin/san-pham/'.$sanPham->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf
            @method('PUT')

            {{-- Thông báo nếu là biến thể --}}
            @if(!$isParent)
                <div class="alert alert-info mb-3">
                    <i class="fas fa-link me-1"></i>
                    Biến thể của <strong>{{ $sanPham->sanPhamCha?->ten_san_pham ?? 'sản phẩm cha' }}</strong>
                </div>
            @endif

            {{-- === THÔNG TIN CHUNG (cả cha lẫn biến thể) === --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mã sản phẩm</label>
                    <input type="text" class="form-control" value="{{ $sanPham->ma_hang }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Barcode</label>
                    <input type="text" name="ma_vach" class="form-control"
                           value="{{ old('ma_vach', $sanPham->ma_vach) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" name="ten_san_pham" class="form-control"
                           value="{{ old('ten_san_pham', $sanPham->ten_san_pham) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                    <select class="form-select" name="id_danh_muc" required>
                        <option value="" disabled>Chọn danh mục</option>
                        @foreach($danhMucs as $danhMuc)
                            <option value="{{ $danhMuc->id }}"
                                {{ (old('id_danh_muc', $sanPham->id_danh_muc) == $danhMuc->id) ? 'selected' : '' }}>
                                {{ $danhMuc->ten_danh_muc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Thương hiệu</label>
                    <input type="text" name="thuong_hieu" class="form-control"
                           value="{{ old('thuong_hieu', $sanPham->thuong_hieu) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Đơn vị cơ bản <span class="text-danger">*</span></label>
                    <input type="text" name="don_vi_text" class="form-control"
                           value="{{ old('don_vi_text', $sanPham->donVi->ten_don_vi ?? 'Cái') }}" required>
                </div>

                {{-- Thuộc tính (chỉ cho biến thể) --}}
                @if(!$isParent)
                <div class="col-md-6">
                    <label class="form-label">Thuộc tính</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        @php
                            $selectedAttrIds = $sanPham->thuocTinhs->pluck('id')->toArray();
                        @endphp
                        @forelse($thuocTinhs as $thuocTinh)
                            <div class="mb-2">
                                <strong class="small text-muted">{{ $thuocTinh->ten_thuoc_tinh }}</strong>
                                @foreach($thuocTinh->thuocTinhCons as $child)
                                    <div class="form-check">
                                        <input class="form-check-input attr-check" type="checkbox"
                                               name="thuoc_tinh_ids[]"
                                               value="{{ $child->id }}"
                                               data-ids="{{ $child->id }}"
                                               id="attr_{{ $child->id }}"
                                               {{ in_array($child->id, $selectedAttrIds) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="attr_{{ $child->id }}">
                                            {{ $child->ten_thuoc_tinh }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Chưa có thuộc tính nào.</p>
                        @endforelse
                    </div>
                    <input type="hidden" name="thuoc_tinh_ids" id="thuoc_tinh_ids" value="{{ implode(',', $selectedAttrIds) }}">
                </div>
                @endif

                {{-- Giá vốn --}}
                <div class="col-md-4">
                    <label class="form-label">Giá vốn</label>
                    <div class="input-group">
                        <input type="number" name="gia_von" class="form-control"
                               value="{{ old('gia_von', $sanPham->gia_von) }}" min="0">
                        <span class="input-group-text">đ</span>
                    </div>
                </div>

                {{-- Giá bán (ẩn nếu là sản phẩm cha) --}}
                @if($isParent)
                <div class="col-md-4">
                    <label class="form-label">Giá bán</label>
                    <div class="input-group">
                        <input type="number" class="form-control bg-light" value="0" readonly disabled>
                        <span class="input-group-text">đ</span>
                    </div>
                    <div class="form-text">Giá bán được set theo từng biến thể bên dưới.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tồn kho</label>
                    <input type="number" class="form-control bg-light" value="0" readonly disabled>
                    <div class="form-text">Tồn kho là tổng các biến thể.</div>
                </div>
                @else
                <div class="col-md-4">
                    <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="gia_ban" class="form-control"
                               value="{{ old('gia_ban', $sanPham->gia_ban) }}" min="0" required>
                        <span class="input-group-text">đ</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tồn kho</label>
                    <input type="number" name="so_luong_ton_kho" class="form-control"
                           value="{{ old('so_luong_ton_kho', $sanPham->so_luong_ton_kho) }}" min="0">
                </div>
                @endif

                <div class="col-md-4">
                    <label class="form-label">Định mức tối thiểu</label>
                    <input type="number" name="dinh_muc_toi_thieu" class="form-control"
                           value="{{ old('dinh_muc_toi_thieu', $sanPham->dinh_muc_toi_thieu ?? 0) }}" min="0">
                </div>

                <div class="col-12">
                    <label class="form-label">Mô tả</label>
                    <textarea class="form-control" name="mo_ta" rows="3">{{ old('mo_ta', $sanPham->mo_ta) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Hình ảnh</label>
                    <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                    @if($sanPham->hinh_anh)
                        <div class="mt-2">
                            <img src="{{ asset($sanPham->hinh_anh) }}" alt="Hình sản phẩm"
                                 style="max-width: 180px; height: auto;" class="rounded shadow-sm">
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="trang_thai" value="1" id="trang_thai"
                               {{ ($sanPham->trang_thai ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="trang_thai">Đang bán</label>
                    </div>
                </div>
            </div>

            {{-- === BẢNG QUẢN LÝ BIẾN THỂ (chỉ sản phẩm cha) === --}}
            @if($isParent)
            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-layer-group me-1"></i> Quản lý biến thể</h5>
                <button type="button" class="btn btn-success btn-sm" onclick="addVariantRow()">
                    <i class="fas fa-plus me-1"></i> Thêm biến thể
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="variantTable">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width:35%">Tên biến thể</th>
                            <th style="width:20%">Thuộc tính</th>
                            <th style="width:12%">Barcode</th>
                            <th style="width:12%">Giá bán (đ)</th>
                            <th style="width:10%">Tồn kho</th>
                            <th style="width:11%">Hình ảnh</th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                    <tbody id="variantTableBody">
                        @foreach($sanPham->bienThe as $index => $bienThe)
                        <tr class="variant-row" data-row="{{ $index }}">
                            <input type="hidden" name="bien_the[{{ $index }}][id]" value="{{ $bienThe->id }}">
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                       name="bien_the[{{ $index }}][ten_day_du]"
                                       value="{{ $bienThe->ten_san_pham }}"
                                       placeholder="Tên đầy đủ (tùy chọn)">
                                <input type="hidden" name="bien_the[{{ $index }}][ten_bien_the]" value="1">
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" id="attr-badges-{{ $index }}">
                                    @foreach($bienThe->thuocTinhs as $tt)
                                        <span class="badge bg-primary" data-id="{{ $tt->id }}">
                                            {{ $tt->ten_thuoc_tinh }}
                                            <button type="button" class="btn-close btn-close-white ms-1" style="font-size:0.5rem;"
                                                    onclick="removeAttrBadge(this, {{ $index }})"></button>
                                        </span>
                                    @endforeach
                                </div>
                                <input type="hidden" name="bien_the[{{ $index }}][thuoc_tinh_ids]"
                                       id="attr_ids_{{ $index }}"
                                       value="{{ implode(',', $bienThe->thuocTinhs->pluck('id')->toArray()) }}">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-1"
                                        onclick="openAttrModal({{ $index }})">
                                    <i class="fas fa-plus me-1"></i> Chọn thuộc tính
                                </button>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                       name="bien_the[{{ $index }}][ma_vach]"
                                       value="{{ $bienThe->ma_vach ?? '' }}"
                                       placeholder="Barcode">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm"
                                       name="bien_the[{{ $index }}][gia_ban]"
                                       value="{{ $bienThe->gia_ban }}" min="0" placeholder="0">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm"
                                       name="bien_the[{{ $index }}][so_luong]"
                                       value="{{ $bienThe->so_luong_ton_kho }}" min="0" placeholder="0">
                            </td>
                            <td>
                                @if($bienThe->hinh_anh)
                                    <img src="{{ asset($bienThe->hinh_anh) }}" class="rounded mb-1"
                                         style="width:50px;height:50px;object-fit:cover;">
                                @endif
                                <input type="file" class="form-control form-control-sm"
                                       name="bien_the[{{ $index }}][hinh_anh]" accept="image/*">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="removeVariantRow(this, {{ $bienThe->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($sanPham->bienThe->isEmpty())
                    <p class="text-center text-muted py-3">Chưa có biến thể nào. Nhấn "Thêm biến thể" để tạo.</p>
                @endif
            </div>
            @endif

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ url('admin/san-pham') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal chọn thuộc tính --}}
<div class="modal fade" id="attrModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn thuộc tính</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @foreach($thuocTinhs as $tt)
                    <div class="mb-3">
                        <strong class="small">{{ $tt->ten_thuoc_tinh }}</strong>
                        <div class="mt-1">
                            @foreach($tt->thuocTinhCons as $child)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input attr-option" type="checkbox"
                                           id="modal_attr_{{ $child->id }}"
                                           value="{{ $child->id }}">
                                    <label class="form-check-label small" for="modal_attr_{{ $child->id }}">
                                        {{ $child->ten_thuoc_tinh }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="applyAttrs()">Áp dụng</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
let variantCounter = {{ $sanPham->bienThe->count() }};
let currentVariantRow = null;

function addVariantRow() {
    const idx = variantCounter++;
    const tr = document.createElement('tr');
    tr.className = 'variant-row';
    tr.dataset.row = idx;
    tr.innerHTML = `
        <input type="hidden" name="bien_the[${idx}][id]" value="">
        <td>
            <input type="text" class="form-control form-control-sm"
                   name="bien_the[${idx}][ten_day_du]" placeholder="Tên biến thể đầy đủ">
            <input type="hidden" name="bien_the[${idx}][ten_bien_the]" value="1">
        </td>
        <td>
            <div class="d-flex flex-wrap gap-1" id="attr-badges-${idx}"></div>
            <input type="hidden" name="bien_the[${idx}][thuoc_tinh_ids]" id="attr_ids_${idx}" value="">
            <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-1"
                    onclick="openAttrModal(${idx})">
                <i class="fas fa-plus me-1"></i> Chọn thuộc tính
            </button>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm"
                   name="bien_the[${idx}][ma_vach]" placeholder="Barcode">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm"
                   name="bien_the[${idx}][gia_ban]" value="0" min="0" placeholder="0">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm"
                   name="bien_the[${idx}][so_luong]" value="0" min="0" placeholder="0">
        </td>
        <td>
            <input type="file" class="form-control form-control-sm"
                   name="bien_the[${idx}][hinh_anh]" accept="image/*">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm"
                    onclick="removeVariantRow(this, null)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    document.getElementById('variantTableBody').appendChild(tr);

    // Xóa thông báo "chưa có biến thể" nếu có
    const emptyMsg = document.querySelector('#variantTableBody + p');
    if (emptyMsg) emptyMsg.remove();
}

function removeVariantRow(btn, dbId) {
    if (dbId) {
        // Đánh dấu xóa: tạo input hidden
        const form = document.getElementById('productForm');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'deleted_variant_ids[]';
        input.value = dbId;
        form.appendChild(input);
    }
    btn.closest('tr').remove();
}

function removeAttrBadge(btn, rowIdx) {
    const badge = btn.closest('span');
    const id = badge.dataset.id;
    badge.remove();

    // Cập nhật hidden input
    const hidden = document.getElementById('attr_ids_' + rowIdx);
    let ids = hidden.value ? hidden.value.split(',').filter(i => i != id) : [];
    hidden.value = ids.join(',');

    // Uncheck modal checkbox nếu đang mở
    const modalCheck = document.getElementById('modal_attr_' + id);
    if (modalCheck) modalCheck.checked = false;
}

function openAttrModal(rowIdx) {
    currentVariantRow = rowIdx;
    const hidden = document.getElementById('attr_ids_' + rowIdx);
    const currentIds = hidden.value ? hidden.value.split(',').filter(i => i) : [];

    // Check các checkbox đang active
    document.querySelectorAll('.attr-option').forEach(cb => {
        cb.checked = currentIds.includes(cb.value);
    });

    const modal = new bootstrap.Modal(document.getElementById('attrModal'));
    modal.show();
}

function applyAttrs() {
    if (currentVariantRow === null) return;

    const checked = Array.from(document.querySelectorAll('.attr-option:checked'));
    const ids = checked.map(cb => cb.value);
    const labels = checked.map(cb => cb.nextElementSibling.textContent.trim());

    // Cập nhật hidden input
    document.getElementById('attr_ids_' + currentVariantRow).value = ids.join(',');

    // Cập nhật badges
    const badgeDiv = document.getElementById('attr-badges-' + currentVariantRow);
    badgeDiv.innerHTML = '';
    checked.forEach(cb => {
        const label = cb.nextElementSibling.textContent.trim();
        const id = cb.value;
        const span = document.createElement('span');
        span.className = 'badge bg-primary';
        span.dataset.id = id;
        span.innerHTML = label + ' <button type="button" class="btn-close btn-close-white ms-1" style="font-size:0.5rem;" onclick="removeAttrBadge(this, ' + currentVariantRow + ')"></button>';
        badgeDiv.appendChild(span);
    });

    bootstrap.Modal.getInstance(document.getElementById('attrModal')).hide();
}

// Cập nhật hidden khi click checkbox trực tiếp trên tr (dành cho variant row đang sửa)
document.querySelectorAll('.attr-check').forEach(cb => {
    cb.addEventListener('change', function() {
        const allChecked = Array.from(document.querySelectorAll('.attr-check:checked')).map(c => c.value);
        document.getElementById('thuoc_tinh_ids').value = allChecked.join(',');
    });
});
</script>
@endsection
