@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết Lô hàng - SmartMart')

@section('content')

{{-- ============================================================
   Page Header & Breadcrumb
   ============================================================ --}}
<div class="mb-3 d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="fas fa-layer-group me-2 text-primary"></i>Chi tiết Lô hàng
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item active">{{ $loHang->ma_lo ?: 'Lô #' . $loHang->id }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
        {{-- <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i>In báo cáo
        </button> --}}
        <button class="btn btn-outline-success btn-sm" id="btn-xuat-excel">
            <i class="fas fa-file-excel me-1"></i>Xuất Excel
        </button>
    </div>
</div>

{{-- ============================================================
   Card: Thông tin Lô hàng
   ============================================================ --}}
<div class="card lo-info-card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">
            <i class="fas fa-info-circle me-2 text-info"></i>Thông tin lô hàng
        </h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="info-item">
                    <label class="info-label">Mã lô:</label>
                    <div class="info-value">
                        <code class="kho-code">{{ $loHang->ma_lo ?: 'L-' . $loHang->id }}</code>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <label class="info-label">Nhà cung cấp:</label>
                    <div class="info-value">
                        {{ $loHang->nhaCungCap?->ten_nha_cung_cap ?? '--' }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <label class="info-label">Ngày nhập:</label>
                    <div class="info-value">
                        {{ $loHang->ngay_nhap ? \Carbon\Carbon::parse($loHang->ngay_nhap)->format('d/m/Y') : '--' }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <label class="info-label">Người tạo:</label>
                    <div class="info-value">
                        {{ $loHang->phieu?->nguoiDung?->ho_ten ?? '--' }}
                    </div>
                </div>
            </div>
            @if($loHang->ghi_chu)
            <div class="col-12">
                <div class="info-item">
                    <label class="info-label">Ghi chú:</label>
                    <div class="info-value">{{ $loHang->ghi_chu }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ============================================================
   Row KPI Cards
   ============================================================ --}}
<div class="row g-3 mb-4 lo-kpi-row">
    <div class="col-xl-4 col-md-4">
        <div class="stat-card kho-kpi-card shadow-sm h-100">
            <div class="stat-card-icon stat-icon-blue">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-card-label">Tổng Giá trị Ban đầu</div>
            <div class="stat-card-value kho-kpi-money">
                {{ number_format($tongGiaTriBanDau, 0, ',', '.') }} <span class="kho-kpi-unit">đ</span>
            </div>
            <div class="kho-kpi-sub text-muted">Giá trị nhập kho ban đầu</div>
        </div>
    </div>
    <div class="col-xl-4 col-md-4">
        <div class="stat-card kho-kpi-card shadow-sm h-100">
            <div class="stat-card-icon stat-icon-green">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="stat-card-label">Giá trị Còn lại</div>
            <div class="stat-card-value kho-kpi-money">
                {{ number_format($tongGiaTriConLai, 0, ',', '.') }} <span class="kho-kpi-unit">đ</span>
            </div>
            <div class="kho-kpi-sub text-muted">Giá trị tồn kho hiện tại</div>
        </div>
    </div>
    <div class="col-xl-4 col-md-4">
        <div class="stat-card kho-kpi-card shadow-sm h-100">
            <div class="stat-card-icon stat-icon-orange">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-card-label">Tỷ lệ Tồn kho</div>
            <div class="stat-card-value">
                {{ number_format($tyLeTonKho, 1) }}%
            </div>
            <div class="kho-kpi-sub text-muted">Còn {{ number_format($tongSlTon) }} / {{ number_format($tongSlNhap) }}</div>
        </div>
    </div>
</div>

{{-- ============================================================
   Card: Danh sách Sản phẩm trong Lô
   ============================================================ --}}
<div class="card table-admin mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Danh sách Sản phẩm trong Lô</h5>
        <span class="text-muted small">{{ count($chiTiet) }} sản phẩm</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle lo-product-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:50px">STT</th>
                        <th>Sản phẩm / Biến thể</th>
                        <th style="width:130px">Mã vạch</th>
                        <th class="text-end" style="width:100px">SL Nhập</th>
                        <th class="text-end" style="width:100px">SL Tồn</th>
                        <th class="text-end" style="width:100px">SL Xuất</th>
                        <th class="text-end" style="width:120px">Giá nhập</th>
                        <th class="text-end" style="width:150px">GT Ban đầu</th>
                        <th class="text-end" style="width:150px">GT Còn lại</th>
                        <th class="text-center" style="width:110px">HSD</th>
                        <th class="text-center" style="width:100px">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tongGtBanDauSum = 0;
                        $tongGtConLaiSum = 0;
                        $tongSlNhapSum = 0;
                        $tongSlTonSum = 0;
                        $tongSlXuatSum = 0;
                    @endphp
                    @forelse($chiTiet as $index => $ct)
                        @php
                            $slNhap = (int) $ct->so_luong_nhap;
                            $slTon = (int) $ct->so_luong_ton;
                            $slXuat = $slNhap - $slTon;
                            $giaNhap = (float) $ct->gia_nhap;
                            $gtBanDau = $slNhap * $giaNhap;
                            $gtConLai = $slTon * $giaNhap;
                            
                            $tongGtBanDauSum += $gtBanDau;
                            $tongGtConLaiSum += $gtConLai;
                            $tongSlNhapSum += $slNhap;
                            $tongSlTonSum += $slTon;
                            $tongSlXuatSum += $slXuat;

                            $variant = $ct->variant;
                            $product = $variant?->product ?? $ct->product;
                            $tenSp = $product?->ten_san_pham ?? 'N/A';
                            $tenBt = $variant?->ten_bien_the ?: 'Mặc định';
                            $maVach = $variant?->ma_vach ?: '--';
                            
                            // HSD color
                            $hsd = $ct->han_su_dung ? \Carbon\Carbon::parse($ct->han_su_dung) : null;
                            $hsdCls = '';
                            if ($hsd) {
                                if ($hsd->isPast()) {
                                    $hsdCls = 'text-danger fw-bold';
                                } elseif ($hsd->diffInDays(now()) <= 30) {
                                    $hsdCls = 'text-warning fw-bold';
                                }
                            }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="product-name">{{ $tenSp }}</div>
                                <div class="product-meta">
                                    <i class="fas fa-tag"></i>{{ $tenBt }}
                                    @if($product?->danhMuc)
                                        <span class="mx-1">·</span>
                                        <i class="fas fa-folder-open"></i>{{ $product->danhMuc->ten_danh_muc }}
                                    @endif
                                </div>
                            </td>
                            <td><code class="kho-code">{{ $maVach }}</code></td>
                            <td class="text-end"><span class="num-value">{{ number_format($slNhap) }}</span></td>
                            <td class="text-end">
                                <span class="num-value {{ $slTon > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ number_format($slTon) }}
                                </span>
                            </td>
                            <td class="text-end"><span class="num-value">{{ number_format($slXuat) }}</span></td>
                            <td class="text-end">
                                <span class="kho-money kho-money-sm">
                                    {{ number_format($giaNhap, 0, ',', '.') }} <span class="kho-money-unit">đ</span>
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="kho-money fw-bold">
                                    {{ number_format($gtBanDau, 0, ',', '.') }} <span class="kho-money-unit">đ</span>
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="kho-money {{ $slTon > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ number_format($gtConLai, 0, ',', '.') }} <span class="kho-money-unit">đ</span>
                                </span>
                            </td>
                            <td class="text-center">
                                @if($hsd)
                                    <span class="{{ $hsdCls }}">{{ $hsd->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($slTon > 0)
                                    <span class="badge bg-success">Còn hàng</span>
                                @else
                                    <span class="badge bg-secondary">Hết</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Lô hàng này chưa có sản phẩm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($chiTiet) > 0)
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">TỔNG CỘNG:</td>
                        <td class="text-end">{{ number_format($tongSlNhapSum) }}</td>
                        <td class="text-end">{{ number_format($tongSlTonSum) }}</td>
                        <td class="text-end">{{ number_format($tongSlXuatSum) }}</td>
                        <td></td>
                        <td class="text-end">
                            <span class="kho-money">
                                {{ number_format($tongGtBanDauSum, 0, ',', '.') }} <span class="kho-money-unit">đ</span>
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="kho-money">
                                {{ number_format($tongGtConLaiSum, 0, ',', '.') }} <span class="kho-money-unit">đ</span>
                            </span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- ============================================================
   Card: Lịch sử Xuất hàng
   ============================================================ --}}
