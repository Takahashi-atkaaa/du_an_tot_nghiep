@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý ca làm việc - SmartMart')
<style>
    .invoice-container{
        background:#fff;
        padding:30px;
        border-radius:15px;
        box-shadow:0 4px 20px rgba(0,0,0,0.08);
        margin:20px auto;
    }

    .invoice-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        border-bottom:2px solid #e9ecef;
        padding-bottom:15px;
        margin-bottom:20px;
    }

    .invoice-title{
        font-size:32px;
        font-weight:700;
        color:#0d6efd;
        margin:0;
    }

    .invoice-info{
        background:#f8f9fa;
        border-radius:10px;
        padding:15px 20px;
        margin-bottom:20px;
    }

    .invoice-info p{
        margin-bottom:8px;
        font-size:15px;
    }

    .invoice-info span{
        font-weight:600;
        color:#495057;
    }

    .table-invoice{
        width:100%;
        border-radius:10px;
        overflow:hidden;
    }

    .table-invoice thead{
        background:#0d6efd;
        color:white;
    }

    .table-invoice th{
        text-align:center;
        vertical-align:middle;
    }

    .table-invoice td{
        vertical-align:middle;
    }

    .table-invoice tbody tr:hover{
        background:#f8f9fa;
        transition:0.3s;
    }

    .money{
        color:#dc3545;
        font-weight:600;
    }

    .total-box{
        margin-top:20px;
        text-align:right;
    }

    .total-box h3{
        display:inline-block;
        background:#fff3cd;
        color:#dc3545;
        padding:12px 25px;
        border-radius:10px;
        font-size:24px;
        font-weight:700;
        border:1px solid #ffe69c;
    }

    .btn-back{
        border-radius:8px;
        padding:8px 20px;
        font-weight:600;
    }

    .badge-phieu{
        background:#198754;
        color:white;
        padding:5px 10px;
        border-radius:20px;
        font-size:13px;
    }

    .badge-none{
        background:#dc3545;
        color:white;
        padding:5px 10px;
        border-radius:20px;
        font-size:13px;
    }
</style>

@section('content')
<div class="invoice-container">

<div class="invoice-header">
    <h1 class="invoice-title">🧾 HÓA ĐƠN BÁN HÀNG</h1>
    <div>
        <strong>#HD{{ $hoaDon->id }}</strong>
    </div>
</div>

<div class="invoice-info">
    <p><span>Nhân viên:</span> {{ $hoaDon->nguoiDung->ho_ten }}</p>
    <p><span>Thời gian:</span> {{ $hoaDon->created_at }}</p>
    <p><span>Ca làm việc:</span> {{ $hoaDon->caLamViec->ten_ca }}</p>
</div>

<table class="table table-bordered table-hover table-invoice">
    <thead>
        <tr>
            <th>STT</th>
            <th>Sản phẩm</th>
            <th>Giá bán</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
            <th>Phiếu nhập</th>
        </tr>
    </thead>

    <tbody>
        @foreach($chiTietHoaDon as $ct)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>

            <td>{{ $ct->sanPham->ten_san_pham ?? 'N/A' }}</td>

            <td class="money">
                {{ number_format($ct->gia_ban) }} đ
            </td>

            <td class="text-center">
                {{ $ct->so_luong }}
            </td>

            <td class="money">
                {{ number_format($ct->gia_ban * $ct->so_luong) }} đ
            </td>

            <td class="text-center">
                @if($ct->chiTietPhieu)
                    <span class="badge-phieu">
                        #{{ $ct->id_phieu }}
                    </span>
                @else
                    <span class="badge-none">
                        Không có
                    </span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<a href="{{ route('lich-su-ngay-lam-viec.cac-ca-lam', $ngay) }}" class="btn btn-secondary btn-back">
    ← Quay lại
</a>

<div class="total-box">
    <h3>
        Tổng tiền:
        {{ number_format($hoaDon->tong_tien_hang) }} đ
    </h3>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const totalBox = document.querySelector('.total-box h3');

    totalBox.animate(
        [
            { transform: 'scale(0.9)', opacity: 0.5 },
            { transform: 'scale(1)', opacity: 1 }
        ],
        {
            duration: 600,
            easing: 'ease-out'
        }
    );

});
</script>

@endsection
