@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Kho hàng - SmartMart')

@section('content')

{{-- ============================================================
   Page Header
   ============================================================ --}}
<div class="mb-3">
    <h4 class="fw-bold mb-1">
        <i class="fas fa-warehouse me-2 text-primary"></i>Quản lý Kho hàng
    </h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Kho hàng</li>
        </ol>
    </nav>
</div>

{{-- Alert container --}}
<div id="kh-alert-container"></div>

{{-- ============================================================
   KHỐI LỌC + TÌM KIẾM (ĐỒNG BỘ TRANG SẢN PHẨM)
   ============================================================ --}}
<div class="card table-admin mb-4 search-filter-card"
     x-data="{ showFilter: false }">
    <div class="card-body p-3">
        <form id="khoSearchForm" method="get" action="{{ url('admin/kho-hang') }}">
            {{-- THANH CÔNG CỤ CHÍNH --}}
            <div class="d-flex flex-wrap align-items-center gap-2">

                {{-- Ô TÌM KIẾM CHÍNH --}}
                <div class="flex-grow-1" style="min-width: 260px;">
                    <div class="input-group search-input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               id="tk-filter-ten"
                               class="form-control border-start-0"
                               name="keyword"
                               value="{{ request('keyword') }}"
                               placeholder="Tìm theo tên sản phẩm, mã vạch, mã hàng...">
                    </div>
                </div>

                {{-- NÚT LỌC NÂNG CAO --}}
                <button type="button"
                        class="btn btn-advanced-filter"
                        :class="{ 'active': showFilter }"
                        @click="showFilter = !showFilter"
                        aria-expanded="false"
                        :aria-expanded="showFilter.toString()">
                    <i class="fas fa-filter me-1"></i>
                    <span>Lọc nâng cao</span>
                    <span class="badge bg-primary ms-2 filter-badge kho-filter-count" style="display:none;">0</span>
                    <i class="fas fa-chevron-down ms-2 chevron-icon"
                       :class="{ 'rotated': showFilter }"></i>
                </button>
            </div>

            {{-- PANEL LỌC NÂNG CAO --}}
            <div class="filter-panel-wrapper"
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
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label filter-label">Danh mục</label>
                            <select class="form-select form-select-sm filter-select" id="tk-filter-danh-muc" name="danh_muc_id">
                                <option value="">Tất cả danh mục</option>
                            </select>
                        </div>

                        {{-- Trạng thái tồn kho --}}
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label filter-label">Trạng thái tồn kho</label>
                            <select class="form-select form-select-sm filter-select" id="tk-filter-status" name="trang_thai_ton">
                                <option value="" @selected(request('trang_thai_ton') == '')>Tất cả</option>
                                <option value="binh-thuong" @selected(request('trang_thai_ton') == 'binh-thuong')>Bình thường</option>
                                <option value="duoi-dinh-muc" @selected(request('trang_thai_ton') == 'duoi-dinh-muc')>Dưới định mức</option>
                                <option value="het-hang" @selected(request('trang_thai_ton') == 'het-hang')>Hết hàng</option>
                            </select>
                        </div>

                        {{-- Nhà cung cấp --}}
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label filter-label">Nhà cung cấp</label>
                            <select class="form-select form-select-sm filter-select" id="tk-filter-ncc" name="nha_cung_cap">
                                <option value="">Tất cả nhà cung cấp</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-panel-footer">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-reset-filter" id="tk-btn-reset">
                                <i class="fas fa-undo me-1"></i>Thiết lập lại
                            </button>
                            <button type="submit" class="btn btn-apply-filter">
                                <i class="fas fa-check me-1"></i>Áp dụng lọc
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================
   DẢI THẺ KPI (Top Widgets)
   ============================================================ --}}
<div class="row g-3 mb-4 stats-cards-row">
    <div class="col-xl-6 col-md-6">
        <div class="stat-card kho-kpi-card shadow-sm h-100 kho-kpi-giatri" data-kpi="tong-gia-tri-kho">
            <div class="stat-card-icon stat-icon-blue">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-card-label">Tổng Giá trị Kho</div>
            <div class="stat-card-value kho-kpi-money" id="stat-tong-gia-tri-kho">
                {{ number_format($kpiTongGiaTriKho ?? 0, 0, ',', '.') }} <span class="kho-kpi-unit">đ</span>
            </div>
            <div class="kho-kpi-sub text-muted">Vốn hàng hóa đang nắm giữ</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card kho-kpi-card shadow-sm h-100" data-kpi="sp-duoi-dinh-muc">
            <div class="stat-card-icon stat-icon-red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-card-label">SP dưới định mức</div>
            <div class="stat-card-value" id="stat-sp-duoi-dinh-muc">
                {{ number_format($kpiSpDuoiDinhMuc ?? 0, 0, ',', '.') }}
            </div>
            <div class="kho-kpi-sub text-muted">Cần nhập thêm</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card kho-kpi-card shadow-sm h-100" data-kpi="sp-het-hang">
            <div class="stat-card-icon stat-icon-orange">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="stat-card-label">SP hết hàng</div>
            <div class="stat-card-value" id="stat-sp-het-hang">
                {{ number_format($kpiSpHetHang ?? 0, 0, ',', '.') }}
            </div>
            <div class="kho-kpi-sub text-muted">Tổng tồn = 0</div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="khoHangTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-ton-kho" data-bs-toggle="tab" data-bs-target="#content-ton-kho" type="button">
            <i class="fas fa-boxes me-1"></i>Tồn kho
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-lo-hang" data-bs-toggle="tab" data-bs-target="#content-lo-hang" type="button">
            <i class="fas fa-layer-group me-1"></i>Lô hàng
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('kiem-kho.index') }}" class="nav-link">
            <i class="fas fa-clipboard-check me-1"></i>Kiểm kho
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-nhap-hang" data-bs-toggle="tab" data-bs-target="#content-nhap-hang" type="button">
            <i class="fas fa-arrow-down text-success me-1"></i>Nhập hàng
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-xuat-hang" data-bs-toggle="tab" data-bs-target="#content-xuat-hang" type="button">
            <i class="fas fa-arrow-up text-danger me-1"></i>Xuất hàng
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-canh-bao" data-bs-toggle="tab" data-bs-target="#content-canh-bao" type="button">
            <i class="fas fa-triangle-exclamation me-1"></i>Cảnh báo
            <span class="badge bg-danger ms-1" id="badge-canh-bao" style="display:none">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-ncc" data-bs-toggle="tab" data-bs-target="#content-ncc" type="button">
            <i class="fas fa-truck me-1"></i>Nhà cung cấp
        </button>
    </li>
