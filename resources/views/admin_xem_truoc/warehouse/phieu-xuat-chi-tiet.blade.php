@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết Phiếu xuất - SmartMart')

@section('content')
<style>
    .px-detail-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .px-stat {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        padding: 16px 18px;
        height: 100%;
    }

    .px-stat .label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .px-stat .value {
        font-weight: 800;
        font-size: 22px;
        color: #0f172a;
        line-height: 1.2;
    }

    .px-stat .sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
    }

    .px-info-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .px-info-value {
        font-weight: 600;
        color: #0f172a;
    }

    .px-note {
        background: #f8fafc;
        border: 1px dashed #dbe4ee;
        border-radius: 12px;
        padding: 12px 14px;
    }
</style>

@php
    $phieu = $phieuXuat->phieu;
    $nhaCungCap = $phieu?->nhaCungCap;
    $nguoiTao = $phieu?->nguoiDung;
    $phieuNhapLienQuan = $phieuXuat->phieuNhapLienQuan;
    
    $chiTietList = $chiTietPhieu ?? $phieuXuat->chiTietPhieu;
    $tongSoDong = $tongSoDong ?? $chiTietList->count();
    $tongSoLuong = $tongSoLuong ?? $chiTietList->sum(fn ($ct) => (float) ($ct->so_luong ?? 0));
    $soLoaiSanPham = $soLoaiSanPham ?? $chiTietList->pluck('variant_id')->filter()->unique()->count();
    $loaiXuatLabel = $phieuXuat->loai_xuat === 'tra_hang_nha_cung_cap'
        ? 'Trả hàng NCC'
        : 'Tiêu hủy';
    $loaiXuatBadge = $phieuXuat->loai_xuat === 'tra_hang_nha_cung_cap'
        ? 'bg-warning text-dark'
        : 'bg-danger';
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="fas fa-file-alt me-2 text-danger"></i>Chi tiết phiếu xuất
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang/phieu-xuat') }}">Phiếu xuất</a></li>
                <li class="breadcrumb-item active">PX-{{ $phieuXuat->id_phieu }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="px-stat">
            <div class="label">Mã phiếu</div>
            <div class="value"><span class="badge bg-primary fs-6">PX-{{ $phieuXuat->id_phieu }}</span></div>
            <div class="sub">{{ $loaiXuatLabel }}</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="px-stat">
            <div class="label">Tổng số lượng</div>
            <div class="value text-danger">{{ number_format($tongSoLuong, 0, ',', '.') }}</div>
            <div class="sub">{{ $tongSoDong }} dòng chi tiết</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="px-stat">
            <div class="label">Số loại sản phẩm</div>
            <div class="value">{{ $soLoaiSanPham }}</div>
            <div class="sub">Theo biến thể trong phiếu</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="px-stat">
            <div class="label">Nhà cung cấp</div>
            <div class="value" style="font-size: 18px;">{{ $nhaCungCap?->ten_nha_cung_cap ?? '--' }}</div>
            <div class="sub">Thông tin theo phiếu gốc</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card px-detail-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h5 class="mb-0"><i class="fas fa-circle-info me-2 text-danger"></i>Thông tin phiếu xuất</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="px-info-label">Ngày tạo</div>
                        <div class="px-info-value">{{ optional($phieuXuat->created_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="px-info-label">Người tạo</div>
                        <div class="px-info-value">{{ $nguoiTao?->ho_ten ?? '--' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="px-info-label">Loại xuất</div>
                        <div class="px-info-value">
                            <span class="badge {{ $loaiXuatBadge }}">{{ $loaiXuatLabel }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="px-info-label">Phiếu hệ thống</div>
                        <div class="px-info-value">#{{ $phieu?->id ?? '--' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="px-info-label">Phiếu nhập liên quan</div>
                        <div class="px-info-value">
                            @if($phieuNhapLienQuanRecord)
                                <a href="{{ route('phieu-nhap.show', $phieuNhapLienQuanRecord->id) }}" class="text-decoration-none">
                                    PN-{{ $phieuNhapLienQuanRecord->id_phieu }}
                                </a>
                            @elseif($phieuXuat->id_phieu_nhap_lien_quan)
                                #{{ $phieuXuat->id_phieu_nhap_lien_quan }}
                            @else
                                --
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                    <div class="px-info-label">Ngày phiếu hệ thống</div>
                    <div class="px-info-value">{{ optional($phieu?->created_at)->format('d/m/Y H:i') ?? '--' }}</div>
                </div>
                    <div class="col-md-12">
                        <div class="px-info-label">Lý do</div>
                        <div class="px-info-value">{{ $phieuXuat->ly_do ?: '--' }}</div>
                    </div>
                    <div class="col-md-12">
                        <div class="px-info-label">Ghi chú</div>
                        <div class="px-info-value">{{ $phieuXuat->ghi_chu ?: '--' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card px-detail-card h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h5 class="mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Tóm tắt vận hành</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="px-info-label">Mã phiếu</div>
                    <div class="px-info-value">PX-{{ $phieuXuat->id_phieu }}</div>
                </div>
                <div class="mb-3">
                    <div class="px-info-label">Số dòng chi tiết</div>
                    <div class="px-info-value">{{ $tongSoDong }}</div>
                </div>
                <div class="mb-3">
                    <div class="px-info-label">Tổng số lượng</div>
                    <div class="px-info-value text-danger">{{ number_format($tongSoLuong, 0, ',', '.') }}</div>
                </div>
                <div class="mb-3">
                    <div class="px-info-label">Nhà cung cấp</div>
                    <div class="px-info-value">{{ $nhaCungCap?->ten_nha_cung_cap ?? '--' }}</div>
                </div>
                <div class="mb-0">
                    <div class="px-info-label">Quy tắc xuất</div>
                    <div class="px-info-value">
                        <span class="badge bg-secondary">FEFO - lô gần HSD nhất xuất trước</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card px-detail-card">
    <div class="card-header bg-white border-0 pt-3 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="fas fa-table me-2 text-danger"></i>Danh sách chi tiết phiếu xuất</h5>
            <span class="text-muted small">Sản phẩm, biến thể, lô xuất và số lượng</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-center">Biến thể</th>
                        <th class="text-center">Lô FEFO</th>
                        <th class="text-center">HSD lô</th>
                        <th class="text-center">SL xuất</th>
                        <th class="text-center">Tồn còn lại</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chiTietList as $ct)
                        @php
                            $sanPham = $ct->variant?->product;
                            $tenSanPham = $sanPham?->ten_san_pham ?? ('SP ' . ($ct->id_san_pham ?? '--'));
                            $tenBienThe = $ct->variant?->ten_bien_the;
                            $maVach = $ct->variant?->ma_vach ?? '--';
                            $maLo = $ct->chiTietLoHang?->loHang?->ma_lo ?? ('L-' . ($ct->id_lo_hang ?? '--'));
                            $hsdText = $ct->han_su_dung ? \Carbon\Carbon::parse($ct->han_su_dung)->format('d/m/Y') : '--';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $tenSanPham }}</div>
                                <div class="text-muted small">Mã vạch: {{ $maVach }}</div>
                            </td>
                            <td class="text-center">
                                @if($tenBienThe)
                                    <span class="badge bg-light text-dark border">{{ $tenBienThe }}</span>
                                @else
                                    --
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $maLo }}</span>
                            </td>
                            <td class="text-center">{{ $hsdText }}</td>
                            <td class="text-center fw-bold text-danger">{{ number_format($ct->so_luong ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($ct->so_luong_con_lai ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Không có chi tiết phiếu xuất.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Tổng SL xuất:</td>
                        <td class="text-center fw-bold text-danger">{{ number_format($tongSoLuong, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <div class="px-note small text-muted">
        <i class="fas fa-circle-info me-1"></i>
        Phiếu xuất này được hiển thị theo dữ liệu FEFO đã ghi nhận lúc tạo phiếu.
    </div>
</div>
@endsection
