@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thêm thiết lập lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-plus me-2"></i>Thêm thiết lập lương</h4>
    <a href="{{ route('thiet-lap-luong.index') }}" class="btn btn-secondary">Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('thiet-lap-luong.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nhân viên <span class="text-danger">*</span></label>
                    <select name="id_nguoi_dung" class="form-select" required>
                        <option value="" disabled selected>-- Chọn nhân viên --</option>
                        @foreach(\App\Models\NguoiDung::where('trang_thai', 1)->get() as $nv)
                            <option value="{{ $nv->id }}">{{ $nv->ho_ten }} ({{ $nv->vai_tro }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Lương theo giờ</label>
                    <input type="number" name="luong_theo_gio" class="form-control" min="0" placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phụ cấp</label>
                    <input type="number" name="phu_cap" class="form-control" min="0" placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phạt đi muộn</label>
                    <input type="number" name="phat_di_muon" class="form-control" min="0" placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lương tăng ca</label>
                    <input type="number" name="luong_tang_ca" class="form-control" min="0" placeholder="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Thưởng</label>
                    <input type="number" name="thuong" class="form-control" min="0" placeholder="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phạt bỏ ca</label>
                    <input type="number" name="phat_bo_ca" class="form-control" min="0" placeholder="0">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                <a href="{{ route('thiet-lap-luong.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