</ul>

{{-- Tab Content --}}
<div class="tab-content" id="khoHangTabContent">

    {{-- ============================================ TỒN KHO (Kế toán / Vận hành kho) ============================================ --}}
    <div class="tab-pane fade show active" id="content-ton-kho" role="tabpanel">
        <div class="card table-admin kho-table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-warehouse me-2"></i>Báo cáo Tồn kho &amp; Giá trị vốn</h5>
                <span class="text-muted small">
                    Hiển thị {{ $sanPhams->count() }} / {{ $sanPhams->total() }} sản phẩm
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle kho-table kho-table-parent">
                        <colgroup>
                            <col style="width:40px">
                            <col>
                            <col style="width:130px">
                            <col style="width:170px">
                            <col style="width:200px">
                            <col style="width:130px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center"></th>
                                <th>Sản phẩm</th>
                                <th class="text-end">Tổng Tồn</th>
                                <th class="text-end">Tổng Giá trị vốn</th>
                                <th class="text-center">Tình trạng Kho</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        @forelse($sanPhams as $sp)
                            @php
                                $tongTon = (int) $sp->tong_ton;
                                $tongGiaTriVon = (float) $sp->tong_gia_tri_ton;
                                $tinhTrang = $sp->tinh_trang;

                                $ttLabel = match ($tinhTrang) {
                                    'het-sach' => 'Hết sạch',
                                    'co-thieu' => 'Có hàng thiếu',
                                    default    => 'An toàn',
                                };
                                $ttCls = match ($tinhTrang) {
                                    'het-sach' => 'badge-status-gray',
                                    'co-thieu' => 'badge-status-red',
                                    default    => 'badge-status-green',
                                };

                                $variants = $sp->bienTheSanPhams;
                                $thumb = $variants->first()?->hinh_anh;
                                $thumbUrl = $thumb ? \App\Models\BienTheSanPham::resolveImageUrl($thumb) : null;
                            @endphp
                            <tbody x-data="{ expanded: false }" class="kho-group">
                                {{-- Dòng cha (click để expand) --}}
                                <tr class="clickable-row sp-parent-row"
                                    :class="{ 'is-expanded': expanded }"
                                    @click="expanded = !expanded">
                                    <td class="text-center">
                                        <i class="fas fa-chevron-right toggle-icon"
                                           :class="{ 'rotated': expanded }"></i>
                                    </td>
                                    <td class="product-cell">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($thumbUrl)
                                                <img src="{{ $thumbUrl }}" alt="" class="kho-row-thumb">
                                            @else
                                                <span class="kho-row-thumb placeholder">
                                                    <i class="fas fa-box"></i>
                                                </span>
                                            @endif
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="product-name">{{ $sp->ten_san_pham }}</div>
                                                <div class="product-meta">
                                                    @if($sp->danhMuc)
                                                        <i class="fas fa-folder-open"></i>{{ $sp->danhMuc->ten_danh_muc }}
                                                    @endif
                                                    @if($sp->thuong_hieu)
                                                        <span class="mx-1">·</span>
                                                        <i class="fas fa-tag"></i>{{ $sp->thuong_hieu }}
                                                    @endif
                                                    <span class="mx-1">·</span>
                                                    {{ $variants->count() }} biến thể
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="num-cell text-end">
                                        <span class="num-value {{ $tongTon > 0 ? 'text-dark' : 'text-muted' }}">
                                            {{ number_format($tongTon, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="kho-money">
                                            {{ number_format($tongGiaTriVon, 0, ',', '.') }} <span class="kho-money-unit">đ</span>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge {{ $ttCls }}">
                                            <span class="status-dot"></span>
                                            {{ $ttLabel }}
                                        </span>
                                    </td>
                                    <td class="text-center" @click.stop>
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                    class="btn btn-icon btn-icon-soft-primary"
                                                    title="Xem chi tiết"
                                                    @click="expanded = !expanded">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Dòng Dropdown — Bảng báo cáo biến thể (đơn) --}}
                                <tr class="sp-expand-row"
                                    x-show="expanded"
                                    x-collapse
                                    x-cloak>
                                    <td colspan="6" class="p-0 border-0">
                                        <div class="kho-dropdown">
                                            @if($variants->isEmpty())
                                                <div class="kho-empty">Sản phẩm này chưa có biến thể.</div>
                                            @else
                                                <div class="kho-report-header">
                                                    <i class="fas fa-list-ul me-2"></i>
                                                    Báo cáo chi tiết biến thể
                                                    <span class="ms-2 kho-report-count">{{ $variants->count() }} dòng</span>
                                                </div>
                                                <div class="kho-nested-wrap">
                                                    <table class="kho-nested-table kho-report-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Biến thể</th>
                                                                <th>Mã Vạch</th>
                                                                <th class="text-end">Tồn / Định mức</th>
                                                                <th class="text-end">Giá trị vốn</th>
                                                                <th class="text-center">Trạng thái</th>
                                                                <th class="text-center">Thao tác</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($variants as $bt)
                                                                @php
                                                                    $sl = (int) ($bt->so_luong_ton ?? 0);
                                                                    $dm = (int) ($bt->dinh_muc_toi_thieu ?? 0);
                                                                    $giaVon = (float) ($bt->gia_von ?? 0);
                                                                    $giaTriVonBt = $sl * $giaVon;

                                                                    // Tình trạng biến thể (dùng Dot indicator)
                                                                    if ($sl <= 0) {
                                                                        $dotCls = 'dot-gray';
                                                                        $statusLabel = 'Hết hàng';
                                                                        $numCls = '';
                                                                    } elseif ($sl < $dm) {
                                                                        $dotCls = 'dot-red';
                                                                        $statusLabel = 'Dưới định mức';
                                                                        $numCls = 'text-red-600 fw-bold';
                                                                    } else {
                                                                        $dotCls = 'dot-green';
                                                                        $statusLabel = 'An toàn';
                                                                        $numCls = '';
                                                                    }

                                                                    $tenHienThi = $bt->ten_bien_the ?: $bt->ten_don_vi ?: ('Variant #' . $bt->id);
                                                                    $maVach = $bt->ma_vach ?: ($bt->ma_hang ?: '—');
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <div class="variant-name">{{ $tenHienThi }}</div>
                                                                    </td>
                                                                    <td>
                                                                        <code class="kho-code">{{ $maVach }}</code>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <div class="kho-ton-stack">
                                                                            <span class="num-value {{ $numCls }}">{{ number_format($sl, 0, ',', '.') }}</span>
                                                                            <span class="kho-ton-divider">/</span>
                                                                            <span class="kho-ton-dm">{{ number_format($dm, 0, ',', '.') }}</span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="kho-money kho-money-sm">
                                                                            {{ number_format($giaTriVonBt, 0, ',', '.') }} <span class="kho-money-unit">đ</span>
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="status-dot-cell {{ $dotCls }}" title="{{ $statusLabel }}">
                                                                            <span class="status-dot"></span>
                                                                            <span class="status-label">{{ $statusLabel }}</span>
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center" @click.stop>
                                                                        <div class="d-inline-flex gap-1">
                                                                            <button type="button"
                                                                                    class="btn btn-icon btn-icon-soft-primary btn-xem-lo-bt"
                                                                                    data-variant-id="{{ $bt->id }}"
                                                                                    data-sp-id="{{ $sp->id }}"
                                                                                    data-sp-name="{{ $sp->ten_san_pham }}"
                                                                                    data-bt-name="{{ $tenHienThi }}"
                                                                                    title="Xem chi tiết Lô (FEFO)">
                                                                                <i class="fas fa-boxes-stacked"></i>
                                                                            </button>
                                                                            <button type="button"
                                                                                    class="btn btn-icon btn-icon-soft-secondary"
                                                                                    title="In tem vạch"
                                                                                    onclick="alert('Tính năng in tem vạch đang được phát triển.'); return false;">
                                                                                <i class="fas fa-barcode"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        Không có sản phẩm nào trong kho.
                                    </td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted small">
                        Trang {{ $sanPhams->currentPage() }} / {{ $sanPhams->lastPage() }}
                    </span>
                    {{ $sanPhams->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ LÔ HÀNG ============================================ --}}
    <div class="tab-pane fade" id="content-lo-hang" role="tabpanel">
        <div class="card table-admin mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" id="lh-filter-ma" placeholder="Mã lô...">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm" onclick="loadLoHang(1)"><i class="fas fa-filter me-1"></i>Lọc</button>
                        <a href="{{ url('admin/kho-hang') }}" class="btn btn-light border btn-sm"><i class="fas fa-redo"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã lô</th>
                                <th>NCC</th>
                                <th class="text-center">Ngày nhập</th>
                                <th class="text-center">Số lượng nhập</th>
                                <th class="text-center">Còn lại</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="lh-bang"></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="lh-thong-tin"></span>
                    <nav id="lh-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ NHẬP HÀNG ============================================ --}}
    <div class="tab-pane fade" id="content-nhap-hang" role="tabpanel">
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button class="btn btn-outline-success btn-sm" id="pn-btn-export"><i class="fas fa-file-excel me-1"></i>Xuất Excel</button>
            <button class="btn btn-outline-success btn-sm" id="pn-btn-import"><i class="fas fa-file-import me-1"></i>Import Excel</button>
            <a href="{{ route('phieu-nhap.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i>Tạo nhập hàng
            </a>
        </div>
        <div class="card table-admin mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="pn-filter-loai">
                            <option value="">Tất cả loại</option>
                            <option value="mua_hang">Nhập mua hàng</option>
                            <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="pn-filter-tu">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="pn-filter-den">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm" onclick="loadPhieuNhap(1)"><i class="fas fa-filter me-1"></i>Lọc</button>
                        <a href="{{ url('admin/kho-hang') }}" class="btn btn-light border btn-sm"><i class="fas fa-redo"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã phiếu</th>
                                <th>Loại</th>
                                <th>NCC</th>
                                <th>Người tạo</th>
                                <th class="text-center">Ngày</th>
                                <th class="text-center">Tổng giá trị</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="pn-bang"></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="pn-thong-tin"></span>
                    <nav id="pn-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ XUẤT HÀNG ============================================ --}}
    <div class="tab-pane fade" id="content-xuat-hang" role="tabpanel">
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button class="btn btn-outline-danger btn-sm" id="px-btn-export"><i class="fas fa-file-excel me-1"></i>Xuất Excel</button>
            <a href="{{ route('phieu-xuat.create') }}" class="btn btn-danger btn-sm" id="px-btn-tao"><i class="fas fa-plus me-1"></i>Tạo phiếu xuất</a>
        </div>
        <div class="card table-admin mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="px-filter-loai">
                            <option value="">Tất cả loại</option>
                            <option value="tieu_huy">Tiêu hủy</option>
                            <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="px-filter-tu">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" id="px-filter-den">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm" onclick="loadPhieuXuat(1)"><i class="fas fa-filter me-1"></i>Lọc</button>
                        <a href="{{ url('admin/kho-hang') }}" class="btn btn-light border btn-sm"><i class="fas fa-redo"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã phiếu</th>
                                <th>Loại</th>
                                <th>NCC</th>
                                <th>Lý do</th>
                                <th>Người tạo</th>
                                <th class="text-center">Ngày</th>
                                <th class="text-center">Tổng SL</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="px-bang"></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="px-thong-tin"></span>
                    <nav id="px-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ CẢNH BÁO ============================================ --}}
    <div class="tab-pane fade" id="content-canh-bao" role="tabpanel">
        <div class="row g-4" id="canh-bao-content">
            <div class="text-center text-muted py-5 col-12">Đang tải cảnh báo...</div>
        </div>
    </div>

    {{-- ============================================ NHÀ CUNG CẤP ============================================ --}}
    <div class="tab-pane fade" id="content-ncc" role="tabpanel">
        <div class="d-flex justify-content-between mb-3 gap-2">
            <div class="input-group input-group-sm" style="max-width:300px">
                <input type="text" class="form-control" id="ncc-search" placeholder="Tìm tên NCC...">
                <button class="btn btn-outline-primary" id="ncc-btn-search" type="button"><i class="fas fa-search"></i></button>
                <button class="btn btn-outline-secondary" id="ncc-btn-refresh" type="button"><i class="fas fa-redo"></i></button>
            </div>
            <button class="btn btn-primary btn-sm" id="ncc-btn-them"><i class="fas fa-plus me-1"></i>Thêm nhà cung cấp</button>
        </div>
        <div class="card table-admin">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên NCC</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Người đại diện</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="ncc-bang"></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small" id="ncc-thong-tin"></span>
                    <nav id="ncc-phan-trang"></nav>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('modals')

@php $today = date('Y-m-d'); @endphp

{{-- ===================== MODAL: XUẤT EXCEL NHẬP HÀNG ===================== --}}
<div class="modal fade" id="modal-xuat-pn" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-xuat-pn">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-file-excel me-2"></i>Xuất Excel nhập hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Loại phiếu</label>
                        <select id="xuat-pn-loai" class="form-select">
                            <option value="">Tất cả loại</option>
                            <option value="mua_hang">Nhập mua hàng</option>
                            <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                        </select>
                    </div>

                    <label class="form-label">Khoảng thời gian</label>
                    <div class="btn-group btn-group-sm d-flex flex-wrap mb-2" role="group">
                        <button type="button" class="btn btn-outline-secondary pn-preset" data-preset="today">Hôm nay</button>
                        <button type="button" class="btn btn-outline-secondary pn-preset" data-preset="7days">7 ngày qua</button>
                        <button type="button" class="btn btn-outline-secondary pn-preset" data-preset="this_month">Tháng này</button>
                        <button type="button" class="btn btn-outline-secondary pn-preset" data-preset="last_month">Tháng trước</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">Từ ngày</label>
                            <input type="date" id="xuat-pn-tu" class="form-control" max="{{ $today }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Đến ngày</label>
                            <input type="date" id="xuat-pn-den" class="form-control" max="{{ $today }}">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Nếu bỏ trống cả 2 ô, hệ thống sẽ xuất toàn bộ dữ liệu.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Tải Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: XUẤT EXCEL XUẤT HÀNG ===================== --}}
