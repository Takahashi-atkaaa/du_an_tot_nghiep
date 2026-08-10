@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết hóa đơn')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        Chi tiết hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}
    </h4>

    <div>
        <a href="{{ route('admin.hoa-don.index') }}" class="btn btn-secondary">
            Quay lại
        </a>

        <button onclick="window.print()" class="btn btn-success">
            <i class="fas fa-print me-1"></i> In hóa đơn
        </button>
        @if(!in_array($hoaDon->trang_thai, ['Đã hủy', 'Đã trả toàn bộ']))
            <a href="{{ route('admin.hoa-don.doi-tra', $hoaDon->id) }}" class="btn btn-warning ms-2">
                <i class="fas fa-exchange-alt me-1"></i> Đổi / Trả hàng
            </a>
        @endif
    </div>
</div>

<div class="card" id="invoiceArea">
    <div class="card-body">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-success">SMART MART</h3>
            <p class="mb-1">HÓA ĐƠN BÁN HÀNG</p>
            <strong>#HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</strong>
        </div>

        <hr>

        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Ngày tạo:</strong> {{ \Carbon\Carbon::parse($hoaDon->created_at)->format('d/m/Y H:i') }}</p>
                <p><strong>Nhân viên:</strong> {{ $hoaDon->ten_nhan_vien ?? 'Nhân viên' }}</p>
                <p><strong>Khách hàng:</strong> {{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}</p>
            </div>

            <div class="col-md-6">
                <p><strong>SĐT:</strong> {{ $hoaDon->so_dien_thoai ?? '---' }}</p>
                <p><strong>Thanh toán:</strong> {{ $hoaDon->phuong_thuc_thanh_toan }}</p>
                <p><strong>Khuyến mãi:</strong> {{ $hoaDon->ten_khuyen_mai ?? 'Không áp dụng' }}</p>
                <p><strong>Trạng thái:</strong> {{ $hoaDon->trang_thai }}</p>
            </div>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-success">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Mã vạch</th>
                    <th class="text-center">SL</th>
                    <th class="text-end">Giá bán</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chiTiet as $item)
                    <tr>
                        <td>
                            {{ $item->ten_san_pham }}
                            @php
                                $variantName = $item->ten_don_vi ?: $item->ten_bien_the;
                            @endphp
                            @if($variantName)
                                <br><small class="text-muted">({{ $variantName }})</small>
                            @endif
                        </td>
                        <td>{{ $item->ma_vach ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->so_luong }}</td>
                        <td class="text-end">{{ number_format($item->gia_ban, 0, ',', '.') }}đ</td>
                        <td class="text-end">{{ number_format($item->thanh_tien, 0, ',', '.') }}đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="d-flex justify-content-between mb-2">
                    <span>Tổng tiền hàng:</span>
                    <strong>{{ number_format($hoaDon->tong_tien_hang, 0, ',', '.') }}đ</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Giảm giá:</span>
                    <strong>{{ number_format($hoaDon->tien_giam_gia, 0, ',', '.') }}đ</strong>
                </div>

                <div class="d-flex justify-content-between mb-2 fs-5 text-success">
                    <span>Khách cần trả:</span>
                    <strong>{{ number_format($hoaDon->khach_can_tra, 0, ',', '.') }}đ</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Khách đưa:</span>
                    <strong>{{ number_format($hoaDon->tien_khach_dua, 0, ',', '.') }}đ</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Tiền thừa:</span>
                    <strong>{{ number_format($hoaDon->tien_thua, 0, ',', '.') }}đ</strong>
                </div>

                <div class="d-flex justify-content-between mt-2">
                    <span>Điểm sử dụng:</span>
                    <strong class="text-danger">-{{ number_format($hoaDon->diem_su_dung ?? 0) }}</strong>
                </div>

                <div class="d-flex justify-content-between">
                    <span>Điểm thu được:</span>
                    <strong class="text-success">+{{ number_format($hoaDon->diem_thu_duoc ?? 0) }}</strong>
                </div>
            </div>
        </div>

        <hr>

        <div class="mb-4">
            <h5 class="fw-bold mb-3">Chi tiết điểm tích lũy từ hóa đơn</h5>
            @if(isset($diemTichDiems) && $diemTichDiems->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Thời gian</th>
                                <th>Loại</th>
                                <th>Số điểm</th>
                                <th>Lý do</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($diemTichDiems as $diem)
                                @php
                                    $loai = mb_strtolower(trim($diem->loai_bien_dong ?? ''));
                                    $isCong = in_array($loai, ['cong', 'tang', 'tăng']) || ($loai === '' && $diem->so_diem >= 0);
                                @endphp
                                <tr>
                                    <td>{{ optional($diem->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $isCong ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                            {{ $isCong ? 'Tăng điểm' : 'Giảm điểm' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold {{ $isCong ? 'text-success' : 'text-danger' }}">
                                        {{ $isCong ? '+' : '-' }}{{ number_format(abs($diem->so_diem)) }}
                                    </td>
                                    <td>{{ $diem->ly_do ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Hóa đơn này không có thay đổi điểm tích lũy.</p>
            @endif
        </div>

        <hr>

        <p class="text-center mb-0">Cảm ơn quý khách!</p>
    </div>
</div>

@if(isset($phieuDoiTra) && $phieuDoiTra)
<div class="text-center mt-4 mb-4 d-print-none">
    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalPhieuDoiTra">
        <i class="fas fa-file-invoice me-2"></i> Hóa đơn đổi trả sản phẩm
    </button>
</div>

<!-- Modal Hóa đơn đổi trả -->
<div class="modal fade" id="modalPhieuDoiTra" tabindex="-1" aria-labelledby="modalPhieuDoiTraLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalPhieuDoiTraLabel">Chi tiết Phiếu Đổi/Trả #PT{{ str_pad($phieuDoiTra->id, 4, '0', STR_PAD_LEFT) }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <strong>Ghi chú:</strong> {{ $phieuDoiTra->ghi_chu }} <br>
            <strong>Ngày thực hiện:</strong> {{ \Carbon\Carbon::parse($phieuDoiTra->created_at)->format('d/m/Y H:i') }}
        </div>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Phân loại</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Đơn giá</th>
                    <th class="text-end">Thành tiền</th>
                    <th>Loại</th>
                </tr>
            </thead>
            <tbody>
                @foreach($phieuDoiTra->chi_tiet as $ct)
                @php
                    $isTra = str_contains($ct->ghi_chu, 'Hàng khách trả');
                @endphp
                <tr>
                    <td>{{ $ct->ten_san_pham }}</td>
                    <td>{{ $ct->ten_don_vi ?: $ct->ten_bien_the }}</td>
                    <td class="text-center">{{ $ct->so_luong }}</td>
                    <td class="text-end">{{ number_format($ct->gia_nhap, 0, ',', '.') }}đ</td>
                    <td class="text-end">{{ number_format($ct->so_luong * $ct->gia_nhap, 0, ',', '.') }}đ</td>
                    <td>
                        @if($isTra)
                            <span class="badge bg-danger">Trả hàng</span>
                            @if(str_contains($ct->ghi_chu, 'Lỗi'))
                                <span class="badge bg-warning text-dark">Hàng lỗi</span>
                            @endif
                        @else
                            <span class="badge bg-success">Đổi mới</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>
@endif

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #invoiceArea, #invoiceArea * {
        visibility: visible;
    }

    #invoiceArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none;
    }

    .btn,
    .sidebar,
    .navbar,
    .header,
    .topbar {
        display: none !important;
    }
}
</style>
@endsection