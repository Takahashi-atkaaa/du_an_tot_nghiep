@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết giao ca')

<style>
    body{
        background:#f4f7fb;
    }

    .container{
        max-width:1100px;
    }

    .card{
        border:none;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 10px 30px rgba(0,0,0,.08);
    }

    .card-header{
        background:linear-gradient(135deg,#0d6efd,#4f8dfd);
        color:#fff;
        padding:18px 25px;
    }

    .card-header h4{
        margin:0;
        font-weight:700;
    }

    .card-body{
        padding:30px;
        background:#fff;
    }

    .section-title{
        background:#eef5ff;
        color:#0d6efd;
        border-left:5px solid #0d6efd;
        border-radius:8px;
        padding:10px 15px;
        margin-bottom:20px;
        font-size:17px;
        font-weight:700;
    }

    .info-box{
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:20px;
        margin-bottom:30px;
    }

    .info-item{
        margin-bottom:20px;
    }

    .info-label{
        color:#64748b;
        font-size:14px;
        font-weight:600;
        margin-bottom:6px;
    }

    .info-value{
        color:#1e293b;
        font-size:16px;
        font-weight:600;
    }

    .badge{
        padding:8px 15px;
        font-size:14px;
        border-radius:30px;
    }

    .btn-confirm{
        background:linear-gradient(135deg,#16a34a,#22c55e);
        color:#fff;
        border:none;
        border-radius:10px;
        padding:12px 35px;
        font-weight:600;
        transition:.3s;
    }

    .btn-confirm:hover{
        color:#fff;
        transform:translateY(-2px);
        box-shadow:0 8px 18px rgba(34,197,94,.3);
    }
</style>

@section('content')

<div class="container">

    <div class="card">

        <div class="d-inline-block bg-primary text-white px-4 py-2 rounded">
            <h4 class="mb-0">
                <i class="fas fa-file-signature me-2"></i>
                Chi tiết phiếu giao ca
            </h4>
        </div>

        <div class="card-body">

            {{-- Thông tin ca làm --}}
            <div class="section-title">
                👤 Thông tin ca làm
            </div>

            <div class="info-box">

                <div class="row">

                    <div class="col-md-6 info-item">
                        <div class="info-label">Mã giao ca</div>
                        <div class="info-value">
                            {{ $giaoCa->id }}
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Ca làm việc</div>
                        <div class="info-value">
                            {{ $giaoCa->caLamViec->ten_ca }}
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Trưởng ca bàn giao</div>
                        <div class="info-value">
                            {{ $giaoCa->truongCaBanGiao->ho_ten }}
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Trưởng ca nhận</div>
                        <div class="info-value">
                            {{ $giaoCa->truongCaNhanCa->ho_ten }}
                        </div>
                    </div>

                </div>

            </div>

            {{-- Thời gian --}}
            <div class="section-title">
                🕒 Thời gian ca làm
            </div>

            <div class="info-box">

                <div class="row">

                    <div class="col-md-6 info-item">
                        <div class="info-label">Thời gian bắt đầu</div>
                        <div class="info-value">
                            {{ $giaoCa->thoi_gian_bat_dau_ca }}
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Thời gian kết thúc</div>
                        <div class="info-value">
                            {{ $giaoCa->thoi_gian_ket_thuc_ca }}
                        </div>
                    </div>

                </div>

            </div>

            {{-- Tiền --}}
            <div class="section-title">
                💰 Thông tin tiền mặt
            </div>

            <div class="info-box">

                <div class="row">

                    <div class="col-md-4 info-item">
                        <div class="info-label">Tiền đầu ca</div>
                        <div class="info-value">
                            {{ number_format($giaoCa->tien_dau_ca,0,',','.') }} VNĐ
                        </div>
                    </div>

                    <div class="col-md-4 info-item">
                        <div class="info-label">Tiền cuối ca</div>
                        <div class="info-value">
                            {{ number_format($giaoCa->tien_cuoi_ca,0,',','.') }} VNĐ
                        </div>
                    </div>

                    <div class="col-md-4 info-item">

                        <div class="info-label">
                            Chênh lệch
                        </div>

                        <div class="info-value">

                            @if($giaoCa->chenh_lech > 0)

                                <span class="text-success">
                                    +{{ number_format($giaoCa->chenh_lech,0,',','.') }} VNĐ
                                </span>

                            @elseif($giaoCa->chenh_lech < 0)

                                <span class="text-danger">
                                    {{ number_format($giaoCa->chenh_lech,0,',','.') }} VNĐ
                                </span>

                            @else

                                <span class="text-primary">
                                    0 VNĐ
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

            {{-- Thông tin khác --}}
            <div class="section-title">
                📋 Thông tin khác
            </div>

            <div class="info-box">

                <div class="row">

                    <div class="col-md-6 info-item">

                        <div class="info-label">
                            Trạng thái
                        </div>

                        <div class="info-value">

                            @if($giaoCa->trang_thai == 0)

                                <span class="badge bg-warning text-dark">
                                    Chờ xác nhận
                                </span>

                            @elseif($giaoCa->trang_thai == 2)
                                <span class="badge bg-danger">
                                    Đã bị từ chối
                                </span>
                            @else

                                <span class="badge bg-success">
                                    Đã xác nhận
                                </span>

                            @endif

                        </div>

                    </div>

                    <div class="col-md-6 info-item">

                        <div class="info-label">
                            Ngày tạo
                        </div>

                        <div class="info-value">
                            {{ $giaoCa->created_at }}
                        </div>

                    </div>

                    <div class="col-12">

                        <div class="info-label">
                            Ghi chú
                        </div>

                        <div class="info-value">
                            {{ $giaoCa->ghi_chu ?? 'Không có ghi chú' }}
                        </div>

                    </div>

                </div>

            </div>

            @if(
                Auth::id() == $giaoCa->id_truong_ca_nhan_ca &&
                $giaoCa->trang_thai == 0 || Auth::id() == $giaoCa->id_truong_ca_nhan_ca &&
                $giaoCa->trang_thai == 2
            )

                <div class="d-flex justify-content-between align-items-center mt-4">

                    <form action="{{ route('giao-ca.tu-choi', $giaoCa->id) }}"
                          method="POST">

                        @csrf
                        @method('PUT')

                        <button
                            type="submit"
                            class="btn btn-danger">
                            Từ chối nhật giao ca

                        </button>

                    </form>

                    <form action="{{ route('giao-ca.xac-nhan', $giaoCa->id) }}"
                          method="POST">

                        @csrf
                        @method('PUT')

                        <button
                            type="submit"
                            class="btn btn-primary">
                            Xác nhận giao ca

                        </button>

                    </form>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection