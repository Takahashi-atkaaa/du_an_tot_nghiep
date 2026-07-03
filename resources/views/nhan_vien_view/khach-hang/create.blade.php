@extends('nhan_vien_view.layouts.nhan_vien')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow border-0">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Thêm khách hàng mới
                    </h4>
                </div>

                <div class="card-body p-4">

                    <form method="POST"
                          action="{{ route('nhan-vien.khach-hang.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Tên khách hàng
                            </label>

                            <input type="text"
                                   name="ten_khach_hang"
                                   value="{{ old('ten_khach_hang') }}"
                                   class="form-control @error('ten_khach_hang') is-invalid @enderror"
                                   placeholder="Nhập tên khách hàng">

                            @error('ten_khach_hang')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Số điện thoại
                                    </label>

                                    <input type="text"
                                           name="so_dien_thoai"
                                           value="{{ old('so_dien_thoai') }}"
                                           class="form-control @error('so_dien_thoai') is-invalid @enderror"
                                           placeholder="Nhập số điện thoại">

                                    @error('so_dien_thoai')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Email
                                    </label>

                                    <input type="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           class="form-control @error('email') is-invalid @enderror"
                                           placeholder="example@gmail.com">

                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Địa chỉ
                            </label>

                            <textarea name="dia_chi"
                                      rows="4"
                                      class="form-control @error('dia_chi') is-invalid @enderror"
                                      placeholder="Nhập địa chỉ khách hàng">{{ old('dia_chi') }}</textarea>

                            @error('dia_chi')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">

                            <a href="{{ route('nhan-vien.khach-hang.index') }}"
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Quay lại
                            </a>

                            <button type="submit"
                                    class="btn btn-success px-4">
                                <i class="fas fa-save me-1"></i>
                                Lưu khách hàng
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection