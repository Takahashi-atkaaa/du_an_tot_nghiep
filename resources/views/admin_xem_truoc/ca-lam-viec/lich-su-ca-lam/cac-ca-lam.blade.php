@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Các là làm trong ngày - SmartMart')

@section('content')

<style>
    .ca-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .ca-card {
        display: block;
        padding: 18px;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        text-decoration: none;
        color: #333;
        font-weight: 600;
        text-align: center;
        border-left: 5px solid #0d6efd;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .ca-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.15);
        background: #f8f9ff;
    }

    .ca-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #0d6efd, #6610f2);
    }

    .thong-ke-ngay{
        display: flex;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .the-thong-ke{
        flex: 1;
        min-width: 250px;
        display: flex;
        align-items: center;
        gap: 15px;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .the-thong-ke:hover{
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }

    .icon{
        font-size: 40px;
    }

    .tieu-de{
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .gia-tri{
        font-size: 22px;
        font-weight: bold;
    }

    .doanh-thu{
        border-left: 5px solid red;
    }

    .doanh-thu .gia-tri{
        color: red;
    }

    .hoa-don{
        border-left: 5px solid green;
    }

    .hoa-don .gia-tri{
        color: green;
    }
    .quay_lai{
        margin-top:20px;
    }
</style>

    <div class="thong-ke-ngay">
        <div class="the-thong-ke doanh-thu">
            <div class="icon">💰</div>
            <div class="noi-dung">
                <div class="tieu-de">Tổng doanh thu</div>
                <div class="gia-tri">{{ number_format($tongDoanhThuNgay) }} đ</div>
            </div>
        </div>

        <div class="the-thong-ke hoa-don">
            <div class="icon">🧾</div>
            <div class="noi-dung">
                <div class="tieu-de">Tổng số hóa đơn</div>
                <div class="gia-tri">{{ $tongSoHoaDonNgay }}</div>
            </div>
        </div>
    </div>

    <div class="ca-container">
        @foreach($caLam2 as $ca)
            <a href="{{route('lich-su-ngay-lam-viec.chi_tiet_ca_lam', ['id_ca'=>$ca->id_ca_lam_viec, 'ngay'=>$ngay])}}" class="ca-card">
                <div class="ca-icon">🕒</div>
                <div class="ca-name">{{ $ca->caLamViec->ten_ca }}</div>
            </a>
        @endforeach
    </div>

    <a href="{{route('lich-su-ca-lam-viec.index')}}" class="btn btn-dark mb-3 quay_lai">Quay lại</a>

@endsection