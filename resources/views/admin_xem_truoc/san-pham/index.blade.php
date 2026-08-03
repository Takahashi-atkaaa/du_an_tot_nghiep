@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Sản phẩm</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importProductModal">
            <i class="fas fa-file-import me-2"></i>Import
        </button>
        <button class="btn btn-outline-success" id="btnExportExcel">
            <i class="fas fa-file-export me-2"></i>Export
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-2"></i>Thêm sản phẩm
        </button>
        <button class="btn btn-outline-secondary" id="startQrScanBtn">
            <i class="fas fa-barcode me-2"></i>Quét mã vạch
        </button>
        <a href="{{ url('admin/san-pham/trash') }}" class="btn btn-outline-danger">
            <i class="fas fa-trash me-2"></i>Thùng rác
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card table-admin mb-4">
    <div class="card-body">
        <form id="searchProductForm" action="{{ url('admin/san-pham') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchKeywordInput" class="form-control" name="keyword" value="{{ $keyword ?? '' }}" placeholder="Tìm kiếm sản phẩm...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="danh_muc">
                        <option value="">Tất cả danh mục</option>
                        @foreach($danhMucs as $danhMuc)
                            <option value="{{ $danhMuc->id }}" {{ (string)$danhMuc->id === (string)($danhMucId ?? '') ? 'selected' : '' }}>{{ $danhMuc->ten_danh_muc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="trang_thai">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ $trangThai === '1' || $trangThai === 1 ? 'selected' : '' }}>Đang bán</option>
                        <option value="0" {{ $trangThai === '0' || $trangThai === 0 ? 'selected' : '' }}>Ngừng bán</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quét mã vạch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrScanner" style="width:100%; min-height:300px; background:#000; border-radius:6px; position:relative;">
                    <div id="qrScannerLoading" class="text-center text-white py-5" style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                        <div class="spinner-border text-light mb-2" role="status"></div>
                        <div>Đang khởi động camera...</div>
                    </div>
                </div>
                <div id="qrScannerError" class="alert alert-danger mt-2 d-none" role="alert"></div>
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-secondary" id="stopQrScanBtn">Dừng quét</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="bulkActionForm" action="{{ url('admin/san-pham/bulk-action') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <div id="selectedIdsContainer"></div>
</form>

{{-- ============================================================
DROPDOWN ĐƠN VỊ QUY ĐỔI - 1 instance duy nhất, fixed position
Tuyệt đối KHÔNG nằm trong bảng để không phá vỡ layout
============================================================ --}}
<div id="unitConversionDropdown" class="unit-conversion-popover" style="display:none;">
    <div class="unit-popover-header">
        <i class="fas fa-balance-scale me-1"></i>
        <span>Đơn vị quy đổi</span>
    </div>
    <ul id="unitConversionList" class="unit-popover-list"></ul>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                    <label class="form-check-label text-muted" for="selectAllCheckbox">Chọn tất cả</label>
                </div>
                <div id="bulkActionButtons" class="d-none">
                    <span class="text-muted me-2" id="selectedCount">0 đã chọn</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="submitBulkAction('activate')"><i class="fas fa-check me-1"></i>Bật</button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="submitBulkAction('deactivate')"><i class="fas fa-ban me-1"></i>Tắt</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="submitBulkAction('delete')"><i class="fas fa-trash me-1"></i>Xóa</button>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Tổng: <strong id="totalProducts">{{ $sanPhams->total() }}</strong> sản phẩm</span>
            </div>
        </div>

        @if($sanPhams->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th style="width:60px;">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th style="width:130px;">Danh mục</th>
                        <th style="width:160px;">Đơn vị</th>
                        <th style="width:140px;">Biến thể</th>
                        <th style="width:140px;">Giá bán</th>
                        <th style="width:90px;">Tồn kho</th>
                        <th style="width:110px;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($sanPhams as $sp)
                        @php
                            // Danh sách biến thể (mỗi variant = 1 dòng con trong ERP)
                            $variants = $sp->variants;
                            $variantCount = $variants->count();
                            $hasManyVariants = ($variantCount > 1);

                            // Tổng tồn kho của tất cả biến thể
                            $tongTonKho = $variants->sum(function($v) { return (int)($v->so_luong_ton ?? 0); });

                            // Khoảng giá bán (min-max) giữa các biến thể
                            $allPrices = $variants->map(function($v) { return (float)($v->gia_ban ?? 0); })->all();
                            $minPrice = !empty($allPrices) ? min($allPrices) : 0;
                            $maxPrice = !empty($allPrices) ? max($allPrices) : 0;

                            // Trạng thái tổng: dựa vào tổng tồn kho + trạng thái sp
                            $trangThaiSP = $sp->trang_thai ?? true;
                            $firstVariant = $variants->first();
                        @endphp

                        @if($variantCount > 0)
                        {{-- ============================================================
                        DÒNG CHA (PARENT ROW) - DUY NHẤT 1 dòng cho mỗi sản phẩm
                        Hiển thị thông tin tổng: tên SP, danh mục, tổng tồn kho, khoảng giá
                        Cột Đơn vị: để trống (-) nếu có nhiều biến thể,
                                     hoặc tên ĐVCB + Badge trigger Dropdown nếu chỉ có 1
                        ============================================================ --}}
                        <tr class="product-parent-row {{ !$trangThaiSP ? 'table-secondary opacity-50' : '' }}"
                            style="cursor:pointer;"
                            data-id="{{ $sp->id }}"
                            data-variant-id="{{ $firstVariant?->id ?? '' }}"
                            data-unit-id=""
                            data-product-id="{{ $sp->id }}"
                            data-target-id="{{ $sp->id }}"
                            data-row-type="goc"
                            data-base-donvi="{{ $firstVariant?->ten_don_vi ?? '' }}"
                            data-base-gia="{{ $firstVariant?->gia_ban ?? 0 }}"
                            data-base-giavon="{{ $firstVariant?->gia_von ?? 0 }}"
                            data-base-tonkho="{{ $firstVariant?->so_luong_ton ?? 0 }}"
                            data-base-mahang="{{ $firstVariant?->ma_hang ?? '' }}"
                            data-base-mavach="{{ $firstVariant?->ma_vach ?? '' }}"
                            data-base-trangthai="{{ $trangThaiSP ? '1' : '0' }}"
                            data-base-dinhmuc="{{ $firstVariant?->dinh_muc_toi_thieu ?? 0 }}">

                            {{-- Toggle expand --}}
                            <td onclick="event.stopPropagation();">
                                <div class="d-flex align-items-center gap-1">
                                    @if($hasManyVariants)
                                        <button class="btn btn-sm btn-light p-0 border-0 expand-btn"
                                                id="expandBtn{{ $sp->id }}"
                                                onclick="event.stopPropagation(); window.toggleVariants && window.toggleVariants({{ $sp->id }})"
                                                title="Mở rộng">
                                            <i class="fas fa-chevron-down" style="font-size:0.7rem; transition:transform 0.2s;"></i>
                                        </button>
                                    @endif
                                    <input type="checkbox" class="form-check-input product-checkbox"
                                           value="{{ $sp->id }}"
                                           data-product-id="{{ $sp->id }}"
                                           data-type="goc"
                                           onclick="event.stopPropagation();">
                                </div>
                            </td>

                            {{-- Hình ảnh (lấy ảnh variant đầu) --}}
                            <td>
                                @php $firstImg = $firstVariant?->hinh_anh ?? $sp->hinh_anh; @endphp
                                @if(!empty($firstImg))
                                    @php
                                        $firstImgSrc = (str_starts_with($firstImg, 'http://') || str_starts_with($firstImg, 'https://'))
                                            ? $firstImg
                                            : (str_starts_with($firstImg, '/') ? $firstImg : asset($firstImg));
                                    @endphp
                                    <img src="{{ $firstImgSrc }}" alt="{{ $sp->ten_san_pham }}"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                @else
                                    <div style="width:48px;height:48px;border-radius:6px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            {{-- Tên sản phẩm + Thương hiệu --}}
                            <td>
                                <div>
                                    <span class="fw-semibold" style="font-size:0.9rem;">{{ $sp->ten_san_pham }}</span>
                                    @if(!empty($sp->thuong_hieu))
                                        <div class="small text-muted">{{ $sp->thuong_hieu }}</div>
                                    @endif
                                    @if($hasManyVariants)
                                        <div class="mt-1">
                                            <span class="badge bg-light text-dark border" style="font-size:0.68rem;">
                                                <i class="fas fa-layer-group me-1"></i>{{ $variantCount }} biến thể
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Danh mục --}}
                            <td>
                                <span class="text-muted small">{{ $sp->danhMuc?->ten_danh_muc ?? '-' }}</span>
                            </td>

                            {{-- Đơn vị --}}
                            <td>
                                @if($hasManyVariants)
                                    {{-- Nhiều biến thể: ĐỂ TRỐNG dấu "-" --}}
                                    <span class="text-muted small">—</span>
                                @else
                                    {{-- Chỉ có 1 biến thể: hiển thị tên đơn vị cơ bản + Badge trigger Dropdown --}}
                                    @php
                                        $onlyVariant = $firstVariant;
                                        $laDonVi = $onlyVariant?->la_don_vi ?? false;
                                        $tenDonViCoBan = $laDonVi ? ($onlyVariant?->ten_don_vi ?? '') : ($onlyVariant?->ten_bien_the ?? '');
                                        $units = $onlyVariant?->units ?? collect();
                                        $unitsCount = $units->count();
                                        $unitsJson = $units->map(function($u) {
                                            return [
                                                'id' => $u->id,
                                                'ten_don_vi' => $u->ten_don_vi,
                                                'ty_le' => (int)($u->so_luong_san_pham_trong_don_vi ?: 1),
                                                'ty_le_quy_doi' => (int)($u->so_luong_san_pham_trong_don_vi ?: 1),
                                                'gia_ban' => (float)($u->gia_ban_quy_doi ?? 0),
                                                'gia_ban_quy_doi' => (float)($u->gia_ban_quy_doi ?? 0),
                                                'gia_von' => (float)($u->gia_von_quy_doi ?? 0),
                                                'gia_von_quy_doi' => (float)($u->gia_von_quy_doi ?? 0),
                                                'ma_hang' => $u->ma_hang ?? '',
                                                'ma_vach' => $u->ma_vach ?? '',
                                                'hinh_anh' => $u->hinh_anh ?? '',
                                            ];
                                        })->values()->toJson();
                                    @endphp
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="small fw-medium js-donvi">{{ $tenDonViCoBan ?: '—' }}</span>
                                        @if($unitsCount > 0)
                                            <div class="unit-dropdown-container position-relative d-inline-block">
                                                <button type="button"
                                                        class="unit-dropdown-toggle badge bg-info text-white border-0"
                                                        style="font-size:0.65rem; cursor:pointer;"
                                                        data-product-id="{{ $sp->id }}"
                                                        data-variant-id="{{ $onlyVariant->id }}"
                                                        data-units="{{ $unitsJson }}"
                                                        data-row-selector="product-parent-row"
                                                        onclick="event.stopPropagation(); window.toggleUnitDropdown(this);">
                                                    +{{ $unitsCount }} ĐVT <i class="fas fa-caret-down ms-1"></i>
                                                </button>
                                                <ul class="unit-dropdown-menu unit-popover-list" style="display:none; position:absolute; top:100%; left:0; z-index:1055; min-width:220px; max-width:280px; background:#fff; border:1px solid #dee2e6; border-radius:8px; box-shadow:0 4px 18px rgba(0,0,0,0.15); padding:4px 0; margin:0; list-style:none;">
                                                    <li class="is-base" style="padding:8px 12px; cursor:pointer; background:#e7f5ff; font-weight:600; border-bottom:1px solid #f1f3f5;" onclick="event.stopPropagation(); window.selectBaseUnit(this);">
                                                        <span class="unit-name">Đơn vị cơ bản</span>
                                                        <span class="unit-ratio" style="font-size:0.7rem; padding:2px 7px; border-radius:10px; background:#0d6efd; color:#fff; font-weight:600; margin-left:8px;">×1</span>
                                                    </li>
                                                    @foreach($units as $u)
                                                        @php
                                                            $unitObj = [
                                                                'id' => $u->id,
                                                                'ten_don_vi' => $u->ten_don_vi,
                                                                'ty_le' => (int)($u->so_luong_san_pham_trong_don_vi ?: 1),
                                                                'ty_le_quy_doi' => (int)($u->so_luong_san_pham_trong_don_vi ?: 1),
                                                                'gia_ban' => (float)($u->gia_ban_quy_doi ?? 0),
                                                                'gia_ban_quy_doi' => (float)($u->gia_ban_quy_doi ?? 0),
                                                                'gia_von' => (float)($u->gia_von_quy_doi ?? 0),
                                                                'gia_von_quy_doi' => (float)($u->gia_von_quy_doi ?? 0),
                                                                'ma_hang' => $u->ma_hang ?? '',
                                                                'ma_vach' => $u->ma_vach ?? '',
                                                                'hinh_anh' => $u->hinh_anh ?? '',
                                                            ];
                                                        @endphp
                                                        <li data-unit-obj='{{ json_encode($unitObj, JSON_UNESCAPED_UNICODE) }}'
                                                            style="padding:8px 12px; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:8px; border-bottom:1px solid #f1f3f5;"
                                                            onclick="event.stopPropagation(); window.selectUnitFromDropdown(this);">
                                                            <span class="unit-name">{{ $u->ten_don_vi }}</span>
                                                            <span class="unit-ratio" style="font-size:0.7rem; padding:2px 7px; border-radius:10px; background:#e9ecef; color:#495057; font-weight:600;">×{{ (int)($u->so_luong_san_pham_trong_don_vi ?: 1) }}</span>
                                                            <span class="unit-price" style="font-size:0.72rem; color:#6c757d; margin-left:auto;">{{ number_format((float)($u->gia_ban_quy_doi ?? 0), 0, ',', '.') }} d</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Biến thể (cột tóm tắt cho dòng cha) --}}
                            <td>
                                @if($hasManyVariants)
                                    <span class="text-muted small">{{ $variantCount }} loại</span>
                                @else
                                    @php
                                        $fv = $firstVariant;
                                        $fvLaDonVi = $fv?->la_don_vi ?? false;
                                    @endphp
                                    @if(!$fvLaDonVi && !empty($fv?->ten_bien_the))
                                        <span class="small">{{ $fv->ten_bien_the }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                @endif
                            </td>

                            {{-- Giá bán (khoảng min-max nếu nhiều biến thể) --}}
                            <td>
                                @if($variantCount > 0)
                                    @if($hasManyVariants)
                                        @if($minPrice == $maxPrice)
                                            <span class="fw-bold text-primary" style="font-size:0.88rem;">
                                                {{ number_format($minPrice, 0, ',', '.') }} d
                                            </span>
                                        @else
                                            <span class="fw-bold text-primary" style="font-size:0.88rem;">
                                                {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }} d
                                            </span>
                                        @endif
                                    @else
                                        <span class="fw-bold text-primary js-giaban" style="font-size:0.88rem;">
                                            {{ number_format((float)($firstVariant?->gia_ban ?? 0), 0, ',', '.') }} d
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Tồn kho (tổng các variants) --}}
                            <td>
                                @if($hasManyVariants)
                                    <span class="{{ $tongTonKho <= 0 ? 'text-danger' : ($tongTonKho <= 10 ? 'text-warning' : 'text-muted') }} small fw-medium">
                                        {{ $tongTonKho }}
                                    </span>
                                @else
                                    <span class="js-tonkho {{ ($firstVariant?->so_luong_ton ?? 0) <= 0 ? 'text-danger' : (($firstVariant?->so_luong_ton ?? 0) <= 10 ? 'text-warning' : 'text-muted') }} small fw-medium">
                                        {{ $firstVariant?->so_luong_ton ?? 0 }}
                                    </span>
                                @endif
                            </td>

                            {{-- Trạng thái --}}
                            <td>
                                @if($hasManyVariants)
                                    @if(!$trangThaiSP)
                                        <span class="badge bg-danger">Ngừng bán</span>
                                    @elseif($tongTonKho <= 0)
                                        <span class="badge bg-secondary">Hết hàng</span>
                                    @elseif($tongTonKho <= 10)
                                        <span class="badge bg-warning text-dark">Sắp hết</span>
                                    @else
                                        <span class="badge bg-success">Còn hàng</span>
                                    @endif
                                @else
                                    <span class="js-trangthai">
                                        @if(!$trangThaiSP)
                                            <span class="badge bg-danger">Ngừng bán</span>
                                        @elseif(($firstVariant?->so_luong_ton ?? 0) <= 0)
                                            <span class="badge bg-secondary">Hết hàng</span>
                                        @elseif(($firstVariant?->so_luong_ton ?? 0) <= 10)
                                            <span class="badge bg-warning text-dark">Sắp hết</span>
                                        @else
                                            <span class="badge bg-success">Còn hàng</span>
                                        @endif
                                    </span>
                                @endif
                            </td>
                        </tr>

                        {{-- ============================================================
                        DÒNG CON (VARIANT CHILD ROW) - Mỗi variant 1 dòng
                        Chỉ hiển thị khi user click expand (toggleVariants)
                        Cột Đơn vị: tên đơn vị cơ bản + Badge trigger Dropdown đơn vị quy đổi
                        ============================================================ --}}
                        @foreach($variants as $vIndex => $variant)
                            @php
                                $laDonVi = $variant->la_don_vi ?? false;
                                $tenDonViCoBan = $laDonVi ? ($variant->ten_don_vi ?? '') : ($variant->ten_bien_the ?? '');
                                $units = $variant->units;
                                $unitsCount = $units->count();
                                $unitsJson = $units->map(function($u) {
                                    return [
                                        'id' => $u->id,
                                        'ten_don_vi' => $u->ten_don_vi,
                                        'ty_le' => (int)($u->so_luong_san_pham_trong_don_vi ?: 1),
                                        'ty_le_quy_doi' => (int)($u->so_luong_san_pham_trong_don_vi ?: 1),
                                        'gia_ban' => (float)($u->gia_ban_quy_doi ?? 0),
                                        'gia_ban_quy_doi' => (float)($u->gia_ban_quy_doi ?? 0),
                                        'gia_von' => (float)($u->gia_von_quy_doi ?? 0),
                                        'gia_von_quy_doi' => (float)($u->gia_von_quy_doi ?? 0),
                                        'ma_hang' => $u->ma_hang ?? '',
                                        'ma_vach' => $u->ma_vach ?? '',
                                        'hinh_anh' => $u->hinh_anh ?? '',
                                    ];
                                })->values()->toJson();
                                $tonKhoV = (int)($variant->so_luong_ton ?? 0);
                                $trangThaiV = $variant->trang_thai ?? true;
                            @endphp

                            <tr id="variantRow{{ $sp->id }}_{{ $vIndex }}"
                                class="variant-child-row"
                                style="display:none; background:#fafafa; cursor:pointer;"
                                data-id="{{ $variant->id }}"
                                data-variant-id="{{ $variant->id }}"
                                data-unit-id=""
                                data-target-id="{{ $variant->id }}"
                                data-type="goc"
                                data-row-type="goc"
                                data-product-id="{{ $sp->id }}"
                                data-base-donvi="{{ $laDonVi ? ($variant->ten_don_vi ?? '') : ($variant->ten_bien_the ?? '') }}"
                                data-base-gia="{{ $variant->gia_ban ?? 0 }}"
                                data-base-giavon="{{ $variant->gia_von ?? 0 }}"
                                data-base-tonkho="{{ $variant->so_luong_ton ?? 0 }}"
                                data-base-mahang="{{ $variant->ma_hang ?? '' }}"
                                data-base-mavach="{{ $variant->ma_vach ?? '' }}"
                                data-base-trangthai="{{ $trangThaiV ? '1' : '0' }}"
                                data-base-dinhmuc="{{ $variant->dinh_muc_toi_thieu ?? 0 }}">
                                <td></td>

                                {{-- Ảnh nhỏ --}}
                                <td>
                                    @if(!empty($variant->hinh_anh))
                                        @php
                                            $vImgSrc = (str_starts_with($variant->hinh_anh, 'http://') || str_starts_with($variant->hinh_anh, 'https://'))
                                                ? $variant->hinh_anh
                                                : (str_starts_with($variant->hinh_anh, '/') ? $variant->hinh_anh : asset($variant->hinh_anh));
                                        @endphp
                                        <img src="{{ $vImgSrc }}" alt=""
                                             style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                    @else
                                        <div style="width:40px;height:40px;border-radius:4px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-cube text-muted" style="font-size:0.8rem;"></i>
                                        </div>
                                    @endif
                                </td>

                                {{-- Tên biến thể --}}
                                <td style="padding-left: 2rem !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-arrow-turn-down-right text-muted" style="font-size:0.55rem;"></i>
                                        <span class="fw-semibold" style="font-size:0.85rem;">
                                            {{ $variant->ten_bien_the ?? $sp->ten_san_pham }}
                                        </span>
                                    </div>
                                    @if(!empty($variant->ma_vach))
                                        <div class="small text-muted js-mavach" style="padding-left: 1.1rem; font-size:0.7rem;">#{{ $variant->ma_vach }}</div>
                                    @else
                                        <div class="small text-muted js-mavach" style="padding-left: 1.1rem; font-size:0.7rem;">—</div>
                                    @endif
                                    @if(!empty($variant->ma_hang))
                                        <div class="small text-muted js-mahang" style="padding-left: 1.1rem; font-size:0.7rem;">MH: {{ $variant->ma_hang }}</div>
                                    @else
                                        <div class="small text-muted js-mahang" style="padding-left: 1.1rem; font-size:0.7rem;">MH: —</div>
                                    @endif
                                </td>

                                {{-- Danh mục (placeholder) --}}
                                <td><span class="text-muted small">—</span></td>

                                {{-- Đơn vị: tên đơn vị cơ bản + Badge trigger Dropdown --}}
                                <td>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="small fw-medium js-donvi">{{ $tenDonViCoBan ?: '—' }}</span>
                                        @if($unitsCount > 0)
                                            <div class="unit-dropdown-container position-relative d-inline-block">
                                                <button type="button"
                                                        class="unit-dropdown-toggle badge bg-info text-white border-0"
                                                        style="font-size:0.65rem; cursor:pointer;"
                                                        data-product-id="{{ $sp->id }}"
                                                        data-variant-id="{{ $variant->id }}"
                                                        data-units="{{ $unitsJson }}"
                                                        data-row-selector="variant-child-row"
                                                        onclick="event.stopPropagation(); window.toggleUnitDropdown(this);">
                                                    +{{ $unitsCount }} ĐVT <i class="fas fa-caret-down ms-1"></i>
                                                </button>
                                                <ul class="unit-dropdown-menu unit-popover-list" style="display:none; position:absolute; top:100%; left:0; z-index:1055; min-width:220px; max-width:280px; background:#fff; border:1px solid #dee2e6; border-radius:8px; box-shadow:0 4px 18px rgba(0,0,0,0.15); padding:4px 0; margin:0; list-style:none;">
                                                    <li class="is-base" style="padding:8px 12px; cursor:pointer; background:#e7f5ff; font-weight:600; border-bottom:1px solid #f1f3f5;" onclick="event.stopPropagation(); window.selectBaseUnit(this);">
                                                        <span class="unit-name">Đơn vị cơ bản</span>
                                                        <span class="unit-ratio" style="font-size:0.7rem; padding:2px 7px; border-radius:10px; background:#0d6efd; color:#fff; font-weight:600; margin-left:8px;">×1</span>
                                                    </li>
                                                    @foreach($units as $u)
                                                        @php
                                                            $unitObj = [
                                                                'id' => $u->id,
                                                                'ten_don_vi' => $u->ten_don_vi,
                                                                'ty_le' => (int)($u->so_luong_san_pham_trong_don_vi ?: 1),
                                                                'ty_le_quy_doi' => (int)($u->so_luong_san_pham_trong_don_vi ?: 1),
                                                                'gia_ban' => (float)($u->gia_ban_quy_doi ?? 0),
                                                                'gia_ban_quy_doi' => (float)($u->gia_ban_quy_doi ?? 0),
                                                                'gia_von' => (float)($u->gia_von_quy_doi ?? 0),
                                                                'gia_von_quy_doi' => (float)($u->gia_von_quy_doi ?? 0),
                                                                'ma_hang' => $u->ma_hang ?? '',
                                                                'ma_vach' => $u->ma_vach ?? '',
                                                                'hinh_anh' => $u->hinh_anh ?? '',
                                                            ];
                                                        @endphp
                                                        <li data-unit-obj='{{ json_encode($unitObj, JSON_UNESCAPED_UNICODE) }}'
                                                            style="padding:8px 12px; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:8px; border-bottom:1px solid #f1f3f5;"
                                                            onclick="event.stopPropagation(); window.selectUnitFromDropdown(this);">
                                                            <span class="unit-name">{{ $u->ten_don_vi }}</span>
                                                            <span class="unit-ratio" style="font-size:0.7rem; padding:2px 7px; border-radius:10px; background:#e9ecef; color:#495057; font-weight:600;">×{{ (int)($u->so_luong_san_pham_trong_don_vi ?: 1) }}</span>
                                                            <span class="unit-price" style="font-size:0.72rem; color:#6c757d; margin-left:auto;">{{ number_format((float)($u->gia_ban_quy_doi ?? 0), 0, ',', '.') }} d</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Biến thể --}}
                                <td>
                                    @if(!$laDonVi && !empty($variant->ten_bien_the))
                                        <span class="small">{{ $variant->ten_bien_the }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Giá bán (của variant này) --}}
                                <td>
                                    <span class="fw-bold text-primary js-giaban" style="font-size:0.85rem;">
                                        {{ number_format((float)$variant->gia_ban, 0, ',', '.') }} d
                                    </span>
                                </td>

                                {{-- Tồn kho (của variant này) --}}
                                <td>
                                    <span class="js-tonkho {{ $tonKhoV <= 0 ? 'text-danger' : ($tonKhoV <= 3 ? 'text-warning' : 'text-muted') }} small">
                                        {{ $tonKhoV }}
                                    </span>
                                </td>

                                {{-- Trạng thái --}}
                                <td>
                                    <span class="js-trangthai">
                                        @if(!$trangThaiV)
                                            <span class="badge bg-danger">Ngừng</span>
                                        @elseif($tonKhoV <= 0)
                                            <span class="badge bg-secondary">Hết</span>
                                        @elseif($tonKhoV <= 3)
                                            <span class="badge bg-warning text-dark">Sắp hết</span>
                                        @else
                                            <span class="badge bg-success">Còn</span>
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <p class="mb-0">Hiện chưa có sản phẩm nào.</p>
        </div>
        @endif
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">
                Hiển thị {{ $sanPhams->firstItem() ?? 0 }} - {{ $sanPhams->lastItem() ?? 0 }} trên {{ $sanPhams->total() }} sản phẩm
            </span>
            <nav>{{ $sanPhams->links('pagination::bootstrap-5') }}</nav>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="productDetailDrawer" style="width:680px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Chi tiết sản phẩm</h5>
        <div class="d-flex gap-2">
            <a href="#" id="drawerEditBtn" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Sửa</a>
            <button type="button" class="btn btn-sm btn-danger" id="drawerDeleteBtn" onclick="confirmDeleteFromDrawer()"><i class="fas fa-trash me-1"></i>Xóa</button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
    </div>
    <div class="offcanvas-body p-0" id="drawerBody" style="overflow-y:auto;">
        <div class="d-flex justify-content-center align-items-center" style="min-height:300px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted mb-0">Đang tải...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-fullheight">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0d6efd 0%,#0b5ed7 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-box-open me-2"></i>THÊM SẢN PHẨM</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('admin/san-pham') }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                <input type="file" name="hinh_anh" id="nativeHinhAnhInput" class="d-none" accept="image/*">
                <div class="modal-body p-0" id="addProductModalBody">
                    <div id="createProductApp" v-cloak></div>
                </div>
                <div class="modal-footer bg-light flex-column align-items-stretch">
                    <div id="formErrorBox" class="alert alert-danger py-2 px-3 mb-2 small d-none" role="alert" style="white-space:pre-line;"></div>
                    {{-- ============================================================
                    YÊU CẦU 2: CẢNH BÁO TRÙNG NHÓM THUỘC TÍNH (FRONTEND)
                    ============================================================ --}}
                    <div id="duplicateAttrGroupWarning" class="alert alert-danger py-2 px-3 mb-2 small d-none" role="alert" style="white-space:pre-line;">
                        <i class="fas fa-exclamation-circle me-1"></i>
                    </div>
                    {{-- ============================================================
                    YÊU CẦU 2: CẢNH BÁO TRÙNG LẶP BIẾN THỂ (FRONTEND)
                    ============================================================ --}}
                    <div id="duplicateVariantWarning" class="alert alert-warning py-2 px-3 mb-2 small d-none" role="alert" style="white-space:pre-line;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                    </div>
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Điền đầy đủ thông tin trước khi lưu</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="button" class="btn btn-success px-4" id="btnLuuSanPham">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="btnLuuSpinner" role="status"></span>
                                <i class="fas fa-save me-1" id="btnLuuIcon"></i>Lưu sản phẩm
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.7/minified/html5-qrcode.min.js'"></script>
<script src="https://unpkg.com/vue@3.4.27/dist/vue.global.prod.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
@php
    $attrsPayload = $thuocTinhChas->map(function ($g) {
        return [
            'id' => $g->id,
            'name' => $g->ten_thuoc_tinh,
            'values' => $g->thuocTinhCons->map(function ($v) {
                return ['id' => $v->id, 'label' => $v->ten_thuoc_tinh];
            })->values()->all(),
        ];
    })->values()->all();
