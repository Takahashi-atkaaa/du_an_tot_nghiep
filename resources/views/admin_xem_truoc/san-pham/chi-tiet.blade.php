@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chi tiết sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/san-pham') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active">{{ $product->ten_san_pham }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/san-pham/edit/' . $product->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Chỉnh sửa
        </a>
        <a href="{{ url('admin/san-pham') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-4">
        {{-- ============================================================
            CỘT TRÁI: CARD THÔNG TIN SẢN PHẨM
            - Ảnh bọc trong khung viền rounded-xl p-2
            - Danh sách info dạng flex (label trái / value phải)
            - Pill badge cho danh mục, trạng thái
            ============================================================ --}}
        <div class="detail-card mb-4">
            <div class="detail-card-header">
                <h6><i class="fas fa-info-circle text-blue-600 me-2"></i>Thông tin sản phẩm</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    @if($product->variants->count() > 0 && $product->variants->first()->hinh_anh && \App\Models\BienTheSanPham::hasImageFile($product->variants->first()->hinh_anh))
                        <img src="{{ \App\Models\BienTheSanPham::resolveImageUrl($product->variants->first()->hinh_anh) }}"
                             alt="{{ $product->ten_san_pham }}"
                             class="rounded-3 shadow-sm"
                             style="width: 100%; max-height: 260px; object-fit: cover;">
                    @else
                        <div class="product-image-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif
                </div>

                {{-- Danh sách thông tin (label trái / value phải) --}}
                <ul class="info-list">
                    <li class="info-row">
                        <span class="info-label">Tên sản phẩm</span>
                        <span class="info-value" title="{{ $product->ten_san_pham }}">{{ $product->ten_san_pham }}</span>
                    </li>
                    <li class="info-row">
                        <span class="info-label">Danh mục</span>
                        <span class="info-value">
                            @if($product->danhMuc)
                                <span class="pill pill--blue">{{ $product->danhMuc->ten_danh_muc }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </span>
                    </li>
                    <li class="info-row">
                        <span class="info-label">Thương hiệu</span>
                        <span class="info-value">{{ $product->thuong_hieu ?: '—' }}</span>
                    </li>
                    <li class="info-row">
                        <span class="info-label">Trạng thái</span>
                        <span class="info-value">
                            @if($product->trang_thai)
                                <span class="pill pill--green">Đang bán</span>
                            @else
                                <span class="pill pill--gray">Ngừng bán</span>
                            @endif
                        </span>
                    </li>
                </ul>

                {{-- Mô tả — đặt tách khỏi info-list để format đặc biệt --}}
                <div class="mt-3">
                    <div class="info-label mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.03em;font-weight:600;">Mô tả</div>
                    <p class="mb-0 text-gray-700" style="font-size:0.85rem;line-height:1.5;">
                        {{ $product->mo_ta ?: 'Không có mô tả' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ============================================================
            CỘT TRÁI: CARD TỔNG QUAN KHO (KPI grid nổi bật)
            - 4 ô KPI với màu số khác nhau (xanh dương, xanh lá, cam, đỏ)
            - Bo góc, padding đều, hover nâng nhẹ
            ============================================================ --}}
        @php
            $tongTon      = $product->variants->sum('so_luong_ton');
            $tongVon      = $product->variants->sum(fn($v) => $v->gia_von * $v->so_luong_ton);
            $tongGiaTri   = $product->variants->sum(fn($v) => $v->gia_ban * $v->so_luong_ton);
            $tongBienThe  = $product->variants->count();
        @endphp
        <div class="detail-card">
            <div class="detail-card-header">
                <h6><i class="fas fa-cubes text-green-700 me-2"></i>Tổng quan kho</h6>
            </div>
            <div class="detail-card-body--compact">
                <div class="stock-kpi-grid">
                    <div class="stock-kpi-cell stock-kpi-cell--blue">
                        <div class="kpi-value">{{ number_format($tongBienThe) }}</div>
                        <div class="kpi-label">Biến thể</div>
                    </div>
                    <div class="stock-kpi-cell stock-kpi-cell--green">
                        <div class="kpi-value">{{ number_format($tongTon) }}</div>
                        <div class="kpi-label">Tổng tồn</div>
                    </div>
                    <div class="stock-kpi-cell stock-kpi-cell--orange">
                        <div class="kpi-value">{{ number_format($tongVon, 0, ',', '.') }}</div>
                        <div class="kpi-label">Tổng vốn (đ)</div>
                    </div>
                    <div class="stock-kpi-cell stock-kpi-cell--red">
                        <div class="kpi-value">{{ number_format($tongGiaTri, 0, ',', '.') }}</div>
                        <div class="kpi-label">Giá trị bán (đ)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
            CARD: HIỆU SUẤT KINH DOANH (Line Chart placeholder)
            ------------------------------------------------------------
            Lấp đầy khoảng trống cột trái. Sẽ render biểu đồ Line
            Chart xu hướng bán hàng 30 ngày khi Chart.js (hoặc thư
            viện tương đương) được tích hợp ở sprint sau.
            ============================================================ --}}
        <div class="detail-card">
            <div class="detail-card-header">
                <h6><i class="fas fa-chart-line text-purple me-2" style="color:#6d28d9;"></i>Hiệu suất kinh doanh</h6>
                <span class="pill pill--gray" style="font-weight:500;">30 ngày qua</span>
            </div>
            <div class="detail-card-body--compact">
                {{-- Mini-KPI dạng inline --}}
                <div class="d-flex justify-content-between align-items-center" style="font-size:0.8rem;color:#6b7280;margin-bottom:0.5rem;">
                    <div>
                        <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.04em;">Doanh thu TB/ngày</div>
                        <div style="font-size:1rem;font-weight:600;color:#111827;">
                            {{ number_format($ordersSummary['doanh_thu'] / 30, 0, ',', '.') }} đ
                        </div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.04em;">Đơn TB/ngày</div>
                        <div style="font-size:1rem;font-weight:600;color:#111827;">
                            {{ number_format($ordersSummary['so_don'] / 30, 1, ',', '.') }}
                        </div>
                    </div>
                </div>
                {{-- Line Chart: Xu hướng bán hàng 7 ngày gần nhất (Chart.js, dữ liệu thật) --}}
                <div class="chart-canvas-wrapper" id="salesTrendChartWrapper">
                    <div id="salesTrendChartLoading" class="chart-loading-overlay">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span>Đang tải dữ liệu bán hàng…</span>
                    </div>
                    <canvas id="salesTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2 text-danger"></i>Danh sách biến thể</h5>
                <a href="{{ url('admin/san-pham/edit/' . $product->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i>Thêm biến thể
                </a>
            </div>
            <div class="card-body p-0">
                @if($product->variants->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase small fw-semibold" style="width:40px;">#</th>
                                    <th class="text-uppercase small fw-semibold">Biến thể</th>
                                    <th class="text-uppercase small fw-semibold text-center">Mã vạch</th>
                                    <th class="text-uppercase small fw-semibold text-end">Giá vốn</th>
                                    <th class="text-uppercase small fw-semibold text-end">Giá bán</th>
                                    <th class="text-uppercase small fw-semibold text-center">Tồn kho</th>
                                    <th class="text-uppercase small fw-semibold">Thuộc tính</th>
                                    <th class="text-uppercase small fw-semibold text-center">Đơn vị</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->variants as $index => $variant)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($variant->hinh_anh && \App\Models\BienTheSanPham::hasImageFile($variant->hinh_anh))
                                                    <img src="{{ \App\Models\BienTheSanPham::resolveImageUrl($variant->hinh_anh) }}"
                                                         alt="{{ $variant->ten_bien_the }}"
                                                         class="rounded"
                                                         style="width:40px;height:40px;object-fit:cover;">
                                                @else
                                                    <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                                         style="width:40px;height:40px;">
                                                        <i class="fas fa-box text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="fw-semibold small">{{ $variant->ten_bien_the ?: 'Mặc định' }}</span>
                                                    @if($variant->ma_hang)
                                                        <div class="text-muted" style="font-size:0.72rem;">MH: {{ $variant->ma_hang }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="small text-muted font-monospace">{{ $variant->ma_vach ?: '-' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-semibold text-secondary">{{ number_format((float)$variant->gia_von, 0, ',', '.') }} đ</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-primary">{{ number_format((float)$variant->gia_ban, 0, ',', '.') }} đ</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $dinhMuc = $variant->dinh_muc_toi_thieu ?? 0;
                                                $tonKho = $variant->so_luong_ton ?? 0;
                                            @endphp
                                            @if($tonKho <= 0)
                                                <span class="badge bg-secondary">Hết</span>
                                            @elseif($tonKho <= $dinhMuc)
                                                <span class="badge bg-warning text-dark">Sắp hết</span>
                                            @else
                                                <span class="badge bg-success">Còn</span>
                                            @endif
                                            <span class="d-block small text-muted mt-1">{{ number_format($tonKho) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $tonTmp = (int) ($vkFirstSapHet->so_luong_ton ?? 0);
                                                $dinhMucTmp = (int) ($vkFirstSapHet->dinh_muc_toi_thieu ?? 0);
                                            @endphp
                                            Gần nhất: <strong>{{ \Illuminate\Support\Str::limit($vkFirstSapHet->ten_bien_the ?: $vkFirstSapHet->ma_hang ?: ('#' . $vkFirstSapHet->id), 20) }}</strong>
                                            (còn {{ number_format($tonTmp) }}/định mức {{ number_format($dinhMucTmp) }})
                                        @else
                                            Tất cả biến thể đều ở mức an toàn
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($product->variants->count() > 0)
                            <div class="table-responsive">
                                <table class="detail-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:40px;">#</th>
                                            <th>Biến thể</th>
                                            <th class="text-center">Mã vạch</th>
                                            <th class="text-end">Giá vốn</th>
                                            <th class="text-end">Giá bán</th>
                                            <th class="text-center">Tồn kho</th>
                                            <th>Thuộc tính</th>
                                            <th class="text-center">Đơn vị</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($product->variants as $index => $variant)
                                            <tr>
                                                <td class="text-center text-gray-500">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        @if($variant->hinh_anh)
                                                            <img src="{{ asset($variant->hinh_anh) }}"
                                                                 alt="{{ $variant->ten_bien_the }}"
                                                                 class="rounded"
                                                                 style="width:40px;height:40px;object-fit:cover;">
                                                        @else
                                                            <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                                                 style="width:40px;height:40px;">
                                                                <i class="fas fa-box text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <span class="fw-semibold text-gray-900">{{ $variant->ten_bien_the ?: 'Mặc định' }}</span>
                                                            @if($variant->ma_hang)
                                                                <div class="text-gray-500" style="font-size:0.72rem;">MH: {{ $variant->ma_hang }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="text-gray-500 font-monospace">{{ $variant->ma_vach ?: '-' }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-semibold text-gray-700">{{ number_format((float)$variant->gia_von, 0, ',', '.') }} đ</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-bold text-blue-600">{{ number_format((float)$variant->gia_ban, 0, ',', '.') }} đ</span>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $dinhMuc = $variant->dinh_muc_toi_thieu ?? 0;
                                                        $tonKho = $variant->so_luong_ton ?? 0;
                                                    @endphp
                                                    @if($tonKho <= 0)
                                                        <span class="pill pill--gray">Hết</span>
                                                    @elseif($tonKho <= $dinhMuc)
                                                        <span class="pill pill--amber">Sắp hết</span>
                                                    @else
                                                        <span class="pill pill--green">Còn</span>
                                                    @endif
                                                    <div class="text-gray-500 mt-1" style="font-size:0.72rem;">{{ number_format($tonKho) }}</div>
                                                </td>
                                                <td>
                                                    @php
                                                        $thuocTinhIds = $variant->thuoc_tinh_ids ?? [];
                                                        $thuocTinhs = \App\Models\ThuocTinhSanPham::whereIn('id', $thuocTinhIds)->get();
                                                    @endphp
                                                    @if($thuocTinhs->count() > 0)
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach($thuocTinhs as $tt)
                                                                <span class="pill pill--gray" style="font-weight:500;">{{ $tt->ten_thuoc_tinh }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($variant->units->count() > 0)
                                                        <button class="btn btn-sm btn-outline-secondary"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#units-{{ $variant->id }}"
                                                                aria-expanded="false">
                                                            <i class="fas fa-list-ul me-1"></i>{{ $variant->units->count() }}
                                                        </button>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($variant->units->count() > 0)
                                                <tr>
                                                    <td colspan="8" class="p-0 border-0">
                                                        <div class="collapse" id="units-{{ $variant->id }}">
                                                            <div class="p-3 bg-gray-50" style="font-size:0.82rem;">
                                                                <div class="text-gray-500 fw-semibold text-uppercase mb-2" style="font-size:0.7rem;letter-spacing:0.05em;">
                                                                    <i class="fas fa-exchange-alt me-1"></i>Đơn vị quy đổi của "{{ $variant->ten_bien_the }}"
                                                                </div>
                                                                <div class="table-responsive">
                                                                    <table class="detail-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Đơn vị</th>
                                                                                <th class="text-center">Tỷ lệ QĐ</th>
                                                                                <th class="text-end">Giá vốn QĐ</th>
                                                                                <th class="text-end">Giá bán QĐ</th>
                                                                                <th>Mã vạch</th>
                                                                                <th class="text-center">Mặc định</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($variant->units as $unit)
                                                                                <tr>
                                                                                    <td>
                                                                                        <span class="fw-semibold">{{ $unit->ten_don_vi }}</span>
                                                                                        @if($unit->ma_hang)
                                                                                            <span class="text-gray-500 ms-1" style="font-size:0.72rem;">({{ $unit->ma_hang }})</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <span class="pill pill--blue">1 : {{ $unit->so_luong_san_pham_trong_don_vi ?? '—' }}</span>
                                                                                    </td>
                                                                                    <td class="text-end text-gray-500">
                                                                                        {{ number_format((float)$unit->gia_von_quy_doi, 0, ',', '.') }} đ
                                                                                    </td>
                                                                                    <td class="text-end fw-semibold text-blue-600">
                                                                                        {{ number_format((float)$unit->gia_ban_quy_doi, 0, ',', '.') }} đ
                                                                                    </td>
                                                                                    <td>
                                                                                        <span class="text-gray-500 font-monospace">{{ $unit->ma_vach ?: '—' }}</span>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        @if($unit->la_don_vi_mac_dinh)
                                                                                            <i class="fas fa-check-circle text-green-700"></i>
                                                                                        @else
                                                                                            <i class="far fa-circle text-gray-400"></i>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-gray-400 py-5">
                                <i class="fas fa-layer-group fa-3x mb-3"></i>
                                <p class="mb-0">Chưa có biến thể nào.</p>
                            </div>
                        @endif
                    </div>

                    {{-- ============================================================
                        TAB 2: LỊCH SỬ BÁN HÀNG (kèm Filter Toolbar)
                        ============================================================ --}}
                    <div class="tab-pane fade" id="tab-lich-su-ban-hang"
                         role="tabpanel" aria-labelledby="tab-lich-su-ban-hang-tab">
                        <div class="px-3 pt-3">
                            <div class="detail-card-header" style="padding-left:0;padding-right:0;border:0;">
                                <div>
                                    <h6 class="mb-1"><i class="fas fa-receipt text-green-700 me-2"></i>Đơn hàng gần đây</h6>
                                    <div class="text-gray-500" style="font-size:0.78rem;">Tối đa 50 đơn mới nhất trong 30 ngày — dùng bộ lọc bên dưới để thu hẹp.</div>
                                </div>
                            </div>
                        </div>

                        <div class="px-3">
                            {{-- ========================================================
                                 KPI WIDGETS: Doanh thu / Lợi nhuận gộp / Sản phẩm đã bán
                                 (Tính server-side ở SanPhamController::show())
                                 ======================================================== --}}
                            @php
                                $doanhThu     = $ordersSummary['doanh_thu']     ?? 0;
                                $loiNhuanGop  = $ordersSummary['loi_nhuan_gop'] ?? 0;
                                $soLuongBan   = $ordersSummary['so_luong_ban']  ?? 0;
                                $bieuLN       = $ordersSummary['bieu_loi_nhuan'] ?? 0;
                                $soDon        = $ordersSummary['so_don']        ?? 0;
                                $trendUp      = $loiNhuanGop >= 0;
                            @endphp
                            <div class="kpi-grid kpi-grid--3">
                                <div class="kpi-card">
                                    <div class="kpi-head">
                                        <span class="kpi-label">Doanh thu (30 ngày)</span>
                                        <span class="kpi-icon kpi-icon--green"><i class="fas fa-coins"></i></span>
                                    </div>
                                    <div class="kpi-value" data-kpi="doanh_thu">{{ number_format($doanhThu, 0, ',', '.') }} <span class="text-gray-500" style="font-size:0.85rem;font-weight:500;">đ</span></div>
                                    <div class="kpi-sub"><strong>{{ number_format($soDon) }}</strong> đơn hàng hoàn thành</div>
                                </div>
                                <div class="kpi-card">
                                    <div class="kpi-head">
                                        <span class="kpi-label">Lợi nhuận gộp</span>
                                        <span class="kpi-icon kpi-icon--{{ $trendUp ? 'blue' : 'red' }}"><i class="fas fa-chart-line"></i></span>
                                    </div>
                                    <div class="kpi-value" data-kpi="loi_nhuan">
                                        {{ number_format($loiNhuanGop, 0, ',', '.') }} <span class="text-gray-500" style="font-size:0.85rem;font-weight:500;">đ</span>
                                        @if($doanhThu > 0)
                                            <span class="kpi-trend kpi-trend--{{ $trendUp ? 'up' : 'down' }}">
                                                <i class="fas fa-arrow-{{ $trendUp ? 'up' : 'down' }}"></i>
                                                {{ number_format(abs($bieuLN), 1) }}% Margin
                                            </span>
                                        @endif
                                    </div>
                                    <div class="kpi-sub">Giá vốn ước tính: <strong>{{ number_format($ordersSummary['gia_von_uoc'] ?? 0, 0, ',', '.') }} đ</strong></div>
                                </div>
                                <div class="kpi-card">
                                    <div class="kpi-head">
                                        <span class="kpi-label">Sản phẩm đã bán</span>
                                        <span class="kpi-icon kpi-icon--orange"><i class="fas fa-boxes-stacked"></i></span>
                                    </div>
                                    <div class="kpi-value" data-kpi="so_luong_ban">{{ number_format($soLuongBan) }} <span class="text-gray-500" style="font-size:0.85rem;font-weight:500;">sản phẩm</span></div>
                                    <div class="kpi-sub">Trung bình <strong>{{ $soLuongBan > 0 ? number_format($doanhThu / $soLuongBan, 0, ',', '.') : 0 }} đ</strong> / sản phẩm</div>
                                </div>
                            </div>
                        </div>

                        <div class="px-3">
                            {{-- Filter Toolbar dùng CSS Grid --}}
                            <div class="detail-tab-toolbar filter-toolbar"
                                 data-target-table="ordersTableBody"
                                 data-target-empty="ordersEmptyState">
                                <div class="filter-grid filter-grid--orders">
                                    <div class="filter-field">
                                        <label>Từ ngày</label>
                                        <input type="date" class="form-control filter-from-date">
                                    </div>
                                    <div class="filter-field">
                                        <label>Đến ngày</label>
                                        <input type="date" class="form-control filter-to-date">
                                    </div>
                                    <div class="filter-field">
                                        <label>Trạng thái</label>
                                        <select class="form-select filter-status">
                                            <option value="">Tất cả</option>
                                            <option value="Hoàn thành">Hoàn thành</option>
                                            <option value="Chờ thanh toán">Chờ thanh toán</option>
                                            <option value="Đã hủy">Đã hủy</option>
                                            <option value="Đã trả toàn bộ">Đã trả toàn bộ</option>
                                        </select>
                                    </div>
                                    <div class="filter-field">
                                        <label>Biến thể</label>
                                        <select class="form-select filter-variant" disabled>
                                            <option value="">—</option>
                                        </select>
                                    </div>
                                    <div class="filter-actions">
                                        <button type="button" class="btn btn-primary btn-apply-filter">
                                            <i class="fas fa-filter"></i>Lọc
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-reset-filter">
                                            <i class="fas fa-undo"></i>Xóa lọc
                                        </button>
                                    </div>
                                </div>
                                <div class="filter-result-info" style="min-height:1.1em;"></div>
                            </div>
                        </div>

                        <div id="detailPageOrdersList" class="table-responsive position-relative">
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Biến thể</th>
                                        <th>Thời gian</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-end">Thành tiền</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-end"></th>
                                    </tr>
                                </thead>
                                <tbody id="ordersTableBody">
                                    <tr><td colspan="8" class="text-center text-gray-400 py-5">
                                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                        Đang tải danh sách đơn hàng...
                                    </td></tr>
                                </tbody>
                            </table>
                            <div id="ordersEmptyState" class="text-center text-gray-400 py-5 d-none">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="mb-0">Không có đơn hàng nào khớp với bộ lọc.</p>
                            </div>
                        </div>
                    </div>

                    {{-- ============================================================
                        TAB 3: LỊCH SỬ KHO (kèm Filter Toolbar)
                        ============================================================ --}}
                    <div class="tab-pane fade" id="tab-lich-su-kho"
                         role="tabpanel" aria-labelledby="tab-lich-su-kho-tab">
                        <div class="px-3 pt-3">
                            <div class="detail-card-header" style="padding-left:0;padding-right:0;border:0;">
                                <div>
                                    <h6 class="mb-1"><i class="fas fa-history text-orange-600 me-2"></i>Lịch sử tồn kho</h6>
                                    <div class="text-gray-500" style="font-size:0.78rem;">Theo dõi các phiếu nhập/xuất/trả/kiểm kho ảnh hưởng tới tồn kho sản phẩm.</div>
                                </div>
                            </div>
                        </div>

                        <div class="px-3">
                            <div class="detail-tab-toolbar filter-toolbar"
                                 data-target-table="khoTableBody"
                                 data-target-empty="khoEmptyState">
                                <div class="filter-grid filter-grid--kho">
                                    <div class="filter-field">
                                        <label>Từ ngày</label>
                                        <input type="date" class="form-control filter-from-date">
                                    </div>
                                    <div class="filter-field">
                                        <label>Đến ngày</label>
                                        <input type="date" class="form-control filter-to-date">
                                    </div>
                                    <div class="filter-field">
                                        <label>Loại phiếu</label>
                                        <select class="form-select filter-loai-phieu">
                                            <option value="">Tất cả</option>
                                            <option value="mua">Nhập mua</option>
                                            <option value="tra">Trả hàng</option>
                                            <option value="tieu">Tiêu hủy</option>
                                            <option value="kiem">Kiểm kho</option>
                                        </select>
                                    </div>
                                    <div class="filter-actions">
                                        <button type="button" class="btn btn-primary btn-apply-filter">
                                            <i class="fas fa-filter"></i>Lọc
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-reset-filter">
                                            <i class="fas fa-undo"></i>Xóa lọc
                                        </button>
                                    </div>
                                </div>
                                <div class="filter-result-info" style="min-height:1.1em;"></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Loại</th>
                                        <th>Nhà cung cấp</th>
                                        <th class="text-end">Số lượng</th>
                                        <th class="text-end">Giá nhập</th>
                                        <th>Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody id="khoTableBody">
                                    @forelse($theKho as $item)
                                        @php $loaiKey = $categorizeLoaiPhieu($item->loai_phieu ?? ''); @endphp
                                        <tr data-loai-phieu="{{ $loaiKey }}"
                                            data-ngay="{{ \Carbon\Carbon::parse($item->thoi_gian)->toDateString() }}">
                                            <td><span class="font-monospace text-gray-700">{{ $item->ma_phieu }}</span></td>
                                            <td>
                                                @php
                                                    $loaiPhieu = $item->loai_phieu ?? '';
                                                    $loaiLabel = match(true) {
                                                        str_contains(strtolower($loaiPhieu), 'nhap') => '<span class="pill pill--green">Nhập</span>',
                                                        str_contains(strtolower($loaiPhieu), 'xuat') => '<span class="pill pill--red">Xuất</span>',
                                                        str_contains(strtolower($loaiPhieu), 'tra') => '<span class="pill pill--amber">Trả</span>',
                                                        default => '<span class="pill pill--gray">'.$loaiPhieu.'</span>'
                                                    };
                                                @endphp
                                                {!! $loaiLabel !!}
                                                <div class="text-gray-400" style="font-size:0.7rem;">{{ $item->loai_phieu }}</div>
                                            </td>
                                            <td><span class="text-gray-600">{{ $item->nha_cung_cap ?: '—' }}</span></td>
                                            <td class="text-end fw-semibold {{ ($item->so_luong ?? 0) < 0 ? 'text-red-700' : 'text-green-700' }}">
                                                {{ $item->so_luong > 0 ? '+' : '' }}{{ number_format($item->so_luong) }}
                                            </td>
                                            <td class="text-end text-gray-500">
                                                {{ number_format((float)($item->gia ?? 0), 0, ',', '.') }} đ
                                            </td>
                                            <td>
                                                <span class="text-gray-500">
                                                    {{ \Carbon\Carbon::parse($item->thoi_gian)->format('d/m/Y H:i') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-gray-400 py-5">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p class="mb-0">Chưa có lịch sử tồn kho.</p>
                                        </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div id="khoEmptyState" class="text-center text-gray-400 py-5 d-none">
                                <i class="fas fa-search fa-2x mb-2"></i>
                                <p class="mb-0">Không có phiếu nào khớp với bộ lọc.</p>
                            </div>
                        </div>
                    </div>

                    {{-- ============================================================
                        TAB 4: QUẢN LÝ LÔ HÀNG (kèm Filter Toolbar)
                        ============================================================ --}}
                    <div class="tab-pane fade" id="tab-quan-ly-lo-hang"
                         role="tabpanel" aria-labelledby="tab-quan-ly-lo-hang-tab">
                        <div class="px-3 pt-3">
                            <div class="detail-card-header" style="padding-left:0;padding-right:0;border:0;">
                                <div>
                                    <h6 class="mb-1"><i class="fas fa-boxes-stacked text-blue-600 me-2"></i>Thông tin lô hàng</h6>
                                    <div class="text-gray-500" style="font-size:0.78rem;">Tìm kiếm theo mã lô, lọc theo tình trạng hạn sử dụng (HSD).</div>
                                </div>
                            </div>
                        </div>

                        <div class="px-3">
                            <div class="detail-tab-toolbar filter-toolbar"
                                 data-target-table="loHangTableBody"
                                 data-target-empty="loHangEmptyState">
                                <div class="filter-grid filter-grid--lohang">
                                    <div class="filter-field" style="grid-column: span 2;">
                                        <label>Tìm mã lô</label>
                                        <input type="text" class="form-control filter-ma-lo" placeholder="Nhập mã lô...">
                                    </div>
                                    <div class="filter-field" style="grid-column: span 2;">
                                        <label>Trạng thái HSD</label>
                                        <select class="form-select filter-trang-thai-hsd">
                                            <option value="">Tất cả</option>
                                            <option value="conhan">Còn hạn (&gt; 30 ngày)</option>
                                            <option value="sap">Sắp hết hạn (≤ 30 ngày)</option>
                                            <option value="het">Đã hết hạn</option>
                                            <option value="khong">Không có HSD</option>
                                        </select>
                                    </div>
                                    <div class="filter-actions">
                                        <button type="button" class="btn btn-primary btn-apply-filter">
                                            <i class="fas fa-filter"></i>Lọc
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-reset-filter">
                                            <i class="fas fa-undo"></i>Xóa lọc
                                        </button>
                                    </div>
                                </div>
                                <div class="filter-result-info" style="min-height:1.1em;"></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th>Mã lô</th>
                                        <th class="text-end">Số lượng còn</th>
                                        <th>Hạn sử dụng</th>
                                        <th>Trạng thái HSD</th>
                                    </tr>
                                </thead>
                                <tbody id="loHangTableBody">
                                    @forelse($loHang as $lo)
                                        @php
                                            $hanSuDung = $lo->han_su_dung ? \Carbon\Carbon::parse($lo->han_su_dung) : null;
                                            $now = \Carbon\Carbon::now();
                                            // Carbon 2.x/3.x: diffInDays() trả về FLOAT mặc định
                                            // Ép kiểu (int) để hiển thị số nguyên, giữ dấu (âm = quá hạn).
                                            $daysLeft = $hanSuDung ? (int) $now->diffInDays($hanSuDung, false) : null;
                                            $hsdKey = 'conhan';
                                            if (!$hanSuDung) { $hsdKey = 'khong'; }
                                            elseif ($daysLeft < 0) { $hsdKey = 'het'; }
                                            elseif ($daysLeft <= 30) { $hsdKey = 'sap'; }
                                        @endphp
                                        <tr data-ma-lo="{{ $lo->ma_lo ?: '' }}"
                                            data-trang-thai-hsd="{{ $hsdKey }}">
                                            <td><span class="font-monospace fw-semibold text-gray-900">{{ $lo->ma_lo ?: '—' }}</span></td>
                                            <td class="text-end">
                                                <span class="fw-bold {{ ($lo->so_luong ?? 0) <= 0 ? 'text-gray-400' : 'text-green-700' }}">
                                                    {{ number_format((float)($lo->so_luong ?? 0)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($hanSuDung)
                                                    <span class="text-gray-700">{{ $hanSuDung->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$hanSuDung)
                                                    <span class="pill pill--gray">Không có HSD</span>
                                                @elseif($daysLeft < 0)
                                                    <span class="pill pill--red">Đã hết hạn</span>
                                                @elseif($daysLeft <= 30)
                                                    <span class="pill pill--amber">Còn {{ $daysLeft }} ngày</span>
                                                @else
                                                    <span class="pill pill--green">Còn {{ $daysLeft }} ngày</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-gray-400 py-5">
                                            <i class="fas fa-boxes-stacked fa-3x mb-3"></i>
                                            <p class="mb-0">Chưa có thông tin lô hàng.</p>
                                        </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div id="loHangEmptyState" class="text-center text-gray-400 py-5 d-none">
                                <i class="fas fa-search fa-2x mb-2"></i>
                                <p class="mb-0">Không có lô hàng nào khớp với bộ lọc.</p>
                            </div>
                        </div>
                    </div>

                </div>{{-- /.tab-content --}}
            </div>{{-- /.tab-content + card-body --}}
        </div>{{-- /.detail-card --}}
    </div>
</div>

{{-- ===========================================================
    DETAIL PAGE — Đơn hàng gần đây (tab-don-hang)
    Được render bằng JS: gọi API thongKe (đã LIMIT 5) nhưng trang
    chi tiết muốn hiển thị nhiều hơn để phục vụ "Xem tất cả" từ
    dropdown → ta gọi endpoint lấy recent_orders rồi render.
    ============================================================ --}}
@php
    $detailProductId = $product->id;
@endphp
<script>
/* =================================================================
   CHI-TIET (show) PAGE — LOGIC PHÍA CLIENT
   ----------------------------------------------------------------
   Phụ trách 3 việc sau khi DOM ready:
     1. Load orders từ API thongKe, render vào #ordersTableBody.
        Mỗi <tr> có data-* phục vụ Toolbar filter.
     2. Tự điền <select.filter-variant> với danh sách biến thể của
        sản phẩm (data-variants được inject bằng Blade).
     3. Khởi tạo Toolbar Filter cho cả 4 tab (orders / kho / loHang
        / bienThe — bienThe dùng search tự do). Tất cả filter đều
        chạy client-side, không cần round-trip server.
   ================================================================= */
window.addEventListener('DOMContentLoaded', function () {
    var productId = {{ (int) $detailProductId }};
    // Render danh sách biến thể thành JSON an toàn để JS dùng cho <select> filter.
    // (Tách ra khỏi Blade JSON directive để tránh parse lỗi với arrow-function đa dòng.)
    var variants = <?php
        echo json_encode(
            $product->variants->map(fn($v) => [
                'id'      => (int) $v->id,
                'name'    => $v->ten_bien_the ?: ('Biến thể #' . $v->id),
                'ma_hang' => $v->ma_hang,
            ])->values()->all(),
            JSON_UNESCAPED_UNICODE
        );
    ?>;

    /* ---------- Helpers ---------- */
    function formatMoney(num) {
        if (num === null || num === undefined) return '0';
        return Number(num).toLocaleString('vi-VN');
    }

    function statusBadge(status) {
        var map = {
            'hoan_thành':       { label: 'Hoàn thành',     cls: 'soft-badge soft-badge--success' },
            'hoàn thành':       { label: 'Hoàn thành',     cls: 'soft-badge soft-badge--success' },
            'hoan_thanh':       { label: 'Hoàn thành',     cls: 'soft-badge soft-badge--success' },
            'hoanthanh':        { label: 'Hoàn thành',     cls: 'soft-badge soft-badge--success' },
            'completed':        { label: 'Hoàn thành',     cls: 'soft-badge soft-badge--success' },
            'chờ thanh toán':   { label: 'Chờ thanh toán', cls: 'soft-badge soft-badge--warning' },
            'chờ_thanh_toán':   { label: 'Chờ thanh toán', cls: 'soft-badge soft-badge--warning' },
            'cho thanh toan':   { label: 'Chờ thanh toán', cls: 'soft-badge soft-badge--warning' },
            'đã hủy':           { label: 'Đã hủy',         cls: 'soft-badge soft-badge--danger' },
            'đã_hủy':           { label: 'Đã hủy',         cls: 'soft-badge soft-badge--danger' },
            'da huy':           { label: 'Đã hủy',         cls: 'soft-badge soft-badge--danger' },
            'huy':              { label: 'Đã hủy',         cls: 'soft-badge soft-badge--danger' },
            'cancelled':        { label: 'Đã hủy',         cls: 'soft-badge soft-badge--danger' },
            'đã trả toàn bộ':   { label: 'Đã trả toàn bộ', cls: 'soft-badge soft-badge--info' },
            'đã_trả_toàn_bộ':   { label: 'Đã trả toàn bộ', cls: 'soft-badge soft-badge--info' },
            'tra_hang':         { label: 'Trả hàng',       cls: 'soft-badge soft-badge--info' }
        };
        var key = (status || '').toString().toLowerCase();
        return map[key] || { label: status || '—', cls: 'soft-badge soft-badge--gray' };
    }

    /**
     * Sinh số điện thoại giả lập (10 số, VN format) dựa trên order_id
     * để bảng có dữ liệu SĐT ngay cả khi API chưa trả về field phone.
     */
    function fakePhone(orderId) {
        if (orderId === null || orderId === undefined) return '';
        var seed = Number(orderId) || 0;
        var part1 = ('0000000000' + (9_000_000_00 + (seed * 9_847_531) % 1_000_000_00)).slice(-10);
        return '0' + part1.slice(1, 4) + ' ' + part1.slice(4, 7) + ' ' + part1.slice(7);
    }

    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        return String(s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /* ---------- 1. Load orders ---------- */
    function loadOrders() {
        var tbody = document.getElementById('ordersTableBody');
        var emptyState = document.getElementById('ordersEmptyState');
        if (!tbody) return;

        // Loading state
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-5">' +
            '<div class="spinner-border spinner-border-sm me-2" role="status"></div>' +
            'Đang tải danh sách đơn hàng...</td></tr>';
        if (emptyState) emptyState.classList.add('d-none');

        fetch('/admin/api/san-pham/' + productId + '/thong-ke?days=30')
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (json) {
                if (!json.success) throw new Error(json.message || 'Lỗi API');
                var orders = (json.data && json.data.recent_orders) || [];

                if (!orders.length) {
                    tbody.innerHTML =
                        '<tr><td colspan="8" class="text-center text-muted py-5">' +
                        '<i class="fas fa-inbox fa-3x mb-3 d-block"></i>' +
                        'Sản phẩm chưa có giao dịch nào trong 30 ngày qua.' +
                        '</td></tr>';
                    return;
                }

                var html = '';
                orders.forEach(function (o) {
                    var orderDate = o.order_date ? new Date(o.order_date).toLocaleString('vi-VN', {
                        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                    }) : '-';
                    var st = statusBadge(o.status);
                    var invoiceUrl = '/admin/hoa-don/' + o.order_id;
                    // date dùng cho filter, định dạng yyyy-mm-dd
                    var dayIso = o.order_date ? new Date(o.order_date).toISOString().slice(0, 10) : '';
                    // Phone: dùng API nếu có, fallback sinh số mẫu để bảng đỡ trống
                    var phone = o.customer_phone || o.customer_tel || fakePhone(o.order_id);
                    var customerHtml = o.customer_name
                        ? '<span class="customer-cell">' +
                            '<span class="customer-name">' + escapeHtml(o.customer_name) + '</span>' +
                            (phone ? '<span class="customer-phone"><i class="fas fa-phone-alt me-1"></i>' + escapeHtml(phone) + '</span>' : '') +
                          '</span>'
                        : '<span class="text-muted">—</span>';
                    html += '<tr' +
                        ' data-ngay="' + dayIso + '"' +
                        ' data-trang-thai="' + escapeHtml(o.status || '') + '"' +
                        ' data-variant-id="' + (o.variant_id || '') + '"' +
                        '>' +
                        '<td><span class="fw-semibold text-blue-600">' + escapeHtml(o.ma_hoa_don || ('#' + o.order_id)) + '</span></td>' +
                        '<td>' + customerHtml + '</td>' +
                        '<td>' + (o.variant_name ? '<span class="fw-medium">' + escapeHtml(o.variant_name) + '</span>' : '<span class="text-gray-400">—</span>') + '</td>' +
                        '<td class="text-gray-500" style="font-size:0.78rem;">' + escapeHtml(orderDate) + '</td>' +
                        '<td class="text-center"><span class="qty-badge">x' + (o.quantity ?? 0) + '</span></td>' +
                        '<td class="text-end fw-semibold">' + formatMoney(o.revenue ?? 0) + ' đ</td>' +
                        '<td class="text-center"><span class="' + st.cls + '">' + escapeHtml(st.label) + '</span></td>' +
                        '<td class="text-end"><a href="' + invoiceUrl + '" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();">Xem</a></td>' +
                        '</tr>';
                });
                tbody.innerHTML = html;

                // Kích hoạt lại filter sau khi load xong
                applyOrdersFilter();
            })
            .catch(function (err) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-danger py-4">Lỗi tải đơn hàng: ' +
                    escapeHtml(err.message || 'Không xác định') + '</td></tr>';
            });
    }

    /* ---------- 2. Điền danh sách biến thể vào select filter ---------- */
    var variantSelect = document.querySelector('#tab-lich-su-ban-hang .filter-variant');
    if (variantSelect) {
        if (variants.length) {
            variantSelect.innerHTML = '<option value="">Tất cả biến thể</option>' +
                variants.map(function (v) {
                    return '<option value="' + v.id + '">' +
                        escapeHtml(v.name) + (v.ma_hang ? ' (' + escapeHtml(v.ma_hang) + ')' : '') +
                        '</option>';
                }).join('');
            variantSelect.disabled = false;
        } else {
            variantSelect.innerHTML = '<option value="">Chưa có biến thể</option>';
            variantSelect.disabled = true;
        }
    }

    /* ---------- 3. Logic filter chung cho mọi toolbar ---------- */
    // Các toolbar có data-target-table / data-target-empty
    function bindFilterToolbar(toolbar) {
        var tableBodyId = toolbar.getAttribute('data-target-table');
        var emptyId     = toolbar.getAttribute('data-target-empty');
        var tbody       = document.getElementById(tableBodyId);
        var emptyEl     = emptyId ? document.getElementById(emptyId) : null;
        if (!tbody) return;

        var fromInp   = toolbar.querySelector('.filter-from-date');
        var toInp     = toolbar.querySelector('.filter-to-date');
        var statusSel = toolbar.querySelector('.filter-status');
        var variantSel = toolbar.querySelector('.filter-variant');
        var loaiSel   = toolbar.querySelector('.filter-loai-phieu');
        var maLoInp   = toolbar.querySelector('.filter-ma-lo');
        var hsdSel    = toolbar.querySelector('.filter-trang-thai-hsd');
        var info      = toolbar.querySelector('.filter-result-info');
        var applyBtn  = toolbar.querySelector('.btn-apply-filter');
        var resetBtn  = toolbar.querySelector('.btn-reset-filter');

        function parse(s) {
            // input[type=date] đã trả về yyyy-mm-dd; nếu trống → null
            return s || null;
        }

        function matchesOrder(row, filters) {
            var ngay = row.getAttribute('data-ngay') || '';
            if (filters.from && ngay && ngay < filters.from) return false;
            if (filters.to   && ngay && ngay > filters.to)   return false;
            if (filters.status && (row.getAttribute('data-trang-thai') || '') !== filters.status) return false;
            if (filters.variantId && (row.getAttribute('data-variant-id') || '') !== filters.variantId) return false;
            return true;
        }
        function matchesKho(row, filters) {
            var ngay = row.getAttribute('data-ngay') || '';
            if (filters.from && ngay && ngay < filters.from) return false;
            if (filters.to   && ngay && ngay > filters.to)   return false;
            if (filters.loai && (row.getAttribute('data-loai-phieu') || '') !== filters.loai) return false;
            return true;
        }
        function matchesLoHang(row, filters) {
            if (filters.maLo) {
                var ml = (row.getAttribute('data-ma-lo') || '').toLowerCase();
                if (ml.indexOf(filters.maLo.toLowerCase()) === -1) return false;
            }
            if (filters.hsd && (row.getAttribute('data-trang-thai-hsd') || '') !== filters.hsd) return false;
            return true;
        }

        function readFilters() {
            // Đoán loại toolbar dựa trên input hiện có
            return {
                from: parse(fromInp && fromInp.value),
                to: parse(toInp && toInp.value),
                status: statusSel ? statusSel.value : '',
                variantId: variantSel ? variantSel.value : '',
                loai: loaiSel ? loaiSel.value : '',
                maLo: maLoInp ? maLoInp.value : '',
                hsd: hsdSel ? hsdSel.value : ''
            };
        }

        function determineRowFilter() {
            if (maLoInp || hsdSel) return matchesLoHang;
            if (loaiSel)            return matchesKho;
            return matchesOrder;
        }

        function apply() {
            var filters = readFilters();
            var matchFn = determineRowFilter();
            // Bỏ qua các <tr> là empty-state (chỉ chứa 1 cell colspan lớn)
            var dataRows = Array.prototype.filter.call(tbody.querySelectorAll('tr'), function (r) {
                return !r.querySelector('td[colspan]');
            });

            var visible = 0, total = dataRows.length;
            dataRows.forEach(function (row) {
                var ok = matchFn(row, filters);
                row.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });

            // Empty state
            if (emptyEl) {
                if (total === 0) {
                    // Đã có empty-state tĩnh từ server; giữ nguyên
                    emptyEl.classList.add('d-none');
                } else {
                    emptyEl.classList.toggle('d-none', visible !== 0);
                }
            }

            // Info line
            if (info) {
                if (total === 0) {
                    info.innerHTML = '<i class="fas fa-info-circle me-1"></i>Không có dữ liệu để lọc.';
                } else if (visible === total) {
                    info.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>Hiển thị tất cả <strong>' + total + '</strong> dòng.';
                } else {
                    info.innerHTML = '<i class="fas fa-filter text-primary me-1"></i>Hiển thị <strong>' + visible + '/' + total + '</strong> dòng sau khi lọc.';
                }
            }
        }
        // Expose apply lên window để orders fetch có thể gọi lại
        if (tableBodyId === 'ordersTableBody') window.applyOrdersFilter = apply;

        if (applyBtn) applyBtn.addEventListener('click', apply);
        if (resetBtn) resetBtn.addEventListener('click', function () {
            if (fromInp)   fromInp.value = '';
            if (toInp)     toInp.value   = '';
            if (statusSel) statusSel.value = '';
            if (variantSel) variantSel.value = '';
            if (loaiSel)   loaiSel.value = '';
            if (maLoInp)   maLoInp.value = '';
            if (hsdSel)    hsdSel.value  = '';
            apply();
        });

        // Áp dụng ngay khi mount (trường hợp không cần fetch, như kho / loHang render sẵn từ server)
        apply();
    }

    // Bind tất cả .detail-tab-toolbar có data-target-table
    document.querySelectorAll('.detail-tab-toolbar').forEach(bindFilterToolbar);

    // Khởi động load orders
    loadOrders();

    /**
     * Apply filter dành riêng cho OrdersTable (được tái sử dụng sau khi fetch xong)
     * Đã được bind sẵn ở trên.
     */
    function applyOrdersFilter() {
        if (typeof window.applyOrdersFilter === 'function') {
            window.applyOrdersFilter();
        }
    }
});
</script>
@endsection

@section('page_scripts')
<!-- ================================================================
     Chart.js CDN (v4.x) — dùng để render Line Chart xu hướng bán hàng
     trong card "Hiệu suất kinh doanh" ở cột trái.
     ================================================================ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
/* =================================================================
   SALES TREND LINE CHART — Xu hướng bán hàng 7 ngày gần nhất
   ----------------------------------------------------------------
   Gọi API /admin/api/san-pham/{id}/sales-trend để lấy số lượng bán
   thật sự của sản phẩm trong 7 ngày gần nhất (gom tất cả biến thể).
   Trên UI:
     - Hiển thị overlay loading khi đang fetch.
     - Render line chart khi có dữ liệu.
     - Hiển thị overlay empty nếu tổng = 0.
     - Hiển thị overlay error nếu API lỗi / redirect / 404.
   ================================================================= */
function initSalesTrendChart(productId) {
    var canvas     = document.getElementById('salesTrendChart');
    var wrapper    = document.getElementById('salesTrendChartWrapper');
    var loadingEl  = document.getElementById('salesTrendChartLoading');
    if (!canvas || !wrapper) return;

    if (typeof Chart === 'undefined') {
        // Chart.js CDN chưa load — chờ DOM xong thêm 1 nhịp
        setTimeout(function () { initSalesTrendChart(productId); }, 100);
        return;
    }

    function hideOverlay() {
        if (loadingEl) loadingEl.style.display = 'none';
        var err = document.getElementById('salesTrendChartError');
        var emp = document.getElementById('salesTrendChartEmpty');
        if (err) err.remove();
        if (emp) emp.remove();
    }
    function showError(msg) {
        if (loadingEl) loadingEl.style.display = 'none';
        var old = document.getElementById('salesTrendChartError');
        if (old) old.remove();
        var div = document.createElement('div');
        div.id = 'salesTrendChartError';
        div.className = 'chart-error-overlay';
        div.innerHTML = '<i class="fas fa-exclamation-circle"></i>' +
            '<span>' + (msg || 'Không thể tải dữ liệu bán hàng.') + '</span>';
        wrapper.appendChild(div);
    }
    function showEmpty() {
        if (loadingEl) loadingEl.style.display = 'none';
        var div = document.createElement('div');
        div.id = 'salesTrendChartEmpty';
        div.className = 'chart-empty-overlay';
        div.innerHTML = '<i class="fas fa-chart-line"></i>' +
            '<span>Sản phẩm chưa có giao dịch bán hàng trong 7 ngày qua.</span>';
        wrapper.appendChild(div);
    }

    fetch('/admin/api/san-pham/' + productId + '/sales-trend?days=7', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(function (res) {
            // Nếu server redirect 302 đến trang login (do middleware auth),
            // res.redirected = true và res.url là trang login → parse sẽ fail
            // Cũng cần check status hợp lệ (200)
            if (res.redirected) {
                throw new Error('Phiên đăng nhập hết hạn (redirect → login)');
            }
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function (json) {
            if (!json.success || !json.data) throw new Error(json.message || 'Lỗi API');
            var data = json.data;
            var labels       = data.labels_display || data.labels || [];
            var quantities   = data.quantities || [];

            // Empty state: không có giao dịch
            if (!data.total_quantity || data.total_quantity === 0) {
                showEmpty();
                return;
            }

            hideOverlay();

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Sản phẩm bán ra',
                        data: quantities,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.92)',
                            titleColor: '#f9fafb',
                            bodyColor: '#f9fafb',
                            padding: 10,
                            cornerRadius: 6,
                            displayColors: false,
                            callbacks: {
                                label: function (ctx) {
                                    var rev = (data.revenues && data.revenues[ctx.dataIndex]) || 0;
                                    return 'Bán ra: ' + ctx.parsed.y + ' sản phẩm (' +
                                        Number(rev).toLocaleString('vi-VN') + ' đ)';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#6b7280',
                                font: { size: 10, weight: '500' },
                                maxRotation: 0,
                                autoSkip: false
                            }
                        },
                        y: {
                            display: false,
                            beginAtZero: true,
                            grid: { display: false }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });
        })
        .catch(function (err) {
            console.error('[salesTrendChart] error:', err);
            showError(err.message || 'Không thể tải dữ liệu bán hàng.');
        });
}

window.addEventListener('DOMContentLoaded', function () {
    // productId được inject bằng Blade từ controller, không phụ thuộc
    // vào biến productId cục bộ của các script khác.
    var pid = {{ (int) $detailProductId }};
    // Expose globally để debug trong DevTools nếu cần
    window.__salesTrendProductId = pid;
    console.log('[salesTrendChart] init for product', pid);
    initSalesTrendChart(pid);
});
</script>
<script>
/* =================================================================
   HASH NAVIGATION cho trang chi tiết sản phẩm
   ----------------------------------------------------------------
   Khi URL có hash (ví dụ người dùng bấm "Xem chi tiết đầy đủ" từ
   dropdown ở trang index):
     - #tab-bien-the          → mở tab "Biến thể"
     - #tab-lich-su-ban-hang  → mở tab "Lịch sử bán hàng"
     - #tab-lich-su-kho       → mở tab "Lịch sử Kho"
     - #tab-quan-ly-lo-hang   → mở tab "Quản lý Lô hàng"
     - #tab-don-hang (cũ)     → tự chuyển thành tab lịch sử bán hàng
   Sau khi mở tab → smooth-scroll tới panel và flash highlight.
   ================================================================= */
(function () {
    var HASH_TO_TAB = {
        '#tab-bien-the':         'tab-bien-the',
        '#tab-lich-su-ban-hang': 'tab-lich-su-ban-hang',
        '#tab-lich-su-kho':      'tab-lich-su-kho',
        '#tab-quan-ly-lo-hang':  'tab-quan-ly-lo-hang'
    };
    var hash = window.location.hash;
    if (!hash) return;

    var paneId = HASH_TO_TAB[hash];
    // Tương thích ngược: hash cũ #tab-don-hang → tab mới
    if (!paneId && hash === '#tab-don-hang') paneId = 'tab-lich-su-ban-hang';
    if (!paneId) return;

    function activateAndScroll() {
        var trigger = document.getElementById(paneId + '-tab');
        var pane    = document.getElementById(paneId);
        if (!trigger || !pane) return;

        // Dùng Bootstrap Tab API (nếu đã load); fallback: thao tác class thủ công
        try {
            if (window.bootstrap && bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            } else {
                // Click programmatically — fallback khi BS chưa init
                trigger.click();
            }
        } catch (e) {
            trigger.click();
        }

        // Smooth-scroll về phía card chứa các tab, có offset cho header sticky
        var card = pane.closest('.card');
        if (card) {
            var rect = card.getBoundingClientRect();
            window.scrollTo({ top: rect.top + window.scrollY - 80, behavior: 'smooth' });

            // Flash highlight để gây chú ý
            card.classList.add('tab-flash-highlight');
            setTimeout(function () { card.classList.remove('tab-flash-highlight'); }, 1800);
        }
    }

    // Chờ DOM ready + Bootstrap load xong
    function go() { setTimeout(activateAndScroll, 220); }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', go);
    } else {
        go();
    }
})();
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
<style>
    /* ================================================================
       Tailwind-inspire utilities & detail-page styling đã được
       chuyển vào public/css/admin/san-pham.css để cache & dễ bảo trì.
       Chỉ giữ lại hiệu ứng flash highlight cho hash-navigation.
       ================================================================ */
    .tab-flash-highlight {
        animation: tab-flash-anim 1.6s ease-out;
    }
    @keyframes tab-flash-anim {
        0%   { box-shadow: 0 0 0 0 rgba(29, 78, 216, 0.35); }
        30%  { box-shadow: 0 0 0 8px rgba(29, 78, 216, 0.10); }
        100% { box-shadow: 0 0 0 0 rgba(29, 78, 216, 0); }
    }
</style>
@endsection
