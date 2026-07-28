@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Lịch sử ca làm việc - SmartMart')

<style>
    body{
        background:#f5f7fb;
    }

    .handover-card{
        border:none;
        border-radius:15px;
        overflow:hidden;
    }

    .handover-card .card-header{
        background:linear-gradient(135deg,#0d6efd,#4f8dfd);
        color:#fff;
        padding:18px 25px;
    }

    .handover-card .card-header h4{
        margin:0;
        font-weight:600;
    }

    .handover-card .card-body{
        padding:30px;
    }

    .form-label{
        font-weight:600;
        color:#555;
        margin-bottom:8px;
    }

    .form-control,
    .form-select{
        border-radius:10px;
        height:48px;
    }

    textarea.form-control{
        height:auto;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:#0d6efd;
        box-shadow:0 0 0 .2rem rgba(13,110,253,.15);
    }

    .btn-save{
        border-radius:10px;
        padding:10px 35px;
        font-weight:600;
    }

    .section-title{
        font-size:18px;
        font-weight:600;
        color:#0d6efd;
        margin-bottom:20px;
        border-left:4px solid #0d6efd;
        padding-left:10px;
    }
</style>

@section('content')


   <div class="container">
    <div class="card shadow">
        <div class="card-header">
            <h4>Thêm phiếu giao ca</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('giao-ca.store') }}" method="POST">
                @csrf

                {{-- Trưởng ca bàn giao --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Trưởng ca bàn giao</label>

                        <select name="id_truong_ca_ban_giao" class="form-select">
                            <option value="">-- Chọn trưởng ca --</option>

                            @foreach ($danhSachTruongCa as $nguoi)
                                <option value="{{ $nguoi->id }}"
                                    {{ old('id_truong_ca_ban_giao') == $nguoi->id ? 'selected' : '' }}>
                                    {{ $nguoi->ho_ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                {{-- Trưởng ca nhận ca --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Trưởng ca nhận</label>

                        <select name="id_truong_ca_nhan_ca" class="form-select">
                            <option value="">-- Chọn trưởng ca --</option>

                            @foreach ($danhSachTruongCa as $nguoi)
                                <option value="{{ $nguoi->id }}"
                                    {{ old('id_truong_ca_nhan_ca') == $nguoi->id ? 'selected' : '' }}>
                                    {{ $nguoi->ho_ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ca làm việc --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ca làm việc</label>

                        <select name="id_ca_lam_viec" class="form-select">
                            <option value="">-- Chọn ca --</option>

                            @foreach ($caLamViecs as $ca)
                                <option value="{{ $ca->id }}"
                                    {{ old('id_ca_lam_viec') == $ca->id ? 'selected' : '' }}>
                                    {{ $ca->ten_ca }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                {{-- trạng thái --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Trạng thái</label>

                        <select name="trang_thai" class="form-select">
                            <option value="0">Chờ xác nhận</option>
                        </select>
                    </div>
                </div>

                {{-- Tiền --}}
                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tiền đầu ca</label>

                        <input
                            type="number"
                            class="form-control"
                            name="tien_mat_dau_ca"
                            step="0.01"
                            min="0"
                            value="{{ old('tien_mat_dau_ca') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tiền cuối ca</label>

                        <input
                            type="number"
                            class="form-control"
                            name="tien_mat_cuoi_ca"
                            step="0.01"
                            min="0">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Chênh lệch</label>

                        <input
                            type="number"
                            class="form-control"
                            name="chenh_lech"
                            value="{{ old('chenh_lech') }}">
                    </div>

                </div>

                {{-- Thời gian --}}
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Thời gian bắt đầu</label>

                        <input
                            type="datetime-local"
                            class="form-control"
                            name="thoi_gian_bat_dau_ca"
                            value="{{ old('thoi_gian_bat_dau_ca') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Thời gian kết thúc</label>

                        <input
                            type="datetime-local"
                            class="form-control"
                            name="thoi_gian_ket_thuc_ca"
                            value="{{ old('thoi_gian_ket_thuc_ca') }}">
                    </div>

                </div>

                {{-- Ghi chú --}}
                <div class="mb-4">
                    <label class="form-label">Ghi chú</label>

                    <textarea
                        name="ghi_chu"
                        rows="5"
                        class="form-control">{{ old('ghi_chu') }}</textarea>
                </div>

                {{-- nút --}}
                <div class="text-end">
                    <button class="btn btn-primary btn-save">
                        <i class="fas fa-save me-2"></i>
                        Lưu giao ca
                    </button>
                </div>
              
            </form>
        </div>
    </div>
</div>

@endsection