
@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Lịch sử ca làm việc - SmartMart')

<style>
    a{
        text-decoration: none;
    }
    .thung_chua{
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .vien{
        background: #fff;
        padding:15px;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        transition:0.3;
        border-left:4px solid #0d6efd; 
        width: 100%;
    }
    .vien:hover{
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }

    .ngay{
        font-weight: 600;
        display: block;
        margin-bottom: 5px;
    }

    .tong_don{
        display: block;
        color: #198754;
        margin-bottom: 5px;
    }

    .doanh_thu{
        display: block;
        color: #dc3545;
        font-weight: 600;
    }
    
</style>
@section('content')

<h4>LỊCH SỬ CA LÀM </h4>

    <div class="thung_chua">
        @foreach($ngay2 as $n)
            <a href="{{ route('lich-su-ngay-lam-viec.cac-ca-lam', ['ngay'=> $n->ngay])}}" class="vien">
                <label class="ngay">📅 Ngày: {{ $n->ngay->format('Y-m-d') }}</label>
            </a>
        @endforeach
    </div>

    <div class="mt-3">
        {{ $ngay2->links() }}
    </div>
@endsection


