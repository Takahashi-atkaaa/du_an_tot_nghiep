@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Các là làm trong ngày - SmartMart')

@section('content')

<style>
body{
    background:#f5f7fb;
}

/*===================== Thống kê ngày =====================*/

.thong-ke-ngay{
    display:flex;
    gap:20px;
    margin:20px 0 30px;
    align-items:stretch;
    flex-wrap:wrap;
}

.the-thong-ke{
    flex:1;
    min-width:260px;
    display:flex;
    align-items:center;
    gap:18px;
    padding:22px;
    border-radius:15px;
    background:#fff;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    transition:.25s;
}

.the-thong-ke:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 24px rgba(0,0,0,.15);
}

.icon{
    width:70px;
    height:70px;
    border-radius:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:34px;
    color:white;
}

.tieu-de{
    font-size:15px;
    color:#6c757d;
    margin-bottom:6px;
}

.gia-tri{
    font-size:26px;
    font-weight:700;
}

/* doanh thu */

.doanh-thu{
    border-left:6px solid #dc3545;
}

.doanh-thu .icon{
    background:#dc3545;
}

.doanh-thu .gia-tri{
    color:#dc3545;
}

/* hóa đơn */

.hoa-don{
    border-left:6px solid #198754;
}

.hoa-don .icon{
    background:#198754;
}

.hoa-don .gia-tri{
    color:#198754;
}

/* nút quay lại */

.quay_lai{
    min-width:220px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#000000;
    color:rgb(255, 255, 255) !important;
    text-decoration:none;
    border-radius:15px;
    font-size:18px;
    font-weight:600;
    box-shadow:0 6px 18px rgba(13,110,253,.25);
    transition:.25s;
}

.quay_lai:hover{
    background:#021c43;
    transform:translateY(-4px);
}

/*===================== Card ca =====================*/

.ca-container{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:18px;
    margin-bottom:30px;
}

.ca-card{
    background:white;
    border-radius:15px;
    padding:22px;
    text-align:center;
    text-decoration:none;
    color:#333;
    border-left:6px solid #0d6efd;
    box-shadow:0 5px 16px rgba(0,0,0,.08);
    transition:.25s;
}

.ca-card:hover{
    background:#f8fbff;
    color:#0d6efd;
    transform:translateY(-5px);
}

.ca-active{
    background:#0d6efd;
    color:white !important;
}

.ca-active .ca-name{
    color:white;
}

.ca-icon{
    font-size:34px;
    margin-bottom:10px;
}

.ca-name{
    font-size:18px;
    font-weight:700;
}

/*===================== Card thống kê =====================*/

.card{
    border:none;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 5px 18px rgba(0,0,0,.08);
}

.info-card{
    transition:.25s;
}

.info-card:hover{
    transform:translateY(-4px);
}

.info-card .card-body{
    padding:22px;
}

.icon-box{
    width:60px;
    height:60px;
    border-radius:14px;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-size:25px;
}

