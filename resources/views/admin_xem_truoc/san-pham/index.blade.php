@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quan ly San pham - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quan ly San pham</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">San pham</li>
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
            <i class="fas fa-plus me-2"></i>Them san pham
        </button>
        <button class="btn btn-outline-secondary" id="startQrScanBtn">
            <i class="fas fa-barcode me-2"></i>Quet ma vach
        </button>
        <a href="{{ url('admin/san-pham/trash') }}" class="btn btn-outline-danger">
            <i class="fas fa-trash me-2"></i>Thung rac
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
        <form action="{{ url('admin/san-pham') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchKeywordInput" class="form-control" name="keyword" value="{{ $keyword ?? '' }}" placeholder="Tim kiem san pham...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="danh_muc">
                        <option value="">Tat ca danh muc</option>
                        @foreach($danhMucs as $danhMuc)
                            <option value="{{ $danhMuc->id }}" {{ (string)$danhMuc->id === (string)($danhMucId ?? '') ? 'selected' : '' }}>{{ $danhMuc->ten_danh_muc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="trang_thai">
                        <option value="">Tat ca trang thai</option>
                        <option value="1" {{ $trangThai === '1' || $trangThai === 1 ? 'selected' : '' }}>Dang ban</option>
                        <option value="0" {{ $trangThai === '0' || $trangThai === 0 ? 'selected' : '' }}>Ngung ban</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-filter me-2"></i>Loc
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
                <h5 class="modal-title">Quet ma vach</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrScanner" style="width:100%; min-height:400px;"></div>
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-secondary" id="stopQrScanBtn">Dung quet</button>
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

<div class="card">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                    <label class="form-check-label text-muted" for="selectAllCheckbox">Chon tat ca</label>
                </div>
                <div id="bulkActionButtons" class="d-none">
                    <span class="text-muted me-2" id="selectedCount">0 da chon</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="submitBulkAction('activate')"><i class="fas fa-check me-1"></i>Bat</button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="submitBulkAction('deactivate')"><i class="fas fa-ban me-1"></i>Tat</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="submitBulkAction('delete')"><i class="fas fa-trash me-1"></i>Xoa</button>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Tong: <strong id="totalProducts">{{ $variants->total() }}</strong> dong</span>
            </div>
        </div>

        @if($variants->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th style="width:40px;"></th>
                        <th style="width:60px;">Anh</th>
                        <th>Ten san pham</th>
                        <th>Danh muc</th>
                        <th style="width:130px;">Gia ban</th>
                        <th style="width:80px;">Ton kho</th>
                        <th style="width:100px;">Trang thai</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($productGroups as $group)
                        @php
                            $variantsOfGroup = $group['variants'];
                            $variantCount = count($variantsOfGroup);
                            $tonKho = $group['so_luong_ton'];
                            $trangThai = $group['trang_thai'] ?? true;
                            $hasVariants = $variantCount > 0;
                            $isMultiVariant = $variantCount > 1;
                            $firstVariant = $variantsOfGroup[0] ?? null;
                        @endphp
                        <tr class="product-row"
                            style="cursor:pointer;"
                            data-id="{{ $firstVariant['id'] ?? $group['id'] }}"
                            data-type="product"
                            data-product-id="{{ $group['product_id'] }}"
                            data-first-variant-id="{{ $firstVariant['id'] ?? '' }}"
                            onclick="window.openProductDrawer && window.openProductDrawer('{{ $firstVariant['id'] ?? $group['id'] }}')">
                            <td onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input product-checkbox" value="{{ $group['id'] }}">
                            </td>
                            <td onclick="event.stopPropagation();">
                                @if($hasVariants)
                                    <span class="toggle-variants-btn" onclick="toggleProductVariants(this, event)" style="cursor:pointer; color:#0d6efd;" title="Xem biến thể ({{ $variantCount }})">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($group['hinh_anh']))
                                    <img src="{{ asset($group['hinh_anh']) }}" alt="{{ $group['ten_san_pham'] }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                @else
                                    <div style="width:48px;height:48px;border-radius:6px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <span class="fw-semibold" style="font-size:0.9rem;">{{ $group['ten_san_pham'] }}</span>
                                    @if($isMultiVariant)
                                        <span class="badge bg-light text-muted border ms-1" style="font-size:0.65rem;">{{ $variantCount }} biến thể</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $group['danh_muc'] ?: '-' }}</span>
                            </td>
                            <td>
                                @if($isMultiVariant)
                                    <span class="fw-semibold text-primary" style="font-size:0.85rem;">
                                        {{ number_format((float)$group['gia_min'], 0, ',', '.') }} - {{ number_format((float)$group['gia_max'], 0, ',', '.') }} đ
                                    </span>
                                @else
                                    <span class="fw-bold text-primary" style="font-size:0.88rem;">
                                        {{ number_format((float)$group['gia_min'], 0, ',', '.') }} đ
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-muted">{{ $tonKho }}</span>
                            </td>
                            <td>
                                @if(!$trangThai)
                                    <span class="badge bg-danger">Ngưng bán</span>
                                @elseif($tonKho <= 0)
                                    <span class="badge bg-secondary">Hết hàng</span>
                                @else
                                    <span class="badge bg-success">Còn hàng</span>
                                @endif
                            </td>
                        </tr>
                        @if($hasVariants)
                        <tr class="product-variants-row" data-product-id="{{ $group['id'] }}" style="display:none; background:#fafafa;">
                            <td colspan="8" style="padding: 14px 20px;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold small text-muted">
                                        <i class="fas fa-list-ul me-1"></i>
                                        Danh sách biến thể & đơn vị quy đổi ({{ $variantCount }})
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted" onclick="toggleProductVariants(this, event)">
                                        <i class="fas fa-times"></i> Đóng
                                    </button>
                                </div>
                                <table class="table table-sm mb-0 align-middle" style="background:#fff;">
                                    <thead class="small text-muted">
                                        <tr style="background:#f5f5f5;">
                                            <th>Biến thể</th>
                                            <th>Thuộc tính</th>
                                            <th>Mã vạch</th>
                                            <th class="text-end">Giá bán</th>
                                            <th class="text-center">Tồn</th>
                                            <th class="text-center">Đơn vị QĐ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($variantsOfGroup as $v)
                                            @php $vHasUnits = !empty($v['units']); @endphp
                                            <tr>
                                                <td class="small">
                                                    <span class="fw-semibold">{{ $v['ten_bien_the'] ?: '(Mặc định)' }}</span>
                                                </td>
                                                <td class="small">
                                                    @if(!empty($v['thuoc_tinh_labels']))
                                                        @foreach($v['thuoc_tinh_labels'] as $label)
                                                            <span class="badge bg-light text-dark border me-1" style="font-size:0.7rem;">{{ $label }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="small text-muted">{{ $v['ma_vach'] ?? '-' }}</td>
                                                <td class="small text-end fw-semibold text-primary">{{ number_format((float)($v['gia_ban'] ?? 0), 0, ',', '.') }} đ</td>
                                                <td class="small text-center">{{ $v['so_luong_ton'] }}</td>
                                                <td class="small text-center">
                                                    @if($vHasUnits)
                                                        <span class="badge bg-info text-dark" style="font-size:0.7rem;">{{ count($v['units']) }} đơn vị</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($vHasUnits)
                                            <tr style="background:#fafafa;">
                                                <td colspan="6" class="ps-4 py-2">
                                                    <div class="d-flex flex-wrap gap-3 small">
                                                        @foreach($v['units'] as $u)
                                                            <div class="d-inline-flex align-items-center gap-2 bg-white px-2 py-1 rounded border" style="font-size:0.78rem;">
                                                                <i class="fas fa-cube text-muted"></i>
                                                                <span class="fw-semibold">{{ $u['ten_don_vi'] }}</span>
                                                                <span class="text-muted">×{{ $u['ty_le_quy_doi'] }}</span>
                                                                <span class="text-success fw-semibold">{{ number_format((float)($u['gia_ban_quy_doi'] ?? 0), 0, ',', '.') }} đ</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <p class="mb-0">Hien chua co san pham nao.</p>
        </div>
        @endif
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">
                Hien thi {{ $variants->firstItem() ?? 0 }} - {{ $variants->lastItem() ?? 0 }} tren {{ $variants->total() }} dong
            </span>
            <nav>{{ $variants->links('pagination::bootstrap-5') }}</nav>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="productDetailDrawer" style="width:680px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Chi tiet san pham</h5>
        <div class="d-flex gap-2">
            <a href="#" id="drawerEditBtn" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Sua</a>
            <button type="button" class="btn btn-sm btn-danger" id="drawerDeleteBtn" onclick="confirmDeleteFromDrawer()"><i class="fas fa-trash me-1"></i>Xoa</button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
    </div>
    <div class="offcanvas-body p-0" id="drawerBody" style="overflow-y:auto;">
        <div class="d-flex justify-content-center align-items-center" style="min-height:300px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="text-muted mb-0">Dang tai...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-fullheight">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0d6efd 0%,#0b5ed7 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-box-open me-2"></i>THEM SAN PHAM</h5>
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
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Dien day du thong tin truoc khi luu</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huy bo</button>
                            <button type="button" class="btn btn-success px-4" id="btnLuuSanPham">
                                <span class="spinner-border spinner-border-sm me-1 d-none" id="btnLuuSpinner" role="status"></span>
                                <i class="fas fa-save me-1" id="btnLuuIcon"></i>Luu san pham
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
<script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script>
<script src="https://unpkg.com/vue@3.4.27/dist/vue.global.prod.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Bridge data từ Blade sang Vue 3
    window.__CREATE_PRODUCT_DATA__ = {
        danhMucs: @json($danhMucs->map(fn($d) => ['id' => $d->id, 'ten' => $d->ten_danh_muc])),
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
    };
</script>
<script src="{{ asset('js/admin/san-pham-create-vue.js') }}"></script>
<script src="{{ asset('js/admin/san-pham.js') }}"></script>
@endsection

<!-- ===================== IMPORT MODAL ===================== -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#198754 0%,#157347 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="importProductModalLabel">
                        <i class="fas fa-file-import me-2"></i>Nhap du lieu san pham
                    </h5>
                    <small class="text-white-50">Tu file CSV (.csv)</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('admin/san-pham/import') }}" method="POST" enctype="multipart/form-data" id="importProductForm">
                @csrf
                <input type="hidden" name="_action" value="import">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-file-csv fa-4x text-success mb-3"></i>
                        <h5>Chon file CSV de import</h5>
                        <p class="text-muted small mb-2">File phai co dinh dang <strong>.csv</strong> (UTF-8).</p>
                        <a href="{{ url('admin/san-pham/export-template') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download me-1"></i>Tai mau import
                        </a>
                    </div>

                    <div class="mb-3">
                        <label for="importFileInput" class="form-label fw-semibold">Chon file CSV</label>
                        <input type="file" class="form-control" id="importFileInput" name="excel_file" accept=".csv,text/csv">
                        <div class="form-text">Dung luong toi da: 5MB</div>
                    </div>

                    <!-- Preview section -->
                    <div id="importPreviewSection" class="d-none">
                        <hr>
                        <h6 class="mb-2"><i class="fas fa-eye me-1"></i>Xem truoc du lieu (5 dong dau tien)</h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-bordered table-hover mb-0" id="importPreviewTable">
                                <thead class="table-light"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huy bo</button>
                    <button type="submit" class="btn btn-success" id="btnImportSubmit">
                        <i class="fas fa-upload me-1"></i>Import san pham
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