<div class="card table-admin mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Lịch sử Xuất hàng từ Lô này</h5>
    </div>
    <div class="card-body p-0">
        @if($lichSuXuat->isEmpty())
            <div class="lo-history-empty text-center text-muted py-5">
                <i class="fas fa-inbox fs-2 d-block mb-2 opacity-50"></i>
                <p class="mb-0">Chưa có phiếu xuất nào từ lô này</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Loại xuất</th>
                            <th>Ngày xuất</th>
                            <th>Sản phẩm</th>
                            <th class="text-end">SL Xuất</th>
                            <th>Người tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lichSuXuat as $ls)
                            <tr>
                                <td><code class="kho-code">{{ $ls->ma_phieu }}</code></td>
                                <td>
                                    @if($ls->loai_xuat === 'tieu_huy')
                                        <span class="badge bg-danger">Tiêu hủy</span>
                                    @elseif($ls->loai_xuat === 'tra_hang_nha_cung_cap')
                                        <span class="badge bg-warning text-dark">Trả hàng NCC</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $ls->loai_xuat }}</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($ls->ngay_xuat)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="product-name">{{ $ls->ten_san_pham }}</div>
                                    @if($ls->ten_bien_the)
                                        <div class="product-meta"><i class="fas fa-tag"></i>{{ $ls->ten_bien_the }}</div>
                                    @endif
                                </td>
                                <td class="text-end"><span class="num-value">{{ number_format($ls->so_luong_xuat) }}</span></td>
                                <td>{{ $ls->nguoi_tao ?? '--' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/kho-hang.css') }}">
<style>
    /* Additional styles for chi-tiet page */
    .lo-info-card {
        border-left: 4px solid #3b82f6;
    }
    
    .info-item {
        padding: 8px 0;
    }
    
    .info-label {
        font-size: 0.8rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 4px;
        display: block;
    }
    
    .info-value {
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 500;
    }
    
    .lo-product-table thead th {
        background-color: #f8fafc;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .lo-product-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .lo-history-empty {
        padding: 3rem 1rem;
    }
    
    @media print {
        .btn, .breadcrumb, nav, .sidebar {
            display: none !important;
        }
        
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }
        
        .lo-kpi-row {
            page-break-inside: avoid;
        }
    }
</style>
@endsection

@section('scripts')
<script>
document.getElementById('btn-xuat-excel')?.addEventListener('click', function() {
    alert('Tính năng xuất Excel đang được phát triển.');
});
</script>
@endsection
