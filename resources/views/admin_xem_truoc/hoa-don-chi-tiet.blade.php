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
        @if($hoaDon->trang_thai !== 'Đã hủy')
    <a href="{{ route('admin.hoa-don.tra-hang', $hoaDon->id) }}"
       class="btn btn-warning">
        <i class="fas fa-undo me-1"></i> Trả hàng
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
                        <td>{{ $item->ten_san_pham }}</td>
                        <td>{{ $item->ma_vach }}</td>
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

                <div class="d-flex justify-content-between">
                    <span>Tiền thừa:</span>
                    <strong>{{ number_format($hoaDon->tien_thua, 0, ',', '.') }}đ</strong>
                </div>
            </div>
        </div>

        <hr>

        <p class="text-center mb-0">Cảm ơn quý khách!</p>
    </div>
</div>

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