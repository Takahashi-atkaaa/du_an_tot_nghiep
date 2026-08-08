@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Lịch sử ca làm việc - SmartMart')

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
        box-shadow:0 12px 30px rgba(0,0,0,.08);
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
        padding:35px;
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
        background:#fafcff;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:20px;
        margin-bottom:30px;
    }

    .form-label{
        font-weight:600;
        color:#334155;
    }

    .form-control,
    .form-select{
        height:48px;
        border-radius:10px;
        border:1px solid #dbe4f0;
        transition:.3s;
    }

    textarea.form-control{
        height:120px;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:#0d6efd;
        box-shadow:0 0 0 .2rem rgba(13,110,253,.15);
    }

    .btn-save{
        background:linear-gradient(135deg,#0d6efd,#4f8dfd);
        color:#fff;
        border:none;
        border-radius:10px;
        padding:10px 35px;
        font-weight:600;
        transition:.3s;
    }

    .btn-save:hover{
        color:#fff;
        transform:translateY(-2px);
        box-shadow:0 10px 20px rgba(13,110,253,.25);
    }
</style>

@section('content')

<div class="container">

    <div class="card">

        <div class="d-inline-block bg-primary text-white px-4 py-2 rounded">
            <h4 class="mb-0">
                <i class="fas fa-file-signature me-2"></i>
                Thêm phiếu giao ca
            </h4>
        </div>

        <div class="card-body">

            <form action="{{ route('giao-ca.store') }}" method="POST">

                @csrf

                {{-- Thông tin ca làm --}}
                <div class="section-title">
                    <i class="fas fa-users me-2"></i>
                    Thông tin ca làm
                </div>

                <div class="info-box">

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Trưởng ca bàn giao
                            </label>

                            <select name="id_truong_ca_ban_giao" class="form-select">

                                <option value="">
                                    -- Chọn trưởng ca --
                                </option>

                                @foreach ($danhSachTruongCa as $nguoi)

                                    <option value="{{ $nguoi->id }}"
                                        {{ old('id_truong_ca_ban_giao') == $nguoi->id ? 'selected' : '' }}>

                                        {{ $nguoi->ho_ten }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-4 mb-3">

                            {{-- id ca --}}
                            input
                            <input type="hidden" name="id_ca_lam_viec" value="{{ $ca->id }}">

                            {{-- ngày --}}

                            <label class="form-label">
                                Trưởng ca nhận
                            </label>

                            <select name="id_truong_ca_nhan_ca" class="form-select">

                                <option value="">
                                    -- Chọn trưởng ca --
                                </option>

                                @foreach ($danhSachTruongCa as $nguoi)

                                    <option value="{{ $nguoi->id }}"
                                        {{ old('id_truong_ca_nhan_ca') == $nguoi->id ? 'selected' : '' }}>

                                        {{ $nguoi->ho_ten }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                         <div class="col-md-4">

                            <label class="form-label">
                                Trạng thái
                            </label>

                            <select name="trang_thai" class="form-select">

                                <option value="0">
                                    Chờ xác nhận
                                </option>

                            </select>

                        </div>

                    </div>


                </div>

                {{-- Thời gian --}}
                <div class="section-title">
                    <i class="far fa-clock me-2"></i>
                    Thời gian ca làm
                </div>

                <div class="info-box">

                    <div class="row">

                        <div class="col-md-6">

                            <label class="form-label">
                                Thời gian bắt đầu
                            </label>

                            <input
                                type="datetime-local"
                                class="form-control"
                                name="thoi_gian_bat_dau_ca"
                                value="{{ \Carbon\Carbon::parse($ca->gio_bat_dau)->format('Y-m-d\TH:i') }}">
                                

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Thời gian kết thúc
                            </label>

                            <input
                                type="datetime-local"
                                class="form-control"
                                name="thoi_gian_ket_thuc_ca"
                                value="{{ \Carbon\Carbon::parse($ca->gio_ket_thuc)->format('Y-m-d\TH:i') }}">

                        </div>

                    </div>

                </div>

                {{-- Tiền --}}
                <div class="section-title">
                    <i class="fas fa-wallet me-2"></i>
                    Thông tin tiền mặt
                </div>

                <div class="info-box">
                    <div class="row">

                        <div class="col-md-3">
                            <label class="form-label">
                                Doanh thu tiền mặt của ca
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ number_format($tongTienMatCuaCa, 0, ',', '.') }}"
                                disabled>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Tiền đầu ca
                            </label>

                            <input
                                type="text"
                                class="form-control money"
                                name="tien_mat_dau_ca"
                                value="{{ old('tien_mat_dau_ca') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Tiền cuối ca
                            </label>

                            <input
                                type="text"
                                class="form-control money"
                                name="tien_mat_cuoi_ca"
                                value="{{ old('tien_mat_cuoi_ca') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Số tiền chênh lệch so với DT tiền mặt
                            </label>

                            <input
                                type="text"
                                class="form-control money"
                                name="chenh_lech"
                                value="{{ old('chenh_lech') }}">
                        </div>

                    </div>
                </div>

                {{-- Ghi chú --}}
                <div class="section-title">
                    <i class="fas fa-clipboard me-2"></i>
                    Ghi chú
                </div>

                <div class="info-box">

                    <textarea
                        class="form-control money"
                        rows="5"
                        name="ghi_chu">{{ old('ghi_chu') }}</textarea>

                </div>

                <div class="text-end">

                    <button class="btn btn-primary">
                        Lưu giao ca
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const moneyInputs = document.querySelectorAll('.money');

    moneyInputs.forEach(function(input) {

        // Khi rời khỏi ô input -> format
        input.addEventListener('blur', function () {

            let value = this.value.replace(/\D/g, '');

            if (value === '') {
                this.value = '';
                return;
            }

            this.value = Number(value).toLocaleString('vi-VN');

        });


        // Khi click vào nhập lại -> bỏ format để nhập dễ hơn
        input.addEventListener('focus', function () {

            this.value = this.value.replace(/\./g, '');

        });


    });


    // Submit -> bỏ dấu chấm gửi về Laravel
    document.querySelector('form').addEventListener('submit', function () {

        moneyInputs.forEach(function(input){

            input.value = input.value.replace(/\./g, '');

        });

    });

});
</script>
@endsection