$unitsPayload = $donViMacDinhs->map(fn($u) => [
    'id'   => $u->id,
    'name' => $u->ten_hien_thi, // "Thùng 24"
    'qty'  => $u->so_luong_san_pham_trong_don_vi,
])->values()->all();
@endphp
<script>
    // Bridge data từ Blade sang Vue 3
    window.__CREATE_PRODUCT_DATA__ = {
        danhMucs: @json($danhMucs->map(fn($d) => ['id' => $d->id, 'ten' => $d->ten_danh_muc])),
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
        availableAttributes: @json($attrsPayload),
        availableUnits: @json($unitsPayload)
    };
</script>
<script src="{{ asset('js/admin/san-pham-create-vue.js') }}"></script>
<script src="{{ asset('js/admin/san-pham.js') }}?v={{ time() }}"></script>
@endsection

<!-- ===================== IMPORT MODAL ===================== -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#198754 0%,#157347 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="importProductModalLabel">
                        <i class="fas fa-file-import me-2"></i>Nhập dữ liệu sản phẩm
                    </h5>
                    <small class="text-white-50">Từ file CSV (.csv)</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('admin/san-pham/import') }}" method="POST" enctype="multipart/form-data" id="importProductForm">
                @csrf
                <input type="hidden" name="_action" value="import">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-file-csv fa-4x text-success mb-3"></i>
                        <h5>Chọn file CSV để import</h5>
                        <p class="text-muted small mb-2">File phải có định dạng <strong>.csv</strong> (UTF-8).</p>
                        <a href="{{ url('admin/san-pham/export-template') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download me-1"></i>Tải mẫu import
                        </a>
                    </div>

                    <div class="mb-3">
                        <label for="importFileInput" class="form-label fw-semibold">Chọn file CSV</label>
                        <input type="file" class="form-control" id="importFileInput" name="excel_file" accept=".csv,text/csv">
                        <div class="form-text">Dung lượng tối đa: 5MB</div>
                    </div>

                    <!-- Preview section -->
                    <div id="importPreviewSection" class="d-none">
                        <hr>
                        <h6 class="mb-2"><i class="fas fa-eye me-1"></i>Xem trước dữ liệu (5 dòng đầu tiên)</h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-bordered table-hover mb-0" id="importPreviewTable">
                                <thead class="table-light"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success" id="btnImportSubmit">
                        <i class="fas fa-upload me-1"></i>Import sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
<style>
    [v-cloak] { display: none !important; }
</style>
@endsection
