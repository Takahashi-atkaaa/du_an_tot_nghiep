@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Sản phẩm - SmartMart')

@section('content')
{{-- Breadcrumb + tiêu đề trang. Các nút hành động được gom vào thanh công cụ phía dưới
   để tạo thành 1 thanh ngang duy nhất: [Tìm kiếm] [Lọc nâng cao] [Hành động]. --}}
<div class="mb-3">
    <h4 class="fw-bold mb-1">Quản lý Sản phẩm</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Sản phẩm</li>
        </ol>
    </nav>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card table-admin mb-4 search-filter-card" x-data="{ showFilter: @js(($activeFiltersCount ?? 0) > 0) }">
    <div class="card-body p-3">
        <form id="searchProductForm" action="{{ url('admin/san-pham') }}" method="GET">
            {{-- ============================================================
                THANH CÔNG CỤ CHÍNH (Luôn hiển thị)
                Layout: [ Ô tìm kiếm rộng ] [ Nút Lọc nâng cao ] [ Nhóm Action ]
                ============================================================ --}}
            <div class="d-flex flex-wrap align-items-center gap-2">

                {{-- 1) Ô TÌM KIẾM CHÍNH --}}
                <div class="flex-grow-1" style="min-width: 260px;">
                    <div class="input-group search-input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               id="searchKeywordInput"
                               class="form-control border-start-0"
                               name="keyword"
                               value="{{ $keyword ?? '' }}"
                               placeholder="Tìm kiếm theo tên, mã sản phẩm, mã vạch...">
                        @if(!empty($keyword))
                            <button type="button"
                                    class="btn btn-outline-secondary border-start-0 border-end-0"
                                    onclick="document.getElementById('searchKeywordInput').value=''; document.getElementById('searchKeywordInput').focus();"
                                    title="Xóa từ khóa">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- 2) NÚT "LỌC NÂNG CAO" --}}
                <button type="button"
                        class="btn btn-advanced-filter"
                        :class="{ 'active': showFilter }"
                        @click="showFilter = !showFilter"
                        aria-expanded="false"
                        :aria-expanded="showFilter.toString()">
                    <i class="fas fa-filter me-1"></i>
                    <span>Lọc nâng cao</span>
                    @if(($activeFiltersCount ?? 0) > 0)
                        <span class="badge bg-primary ms-2 filter-badge">{{ $activeFiltersCount ?? 0 }}</span>
                    @endif
                    <i class="fas fa-chevron-down ms-2 chevron-icon"
                       :class="{ 'rotated': showFilter }"></i>
                </button>

                {{-- 3) NHÓM ACTION --}}
                <div class="d-flex gap-2 action-group">
                    <button type="button"
                            class="btn btn-success action-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#importProductModal">
                        <i class="fas fa-file-import me-2"></i>Import
                    </button>
                    <a href="{{ route('san-pham.create') }}" class="btn btn-primary action-btn">
                        <i class="fas fa-plus me-2"></i>Thêm sản phẩm
                    </a>
                    <a href="{{ url('admin/san-pham/trash') }}" class="btn btn-outline-danger action-btn">
                        <i class="fas fa-trash me-2"></i>Thùng rác
                    </a>
                </div>
            </div>

            {{-- ============================================================
                PANEL LỌC NÂNG CAO (Dropdown Collapse - Mặc định ẩn)
                Hiệu ứng: max-height transition để mượt mà, không bị giật
                ============================================================ --}}
            <div class="filter-panel-wrapper"
                 x-ref="filterPanel"
                 x-cloak
                 x-show="showFilter"
                 x-transition:enter="filter-enter"
                 x-transition:enter-start="filter-enter-start"
                 x-transition:enter-end="filter-enter-end"
                 x-transition:leave="filter-leave"
                 x-transition:leave-start="filter-leave-start"
                 x-transition:leave-end="filter-leave-end">
                <div class="filter-panel mt-2">
                    <div class="row g-3">
                        {{-- Danh mục --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label filter-label">Danh mục</label>
                            <select class="form-select form-select-sm filter-select" name="danh_muc">
                                <option value="">Tất cả danh mục</option>
                                @foreach($danhMucs as $danhMuc)
                                    <option value="{{ $danhMuc->id }}" {{ (string)$danhMuc->id === (string)($danhMucId ?? '') ? 'selected' : '' }}>{{ $danhMuc->ten_danh_muc }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Thương hiệu --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label filter-label">Thương hiệu</label>
                            <select class="form-select form-select-sm filter-select" name="thuong_hieu">
                                <option value="">Tất cả thương hiệu</option>
                                @forelse(($thuongHieus ?? []) as $th)
                                    <option value="{{ $th }}" {{ (string)$th === (string)($thuongHieu ?? '') ? 'selected' : '' }}>{{ $th }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                        {{-- Trạng thái kinh doanh --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label filter-label">Trạng thái kinh doanh</label>
                            <select class="form-select form-select-sm filter-select" name="trang_thai">
                                <option value="">Tất cả</option>
                                <option value="1" {{ $trangThai === '1' || $trangThai === 1 ? 'selected' : '' }}>Đang bán</option>
                                <option value="0" {{ $trangThai === '0' || $trangThai === 0 ? 'selected' : '' }}>Ngừng bán</option>
                            </select>
                        </div>

                        {{-- Trạng thái tồn kho --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label filter-label">Trạng thái tồn kho</label>
                            <select class="form-select form-select-sm filter-select" name="ton_kho">
                                <option value="">Tất cả</option>
                                <option value="con_hang" {{ ($tonKho ?? '') === 'con_hang' ? 'selected' : '' }}>Còn hàng</option>
                                <option value="het_hang" {{ ($tonKho ?? '') === 'het_hang' ? 'selected' : '' }}>Hết hàng</option>
                                <option value="duoi_dinh_muc" {{ ($tonKho ?? '') === 'duoi_dinh_muc' ? 'selected' : '' }}>Dưới định mức</option>
                            </select>
                        </div>

                        {{-- Khoảng giá (Giá Từ - Giá Đến) --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label filter-label">Khoảng giá bán</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="number"
                                       class="form-control form-control-sm filter-input"
                                       name="gia_tu"
                                       value="{{ $giaTu ?? '' }}"
                                       placeholder="Từ"
                                       min="0"
                                       step="1000">
                                <span class="text-muted small">—</span>
                                <input type="number"
                                       class="form-control form-control-sm filter-input"
                                       name="gia_den"
                                       value="{{ $giaDen ?? '' }}"
                                       placeholder="Đến"
                                       min="0"
                                       step="1000">
                            </div>
                        </div>

                        {{-- Sắp xếp --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label filter-label">Sắp xếp theo</label>
                            <select class="form-select form-select-sm filter-select" name="sap_xep">
                                <option value="">Mặc định</option>
                                <option value="moi_nhat" {{ ($sapXep ?? '') === 'moi_nhat' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="ban_chay" {{ ($sapXep ?? '') === 'ban_chay' ? 'selected' : '' }}>Bán chạy nhất</option>
                                <option value="ton_kho_nhieu" {{ ($sapXep ?? '') === 'ton_kho_nhieu' ? 'selected' : '' }}>Tồn kho nhiều nhất</option>
                            </select>
                        </div>
                    </div>

                    {{-- Footer: Nút Reset + Áp dụng --}}
                    <div class="filter-panel-footer">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ url('admin/san-pham') }}"
                               class="btn btn-reset-filter"
                               @click.prevent="window.location.href='{{ url('admin/san-pham') }}'">
                                <i class="fas fa-undo me-1"></i>Thiết lập lại
                            </a>
                            <button type="submit" class="btn btn-apply-filter">
                                <i class="fas fa-check me-1"></i>Áp dụng lọc
                                @if(($activeFiltersCount ?? 0) > 0)
                                    <span class="badge bg-light text-primary ms-2">{{ $activeFiltersCount ?? 0 }}</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
            <table class="table table-hover mb-0 align-middle w-100 product-list-table" style="table-layout: fixed;">
                <colgroup>
                    <col style="width:3%">
                    <col style="width:7%">
                    <col style="width:30%">
                    <col style="width:15%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:7%">
                    <col style="width:8%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center"></th>
                        <th class="text-center">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-end">Giá vốn</th>
                        <th class="text-end">Giá bán</th>
                        <th class="text-center">Đã bán</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($sanPhams as $sp)
                        @php
                            // Bỏ qua sản phẩm không có variant / đơn vị quy đổi nào
                            // (flatenedRows rỗng). Tránh truy cập $firstRow->variant khi null.
                            $rows = $sp->flattenedRows;
                        @endphp
                        @if($rows->isEmpty())
                            @continue
                        @endif
                        @php
                            $hasMoreThanOneRow = $rows->count() > 1;
                            $firstRow = $rows->first();
                        @endphp

                        @continue(!$rows->isNotEmpty())

                        @php
                            // ============================================================
                            // FIX: Chọn variant đại diện cho đơn vị cơ bản (dòng chính).
                            // Trước đây: $firstRow = $rows->first() → có thể là variant
                            // không phải đơn vị cơ bản (variant có ten_don_vi trùng với
                            // đơn vị quy đổi, do dữ liệu cũ bị ghi đè nhầm).
                            // Sau khi lưu, variant mới "Cái" có ID lớn hơn được sort
                            // xuống dưới, khiến $firstRow luôn lấy variant cũ "Lon"
                            // nhưng variant cũ có ten_don_vi trùng quy đổi → dropdown trống.
                            // ============================================================
                            $masterVariant = $sp->firstMasterVariant;
                            $firstVariant = $masterVariant ?? $firstRow?->variant ?? null;
                            $firstVariantId = $firstVariant?->id;

                            // Phân loại cho UI mới:
                            //   - $variantRows (loai_dong='goc' AND có thuộc tính) -> hiển thị thành <tr> riêng
                            //   - $conversionRows (loai_dong='quy_doi') -> gộp vào Dropdown tại dòng chính
                            $variantAttrRows = $rows->where('loai_dong', 'goc')
                                ->filter(fn($r) => !empty($r->ten_bien_the_display));
                            // Lọc conversion rows theo variant CHA (chỉ conversion thuộc về dòng chính)
                            $conversionRows = $rows->where('loai_dong', 'quy_doi')
                                ->filter(fn($r) => $r->variant?->id === $firstVariantId);
                            $countQuyDoi = $conversionRows->count();
                            $isDonViOnly = $variantAttrRows->count() === 0 && $countQuyDoi > 0;

                            // Trạng thái tổng hợp cho sản phẩm
                            $trangThaiSP = $sp->trang_thai;
                            // Tổng tồn kho = SUM(so_luong_ton) của TẤT CẢ biến thể
                            // thuộc sản phẩm. Ưu tiên dùng thuộc tính sinh ra từ
                            // withSum('variants', 'so_luong_ton') ở controller
                            // ($sp->variants_sum_so_luong_ton) — chính xác về mặt
                            // SQL, không phụ thuộc vào relation đã eager load.
                            // Fallback: tính từ collection đã load (giữ tương thích
                            // với code cũ, test/debug).
                            $tongTonKho = (int) ($sp->variants_sum_so_luong_ton ?? $sp->variants->sum('so_luong_ton'));
                            $hasManyVariants = $variantAttrRows->count() > 0 || $countQuyDoi > 0;

                            // Đơn vị cơ bản (CHA): lấy từ master variant
                            $baseUnitName = $masterVariant?->ten_don_vi ?: '—';
                            // Biến thể CHA + data gốc (để JS tính toán khi chọn đơn vị)
                            $baseVariant   = $masterVariant ?? $firstRow?->variant ?? null;
                            $baseUnitId    = $baseVariant?->id ?? '';
                            $baseGiaBanGoc = $baseVariant?->gia_ban ?? 0;
                            $baseTonKhoGoc = $baseVariant?->so_luong_ton ?? 0;
                            $baseMaHang    = $baseVariant?->ma_hang ?? '';
                            $baseMaVach    = $baseVariant?->ma_vach ?? '';

                            // ============================================================
                            // GIÁ VỐN (khoảng giá nếu nhiều biến thể/đơn vị quy đổi)
                            // Lấy từ flattenedRows để bao gồm cả:
                            //   - variant.gia_von  (dòng goc)
                            //   - unit.gia_von_quy_doi (dòng quy_doi)
                            // Bỏ qua giá trị <= 0 (chưa set) để tránh range bị lệch về 0
                            // ============================================================
                            $giaVonList = $rows->pluck('gia_von')
                                ->map(fn($v) => (float) $v)
                                ->filter(fn($v) => $v > 0);
                            $giaVonMin = (float) ($giaVonList->min() ?? 0);
                            $giaVonMax = (float) ($giaVonList->max() ?? 0);
                            $hasGiaVon = $giaVonList->isNotEmpty();
                        @endphp

                        {{-- DÒNG CHÍNH (dòng đầu tiên) --}}
                        <tr class="product-parent-row cursor-pointer hover:bg-gray-50 transition-colors {{ !$firstRow->trang_thai ? 'table-secondary opacity-50' : '' }}"
                            data-id="{{ $sp->id }}"
                            data-variant-id="{{ $firstVariant?->id ?? '' }}"
                            data-unit-id=""
                            data-product-id="{{ $sp->id }}"
                            data-target-id="{{ $sp->id }}"
                            data-row-type="goc"
                            data-is-master="{{ $variantAttrRows->count() > 0 ? '1' : '0' }}"
                            data-gia-ban-goc="{{ $baseGiaBanGoc }}"
                            data-ton-kho-goc="{{ $baseTonKhoGoc }}"
                            onclick="window.toggleVariants && window.toggleVariants({{ $sp->id }})">

                            {{-- Cột đầu: chỉ giữ ô Checkbox.
                                Icon mũi tên xổ xuống (chevron) đã được loại bỏ để
                                giao diện gọn gàng hơn. Mở/đóng Dropdown xem nhanh
                                bằng cách click vào bất kỳ vùng nào khác trên dòng.
                                Checkbox dùng event.stopPropagation() để không kích
                                hoạt click mở dropdown khi người dùng tick chọn. --}}
                            <td onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input product-checkbox"
                                       value="{{ $sp->id }}"
                                       data-product-id="{{ $sp->id }}"
                                       data-type="goc"
                                       onclick="event.stopPropagation();">
                            </td>

                            {{-- Hình ảnh (lấy ảnh variant đầu) --}}
                            <td class="text-center">
                                @php $firstImg = $firstVariant?->hinh_anh ?? $sp->hinh_anh; @endphp
                                @if(!empty($firstImg))
                                    @php
                                        $firstImgSrc = \App\Models\BienTheSanPham::resolveImageUrl($firstImg);
                                    @endphp
                                    <img src="{{ $firstImgSrc }}" alt="{{ $sp->ten_san_pham }}"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                @else
                                    <div style="width:48px;height:48px;border-radius:6px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            {{-- Tên sản phẩm + Mã hàng (SKU) + Thương hiệu --}}
                            <td class="align-middle">
                                <div>
                                    <span class="fw-semibold js-tensp" style="font-size:0.9rem; line-height:1.2;">{{ $sp->ten_san_pham }}</span>

                                    {{-- YÊU CẦU 1: Thêm Mã hàng (SKU) màu xám, size nhỏ ngay dưới tên sản phẩm --}}
                                    @if(!empty($baseMaHang) || !empty($baseMaVach))
                                        <div class="text-sm text-gray-500 mt-1 js-mahang"
                                             style="font-size:0.72rem; color:#6b7280; line-height:1.1;">
                                            <i class="fas fa-barcode me-1" style="font-size:0.65rem;"></i>MH: <span class="js-mahang-val">{{ $baseMaHang ?: '—' }}</span>
                                        </div>
                                    @endif

                                    @if(!empty($sp->thuong_hieu))
                                        <div class="small text-muted" style="font-size:0.7rem;">{{ $sp->thuong_hieu }}</div>
                                    @endif
                                </div>
                            </td>

                            {{-- Danh mục --}}
                            <td class="align-middle">
                                <span class="text-muted small">{{ $sp->danhMuc?->ten_danh_muc ?? '-' }}</span>
                            </td>

                            {{-- Giá vốn (khoảng giá nếu có nhiều biến thể/đơn vị quy đổi) --}}
                            <td class="text-end align-middle">
                                @if(!$hasGiaVon)
                                    <span class="text-gray-400">—</span>
                                @elseif($giaVonMin == $giaVonMax)
                                    <span class="text-gray-500 js-giavon" style="font-size:0.85rem;"
                                          data-gia-von="{{ $giaVonMin }}">
                                        {{ number_format($giaVonMin, 0, ',', '.') }} đ
                                    </span>
                                @else
                                    <span class="text-gray-500 js-giavon" style="font-size:0.85rem;"
                                          data-gia-von-min="{{ $giaVonMin }}"
                                          data-gia-von-max="{{ $giaVonMax }}">
                                        {{ number_format($giaVonMin, 0, ',', '.') }} đ - {{ number_format($giaVonMax, 0, ',', '.') }} đ
                                    </span>
                                @endif
                            </td>

                            {{-- Giá bán --}}
                            <td class="text-end align-middle">
                                <span class="js-giaban fw-bold text-primary" style="font-size:0.88rem;"
                                      data-gia-ban="{{ (float)$baseGiaBanGoc }}">
                                    {{ number_format((float)$baseGiaBanGoc, 0, ',', '.') }} d
                                </span>
                            </td>

                            {{-- Đã bán (tổng số lượng từ chi_tiet_hoa_don của hóa đơn Hoàn thành) --}}
                            <td class="text-center align-middle">
                                <span class="js-daban text-muted small fw-medium" data-daban="{{ (int)($sp->tong_da_ban ?? 0) }}">{{ (int)($sp->tong_da_ban ?? 0) }}</span>
                            </td>

                            {{-- Tồn kho (TỔNG tồn kho của TẤT CẢ biến thể) --}}
                            <td class="text-center align-middle">
                                {{--
                                    Hiển thị tổng tồn kho (sum trên toàn bộ biến
                                    thể), không phải tồn kho của 1 biến thể đại
                                    diện. data-base-tonkho vẫn giữ theo biến thể
                                    CHA để JS recalcRow() dùng khi user đổi đơn vị
                                    quy đổi (tỷ lệ chia). Hai giá trị này phục
                                    vụ 2 mục đích khác nhau:
                                      - Display     = tổng thực tế trong kho
                                      - data-base-* = gốc để quy đổi đơn vị
                                --}}
                                <span class="js-tonkho font-semibold {{ $tongTonKho <= 0 ? 'text-danger' : ($tongTonKho <= 10 ? 'text-warning' : 'text-muted') }} small"
                                      data-base-tonkho="{{ (int)$baseTonKhoGoc }}"
                                      data-tong-tonkho="{{ (int)$tongTonKho }}"
                                      title="Tổng tồn kho của tất cả biến thể">
                                    {{ number_format($tongTonKho) }}
                                </span>
                            </td>

                            {{-- Trạng thái (dựa trên TỔNG tồn kho của tất cả biến thể) --}}
                            <td class="text-center align-middle">
                                @if(!$firstRow->trang_thai)
                                    <span class="badge bg-danger">Ngừng bán</span>
                                @elseif($tongTonKho <= 0)
                                    {{-- Tổng tồn kho <= 0: Hết hàng (mọi biến thể đều rỗng) --}}
                                    <span class="badge bg-secondary">Hết hàng</span>
                                @elseif($tongTonKho <= 10)
                                    {{-- Sắp hết: tổng tồn kho còn dưới ngưỡng cảnh báo --}}
                                    <span class="badge bg-warning text-dark">Sắp hết</span>
                                @else
                                    {{-- Tổng tồn kho > 0: Còn hàng --}}
                                    <span class="badge bg-success">Còn hàng</span>
                                @endif
                            </td>
                        </tr>
                        <tr id="productDetailRow{{ $sp->id }}" class="product-detail-row" style="display:none; background:#f8f9fa;">
                            <td colspan="9" class="p-0">
                                <div id="productDetailPanel{{ $sp->id }}" class="product-detail-panel p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="flex-grow-1 me-3">
                                            <h6 class="mb-1 fw-semibold" style="font-size:0.95rem;">
                                                <i class="fas fa-info-circle text-primary me-2"></i>Chi tiết sản phẩm
                                            </h6>
                                            <div class="text-muted small">
                                                {{ $sp->ten_san_pham }} · {{ $sp->danhMuc?->ten_danh_muc ?? 'Không xác định' }}
                                            </div>
                                            <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                                                <span class="badge bg-secondary">{{ $sp->variants->count() }} biến thể</span>
                                                <span class="text-muted small">
                                                    <i class="far fa-eye me-1"></i>Đây là chế độ xem nhanh. Nhấn <strong>“Xem chi tiết”</strong> để mở trang đầy đủ.
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            {{-- ===========================================================
                                                NÚT CHÍNH (PRIMARY CTA) — "Xem chi tiết đầy đủ"
                                                Mô hình Progressive Disclosure: đưa người dùng từ
                                                Quick View trong bảng xổ xuống sang trang chi tiết.
                                                Bằng chứng thị giác: gradient xanh dương + icon
                                                mũi tên trượt khi hover.
                                                ============================================================ --}}
                                            <a href="{{ url('admin/san-pham/' . $sp->id) }}"
                                               class="btn-view-detail-full"
                                               title="Xem chi tiết đầy đủ"
                                               onclick="event.stopPropagation();">
                                                <span class="btn-view-detail-label">Xem chi tiết đầy đủ</span>
                                                <i class="fas fa-arrow-right btn-view-detail-arrow"></i>
                                            </a>
                                            {{-- Nhóm nút phụ: Sửa / Xóa — thu nhỏ về icon-only --}}
                                            <div class="panel-action-group">
                                                <a href="{{ route('san-pham.edit', $sp->id) }}"
                                                   class="btn btn-outline-primary"
                                                   title="Sửa sản phẩm"
                                                   onclick="event.stopPropagation();">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        title="Xóa sản phẩm"
                                                        onclick="event.stopPropagation(); window.deleteProductByUrl('{{ route('san-pham.destroy', $sp->id) }}', {{ $sp->id }}, '{{ addslashes($sp->ten_san_pham) }}');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <ul class="nav nav-tabs nav-tabs-sm mb-3" role="tablist">
                                        <li class="nav-item">
                                            <button type="button" class="nav-link active" data-tab-key="summary" onclick="event.stopPropagation(); window.switchProductTab({{ $sp->id }}, 'summary')">Tổng quan</button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" data-tab-key="variants" onclick="event.stopPropagation(); window.switchProductTab({{ $sp->id }}, 'variants')">Biến thể</button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" data-tab-key="stock" onclick="event.stopPropagation(); window.switchProductTab({{ $sp->id }}, 'stock')">Kho</button>
                                        </li>
                                    </ul>

                                    <div class="product-detail-tabs">
                                        <div id="productSummaryTab{{ $sp->id }}" class="product-tab-content product-summary-tab text-center py-4 text-muted">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            Đang tải thống kê...
                                        </div>
                                        <div id="productVariantsTab{{ $sp->id }}" class="product-tab-content product-variants-tab d-none"></div>
                                        <div id="productStockTab{{ $sp->id }}" class="product-tab-content product-stock-tab d-none"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>

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

{{-- ===================== INLINE JS: DROPDOWN ĐƠN VỊ TÍNH ===================== --}}
<script>
(function () {
    // Format tiền VNĐ: 336000 -> "336.000"
    function formatMoneyVND(amount) {
        var n = parseFloat(amount) || 0;
        return n.toLocaleString('vi-VN') + ' đ';
    }

    // Toggle ẩn/hiện popover các đơn vị quy đổi
    window.toggleUnitDropdown = function (triggerEl) {
        if (!triggerEl) return;
        var container = triggerEl.closest('.unit-dropdown-container');
        if (!container) return;
        var popover = container.querySelector('.unit-popover-list');
        if (!popover) return;

        // Đóng tất cả các popover khác trước
        document.querySelectorAll('.unit-popover-list').forEach(function (p) {
            if (p !== popover) p.classList.add('hidden');
        });

        popover.classList.toggle('hidden');
    };

    // Click bên ngoài sẽ đóng popover
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.unit-dropdown-container')) {
            document.querySelectorAll('.unit-popover-list').forEach(function (p) {
                p.classList.add('hidden');
            });
        }
    });

    // ------------------------------------------------------------
    // Hàm recalcRow: cập nhật DOM (Tên ĐVT, Giá bán, Tồn kho)
    // ------------------------------------------------------------
    function recalcRow(row, unitObj) {
        if (!row || !unitObj) return;

        // 1. Cập nhật Tên Đơn vị (giữ lại dấu ▾ nếu có)
        var elDonVi = row.querySelector('.js-donvi');
        if (elDonVi && unitObj.ten_don_vi) {
            var hasArrow = elDonVi.textContent.indexOf('▾') >= 0;
            elDonVi.textContent = unitObj.ten_don_vi + (hasArrow ? ' ▾' : '');
        }

        // 2. Cập nhật Giá bán (Format tiền VNĐ)
        var elGiaBan = row.querySelector('.js-giaban');
        if (elGiaBan) {
            var gia = parseFloat(unitObj.gia_ban) || 0;
            elGiaBan.textContent = formatMoneyVND(gia);
            elGiaBan.setAttribute('data-gia-ban', gia);
        }

        // 3. Cập nhật Tồn kho (Chia theo tỷ lệ so với tồn kho gốc)
        var elTonKho = row.querySelector('.js-tonkho');
        if (elTonKho) {
            var baseTonKho = parseFloat(elTonKho.getAttribute('data-base-tonkho') || 0);
            var tyLe = parseFloat(unitObj.ty_le) || 1;
            var tonKhoMoi = tyLe > 0 ? Math.floor(baseTonKho / tyLe) : 0;
            elTonKho.textContent = tonKhoMoi;

            // Cập nhật class màu theo ngưỡng
            elTonKho.classList.remove('text-danger', 'text-warning', 'text-muted');
            if (tonKhoMoi <= 0) {
                elTonKho.classList.add('text-danger');
            } else if (tonKhoMoi <= 3) {
                elTonKho.classList.add('text-warning');
            } else {
                elTonKho.classList.add('text-muted');
            }
        }

        // 4. Highlight dòng đang xem đơn vị quy đổi
        if (unitObj.type !== 'base') {
            row.classList.add('table-warning');
        } else {
            row.classList.remove('table-warning');
        }
    }
    // Expose để file JS khác (nếu cần) cũng có thể gọi
    window.recalcRow = recalcRow;

    // ------------------------------------------------------------
    // Chọn 1 đơn vị (base hoặc quy đổi):
    //   - Cập nhật data-* cho row để mở drawer đúng biến thể
    //   - Gọi recalcRow để cập nhật DOM ngay trên lưới
    // ------------------------------------------------------------
    window.selectUnitView = function (rowEl, unitData) {
        if (!rowEl || !unitData) return;

        // Chuẩn hóa: chấp nhận cả key cũ (name/qty/isBase) và key mới (ten_don_vi/ty_le/type)
        var normalized = {
            id:         unitData.id || '',
            type:       unitData.type || (unitData.isBase ? 'base' : 'unit'),
            ten_don_vi: unitData.ten_don_vi || unitData.name || '',
            ty_le:      unitData.ty_le || unitData.qty || 1,
            gia_ban:    unitData.gia_ban || 0,
            ma_hang:    unitData.ma_hang || '',
            ma_vach:    unitData.ma_vach || ''
        };

        // 1. Cập nhật data-* cho row để drawer mở đúng biến thể
        if (normalized.id) {
            rowEl.setAttribute('data-unit-id', normalized.id);
        }
        rowEl.setAttribute('data-row-type', normalized.type === 'base' ? 'goc' : 'quy_doi');
        rowEl.setAttribute('data-target-id', rowEl.getAttribute('data-variant-id') || rowEl.getAttribute('data-id'));

        // 2. Cập nhật DOM trên lưới (Tên ĐVT + Giá bán + Tồn kho)
        recalcRow(rowEl, normalized);
    };
})();
</script>

@include('admin_xem_truoc.san-pham._import-modal')
@endsection

@section('page_scripts')
{{-- Alpine.js dùng cho collapse panel lọc nâng cao + toggle trạng thái nút --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
{{-- san-pham.js cung cấp: toggleVariants, switchProductTab, deleteProductByUrl, ... cho Expandable Row / Quick View --}}
<script src="{{ asset('js/admin/san-pham.js') }}?v={{ time() }}"></script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
<style>
    [v-cloak] { display: none !important; }

    /* =========================================================
       Utility classes bổ sung cho dòng sản phẩm trong bảng danh sách
       (cursor-pointer, hover highlight mềm, transition mượt).
       Bootstrap không cung cấp sẵn nên khai báo tại đây.
       ========================================================= */
    .cursor-pointer { cursor: pointer; }
    .hover\:bg-gray-50:hover > td { background-color: #f9fafb !important; }
    .transition-colors { transition: background-color 0.15s ease, color 0.15s ease; }

    /* =========================================================
       YÊU CẦU MỚI: KHỐI TÌM KIẾM & LỌC (BASIC SEARCH + ADVANCED FILTER)
       Mô hình: Thanh công cụ luôn hiển thị, Panel lọc nâng cao dạng collapse
       ========================================================= */
    .search-filter-card {
        border: 1px solid #f1f3f5;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15,23,42,0.04);
    }

    /* Ô tìm kiếm chính - bo góc + focus state */
    .search-input-group {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .search-input-group:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }
    .search-input-group .input-group-text {
        padding-left: 0.85rem;
        padding-right: 0.5rem;
    }
    .search-input-group .form-control {
        border-left: 0;
        padding-left: 0.25rem;
        font-size: 0.9rem;
    }
    .search-input-group .form-control:focus {
        box-shadow: none;
        border-color: transparent;
    }

    /* Nút "Lọc nâng cao" - toggle panel */
    .btn-advanced-filter {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        color: #374151;
        font-weight: 500;
        font-size: 0.875rem;
        padding: 0.5rem 0.9rem;
        border-radius: 10px;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .btn-advanced-filter:hover {
        background: #f9fafb;
        border-color: #d1d5db;
        color: #1f2937;
    }
    .btn-advanced-filter.active {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8;
    }
    .btn-advanced-filter .filter-badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.2rem 0.45rem;
        border-radius: 999px;
    }
    .btn-advanced-filter .chevron-icon {
        font-size: 0.7rem;
        transition: transform 0.25s ease;
    }
    .btn-advanced-filter .chevron-icon.rotated {
        transform: rotate(180deg);
    }

    /* Nhóm nút action bên phải */
    .action-group .action-btn {
        font-size: 0.85rem;
        padding: 0.5rem 0.85rem;
        border-radius: 10px;
        white-space: nowrap;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .action-group .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(15,23,42,0.08);
    }
    /* Trên mobile: padding nhỏ lại cho nút action để gọn hơn */
    @media (max-width: 575.98px) {
        .action-group .action-btn {
            padding: 0.5rem 0.65rem;
        }
    }

    /* Panel lọc nâng cao - hiệu ứng collapse mượt */
    .filter-panel-wrapper {
        overflow: hidden;
    }
    .filter-panel {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 1rem 1rem 0.5rem;
        box-shadow: 0 2px 8px rgba(15,23,42,0.05);
    }
    .filter-panel-wrapper.filter-enter {
        transition: max-height 0.35s ease, opacity 0.25s ease;
    }
    .filter-panel-wrapper.filter-enter-start {
        max-height: 0;
        opacity: 0;
    }
    .filter-panel-wrapper.filter-enter-end {
        max-height: 600px;
        opacity: 1;
    }
    .filter-panel-wrapper.filter-leave {
        transition: max-height 0.3s ease, opacity 0.2s ease;
    }
    .filter-panel-wrapper.filter-leave-start {
        max-height: 600px;
        opacity: 1;
    }
    .filter-panel-wrapper.filter-leave-end {
        max-height: 0;
        opacity: 0;
    }

    /* Label + input/select trong panel */
    .filter-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .filter-select,
    .filter-input {
        font-size: 0.875rem;
        border-radius: 8px;
        border-color: #e5e7eb;
    }
    .filter-select:focus,
    .filter-input:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(147,197,253,0.25);
    }

    /* Footer của panel (nút Reset + Áp dụng) */
    .filter-panel-footer {
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px dashed #e5e7eb;
    }
    .btn-reset-filter {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        color: #4b5563;
        font-size: 0.85rem;
        padding: 0.45rem 0.9rem;
        border-radius: 8px;
        transition: all 0.15s ease;
    }
    .btn-reset-filter:hover {
        background: #f3f4f6;
        color: #1f2937;
        border-color: #d1d5db;
    }
    .btn-apply-filter {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border: 1px solid #1d4ed8;
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.45rem 1.1rem;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(37,99,235,0.25);
        transition: all 0.15s ease;
    }
    .btn-apply-filter:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(37,99,235,0.35);
    }
    .btn-apply-filter .badge {
        font-size: 0.7rem;
        font-weight: 700;
    }

    /* =========================================================
       BẢNG SẢN PHẨM - LAYOUT FIXED ĐỂ CỘT KHÔNG BỊ DỒN VỀ TRÁI
       ========================================================= */
    .table-responsive {
        overflow-x: auto;
    }
    .product-list-table {
        width: 100% !important;
        table-layout: fixed !important;
    }
    .product-list-table th,
    .product-list-table td {
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
    /* Cột Tên sản phẩm: cho phép xuống dòng nhưng giấu tràn */
    .product-list-table td:nth-child(3) {
        white-space: normal;
        word-break: break-word;
    }
    /* Cột Giá vốn, Giá bán: căn lề phải theo header */
    .product-list-table td.text-end {
        text-align: right !important;
    }
    .product-list-table td.text-center {
        text-align: center !important;
    }
    /* Bo góc + làm đẹp header */
    .product-list-table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
        font-size: 0.82rem;
        color: #495057;
    }
    /* Tăng chiều cao hàng cho dễ đọc */
    .product-list-table tbody tr {
        height: auto;
    }
    .product-list-table tbody td {
        padding: 0.55rem 0.6rem;
        font-size: 0.85rem;
    }

    /* =========================================================
       YÊU CẦU 1: STYLE MÃ HÀNG (SKU) DƯỚI TÊN SẢN PHẨM
       ========================================================= */
    .product-list-table .js-mahang {
        font-size: 0.72rem !important;
        color: #6b7280 !important;
        letter-spacing: 0.01em;
        line-height: 1.15;
    }
    .product-list-table .js-mahang i {
        color: #9ca3af;
    }

    /* =========================================================
       YÊU CẦU 2: 4 THẺ THỐNG KÊ (STAT CARDS) - TEXT-LEFT
       ========================================================= */
    .stats-cards-row { margin-top: 0.25rem; }

    .stat-card {
        position: relative;
        background: #ffffff;
        border: 1px solid #f1f3f5;
        border-radius: 12px;
        padding: 16px 18px;
        text-align: left;
        transition: all 0.18s ease;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15,23,42,0.04);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15,23,42,0.08);
        border-color: #e9ecef;
    }

    /* Icon SVG ở góc trên bên phải - màu nhạt */
    .stat-card-icon {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.85;
    }
    .stat-card-icon svg { display: block; }

    .stat-icon-blue   { background: rgba(59,130,246,0.10); color: #3b82f6; }
    .stat-icon-orange { background: rgba(245,158,11,0.10); color: #f59e0b; }
    .stat-icon-green  { background: rgba(16,185,129,0.10); color: #10b981; }
    .stat-icon-purple { background: rgba(139,92,246,0.10); color: #8b5cf6; }

    /* Style con số chính: text-2xl font-bold text-gray-800 */
    .stat-card-value {
        font-size: 1.55rem;
        line-height: 1.2;
        font-weight: 700;
        color: #1f2937;
        margin-top: 18px;
        margin-bottom: 4px;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat-card-label {
        font-size: 0.78rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Sub-text giả lập tỷ lệ tăng trưởng */
    .stat-card-growth {
        font-size: 0.72rem;
        line-height: 1.3;
        margin-top: 2px;
    }
    .stat-card-growth .text-green-500 { color: #10b981 !important; font-weight: 600; }
    .stat-card-growth .text-gray-400  { color: #9ca3af !important; }
    .stat-card-growth .text-gray-500  { color: #6b7280 !important; font-weight: 500; }

    /* =========================================================
       YÊU CẦU 4: PANEL BLOCK (Top biến thể + Đơn hàng gần nhất)
       ========================================================= */
    .panel-block {
        border: 1px solid #f1f3f5;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15,23,42,0.04);
    }
    .panel-title {
        font-size: 0.92rem;
        letter-spacing: -0.01em;
    }

    /* ---- Top biến thể: list + progress bar ---- */
    .top-variants-list {
        margin: 0;
        padding: 0;
    }
    .top-variant-item {
        padding: 8px 0;
        border-bottom: 1px dashed #f1f3f5;
    }
    .top-variant-item:last-child { border-bottom: none; padding-bottom: 0; }
    .top-variant-item:first-child { padding-top: 0; }

    .variant-name {
        font-size: 0.82rem;
        color: #1f2937;
        line-height: 1.25;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        font-size: 0.65rem;
        font-weight: 700;
        color: #6b7280;
        background: #f3f4f6;
        border-radius: 9px;
        flex-shrink: 0;
    }
    .rank-badge.rank-1 {
        color: #fff;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 1px 3px rgba(217,119,6,0.3);
    }

    .variant-progress {
        background-color: #f3f4f6 !important;
        border-radius: 999px;
        overflow: hidden;
    }
    .variant-progress .progress-bar {
        border-radius: 999px;
        transition: width 0.6s ease;
    }

    /* ---- Đơn hàng gần nhất: mini table ---- */
    .recent-orders-table {
        margin: 0 -3px;
    }
    .recent-orders-table table {
        font-size: 0.82rem;
    }
    .recent-orders-table thead th {
        background: transparent;
        border-bottom: 1px solid #f1f3f5;
        padding: 6px 8px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .recent-orders-table tbody td {
        border: none;
        border-bottom: 1px dashed #f1f3f5;
        padding: 10px 8px;
        vertical-align: middle;
    }
    .recent-order-row {
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .recent-order-row:hover td {
        background-color: #eff6ff;
    }
    .recent-order-row:hover td:first-child {
        box-shadow: inset 3px 0 0 #3b82f6;
    }
    .recent-order-row:hover {
        transform: translateX(2px);
    }
    .recent-order-row:focus {
        outline: none;
        background-color: #eff6ff;
    }
    .recent-order-row:focus td:first-child {
        box-shadow: inset 3px 0 0 #3b82f6;
    }
    .recent-order-row:last-child td {
        border-bottom: none;
    }

    .qty-badge {
        display: inline-block;
        min-width: 36px;
        padding: 2px 8px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border-radius: 10px;
    }

    .badge-status {
        display: inline-block;
        padding: 3px 10px;
        font-size: 0.7rem;
        font-weight: 600;
        border-radius: 999px;
        line-height: 1.2;
    }
    .badge-status.badge-success {
        color: #047857;
        background: #d1fae5;
    }
    .badge-status.badge-warning {
        color: #92400e;
        background: #fef3c7;
    }
    .badge-status.badge-danger {
        color: #b91c1c;
        background: #fee2e2;
    }
    .badge-status.badge-secondary {
        color: #475569;
        background: #f1f5f9;
    }

    /* =========================================================
       PROGRESSIVE DISCLOSURE — NÚT "XEM CHI TIẾT ĐẦY ĐỦ"
       Style nổi bật so với các nút phụ Sửa/Xóa:
         - gradient xanh dương
         - icon mũi tên có chuyển động nhẹ khi hover
         - viền đổ bóng
       ========================================================= */
    .btn-view-detail-full {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border: 1px solid #1d4ed8;
        color: #ffffff;
        font-weight: 600;
        letter-spacing: 0.01em;
        padding: 0.45rem 0.95rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        text-decoration: none;
    }
    .btn-view-detail-full:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.35);
    }
    .btn-view-detail-full:active {
        transform: translateY(0);
    }
    .btn-view-detail-full:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
    }
    /* Mũi tên trượt nhẹ sang phải khi hover → nhấn mạnh "chuyển trang" */
    .btn-view-detail-full:hover .btn-view-detail-arrow {
        transform: translateX(3px);
    }
    .btn-view-detail-arrow {
        transition: transform 0.18s ease;
        font-size: 0.85rem;
    }

    /* "Sửa/Xóa" thu nhỏ về dạng icon-only nút phụ */
    .panel-action-group {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding-left: 6px;
        border-left: 1px solid #e5e7eb;
        margin-left: 4px;
    }
    .panel-action-group .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Hint nhỏ dưới tiêu đề (Progressive Disclosure cue) */
    .quick-view-hint {
        font-size: 0.7rem;
        color: #6b7280;
        line-height: 1.3;
    }

    /* "Xem tất cả" link trong khối "Đơn hàng gần nhất" */
    .view-all-link {
        color: #2563eb;
        font-weight: 500;
        transition: color 0.15s ease, transform 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .view-all-link:hover {
        color: #1d4ed8;
        transform: translateX(2px);
    }
    .view-all-link i {
        transition: transform 0.15s ease;
    }
    .view-all-link:hover i {
        transform: translateX(2px);
    }

    /* Responsive: stack panels trên mobile */
    @media (max-width: 767.98px) {
        .stats-cards-row > [class*="col-"] { margin-bottom: 0.75rem; }
        .stats-bottom-row > [class*="col-"] { margin-bottom: 0.75rem; }
    }
    @media (max-width: 575.98px) {
        /* Trên mobile: ẩn text "Xem chi tiết đầy đủ", chỉ giữ icon */
        .btn-view-detail-full .btn-view-detail-label { display: none !important; }
        .panel-action-group { padding-left: 4px; }
    }
</style>
@endsection
