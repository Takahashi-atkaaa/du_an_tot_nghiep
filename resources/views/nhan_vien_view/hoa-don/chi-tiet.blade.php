@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Chi tiết hóa đơn')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Chi tiết hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</h1>

    <div>
        <a href="{{ url('/nhan-vien/hoa-don') }}" class="btn btn-secondary">
            Quay lại
        </a>

        <button onclick="window.print()" class="btn btn-success">
            <i class="fas fa-print me-1"></i> In hóa đơn
        </button>
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
            </div>
            <div class="col-md-6">
               <p>
                <strong>Khách hàng:</strong>
                {{ $hoaDon->ten_khach_hang ?? 'Khách lẻ' }}
            </p>

            @if(isset($hoaDon->diem_tich_luy))
            <p>
                <strong>Điểm hiện có:</strong>
                {{ number_format($hoaDon->diem_tich_luy) }} điểm
            </p>
            @endif
               @php
    $phuongThucThanhToan = [
        'cash' => 'Tiền mặt',
        'transfer' => 'Chuyển khoản',
        'card' => 'Quẹt thẻ',
        'tien_mat' => 'Tiền mặt',
        'chuyen_khoan' => 'Chuyển khoản',
    ];
@endphp

<p>
    <strong>Thanh toán:</strong>
    {{ $phuongThucThanhToan[$hoaDon->phuong_thuc_thanh_toan] ?? $hoaDon->phuong_thuc_thanh_toan }}
</p>
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
                 @if($hoaDon->id_khach_hang)
                <div class="d-flex justify-content-between text-primary">
                    <span>Điểm tích lũy nhận được:</span>
                    <strong>+{{ $hoaDon->diem_thu_duoc ?? 0 }} điểm</strong>
                </div>
                @endif

               

                @if(($hoaDon->diem_su_dung ?? 0) > 0)
                <div class="d-flex justify-content-between">
                    <span>Điểm đã dùng:</span>
                    <strong class="text-danger">
                        -{{ number_format($hoaDon->diem_su_dung) }} điểm
                    </strong>
                </div>
                @endif
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

    .btn, .border-bottom {
        display: none !important;
    }
}

</style>
@endsection