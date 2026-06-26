@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sửa thiết lập lương - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-edit me-2"></i>Sửa thiết lập lương</h4>
    <a href="{{ route('thiet-lap-luong.index') }}" class="btn btn-secondary">Quay lại</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('thiet-lap-luong.update', $thietLapLuong) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nhân viên</label>
                    <input type="text" class="form-control" value="{{ $thietLapLuong->nguoiDung->ho_ten ?? 'N/A' }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Lương theo giờ</label>
                    <input type="number" name="luong_theo_gio" class="form-control" min="0" value="{{ $thietLapLuong->luong_theo_gio }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phụ cấp</label>
                    <input type="number" name="phu_cap" class="form-control" min="0" value="{{ $thietLapLuong->phu_cap }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phạt đi muộn</label>
                    <input type="number" name="phat_di_muon" class="form-control" min="0" value="{{ $thietLapLuong->phat_di_muon }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lương tăng ca</label>
                    <input type="number" name="luong_tang_ca" class="form-control" min="0" value="{{ $thietLapLuong->luong_tang_ca }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Thưởng</label>
                    <input type="number" name="thuong" class="form-control" min="0" value="{{ $thietLapLuong->thuong }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phạt bỏ ca</label>
                    <input type="number" name="phat_bo_ca" class="form-control" min="0" value="{{ $thietLapLuong->phat_bo_ca }}">
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
