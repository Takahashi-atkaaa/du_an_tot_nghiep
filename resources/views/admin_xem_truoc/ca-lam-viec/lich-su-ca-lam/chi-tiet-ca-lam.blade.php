@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết lịch sửa ca làm việc - SmartMart')

<style>
    .info-card {
        border: none;
        height: 120px;
        border-radius: 18px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .info-card .card-body{
        height: 100%;
    }

    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .icon-primary {
        background: #e7f0ff;
        color: #2563eb;
    }

    .icon-success {
        background: #eaf7ef;
        color: #198754;
    }

    .icon-warning {
        background: #fff4df;
        color: #f59e0b;
    }

    .icon-danger {
        background: #fdecec;
        color: #dc3545;
    }

    .icon-purple {
        background: #f3e8ff;
        color: #9333ea;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
        transition: 0.2s;
    }

    .card-header {
        font-weight: 600;
    }

    .badge {
        padding: 8px 12px;
        font-size: 13px;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }
</style>

@section('content')

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
                                <span>{{ $ca->gio_bat_dau }} - {{ $ca->gio_ket_thuc }}</span>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card info-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box icon-success me-3">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>

                        <div>
                            <div class="text-muted">Ngày </div>
                            <h5 class="mb-0">{{ date('Y/m/d', strtotime($ngay)) }}</h5>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-3">
                <div class="card info-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box icon-warning me-3">
                            <i class="bi bi-alarm-fill"></i>
                        </div>

                        <div>
                            <div class="text-muted">Tên ca</div>
                            <h4 class="mb-0">
                                {{ $ca->ten_ca }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

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
                                {{ number_format($tongDoanhThuCuaCa) }}đ
                            </h5>
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

                        <div class="icon-box icon-purple me-3">
                            <i class="bi bi-receipt"></i>
                        </div>

                        <div>
                            <div class="text-muted">
                                Số hóa đơn
                            </div>

                            <h3 class="mb-0">
                                {{ $tongHoaDoncuaCa }}
                            </h3>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card info-card">
                    <div class="card-body d-flex align-items-center">

                        <div class="icon-box icon-primary me-3">
                            <i class="bi bi-people-fill"></i>
                        </div>

                        <div>
                            <div class="text-muted">
                                Tổng nhân viên
                            </div>

                            <h3 class="mb-0">
                                {{ $tongNhanVienTrongCa }}
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
                                Tiền mặt trong ca
                            </h6>

                            <h4 class="fw-bold text-success mb-0">
                                {{ number_format($tongTienMatCuaCa, 0, ',', '.') }} đ
                            </h4>

                        </div>

                    </div>
                </div>
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
                                            <a href="{{ route('lich-su-ca-lam-giao-ca.tao-giao-ca', ['id_ca' => $ca->id, 'ngay' => $ngay]) }}"
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
                                <th>Điểm danh</th>
                                <th>Hành động</th>
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

                                    <td>

                                        @if ($nv->diemDanh)
                                            <span class="badge bg-success">
                                                {{ $nv->diemDanh->trang_thai_vao_lam }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                Chưa điểm danh
                                            </span>
                                        @endif

                                    </td>

                                    <td>
                                        @if (auth()->user()->id_vai_tro == 1)

                                            @if (in_array($nv->id, $danhSachDiemDanh))
                                                <a href="{{ route('lich-su-ca-lam.chi_tiet_diem_danh', [
                                                    'id_chia_ca_lam_viec' => $nv->id,
                                                    'id_nv' => $nv->id_nguoi_dung,
                                                ]) }}"
                                                    class="btn btn-warning">
                                                    Chi tiết
                                                </a>
                                            @else
                                                <a href="{{ route('lich-su-ca-lam.tao-diem-danh-bu', [
                                                    'id_chia_ca_lam_viec' => $nv->id,
                                                    'id_nv' => $nv->id_nguoi_dung,
                                                ]) }}"
                                                    class="btn btn-primary">
                                                    Chấm công bù
                                                </a>
                                            @endif
                                        @else
                                            {{-- nếu không phải admin thì phải kiểm tra id của trưởng ca kia có nằm trong có đó hay không --}}
                                            @foreach ($danhSachTrongCaTrongCa as $truong_ca)
                                                @if ($truong_ca->id_nguoi_dung == Auth::id())
                                                    @if (in_array($nv->id, $danhSachDiemDanh))
                                                        <a href="{{ route('lich-su-ca-lam.chi_tiet_diem_danh', [
                                                            'id_chia_ca_lam_viec' => $nv->id,
                                                            'id_nv' => $nv->id_nguoi_dung,
                                                        ]) }}"
                                                            class="btn btn-warning">
                                                            Chi tiết
                                                        </a>
                                                    @else
                                                        <a href="{{ route('lich-su-ca-lam.tao-diem-danh-bu', [
                                                            'id_chia_ca_lam_viec' => $nv->id,
                                                            'id_nv' => $nv->id_nguoi_dung,
                                                        ]) }}"
                                                            class="btn btn-primary">
                                                            Chấm công bù
                                                        </a>
                                                    @endif
                                                    @break
                                                @endif
                                            @endforeach
                                        @endif

                                    </td>


                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>
    <a href="{{ route('lich-su-ngay-lam-viec.cac-ca-lam', ['ngay' => $ngay]) }}" class="btn btn-dark">
        Quay lại
    </a>
    </div>

@endsection
