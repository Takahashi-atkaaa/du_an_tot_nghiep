@extends('admin_xem_truoc.layouts.admin')

@section('title', 'điểm danh bù cho nhân viên - SmartMart')

@section('content')

@if (session('thong_bao'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('thong_bao') }}
        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
@endif

<h4>Chấm công bù</h4>
<h5>Nhân viên: {{$nhan_vien->ho_ten}}</h5>


<form action="{{ route('diem-danh.luu-diem-danh-bu')}}" method="POST">
    @csrf

    <input type="hidden" name="id_chia_ca_lam_viec"
           value="{{ $id_chia_ca_lam_viec }}">

    <div class="mb-3">
        <label class="form-label">
            Giờ vào
        </label>
        <input type="datetime-local"
               name="gio_vao"
               class="form-control"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label">
            Giờ tan ca
        </label>
        <input type="datetime-local"
               name="gio_tan_ca"
               class="form-control"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label">
            Số giờ đi muộn
        </label>
        <input type="number"
               step="0.01"
               min="0"
               name="so_gio_di_lam_muon"
               class="form-control"
               value="0">
    </div>

    <div class="mb-3">
        <label class="form-label">
            Số giờ làm thêm
        </label>
        <input type="number"
               step="0.01"
               min="0"
               name="so_gio_lam_them"
               class="form-control"
               value="0">
    </div>

    <div class="mb-3">
        <label class="form-label">
            Trạng thái vào làm
        </label>
        <select name="trang_thai_vao_lam"
                class="form-select">
            <option value="Đúng giờ">Đúng giờ</option>
            <option value="Đi muộn">Đi muộn</option>
            <option value="Nghỉ làm">Nghỉ phép</option>
            <option value="Nghỉ làm">Bỏ ca</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">
            Trạng thái tan ca
        </label>
        <select name="trang_thai_tan_ca"
                class="form-select">
            <option value="Đúng giờ">Đúng giờ</option>
            <option value="Tan sớm">Tan sớm</option>
            <option value="Tăng ca">Tăng ca</option>
        </select>
    </div>

    <button type="submit"
            class="btn btn-primary">
        Xác nhận chấm công bù
    </button>
</form>
@endsection