@extends('admin_xem_truoc.layouts.admin')

@section('content')

@if(session('thong_bao'))
    <div class="alert alert-success">
        {{ session('thong_bao') }}
    </div>
@endif

<h4>Chi tiết chấm công</h4>

<h5>Nhân viên: {{ $nhan_vien->ho_ten }}</h5>

<form action="{{ route('diem-danh.cap-nhat', $cham_cong_nhan_vien->id) }}"
      method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Giờ vào</label>
        <input type="datetime-local"
               name="gio_vao"
               class="form-control"
               value="{{ date('Y-m-d\TH:i', strtotime($cham_cong_nhan_vien->gio_vao)) }}">
    </div>

    <div class="mb-3">
        <label>Giờ tan ca</label>
        <input type="datetime-local"
               name="gio_tan_ca"
               class="form-control"
               value="{{ date('Y-m-d\TH:i', strtotime($cham_cong_nhan_vien->gio_tan_ca)) }}">
    </div>

    <div class="mb-3">
        <label>Số giờ đi muộn</label>
        <input type="number"
               step="0.01"
               name="so_gio_di_lam_muon"
               class="form-control"
               value="{{ $cham_cong_nhan_vien->so_gio_di_lam_muon }}">
    </div>

    <div class="mb-3">
        <label>Số giờ làm thêm</label>
        <input type="number"
               step="0.01"
               name="so_gio_lam_them"
               class="form-control"
               value="{{ $cham_cong_nhan_vien->so_gio_lam_them }}">
    </div>

    <div class="mb-3">
        <label>Trạng thái vào làm</label>
        <select name="trang_thai_vao_lam"
                class="form-select">
            <option value="Đúng giờ"
                {{ $cham_cong_nhan_vien->trang_thai_vao_lam == 'Đúng giờ' ? 'selected' : '' }}>
                Đúng giờ
            </option>

            <option value="Đi muộn"
                {{ $cham_cong_nhan_vien->trang_thai_vao_lam == 'Đi muộn' ? 'selected' : '' }}>
                Đi muộn
            </option>

            <option value="Nghỉ làm"
                {{ $cham_cong_nhan_vien->trang_thai_vao_lam == 'Nghỉ làm' ? 'selected' : '' }}>
                Nghỉ làm
            </option>
        </select>
    </div>

    <div class="mb-3">
        <label>Trạng thái tan ca</label>
        <select name="trang_thai_tan_ca"
                class="form-select">
            <option value="Đúng giờ"
                {{ $cham_cong_nhan_vien->trang_thai_tan_ca == 'Đúng giờ' ? 'selected' : '' }}>
                Đúng giờ
            </option>

            <option value="Tan sớm"
                {{ $cham_cong_nhan_vien->trang_thai_tan_ca == 'Tan sớm' ? 'selected' : '' }}>
                Tan sớm
            </option>

            <option value="Tăng ca"
                {{ $cham_cong_nhan_vien->trang_thai_tan_ca == 'Tăng ca' ? 'selected' : '' }}>
                Tăng ca
            </option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">
        Cập nhật
    </button>
</form>

@endsection