<div class="modal fade" id="modal-xuat-px" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-xuat-px">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-file-excel me-2"></i>Xuất Excel xuất hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Loại phiếu</label>
                        <select id="xuat-px-loai" class="form-select">
                            <option value="">Tất cả loại</option>
                            <option value="tieu_huy">Tiêu hủy</option>
                            <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                        </select>
                    </div>

                    <label class="form-label">Khoảng thời gian</label>
                    <div class="btn-group btn-group-sm d-flex flex-wrap mb-2" role="group">
                        <button type="button" class="btn btn-outline-secondary px-preset" data-preset="today">Hôm nay</button>
                        <button type="button" class="btn btn-outline-secondary px-preset" data-preset="7days">7 ngày qua</button>
                        <button type="button" class="btn btn-outline-secondary px-preset" data-preset="this_month">Tháng này</button>
                        <button type="button" class="btn btn-outline-secondary px-preset" data-preset="last_month">Tháng trước</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">Từ ngày</label>
                            <input type="date" id="xuat-px-tu" class="form-control" max="{{ $today }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Đến ngày</label>
                            <input type="date" id="xuat-px-den" class="form-control" max="{{ $today }}">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Nếu bỏ trống cả 2 ô, hệ thống sẽ xuất toàn bộ dữ liệu.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-download me-1"></i>Tải Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: THÊM NHÀ CUNG CẤP ===================== --}}
