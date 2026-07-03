@extends('nhan_vien_view.layouts.nhan_vien')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="card shadow border-0">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-phone-alt me-2"></i>
                        Cập nhật số điện thoại
                    </h4>
                </div>

                <div class="card-body p-4">

                    <div class="text-center mb-4">

                        <div class="avatar-circle mx-auto mb-3">
                            {{ strtoupper(substr($khachHang->ten_khach_hang, 0, 1)) }}
                        </div>

                        <h5 class="fw-bold">
                            {{ $khachHang->ten_khach_hang }}
                        </h5>

                        <p class="text-muted mb-0">
                            Cập nhật thông tin liên hệ khách hàng
                        </p>

                    </div>

                    <form method="POST"
                          action="{{ route('nhan-vien.khach-hang.update_phone',$khachHang) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Số điện thoại mới
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>

                                <input type="text"
                                       name="so_dien_thoai"
                                       value="{{ old('so_dien_thoai', $khachHang->so_dien_thoai) }}"
                                       class="form-control @error('so_dien_thoai') is-invalid @enderror"
                                       placeholder="Nhập số điện thoại">

                            </div>

                            @error('so_dien_thoai')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="d-flex justify-content-between">

                            <a href="{{ route('nhan-vien.khach-hang.show',$khachHang) }}"
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Quay lại
                            </a>

                            <button type="submit"
                                    class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i>
                                Cập nhật
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

</div>

@endsection