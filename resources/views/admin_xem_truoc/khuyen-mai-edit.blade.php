@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chỉnh sửa Khuyến mãi')

@section('content')
{{-- Tiêu đề trang --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-1">Chỉnh sửa chương trình khuyến mãi</h4>
    <a href="{{ url('/admin/khuyen-mai') }}" class="btn btn-secondary">Quay lại</a>
</div>

{{-- Form sửa khuyến mãi --}}
<div class="card">
    <div class="card-body">

        {{-- Thông báo thành công --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('khuyen-mai.update', $promo->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Tên chương trình --}}
            <div class="mb-3">
                <label class="form-label">Tên chương trình</label>
                <input type="text"
                       name="ten_chuong_trinh"
                       value="{{ old('ten_chuong_trinh', $promo->ten_chuong_trinh) }}"
                       class="form-control">

                @error('ten_chuong_trinh')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Loại giảm giá và giá trị giảm --}}
            <div class="mb-3 row">
                <div class="col-md-6">
                    <label class="form-label">Loại</label>
                    <select name="loai_giam_gia" class="form-select">
                        <option value="percent" {{ old('loai_giam_gia', $promo->loai_giam_gia) == 'percent' ? 'selected' : '' }}>Giảm %</option>
                        <option value="amount" {{ old('loai_giam_gia', $promo->loai_giam_gia) == 'amount' ? 'selected' : '' }}>Giảm tiền</option>
                        <option value="bogo" {{ old('loai_giam_gia', $promo->loai_giam_gia) == 'bogo' ? 'selected' : '' }}>Buy 1 Get 1</option>
                        <option value="gift" {{ old('loai_giam_gia', $promo->loai_giam_gia) == 'gift' ? 'selected' : '' }}>Quà tặng</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Giá trị giảm</label>
                    <input type="number"
                           step="0.01"
                           name="gia_tri_giam"
                           value="{{ old('gia_tri_giam', $promo->gia_tri_giam) }}"
                           class="form-control">

                    @error('gia_tri_giam')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Thời gian áp dụng --}}
            <div class="mb-3 row">
                <div class="col-md-6">
                    <label class="form-label">Ngày bắt đầu</label>
                    <input type="date"
                           name="ngay_bat_dau"
                           value="{{ old('ngay_bat_dau', $promo->ngay_bat_dau ? $promo->ngay_bat_dau->format('Y-m-d') : '') }}"
                           class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ngày kết thúc</label>
                    <input type="date"
                           name="ngay_ket_thuc"
                           value="{{ old('ngay_ket_thuc', $promo->ngay_ket_thuc ? $promo->ngay_ket_thuc->format('Y-m-d') : '') }}"
                           class="form-control">
                </div>
            </div>

            {{-- Mô tả --}}
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="ghi_chu" class="form-control">{{ old('ghi_chu', $promo->ghi_chu) }}</textarea>
            </div>

            {{-- Trạng thái --}}
            <div class="mb-3 form-check">
                <input type="checkbox"
                       name="trang_thai"
                       value="1"
                       class="form-check-input"
                       id="active"
                       {{ old('trang_thai', $promo->trang_thai) ? 'checked' : '' }}>

                <label class="form-check-label" for="active">Kích hoạt</label>
            </div>

            {{-- Nút lưu --}}
            <button class="btn btn-primary">Lưu</button>
        </form>
    </div>
</div>
@endsection