<div class="modal fade" id="modal-them-ncc" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-them-ncc" autocomplete="off">@csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Thêm nhà cung cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" name="ten_nha_cung_cap" id="ncc_them_ten" class="form-control" required>
                        <div class="invalid-feedback" id="ncc_them_ten_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="ncc_them_email" class="form-control">
                        <div class="invalid-feedback" id="ncc_them_email_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="so_dien_thoai" id="ncc_them_sdt" class="form-control" required>
                        <div class="invalid-feedback" id="ncc_them_sdt_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người đại diện</label>
                        <input type="text" name="nguoi_dai_dien" id="ncc_them_nguoi" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="ncc_btn_luu"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: SỬA NHÀ CUNG CẤP ===================== --}}
<div class="modal fade" id="modal-sua-ncc" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-sua-ncc" autocomplete="off">@csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa nhà cung cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ncc_sua_id">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" name="ten_nha_cung_cap" id="ncc_sua_ten" class="form-control" required>
                        <div class="invalid-feedback" id="ncc_sua_ten_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="ncc_sua_email" class="form-control">
                        <div class="invalid-feedback" id="ncc_sua_email_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="so_dien_thoai" id="ncc_sua_sdt" class="form-control" required>
                        <div class="invalid-feedback" id="ncc_sua_sdt_err"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người đại diện</label>
                        <input type="text" name="nguoi_dai_dien" id="ncc_sua_nguoi" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="ncc_btn_cap_nhat"><i class="fas fa-save me-1"></i>Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: XEM CHI TIẾT LÔ HÀNG ===================== --}}
