@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa giao ca')
<style>
    body{
        background:#eef3f8;
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
        background:linear-gradient(135deg,#2563eb,#3b82f6);
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

    .group-title{
        font-size:17px;
        font-weight:700;
        color:#2563eb;
        border-left:5px solid #2563eb;
        padding-left:12px;
        margin:30px 0 20px;
    }

    .group-title:first-child{
        margin-top:0;
    }

    .form-label{
        font-weight:600;
        color:#334155;
        margin-bottom:8px;
    }

    .form-control,
    .form-select{
        border-radius:12px;
        border:1px solid #dbe4f0;
        background:#fafcff;
        height:48px;
        transition:.3s;
    }

    textarea.form-control{
        height:120px;
        resize:none;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:#2563eb;
        box-shadow:0 0 0 .2rem rgba(37,99,235,.15);
        background:#fff;
    }

    .form-control[readonly]{
        background:#f1f5f9;
        font-weight:600;
    }

    .btn-update{
        background:linear-gradient(135deg,#2563eb,#3b82f6);
        color:#fff;
        border:none;
        border-radius:12px;
        padding:12px 35px;
        font-weight:600;
        transition:.3s;
    }

    .btn-update:hover{
        transform:translateY(-2px);
        box-shadow:0 10px 20px rgba(37,99,235,.25);
        color:#fff;
    }
</style>

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="d-inline-block bg-primary text-white px-4 py-2 rounded">
            <h4 class="mb-0">
                <i class="fas fa-file-signature me-2"></i>
                Sửa phiếu giao ca
            </h4>
        </div>

        <div class="card-body">

            <form action="{{ route('giao-ca.cap-nhat', $giaoCa->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Thông tin ca làm  --}}
                <div class="group-title">
                    👤 Thông tin ca làm
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Trưởng ca bàn giao</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $giaoCa->truongCaBanGiao->ho_ten }}"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Trưởng ca nhận</label>

                        <select name="id_truong_ca_nhan_ca" class="form-select">

                            @foreach($danhSachTruongCa as $nguoi)

                                <option value="{{ $nguoi->id }}"
                                    @selected($nguoi->id == $giaoCa->id_truong_ca_nhan_ca)>

                                    {{ $nguoi->ho_ten }}

                                </option>

                            @endforeach

                        </select>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ca làm việc</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $giaoCa->caLamViec->ten_ca }}"
                            readonly>
                    </div>

                </div>

                {{-- Thời gian ca làm --}}
                <div class="group-title">
                    🕒 Thời gian ca làm
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Thời gian bắt đầu</label>

                        <input type="datetime-local"
                            class="form-control"
                            name="thoi_gian_bat_dau_ca"
                            value="{{ \Carbon\Carbon::parse($giaoCa->thoi_gian_bat_dau_ca)->format('Y-m-d\TH:i') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Thời gian kết thúc</label>

                        <input type="datetime-local"
                            class="form-control"
                            name="thoi_gian_ket_thuc_ca"
                            value="{{ $giaoCa->thoi_gian_ket_thuc_ca ? \Carbon\Carbon::parse($giaoCa->thoi_gian_ket_thuc_ca)->format('Y-m-d\TH:i') : '' }}">
                    </div>

                </div>

                {{-- Thông tin tiền mặt --}}
                <div class="group-title">
                    💰 Thông tin chuyển khoản
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Doanh thu chuyển khoản</label>

                        <input type="text"
                            class="form-control money-input"
                            name="doanh_thu_chuyen_khoan"
                            value="{{ $giaoCa->doanh_thu_chuyen_khoan !== null ? number_format((int)$giaoCa->doanh_thu_chuyen_khoan, 0, ',', '.') : '' }}"
                            inputmode="numeric">
                    </div>

                {{-- Thông tin tiền mặt --}}
                <div class="group-title">
                    💰 Thông tin tiền mặt
                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tiền đầu ca</label>

                        <input type="text"
                            class="form-control money-input"
                            id="tien_mat_dau_ca"
                            name="tien_mat_dau_ca"
                            value="{{ $giaoCa->tien_mat_dau_ca !== null ? number_format((int)$giaoCa->tien_mat_dau_ca, 0, ',', '.') : '' }}"
                            inputmode="numeric">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tiền cuối ca</label>

                        <input type="text"
                            class="form-control money-input"
                            id="tien_cuoi_ca"
                            name="tien_mat_cuoi_ca"
                            value="{{ $giaoCa->tien_mat_cuoi_ca !== null ? number_format((int)$giaoCa->tien_mat_cuoi_ca, 0, ',', '.') : '' }}"
                            inputmode="numeric">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Chênh lệch</label>

                        <input type="text"
                            class="form-control money-input"
                            id="chenh_lech"
                            name="chenh_lech"
                            value="{{ $giaoCa->chenh_lech !== null ? number_format((int)$giaoCa->chenh_lech, 0, ',', '.') : '' }}"
                            inputmode="numeric">
                    </div>

                </div>

                {{-- Ghi chú --}}
                <div class="group-title">
                    📝 Ghi chú
                </div>

                <div class="mb-4">

                    <textarea class="form-control"
                            name="ghi_chu"
                            rows="5">{{ $giaoCa->ghi_chu }}</textarea>

                </div>

                {{-- nút cập nhật --}}
                <div class="d-flex justify-content-end">

                    <button class="btn btn-primary">
                        Cập nhật giao ca
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection