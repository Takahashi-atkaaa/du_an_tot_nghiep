@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết Phiếu xuất - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="fas fa-file-alt me-2 text-danger"></i>
            Chi tiết phiếu xuất
            <span class="badge bg-danger ms-2">PX-{{ $phieuXuat->id_phieu }}</span>
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang/phieu-xuat') }}">Phiếu xuất</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
        <a href="{{ url('admin/api/phieu-xuat/' . $phieuXuat->id . '/export') }}" target="_blank" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>Xuất Excel
        </a>
        <a href="{{ url('admin/kho-hang/phieu-xuat/' . $phieuXuat->id . '/edit') }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit me-1"></i>Sửa phiếu
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Thông tin chi tiết sản phẩm --}}
        <div class="card table-admin mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-list me-2 text-danger"></i>Chi tiết sản phẩm xuất</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Sản phẩm</th>
                                <th class="text-center" style="width:120px">SL xuất</th>
                                <th class="text-center" style="width:130px">HSD lô</th>
                                <th class="text-center" style="width:130px">Lô (FEFO)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($phieuXuat->chiTietPhieu as $i => $ct)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <strong>{{ $ct->variant?->product?->ten_san_pham ?? 'SP ' . $ct->id_san_pham }}</strong>
                                        @if ($ct->variant?->ten_bien_the)
                                            <small class="text-muted"> - {{ $ct->variant->ten_bien_the }}</small>
                                        @endif
                                        <br><small class="text-muted">Mã vạch: {{ $ct->variant?->ma_vach ?? '--' }}</small>
                                    </td>
                                    <td class="text-center text-danger fw-bold">{{ number_format($ct->so_luong) }}</td>
                                    <td class="text-center">
                                        @if ($ct->han_su_dung)
                                            {{ \Carbon\Carbon::parse($ct->han_su_dung)->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            {{ $ct->chiTietLoHang?->loHang?->ma_lo ?? 'L-' . $ct->chiTietLoHang?->id_lo_hang }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Không có chi tiết sản phẩm</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Tổng SL xuất:</td>
                                <td class="text-center fw-bold text-danger">{{ number_format($phieuXuat->chiTietPhieu->sum('so_luong')) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Thông tin phiếu --}}
        <div class="card table-admin mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin phiếu</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">Mã phiếu:</td>
                        <td class="fw-bold">PX-{{ $phieuXuat->id_phieu }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Loại xuất:</td>
                        <td>
                            @if ($phieuXuat->loai_xuat === 'tra_hang_nha_cung_cap')
                                <span class="badge bg-warning text-dark">Trả hàng NCC</span>
                            @else
                                <span class="badge bg-danger">Tiêu hủy</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nhà cung cấp:</td>
                        <td>{{ $phieuXuat->phieu?->nhaCungCap?->ten_nha_cung_cap ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Người tạo:</td>
                        <td>{{ $phieuXuat->phieu?->nguoiDung?->ho_ten ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ngày tạo:</td>
                        <td>{{ $phieuXuat->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lý do:</td>
                        <td>{{ $phieuXuat->ly_do ?? '--' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ghi chú:</td>
                        <td>{{ $phieuXuat->ghi_chu ?? '--' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Cảnh báo FEFO --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body small">
                <h6 class="mb-2"><i class="fas fa-lightbulb me-2 text-warning"></i>Lưu ý</h6>
                <ul class="mb-0 ps-3">
                    <li>Phiếu xuất đã được áp dụng <strong>FEFO</strong> (lô có HSD gần nhất).</li>
                    <li>Chi tiết sản phẩm đã xuất <strong>không thể thay đổi</strong> sau khi tạo.</li>
                    <li>Chỉ có thể sửa thông tin cơ bản (loại xuất, NCC, lý do, ghi chú).</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