.icon-primary{background:#0d6efd;}
.icon-success{background:#198754;}
.icon-warning{background:#ffc107;color:#333;}
.icon-danger{background:#dc3545;}
.icon-purple{background:#6f42c1;}
.icon-info{background:#0dcaf0;}

.text-muted{
    font-size:14px;
}

h3,h4,h5{
    font-weight:700;
}

/*===================== Bảng =====================*/

.table{
    margin-bottom:0;
}

.table thead th{
    background:#f8f9fa;
    border-bottom:2px solid #dee2e6;
    padding:15px;
}

.table td{
    padding:15px;
    vertical-align:middle;
}

.table-hover tbody tr:hover{
    background:#f8fbff;
}

/*===================== Button =====================*/

.btn{
    border-radius:10px;
    font-weight:500;
}

.btn-warning{
    color:white;
}

.badge{
    padding:8px 12px;
    font-size:13px;
}

/*===================== Responsive =====================*/

@media(max-width:768px){

    .thong-ke-ngay{
        flex-direction:column;
    }

    .quay_lai{
        min-height:90px;
    }

.ca-active{
    background:#0d6efd;
    color:white !important;
}

.ca-active .ca-name{
    color:white;
}

.ca-active{
    background:linear-gradient(135deg,#0d6efd,#4f8dfd);
    color:white !important;
    border-left-color:#fff;
    box-shadow:0 10px 25px rgba(13,110,253,.35);
}

.icon-danger {
    background-color: #ffe5e5;
    color: #dc3545;
}

.icon-danger i {
    font-size: 22px;
}

.the-thong-ke.ngay {
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
}



}

</style>


   <div class="row mb-4 align-items-stretch">

    <div class="thong-ke-ngay">



    <div class="the-thong-ke ngay">
        <div class="icon">📅</div>

        <div>
            <div class="tieu-de">Ngày</div>
            <h5 class="mb-0">
                {{ date('Y/m/d', strtotime($ngay)) }}
            </h5>
        </div>
    </div>

    <div class="the-thong-ke doanh-thu">
        <div class="icon">💰</div>

        <div>
            <div class="tieu-de">Tổng doanh thu ngày</div>
            <div class="gia-tri">
                {{ number_format($tongDoanhThuNgay) }} đ
            </div>
        </div>
    </div>

    <div class="the-thong-ke hoa-don">
        <div class="icon">🧾</div>

        <div>
            <div class="tieu-de">Tổng số hóa đơn ngày</div>
            <div class="gia-tri">
                {{ $tongSoHoaDonNgay }}
            </div>
        </div>
    </div>

    <div class="the-thong-ke ca">
        <select class="form-select ca-select"
                onchange="if (this.value) window.location.href = this.value;">

            <option value="" disabled>-- Chọn ca làm --</option>

            @foreach ($caLam as $ca)
                <option
                    value="{{ route('lich-su-ngay-lam-viec.cac-ca-lam', [
                        'ngay' => $ngay,
                        'id_ca' => $ca->id_ca_lam_viec
                    ]) }}"
                    {{ $caDangChon->id == $ca->id_ca_lam_viec ? 'selected' : '' }}
                 >
                    {{ $ca->caLamViec->ten_ca }}
                </option>
            @endforeach

        </select>
    </div>

</div>

    {{-- dữ liệu chi tiết của ca --}}
        <div class="container-fluid py-4">

        {{-- Thông tin tổng quan --}}
        <div class="row g-4 mb-4">

            <div class="col-md-3">
                <div class="card info-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box icon-primary me-3">
                            <i class="bi bi-clock-fill"></i>
                        </div>

                        <div>
                            <div class="text-muted">Thời lượng ca</div>
                            <h4>
                                <span>{{ $caDangChon->gio_bat_dau }}- {{ $caDangChon->gio_ket_thuc }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <a style="text-decoration: none;" href="{{ route('hoa-don-cua-ca.hoan-thanh', ['ngay' => $ngay, 'id_ca' => $caDangChon->id]) }}" class="the-thong-ke hoa-don">
                    <div class="icon">🧾</div>

                    <div>
                        <div class="tieu-de">Tổng số hóa đơn ca</div>
                        <div class="gia-tri">
                            {{ $tongHoaDoncuaCa }}
                        </div>
                    </div>
                </a>
            </div>


            <div class="col-md-3">
                <a href="{{ route('hoa-don-cua-ca.huy', ['ngay' => $ngay,'id_ca' => $caDangChon->id]) }}"
                class="the-thong-ke hoa-don"
                style="text-decoration: none; border-left: 4px solid #dc3545;">

                    <div class="icon-box icon-danger me-3">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>

                    <div>
                        <div class="tieu-de">Số hóa đơn hủy</div>

                        <div class="gia-tri" style="color: #dc3545;">
                            {{ $cacHoaDonBiHuyTrongCa }}
                        </div>
                    </div>

                </a>
            </div>


                        {{-- Giao ca --}}
            <div class="col-md-3">
                <div class="card info-card h-100">
                    <div class="card-body d-flex align-items-center">

                        <div class="icon-box icon-info me-3">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>

                        <div class="flex-grow-1">

                            <h6 class="text-muted mb-1">
                                Trạng thái giao ca
                            </h6>

                            {{-- xét xem giao ca có tồn tại hay không --}}
                            @if($giaoCa)
                                {{-- xét theo id người vào --}}
                                @if($giaoCa->trang_thai ==0)
                                    @if(Auth::user()->id_vai_tro == 1 || $danhSachTrongCaTrongCa->contains('id_nguoi_dung', Auth::id()))
                                    {{-- nếu giao ca tồn tại mà id đó là của admin hoặc trưởng ca của ca thì được phép sửa giao ca --}}
                                            <a href="{{ route('giao-ca.sua', ['id' => $giaoCa->id]) }}"
                                                class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                                Sửa giao ca
                                            </a>
                                    @elseif(Auth::id() == $giaoCa->id_truong_ca_nhan_ca)
                                        {{-- nếu ca thuộc người nhân phải vào xác nhận --}}
                                            <a href="{{ route('giao-ca.chi-tiet', ['id' => $giaoCa->id]) }}"
                                                class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-eye"></i>
                                                Bạn cần xác nhận ca
                                            </a>
                                    @else
                                        {{-- nếu giao ca tồn tại mà id không phải admin hay trưởng ca thuộc ca thì chỉ được xem chi tiết --}}
                                            <a href="{{ route('giao-ca.chi-tiet', ['id' => $giaoCa->id]) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                                Xem chi tiết
                                            </a>
                                    @endif
                                @elseif($giaoCa->trang_thai ==2)
                                     @if(Auth::user()->id_vai_tro == 1 || $danhSachTrongCaTrongCa->contains('id_nguoi_dung', Auth::id()))
                                    {{-- nếu giao ca tồn tại mà id đó là của admin hoặc trưởng ca của ca thì được phép sửa giao ca --}}
                                            <span style="color:red";>giao ca bị từ chối</span>
                                            <a href="{{ route('giao-ca.sua', ['id' => $giaoCa->id]) }}"
                                                class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                                Sửa giao ca
                                            </a>
                                    @elseif(Auth::id() == $giaoCa->id_truong_ca_nhan_ca)
                                        {{-- nếu ca thuộc người nhân phải vào xác nhận --}}
                                        <span style="color:red";>Bạn đã từ chối giao ca</span>
                                            <a href="{{ route('giao-ca.chi-tiet', ['id' => $giaoCa->id]) }}"
                                                class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-eye"></i>
                                                Cập nhật lại trạng thái
                                            </a>
                                    @else
                                        {{-- nếu giao ca tồn tại mà id không phải admin hay trưởng ca thuộc ca thì chỉ được xem chi tiết --}}
                                        <span style="color:red";>giao ca bị từ chối</span>
                                            <a href="{{ route('giao-ca.chi-tiet', ['id' => $giaoCa->id]) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                                Xem chi tiết
                                            </a>
                                    @endif
                                @else
                                    @if(Auth::user()->id_vai_tro == 1)
                                            <a href="{{ route('giao-ca.sua', ['id' => $giaoCa->id]) }}"
                                                class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                                Sửa giao ca
                                            </a>
                                    @else
                                            <a href="{{ route('giao-ca.chi-tiet', ['id' => $giaoCa->id]) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                                Xem chi tiết
                                            </a>
                                    @endif
                                @endif

                            {{-- trường hợp giao ca chưa tồn tại  --}}
                            @else
                                @if(Auth::user()->id_vai_tro == 1 || $danhSachTrongCaTrongCa->contains('id_nguoi_dung', Auth::id()))
                                {{-- nếu là admin hoặc trưởng ca thuộc ca thì được phép tạo mới giao ca --}}
                                        <div class="mt-3">
                                            <a href="{{ route('lich-su-ca-lam-giao-ca.tao-giao-ca', ['id_ca' => $caDangChon->id, 'ngay' => $ngay]) }}"
                                                class="btn btn-success btn-sm">
                                                <i class="bi bi-plus-circle"></i>
                                                Tạo giao ca
                                            </a>
                                        </div>
                                @else
                                {{-- trường hợp còn lại sẽ hiển thị chưa tồn tại giao ca --}}
                                    <span>Chưa tồn tại giao ca</span>
                                @endif
                            @endif
                            

                        </div>

                    </div>
                </div>
            </div>


        </div>


        {{-- Thống kê --}}
        <div class="row g-4 mb-4">


            <div class="col-md-3">
                <div class="card info-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box icon-danger me-3">
                            <i class="bi bi-cash-stack"></i>
                        </div>

                        <div>
                            <div class="text-muted">
                                Doanh thu ca
                            </div>

                            <h5 class="mb-0 text-danger">
                                {{ number_format($tongDoanhThuCuaCa) }}  VND
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

           

            <div class="col-md-3">
                <div class="card info-card">
                    <div class="card-body d-flex align-items-center">

                        <div class="icon-box icon-primary me-3">
                            <i class="bi bi-credit-card-2-front-fill"></i>
                        </div>

                        <div>
                            <div class="text-muted">
                                Doanh thu chuyển khoản trong ca
                            </div>

                            <h3 class="mb-0">
                                {{ number_format($doanhThuChuyenKhoan, 0, ',', '.') }} VND
                            </h3>
                        </div>

                    </div>
                </div>
            </div>

                        {{-- Tiền mặt trong ca --}}
            <div class="col-md-3">
                <div class="card info-card">
                    <div class="card-body d-flex align-items-center">

                        <div class="icon-box icon-success me-3">
                            <i class="bi bi-cash-stack"></i>
                        </div>

                        <div>

                            <h6 class="text-muted mb-1">
                                Doanh thu tiền mặt trong ca
                            </h6>

                            <h4 class="fw-bold text-success mb-0">
                                {{ number_format($doanhThuTienMatCuaCa, 0, ',', '.') }} VND
                            </h4>

                        </div>

                    </div>
                </div>
            </div>

            {{-- Số lượng nhân viên --}}
            <div class="col-md-3">
                <div class="card info-card">
                    <div class="card-body d-flex align-items-center">

                        <div class="icon-box icon-warning me-3">
                            <i class="bi bi-people-fill"></i>
                        </div>

                        <div>

                            <h6 class="text-muted mb-1">
                                Số lượng nhân viên trong ca
                            </h6>

                            <h4 class="fw-bold text-warning mb-0">
                                {{ $tongNhanVienTrongCa }}
                            </h4>

                        </div>

                    </div>
                </div>




        </div>

    </div>

    {{-- Hai bảng --}}
    <div class="row">

        {{-- Hóa đơn --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">

                <div class="card-header bg-primary text-white">
                    <i class="bi bi-receipt me-2"></i>
                    Danh sách hóa đơn
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>STT</th>
                                <th>Khách hàng</th>
                                <th>Trạng thái</th>
                                <th>Tổng tiền</th>
                                <th>Thời gian</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($danhSachHoaDon as $hoaDon)
                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        {{ $hoaDon->khachHang?->ten_khach_hang ?? 'Khách lẻ' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-success">
                                            {{ $hoaDon->trang_thai }}
                                        </span>
                                    </td>

                                    <td class="text-danger fw-bold">
                                        {{ number_format($hoaDon->khach_can_tra) }}đ
                                    </td>

                                    <td>
                                        {{ $hoaDon->created_at->format('H:i d/m/Y') }}
                                    </td>

                                    <td>
                                        <a href="{{ route('lich-su-ca-lam-chi-tiet-hoa-don.show', ['id_hoaDon' => $hoaDon->id, 'ngay' => $ngay]) }}"
                                            class="btn btn-warning">
                                            Chi tiết
                                        </a>
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>
        </div>

        {{-- Nhân viên --}}
        <div class="col-lg-6 mb-4">

            <div class="card shadow h-100">

                <div class="card-header bg-success text-white">
                    <i class="bi bi-people-fill me-2"></i>
                    Nhân viên trong ca
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-hover">

                        <thead class="table-light">
                            <tr>
                                <th>Tên nhân viên</th>
                                <th>SĐT</th>
                                <th>Vai trò</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($danhSachNhanVienTrongCa as $nv)
                                <tr>

                                    <td>
                                        {{ $nv->nguoiDung->ho_ten }}
                                    </td>

                                    <td>
                                        {{ $nv->nguoiDung->sdt }}
                                    </td>

                                    <td>
                                        {{ $nv->vai_tro_trong_ca }}
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    </div>

@endsection