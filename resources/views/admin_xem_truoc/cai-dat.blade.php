@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cài đặt - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Cài đặt</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Cài đặt</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-4">
    <!-- Store Info -->
    <div class="col-lg-6">
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-store me-2"></i>Thông tin cửa hàng</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Tên cửa hàng</label>
                    <input type="text" class="form-control" value="SmartMart">
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <textarea class="form-control" rows="2">123 Đường ABC, Quận 1, TP.HCM</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" value="028 1234 5678">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="contact@smartmart.vn">
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                </button>
            </div>
        </div>
    </div>

    <!-- Invoice Settings -->
    <div class="col-lg-6">
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Cài đặt hóa đơn</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Định dạng số hóa đơn</label>
                    <input type="text" class="form-control" value="HD{YYYY}{MM}{DD}{SEQ}">
                    <small class="text-muted">VD: HD20260609001</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">VAT mặc định (%)</label>
                    <input type="number" class="form-control" value="0">
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="autoPrint" checked>
                        <label class="form-check-label" for="autoPrint">Tự động in hóa đơn</label>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showLogo" checked>
                        <label class="form-check-label" for="showLogo">Hiển thị logo trên hóa đơn</label>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                </button>
            </div>
        </div>
    </div>

    <!-- System Settings -->
    <div class="col-lg-6">
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Cài đặt hệ thống</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Múi giờ</label>
                    <select class="form-select">
                        <option selected>Asia/Ho_Chi_Minh (GMT+7)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Đơn vị tiền tệ</label>
                    <select class="form-select">
                        <option selected>VND - Đồng Việt Nam</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Số thập phân</label>
                    <input type="number" class="form-control" value="0" min="0" max="4">
                </div>
            </div>
            <div class="card-footer bg-white">
                <button class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                </button>
            </div>
        </div>
    </div>

    <!-- Working Hours -->
    <div class="col-lg-6">
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Giờ làm việc</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Giờ mở cửa</label>
                    <input type="time" class="form-control" value="08:00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Giờ đóng cửa</label>
                    <input type="time" class="form-control" value="22:00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ngày nghỉ</label>
                    <select class="form-select" multiple>
                        <option>Chủ nhật</option>
                    </select>
                    <small class="text-muted">Giữ Ctrl để chọn nhiều</small>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
