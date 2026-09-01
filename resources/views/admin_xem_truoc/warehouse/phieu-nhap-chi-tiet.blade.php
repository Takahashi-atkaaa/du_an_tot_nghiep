@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết Phiếu nhập - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-file-alt me-2 text-success"></i>Chi tiết Phiếu nhập</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
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

<div class="card table-admin mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin phiếu nhập</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="text-muted small">Mã phiếu</label>
                <div class="fw-bold"><span class="badge bg-primary fs-6">PN-{{ $phieuNhap->id_phieu }}</span></div>
            </div>
            <div class="col-md-3">
                <label class="text-muted small">Loại nhập</label>
                <div class="fw-bold">
                    @if($phieuNhap->loai_nhap === 'mua_hang')
                        <span class="badge bg-success">Nhập mua hàng</span>
                    @else
                        <span class="badge bg-info">Trả lại từ khách</span>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <label class="text-muted small">Nhà cung cấp</label>
                <div class="fw-bold">{{ $phieuNhap->phieu->nhaCungCap->ten_nha_cung_cap ?? '--' }}</div>
            </div>
            <div class="col-md-3">
                <label class="text-muted small">Người tạo</label>
                <div class="fw-bold">{{ $phieuNhap->phieu->nguoiDung->ho_ten ?? $phieuNhap->phieu->id_nguoi_dung ?? '--' }}</div>
            </div>
            <div class="col-md-3">
                <label class="text-muted small">Ngày tạo</label>
                <div class="fw-bold">{{ $phieuNhap->created_at ? $phieuNhap->created_at->format('d/m/Y H:i') : '' }}</div>
            </div>
            <div class="col-md-9">
                <label class="text-muted small">Ghi chú</label>
                <div>{{ $phieuNhap->ghi_chu ?: '--' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card table-admin">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Chi tiết sản phẩm nhập</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:35%">Sản phẩm</th>
                        <th class="text-center" style="width:12%">SL nhập</th>
                        <th class="text-center" style="width:15%">Giá nhập</th>
                        <th class="text-center" style="width:13%">Hạn sử dụng</th>
                        <th class="text-center" style="width:10%">Mã lô</th>
                        <th class="text-end" style="width:15%">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tongGiaTri = 0;
                    @endphp
                    @forelse($phieuNhap->chiTietPhieu as $chiTiet)
                        @php
                            $thanhTien = ($chiTiet->so_luong ?? 0) * ($chiTiet->gia_nhap ?? 0);
                            $tongGiaTri += $thanhTien;
                            $sanPham = $chiTiet->variant ?? $chiTiet->sanPham;
                            $tenSanPham = $sanPham ? $sanPham->ten_san_pham : ('SP-' . $chiTiet->id_san_pham);
                            if($chiTiet->variant && $chiTiet->variant->ten_bien_the) {
                                $tenSanPham .= ' - ' . $chiTiet->variant->ten_bien_the;
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $tenSanPham }}</div>
                                @if($sanPham && $sanPham->ma_vach)
                                    <small class="text-muted">Mã vạch: {{ $sanPham->ma_vach }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($chiTiet->so_luong ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($chiTiet->gia_nhap ?? 0, 0, ',', '.') }} đ</td>
                            <td class="text-center">
                                @if($chiTiet->han_su_dung)
                                    {{ \Carbon\Carbon::parse($chiTiet->han_su_dung)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($chiTiet->loHang)
                                    <span class="badge bg-secondary">{{ $chiTiet->loHang->ma_lo ?? 'L-' . $chiTiet->loHang->id }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($thanhTien, 0, ',', '.') }} đ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Không có chi tiết sản phẩm</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="text-end fw-bold fs-5">Tổng cộng:</td>
                        <td class="text-end fw-bold text-danger fs-5">{{ number_format($tongGiaTri, 0, ',', '.') }} đ</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
