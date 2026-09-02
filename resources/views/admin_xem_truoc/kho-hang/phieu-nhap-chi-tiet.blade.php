@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết Phiếu nhập - SmartMart')

@section('content')
<style>
    .pn-detail-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .pn-stat {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        padding: 16px 18px;
        height: 100%;
    }

    .pn-stat .label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: 6px;
    }

    .pn-stat .value {
        font-weight: 800;
        font-size: 22px;
        color: #0f172a;
        line-height: 1.2;
    }

    .pn-stat .sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
    }

    .pn-info-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: 2px;
    }

    .pn-info-value {
        font-weight: 600;
        color: #0f172a;
    }

    .pn-product-img {
        width: 62px;
        height: 62px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        flex-shrink: 0;
    }

    .pn-mini-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
</style>

@php
    $phieu = $phieuNhap->phieu;
    $nhaCungCap = $phieu?->nhaCungCap;
    $nguoiTao = $phieu?->nguoiDung;
    $hoaDon = $phieuNhap->hoaDon ?? $phieu?->hoaDon;
    $phieuXuatGoc = $phieuNhap->phieuXuatGoc;
    $chiTietList = $chiTietPhieu ?? $phieuNhap->chiTietPhieu;
    $hsdSomNhatText = $hsdSomNhat ? \Carbon\Carbon::parse($hsdSomNhat)->format('d/m/Y') : '--';
    $daysToHsdSomNhat = $hsdSomNhat ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($hsdSomNhat), false) : null;
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="fas fa-file-import me-2 text-success"></i>Chi tiết phiếu nhập
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang/phieu-nhap') }}">Phiếu nhập</a></li>
                <li class="breadcrumb-item active">PN-{{ $phieuNhap->id_phieu }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang/phieu-nhap') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
        <a href="{{ url('/admin/api/phieu-nhap/' . $phieuNhap->id . '/export') }}" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-download me-1"></i>Xuất Excel
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="pn-stat">
            <div class="label">Mã phiếu</div>
            <div class="value"><span class="badge bg-primary fs-6">PN-{{ $phieuNhap->id_phieu }}</span></div>
            <div class="sub">{{ $phieuNhap->loai_nhap === 'mua_hang' ? 'Nhập mua hàng' : 'Trả lại từ khách' }}</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="pn-stat">
            <div class="label">Tổng số lượng</div>
            <div class="value">{{ number_format($tongSoLuong, 0, ',', '.') }}</div>
            <div class="sub">{{ $tongSoDong }} dòng chi tiết</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="pn-stat">
            <div class="label">Tổng giá trị nhập</div>
            <div class="value text-success">{{ number_format($tongThanhTien, 0, ',', '.') }} đ</div>
            <div class="sub">Theo giá nhập của từng dòng</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="pn-stat">
            <div class="label">HSD sớm nhất</div>
            <div class="value">{{ $hsdSomNhatText }}</div>
            <div class="sub">
                @if($daysToHsdSomNhat === null)
                    Chưa có HSD
                @elseif($daysToHsdSomNhat < 0)
                    Đã quá hạn
                @else
                    Còn {{ $daysToHsdSomNhat }} ngày
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card pn-detail-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h5 class="mb-0"><i class="fas fa-circle-info me-2 text-success"></i>Thông tin phiếu nhập</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="pn-info-label">Ngày tạo</div>
                        <div class="pn-info-value">{{ optional($phieuNhap->created_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="pn-info-label">Người tạo</div>
                        <div class="pn-info-value">{{ $nguoiTao?->ho_ten ?? '--' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="pn-info-label">Nhà cung cấp</div>
                        <div class="pn-info-value">{{ $nhaCungCap?->ten_nha_cung_cap ?? '--' }}</div>
                    </div>
                    <div class="col-md-4">
                    <div class="pn-info-label">Hóa đơn liên quan</div>
                    <div class="pn-info-value">
                        @if($hoaDon)
                            @if(auth()->user()?->can('quan_ly_hoa_don'))
                                <a href="{{ route('admin.hoa-don.show', $hoaDon->id) }}" class="text-decoration-none">
                                    #{{ $hoaDon->id }}
                                </a>
                            @else
                                #{{ $hoaDon->id }}
                            @endif
                        @else
                            --
                        @endif
                    </div>
                </div>
                    <div class="col-md-4">
                        <div class="pn-info-label">Phiếu xuất gốc</div>
                        <div class="pn-info-value">
                            @if($phieuXuatGoc)
                                #{{ $phieuXuatGoc->id }}
                            @else
                                --
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pn-info-label">Loại nhập</div>
                        <div class="pn-info-value">
                            @if($phieuNhap->loai_nhap === 'mua_hang')
                                <span class="badge bg-success">Nhập mua hàng</span>
                            @else
                                <span class="badge bg-info">Trả lại từ khách</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="pn-info-label">Ghi chú</div>
                        <div class="pn-info-value">{{ $phieuNhap->ghi_chu ?: '--' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card pn-detail-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h5 class="mb-0"><i class="fas fa-link me-2 text-primary"></i>Thông tin liên quan</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="pn-info-label">Mã phiếu</div>
                    <div class="pn-info-value">PN-{{ $phieuNhap->id_phieu }}</div>
                </div>
                <div class="mb-3">
                    <div class="pn-info-label">Số loại sản phẩm</div>
                    <div class="pn-info-value">{{ $soLoaiSanPham }}</div>
                </div>
                <div class="mb-3">
                    <div class="pn-info-label">Phiếu gốc hệ thống</div>
                    <div class="pn-info-value">#{{ $phieu?->id ?? '--' }}</div>
                </div>
                <div class="mb-3">
                    <div class="pn-info-label">Ngày tạo phiếu gốc</div>
                    <div class="pn-info-value">{{ optional($phieu?->created_at)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="mb-0">
                    <div class="pn-info-label">Trạng thái lô</div>
                    <div class="pn-info-value">
                        <span class="badge bg-secondary">Theo từng dòng chi tiết</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card pn-detail-card">
    <div class="card-header bg-white border-0 pt-3 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="fas fa-list-ul me-2 text-success"></i>Danh sách chi tiết phiếu nhập</h5>
            <span class="text-muted small">Đầy đủ sản phẩm, biến thể, lô hàng, HSD và thành tiền</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 28%">Sản phẩm</th>
                        <th style="width: 14%">Mã / Đơn vị</th>
                        <th class="text-center" style="width: 8%">SL nhập</th>
                        <th class="text-center" style="width: 10%">Giá nhập</th>
                        <th class="text-center" style="width: 10%">Thành tiền</th>
                        <th class="text-center" style="width: 10%">HSD</th>
                        <th class="text-center" style="width: 10%">Lô</th>
                        <th class="text-center" style="width: 10%">Tồn lô</th>
                        <th style="width: 10%">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chiTietList as $chiTiet)
                        @php
                            $variant = $chiTiet->variant;
                            $product = $variant?->product ?? $chiTiet->sanPham;
                            $imageUrl = \App\Models\BienTheSanPham::resolveImageUrl($variant?->hinh_anh ?? null);
                            $tenSanPham = $product?->ten_san_pham ?? ('SP-' . $chiTiet->id_san_pham);
                            if ($variant?->ten_bien_the) {
                                $tenSanPham .= ' - ' . $variant->ten_bien_the;
                            }
                            $thuocTinhLabels = $variant?->thuoc_tinh_labels ?? [];
                            $maVach = $variant?->ma_vach ?? $product?->ma_vach ?? '--';
                            $maHang = $variant?->ma_hang ?? '--';
                            $donVi = $variant?->ten_don_vi ?? $variant?->ten_bien_the ?? '--';
                            $soLuongNhap = (float) ($chiTiet->so_luong ?? 0);
                            $giaNhap = (float) ($chiTiet->gia_nhap ?? 0);
                            $thanhTien = $soLuongNhap * $giaNhap;
                            $hsd = $chiTiet->han_su_dung ? \Carbon\Carbon::parse($chiTiet->han_su_dung) : null;
                            $daysLeft = $hsd ? now()->startOfDay()->diffInDays($hsd, false) : null;
                            $loHang = $chiTiet->loHang;
                            $chiTietLo = $chiTiet->chiTietLoHang;
                            $maLo = $loHang?->ma_lo ?? $chiTiet->ma_lo ?? ('L-' . ($chiTiet->id_lo_hang ?? '--'));
                            $tonLo = $chiTietLo?->so_luong_ton ?? $chiTiet->so_luong_con_lai ?? null;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $imageUrl }}" alt="{{ $tenSanPham }}" class="pn-product-img">
                                    <div>
                                        <div class="fw-semibold">{{ $tenSanPham }}</div>
                                        @if($product?->thuong_hieu)
                                            <div class="text-muted small">Thương hiệu: {{ $product->thuong_hieu }}</div>
                                        @endif
                                        @if(!empty($thuocTinhLabels))
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach($thuocTinhLabels as $label)
                                                    <span class="pn-mini-badge">{{ $label }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small text-muted">Mã vạch</div>
                                <div class="fw-semibold">{{ $maVach }}</div>
                                <div class="small text-muted mt-2">Mã hàng: {{ $maHang }}</div>
                                <div class="small text-muted">Đơn vị: {{ $donVi }}</div>
                            </td>
                            <td class="text-center fw-semibold">{{ number_format($soLuongNhap, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($giaNhap, 0, ',', '.') }} đ</td>
                            <td class="text-center fw-bold text-danger">{{ number_format($thanhTien, 0, ',', '.') }} đ</td>
                            <td class="text-center">
                                @if($hsd)
                                    <div class="fw-semibold">{{ $hsd->format('d/m/Y') }}</div>
                                    @if($daysLeft !== null)
                                        @if($daysLeft < 0)
                                            <span class="badge bg-danger">Hết HSD</span>
                                        @elseif($daysLeft <= 30)
                                            <span class="badge bg-warning text-dark">Còn {{ $daysLeft }} ngày</span>
                                        @else
                                            <span class="badge bg-success">Còn {{ $daysLeft }} ngày</span>
                                        @endif
                                    @endif
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark">{{ $maLo }}</span>
                                @if($loHang?->ngay_nhap)
                                    <div class="small text-muted mt-1">{{ $loHang->ngay_nhap->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tonLo !== null)
                                    <div class="fw-semibold">{{ number_format($tonLo, 0, ',', '.') }}</div>
                                    <div class="small text-muted">còn lại</div>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                {{ $chiTiet->ghi_chu ?: '--' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                Không có chi tiết phiếu nhập.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="2" class="text-end fw-bold">Tổng cộng:</td>
                        <td class="text-center fw-bold">{{ number_format($tongSoLuong, 0, ',', '.') }}</td>
                        <td></td>
                        <td class="text-center fw-bold text-danger">{{ number_format($tongThanhTien, 0, ',', '.') }} đ</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