<div class="modal fade" id="modal-xem-lo" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-layer-group me-2 text-primary"></i>Chi tiết lô hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-xem-lo-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL: FEFO THEO BIẾN THỂ (Tồn kho cha → Con) ===================== --}}
<div class="modal fade" id="modal-fefo-bt" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2 text-primary"></i>
                    <span id="fefo-bt-title">Chi tiết lô hàng (FEFO)</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="fefo-bt-body">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin me-1"></i>Đang tải...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== MODAL: THÊM LÔ HÀNG ===================== --}}
<div class="modal fade" id="modal-them-lo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-them-lo">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-layer-group me-2"></i>Thêm lô hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="id_nha_cung_cap" id="lo-ncc" class="form-select">
                            <option value="">-- Chọn NCC --</option>
                            @foreach($nhaCungCaps as $ncc)
                                <option value="{{ $ncc->id }}">{{ $ncc->ten_nha_cung_cap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày nhập</label>
                        <input type="date" name="ngay_nhap" id="lo-ngay-nhap" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== MODAL: IMPORT PHIẾU NHẬP ===================== --}}
<div class="modal fade" id="modal-import-pn" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import Phiếu Nhập từ Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-import-pn">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Loại nhập <span class="text-danger">*</span></label>
                        <select name="loai_nhap" class="form-select" id="import-pn-loai" required>
                            <option value="mua_hang">Nhập mua hàng</option>
                            <option value="tra_lai_tu_khach">Trả lại từ khách</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="id_nha_cung_cap" id="import-pn-ncc" class="form-select">
                            <option value="">-- Chọn NCC --</option>
                            @foreach($nhaCungCaps as $ncc)
                                <option value="{{ $ncc->id }}">{{ $ncc->ten_nha_cung_cap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="ghi_chu" id="import-pn-ghi-chu" class="form-control" placeholder="Ghi chú phiếu nhập...">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" id="import-pn-file" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <small class="text-muted">Hỗ trợ định dạng .xlsx, .xls, .csv</small>
                    </div>
                    <div class="drop-zone border rounded p-4 text-center" id="drop-zone-pn" style="border-style: dashed; background: #f8f9fa;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-0 text-muted">Kéo thả file Excel vào đây</p>
                        <small class="text-muted">hoặc nhấn "Chọn file" ở trên</small>
                    </div>
                    <div id="import-pn-preview" class="mt-2 d-none">
                        <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                            <i class="fas fa-file-excel me-2 text-success"></i>
                            <span id="import-pn-filename"></span>
                            <button type="button" class="btn-close ms-auto" id="import-pn-remove"></button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-outline-primary" id="import-pn-download-template"><i class="fas fa-download me-1"></i>Tải file mẫu</button>
                    <button type="submit" class="btn btn-success" id="import-pn-submit" disabled><i class="fas fa-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/kho-hang.js') }}?v={{ filemtime(public_path('js/admin/kho-hang.js')) }}"></script>
@endsection

@section('page_scripts')
{{-- Alpine.js + plugin x-collapse để có hiệu ứng xổ xuống mượt mà cho Tab Tồn kho --}}
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.5/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/kho-hang.css') }}">
<style>
    [v-cloak] { display: none !important; }

    /* =========================================================
       KHO HÀNG — Refresh UI (đồng bộ phong cách trang Sản phẩm)
       - Search/filter card, panel lọc nâng cao: kế thừa hoàn toàn từ
         san-pham.css (đã có .search-filter-card, .btn-advanced-filter,
         .search-input-group, .filter-panel, .filter-label...)
       - Chỉ cần fix màu nền card-stat để không phản đè Bootstrap.
       ========================================================= */

    /* 4 thẻ KPI: áp dụng style .stat-card có sẵn, đồng thời tăng shadow + rounded-xl */
    .kho-kpi-card {
        position: relative;
        background: #ffffff;
        border: 1px solid #f1f3f5;
        border-radius: 12px;
        padding: 16px 18px;
        text-align: left;
        transition: all 0.18s ease;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .kho-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        border-color: #e9ecef;
    }
    /* Icon ở góc phải trên — vuông bo, màu nhạt + căn text bên trái */
    .kho-kpi-card .stat-card-icon {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .kho-kpi-card .stat-card-icon i { line-height: 1; }
    .kho-kpi-card .stat-card-label {
        font-size: 0.78rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        max-width: 70%;
    }
    .kho-kpi-card .stat-card-value {
        font-size: 1.55rem;
        line-height: 1.2;
        font-weight: 700;
        color: #1f2937;
        margin-top: 18px;
        margin-bottom: 4px;
        letter-spacing: -0.01em;
    }
    /* Đảm bảo icon màu nhã nhặn, dùng tone Bootstrap */
    .kho-kpi-card .stat-icon-blue   { background: rgba(59, 130, 246, 0.10); color: #3b82f6; }
    .kho-kpi-card .stat-icon-green  { background: rgba(16, 185, 129, 0.10); color: #10b981; }
    .kho-kpi-card .stat-icon-orange { background: rgba(245, 158, 11, 0.10); color: #f59e0b; }
    .kho-kpi-card .stat-icon-red    { background: rgba(220, 53, 69, 0.10); color: #dc3545; }

    /* =========================================================
       KPI — góc nhìn Kế toán (giá trị kho)
       ========================================================= */
    .kho-kpi-card.kho-kpi-giatri {
        background: linear-gradient(135deg, #ffffff 60%, rgba(59, 130, 246, 0.06) 100%);
        border-left: 3px solid #3b82f6;
    }
    .kho-kpi-card .kho-kpi-sub {
        font-size: 0.72rem;
        margin-top: 6px;
        letter-spacing: 0.02em;
    }
    .kho-kpi-card .kho-kpi-money {
        font-variant-numeric: tabular-nums;
        font-size: 1.65rem;
        line-height: 1.1;
    }
    .kho-kpi-card .kho-kpi-unit {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6b7280;
        margin-left: 1px;
    }

    /* =========================================================
       TRẠNG THÁI — Dot indicator + Badge (Sản phẩm cha)
       ========================================================= */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }
    .status-badge .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        display: inline-block;
    }
    .badge-status-green {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .badge-status-green .status-dot { background: #10b981; }
    .badge-status-red {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .badge-status-red .status-dot { background: #ef4444; }
    .badge-status-gray {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .badge-status-gray .status-dot { background: #94a3b8; }

    /* =========================================================
       TRẠNG THÁI — Biến thể (Dot indicator thuần, không pill)
       ========================================================= */
    .status-dot-cell {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        font-weight: 500;
        line-height: 1;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .status-dot-cell .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        display: inline-block;
    }
    .status-dot-cell.dot-green {
        background: rgba(16, 185, 129, 0.08);
        color: #047857;
    }
    .status-dot-cell.dot-green .status-dot { background: #10b981; }
    .status-dot-cell.dot-red {
        background: rgba(239, 68, 68, 0.08);
        color: #b91c1c;
    }
    .status-dot-cell.dot-red .status-dot { background: #ef4444; }
    .status-dot-cell.dot-gray {
        background: #f1f5f9;
        color: #475569;
    }
    .status-dot-cell.dot-gray .status-dot { background: #94a3b8; }

    /* =========================================================
       TIỀN TỆ (ké toán style)
       ========================================================= */
    .kho-money {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        color: #0f172a;
        font-size: 0.92rem;
        white-space: nowrap;
    }
    .kho-money-unit {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        margin-left: 1px;
    }
    .kho-money-sm {
        font-size: 0.85rem;
    }

    /* Code style cho mã vạch / mã hàng */
    .kho-code {
        font-family: 'SF Mono', Menlo, Consolas, monospace;
        font-size: 0.78rem;
        background: #f8fafc;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        color: #334155;
    }

    /* Tồn / Định mức — layout dọc */
    .kho-ton-stack {
        display: inline-flex;
        align-items: baseline;
        gap: 4px;
        justify-content: flex-end;
    }
    .kho-ton-divider {
        color: #94a3b8;
        font-weight: 400;
        font-size: 0.85rem;
    }
    .kho-ton-dm {
        color: #64748b;
        font-weight: 500;
        font-size: 0.78rem;
    }

    /* =========================================================
       BÁO CÁO BIẾN THỂ — header thu gọn
       ========================================================= */
    .kho-report-header {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        margin-bottom: 0.6rem;
        padding-bottom: 0.4rem;
        border-bottom: 1px dashed #e2e8f0;
    }
    .kho-report-count {
        font-size: 0.7rem;
        background: #e2e8f0;
        color: #475569;
        padding: 1px 8px;
        border-radius: 999px;
        font-weight: 700;
        text-transform: none;
        letter-spacing: 0;
    }

    /* =========================================================
       BẢNG TỒN KHO — Reset nền dòng, gộp Mã + Tên
       ========================================================= */
    .kho-table-card .card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f3f5;
    }
    .kho-table thead th {
        background: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: #6b7280;
        border-bottom: 1px solid #e9ecef;
        padding: 0.7rem 0.75rem;
        white-space: nowrap;
    }
    .kho-table tbody td {
        padding: 0.7rem 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    /* Reset nền dòng: KHÔNG tô màu vàng/xám cho SP dưới định mức/hết hàng */
    .kho-table tbody tr {
        background: #ffffff;
    }
    .kho-table tbody tr:hover td {
        background: #f9fafb;
    }

    /* Cột "Sản phẩm" — layout 2 dòng: Tên (đậm) + Mã (xám nhỏ) */
    .kho-table .product-cell {
        line-height: 1.25;
    }
    .kho-table .product-cell .product-name {
        display: block;
        font-weight: 600;
        color: #111827;
        font-size: 0.875rem;
        margin-bottom: 2px;
    }
    .kho-table .product-cell .product-meta {
        display: block;
        color: #6b7280;
        font-size: 0.72rem;
        letter-spacing: 0.01em;
    }
    .kho-table .product-cell .product-meta i {
        margin-right: 2px;
        opacity: 0.7;
    }

    /* Cột số: canh phải + font bán đậm */
    .kho-table .num-cell {
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-weight: 500;
        color: #1f2937;
    }
    .kho-table .num-cell .num-value {
        font-size: 0.9rem;
    }
    /* Cảnh báo tinh tế: tồn kho < định mức (hoặc = 0) → tô đỏ/cam đậm */
    .kho-table .num-cell.is-warn .num-value {
        color: #dc2626;
        font-weight: 700;
    }
    .kho-table .num-cell.is-warn-soft .num-value {
        color: #ea580c;
        font-weight: 700;
    }

    /* Cột "Trạng thái": badge mềm (pill) */
    .kho-table .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        font-size: 0.7rem;
        font-weight: 600;
        border-radius: 999px;
        line-height: 1.4;
    }
    .kho-table .status-pill.ok       { background: #d1fae5; color: #065f46; }
    .kho-table .status-pill.warn     { background: #fef3c7; color: #92400e; }
    .kho-table .status-pill.danger   { background: #fee2e2; color: #b91c1c; }
    .kho-table .status-pill.muted    { background: #f1f5f9; color: #475569; }

    /* Toggle mũi tên xổ xuống */
    .kho-table .toggle-icon {
        color: #9ca3af;
        font-size: 0.75rem;
        transition: transform 0.2s ease;
    }
    .kho-table tr.is-expanded .toggle-icon {
        transform: rotate(90deg);
        color: #374151;
    }
    .kho-table tr.clickable-row {
        cursor: pointer;
    }

    /* =========================================================
       DÒNG MỞ RỘNG — Expandable Row (bg-slate-50, padding, border)
       Lưu ý: <tr> dùng x-collapse của Alpine và .kho-dropdown panel
       - Style chính được override ở .kho-table-parent .sp-expand-row bên dưới
       ========================================================= */

    /* Cụm "TỔNG TỒN KHO" + "TỒN KHO THEO ĐƠN VỊ" — dùng cho modal FEFO */
    .kho-summary {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.85rem 1rem;
    }
    .kho-summary .kho-summary-title {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }
    .kho-summary .kho-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.35rem 0;
        border-bottom: 1px dashed #f3f4f6;
        font-size: 0.85rem;
    }
    .kho-summary .kho-info-row:last-child { border-bottom: 0; }
    .kho-summary .kho-info-label {
        color: #6b7280;
        font-weight: 500;
    }
    .kho-summary .kho-info-value {
        color: #111827;
        font-weight: 600;
    }

    /* Bảng lô hàng (FEFO) — nested table */
    .kho-fefo {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }
    .kho-fefo .kho-fefo-title {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        padding: 0.7rem 1rem 0.5rem;
        margin: 0;
        background: #f9fafb;
        border-bottom: 1px solid #f1f3f5;
    }
    .kho-fefo table {
        width: 100%;
        font-size: 0.82rem;
        border-collapse: collapse;
    }
    .kho-fefo table thead th {
        background: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: #6b7280;
        border-bottom: 1px solid #e9ecef;
        padding: 0.55rem 0.75rem;
        white-space: nowrap;
        text-align: left;
    }
    .kho-fefo table thead th.text-right { text-align: right; }
    .kho-fefo table tbody td {
        padding: 0.55rem 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 0.82rem;
        color: #1f2937;
    }
    .kho-fefo table tbody td.text-right { text-align: right; }
    .kho-fefo table tbody tr:last-child td { border-bottom: 0; }
    .kho-fefo table tbody tr:hover td { background: #f9fafb; }
    .kho-fefo .hsd-warn   { color: #ea580c; font-weight: 600; }
    .kho-fefo .hsd-danger { color: #dc2626; font-weight: 600; }
    .kho-fefo .hsd-ok     { color: #15803d; }
    .kho-fefo .kho-fefo-loading,
    .kho-fefo .kho-fefo-empty {
        padding: 1.25rem;
        text-align: center;
        color: #6b7280;
        font-size: 0.85rem;
    }

    /* Mã lô nhỏ gọn */
    .kho-fefo .lo-badge-mini {
        display: inline-block;
        padding: 1px 8px;
        background: #1f2937;
        color: #ffffff;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    /* =========================================================
       PARENT ROW (Sản phẩm cha) - toggle icon + thumb
       ========================================================= */
    .kho-table-parent .sp-parent-row { cursor: pointer; }
    .kho-table-parent .sp-parent-row .toggle-icon {
        color: #9ca3af;
        font-size: 0.75rem;
        transition: transform 0.18s ease, color 0.18s ease;
    }
    .kho-table-parent .sp-parent-row.is-expanded .toggle-icon {
        transform: rotate(90deg);
        color: #374151;
    }
    .kho-table-parent .kho-row-thumb {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        flex-shrink: 0;
    }
    .kho-table-parent .kho-row-thumb.placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    /* =========================================================
       EXPAND ROW (bg-slate-50 panel chứa nested table)
       - <td> đã có class="p-0 border-0" nên không cần padding ở đây.
       - .kho-dropdown bên trong tự chịu trách nhiệm background.
       ========================================================= */
    .kho-table-parent .sp-expand-row > td {
        background: #f8fafc;
    }

    /* =========================================================
       NESTED TABLE — Danh sách biến thể con
       - Viền mỏng, không nhầm với bảng cha
       ========================================================= */
    .kho-nested-wrap {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }
    .kho-nested-wrap .kho-nested-title {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        padding: 0.65rem 0.9rem;
        margin: 0;
        background: #f9fafb;
        border-bottom: 1px solid #f1f3f5;
    }
    .kho-nested-table {
        width: 100%;
        font-size: 0.82rem;
        border-collapse: collapse;
        margin: 0;
    }
    .kho-nested-table thead th {
        background: #ffffff;
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
        padding: 0.55rem 0.85rem;
        white-space: nowrap;
        text-align: left;
    }
    .kho-nested-table thead th.text-end { text-align: right; }
    .kho-nested-table tbody td {
        padding: 0.55rem 0.85rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 0.82rem;
        color: #1f2937;
    }
    .kho-nested-table tbody td.text-end { text-align: right; }
    .kho-nested-table tbody tr:last-child td { border-bottom: 0; }
    .kho-nested-table tbody tr:hover td { background: #f9fafb; }
    .kho-nested-table .variant-name {
        display: block;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }
    .kho-nested-table .variant-meta {
        display: block;
        font-size: 0.72rem;
        color: #6b7280;
        letter-spacing: 0.01em;
    }
    .kho-nested-table .num-value {
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        color: #1f2937;
    }
    .kho-nested-table .num-value.text-red-600 { color: #dc2626; }

    /* =========================================================
       TBODY GROUP (mỗi sản phẩm cha = 1 <tbody> riêng)
       - Đảm bảo hover chỉ áp dụng cho dòng cha, không lan
       sang dòng dropdown bên dưới.
       ========================================================= */
    .kho-table-parent tbody.kho-group > tr.sp-parent-row:hover > td {
        background: #f9fafb;
    }
    .kho-table-parent tbody.kho-group > tr.sp-expand-row:hover > td {
        background: #f8fafc; /* bg-slate-50 */
    }

    /* =========================================================
       DROP-DOWN PANEL (Mini-Dashboard)
       ========================================================= */
    .kho-dropdown {
        background: #f8fafc;          /* bg-slate-50 */
        padding: 1.25rem;             /* p-5 */
        border-bottom: 1px solid #e5e7eb;
        box-shadow: inset 0 2px 4px 0 rgba(15, 23, 42, 0.04);
    }

    /* =========================================================
       TABS (UI only, Alpine @click)
       ========================================================= */
    .kho-tabs {
        display: flex;
        gap: 0.25rem;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 1rem;
    }
    .kho-tab {
        background: transparent;
        border: 0;
        padding: 0.55rem 0.95rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #64748b;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .kho-tab:hover { color: #334155; }
    .kho-tab.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background: #ffffff;
        border-radius: 6px 6px 0 0;
    }
    .kho-tab-count {
        background: #e2e8f0;
        color: #475569;
        font-size: 0.7rem;
        padding: 1px 7px;
        border-radius: 999px;
        margin-left: 4px;
        font-weight: 700;
    }
    .kho-tab.active .kho-tab-count {
        background: #dbeafe;
        color: #1d4ed8;
    }

    /* =========================================================
       NESTED TABLE (Biến thể) — re-style cho Mini-Dashboard
       ========================================================= */
    .kho-nested-wrap {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
    }
    .kho-nested-table {
        width: 100%;
        font-size: 0.82rem;
        border-collapse: collapse;
        margin: 0;
    }
    .kho-nested-table thead th {
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.6rem 0.85rem;
        white-space: nowrap;
        text-align: left;
    }
    .kho-nested-table thead th.text-end { text-align: right; }
    .kho-nested-table tbody td {
        padding: 0.55rem 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.82rem;
        color: #1f2937;
    }
    .kho-nested-table tbody td.text-end { text-align: right; }
    .kho-nested-table tbody tr:last-child td { border-bottom: 0; }
    .kho-nested-table tbody tr:hover td { background: #f8fafc; }
    .kho-nested-table .variant-name {
        display: block;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }
    .kho-nested-table .variant-meta {
        display: block;
        font-size: 0.72rem;
        color: #6b7280;
        letter-spacing: 0.01em;
    }

    /* =========================================================
       PROGRESS BAR (Tồn / Định mức)
       ========================================================= */
    .kho-ton-block {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }
    .kho-ton-line {
        display: flex;
        align-items: baseline;
        gap: 6px;
        font-variant-numeric: tabular-nums;
    }
    .kho-ton-block .num-value {
        font-weight: 700;
        color: #1f2937;
        font-size: 0.92rem;
    }
    .kho-ton-divider {
        color: #94a3b8;
        font-weight: 400;
        font-size: 0.85rem;
    }
    .kho-ton-dm {
        color: #64748b;
        font-weight: 500;
        font-size: 0.78rem;
    }
    .kho-progress {
        width: 100%;
        max-width: 180px;
        height: 5px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .kho-progress-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.3s ease;
    }
    .kho-progress-bar.kho-bar-ok    { background: #10b981; }   /* xanh */
    .kho-progress-bar.kho-bar-warn  { background: #f59e0b; }   /* cam */
    .kho-progress-bar.kho-bar-empty { background: #cbd5e1; width: 0%; }  /* xám, ẩn */

    /* =========================================================
       ICON BUTTONS (Xem chi tiết, Kiểm kho nhanh)
       ========================================================= */
    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid transparent;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
    }
    .btn-icon:hover { transform: translateY(-1px); }
    .btn-icon-soft-primary {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }
    .btn-icon-soft-primary:hover {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .btn-icon-soft-success {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }
    .btn-icon-soft-success:hover {
        background: #d1fae5;
        color: #065f46;
    }
    .btn-icon-soft-secondary {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }
    .btn-icon-soft-secondary:hover {
        background: #e2e8f0;
        color: #1f2937;
    }

    /* =========================================================
       FEFO summary (Tab 2 trong dropdown)
       ========================================================= */
    .kho-fefo-summary {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
    }
    .kho-fefo-summary .kho-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.45rem 0;
        border-bottom: 1px dashed #f1f5f9;
        font-size: 0.85rem;
    }
    .kho-fefo-summary .kho-info-row:last-child { border-bottom: 0; }
    .kho-fefo-summary .kho-info-label {
        color: #64748b;
        font-weight: 500;
    }
    .kho-fefo-summary .kho-info-value {
        color: #111827;
        font-weight: 600;
    }
    .kho-empty {
        padding: 1.25rem;
        text-align: center;
        color: #64748b;
        font-size: 0.85rem;
        background: #ffffff;
        border: 1px dashed #e2e8f0;
        border-radius: 10px;
    }

    /* =========================================================
       x-cloak: ẩn phần tử Alpine trước khi init
       - Tránh flash hiển thị row dropdown trước khi Alpine xử lý
       ========================================================= */
    [x-cloak] { display: none !important; }
</style>
@endsection
