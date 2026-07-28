@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa giao ca')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header">
            <h4>Sửa phiếu giao ca</h4>
        </div>

        <div class="card-body">

            <form action="{{ route('giao-ca.cap-nhat', $giaoCa->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Trưởng ca bàn giao --}}
                <div class="mb-3">
                    <label class="form-label">Trưởng ca bàn giao</label>

                    <input type="text"
                           class="form-control"
                           value="{{ $giaoCa->truongCaBanGiao->ho_ten }}">
                </div>

                {{-- Trưởng ca nhận --}}
                <div class="mb-3">
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

                {{-- Ca làm việc --}}
                <div class="mb-3">
                    <label class="form-label">Ca làm việc</label>

                    <input type="text"
                           class="form-control"
                           value="{{ $giaoCa->caLamViec->ten_ca }}">
                </div>

                {{-- Tiền đầu ca --}}
                <div class="mb-3">
                    <label class="form-label">Tiền đầu ca</label>

                    <input type="number"
                           class="form-control"
                           id="tien_mat_dau_ca"
                           name="tien_mat_dau_ca"
                           value="{{ $giaoCa->tien_mat_dau_ca }}">
                </div>

                {{-- Tiền cuối ca --}}
                <div class="mb-3">
                    <label class="form-label">Tiền cuối ca</label>

                    <input type="number"
                           class="form-control"
                           id="tien_cuoi_ca"
                           name="tien_mat_cuoi_ca"
                           value="{{ $giaoCa->tien_mat_cuoi_ca }}">
                </div>

                {{-- Chênh lệch --}}
                <div class="mb-3">
                    <label class="form-label">Chênh lệch</label>

                    <input type="number"
                           class="form-control"
                           id="chenh_lech"
                           name="chenh_lech"
                           value={{ $giaoCa->chenh_lech}}>
                </div>

                {{-- Ghi chú --}}
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>

                    <textarea class="form-control"
                              name="ghi_chu"
                              rows="4">{{ $giaoCa->ghi_chu }}</textarea>
                </div>

                <button class="btn btn-primary">
                    <i class="bi bi-save"></i>
                    Cập nhật
                </button>

            </form>

        </div>

    </div>

</div>

@endsection