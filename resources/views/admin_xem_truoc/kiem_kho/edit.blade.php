@extends('admin_xem_truoc.layouts.admin')
@section('title', 'Sửa phiếu kiểm kho')

@section('content')
<div class="container-fluid py-4">
    <div class="kk-page-header">
        <h2><i class="fas fa-edit"></i> Sửa phiếu {{ $phieu->ma_kiem_kho }}</h2>
        <div class="subtitle">Chỉ sửa được khi phiếu ở trạng thái "Phiếu tạm"</div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('kiem-kho.update', $phieu->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Người kiểm</label>
                        <select name="id_nguoi_kiem" class="form-select">
                            @foreach($dsNguoiDung as $nd)
                                <option value="{{ $nd->id }}" {{ old('id_nguoi_kiem', $phieu->id_nguoi_kiem) == $nd->id ? 'selected' : '' }}>{{ $nd->ho_ten }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày kiểm</label>
                        <input type="date" name="ngay_kiem" class="form-control" value="{{ old('ngay_kiem', $phieu->ngay_kiem?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="ghi_chu" class="form-control" rows="3">{{ old('ghi_chu', $phieu->ghi_chu) }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between">
                    <a href="{{ route('kiem-kho.show', $phieu->id) }}" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection