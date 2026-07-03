@extends('nhan_vien_view.layouts.nhan_vien')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow border-0">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Thông tin khách hàng
                    </h4>
                </div>

                <div class="card-body">

                    <div class="text-center mb-4">

                        <div class="avatar-circle mx-auto mb-3">
                            {{ strtoupper(substr($khachHang->ten_khach_hang, 0, 1)) }}
                        </div>

                        <h4 class="fw-bold">
                            {{ $khachHang->ten_khach_hang }}
                        </h4>

                        <span class="badge bg-success px-3 py-2">
                            Khách hàng thân thiết
                        </span>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <div class="info-box">
                                <label>Số điện thoại</label>
                                <div>
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    {{ $khachHang->so_dien_thoai }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="info-box">
                                <label>Email</label>
                                <div>
                                    <i class="fas fa-envelope text-danger me-2"></i>
                                    {{ $khachHang->email ?: 'Chưa cập nhật' }}
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="info-box mb-3">
                        <label>Địa chỉ</label>
                        <div>
                            <i class="fas fa-map-marker-alt text-success me-2"></i>
                            {{ $khachHang->dia_chi ?: 'Chưa cập nhật địa chỉ' }}
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="stat-card bg-warning-subtle">
                                <h6>Điểm tích lũy</h6>
                                <h3 class="mb-0">
                                    {{ number_format($khachHang->diem_tich_luy) }}
                                </h3>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="stat-card bg-success-subtle">
                                <h6>Tổng chi tiêu</h6>
                                <h3 class="mb-0 text-success">
                                    {{ number_format($khachHang->tong_chi_tieu) }} đ
                                </h3>
                            </div>
                        </div>

                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">

                        <a href="{{ route('nhan-vien.khach-hang.index') }}"
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Quay lại
                        </a>

                        <a href="{{ route('nhan-vien.khach-hang.edit_phone',$khachHang) }}"
                           class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>
                            Cập nhật SĐT
                        </a>

                    </div>

                </div>

            </div>

        </div>
    </div>

</div>

@endsection