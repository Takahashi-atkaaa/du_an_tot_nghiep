@extends('nhan_vien_view.layouts.nhan_vien')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Hồ sơ cá nhân</h1>
</div>

<div class="row">
    {{-- Thông tin cá nhân --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <div style="width:120px;height:120px;margin:0 auto;background:#2e7d32;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:48px;color:#fff;font-weight:bold;">NV</span>
                    </div>
                </div>
                <h4>Nhân Viên Test</h4>
                <p class="text-muted mb-1">Nhân viên bán hàng</p>
                <span class="status-badge status-success">Đang hoạt động</span>
                <hr>
                <div class="text-start">
                    <p class="mb-2"><i class="fas fa-calendar me-2 text-success"></i>Ngày vào làm: 01/01/2024</p>
                    <p class="mb-2"><i class="fas fa-phone me-2 text-success"></i>0901234567</p>
                    <p class="mb-0"><i class="fas fa-envelope me-2 text-success"></i>nvtest@smartmart.com</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form chỉnh sửa --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2 text-success"></i>Chỉnh sửa thông tin</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" value="Nhân Viên Test">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" value="1998-05-15">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" value="0901234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="nvtest@smartmart.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" rows="2">123 Đường Test, Phường 1, Quận 1, TP.HCM</textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select class="form-select">
                                <option selected>Nam</option>
                                <option>Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vai trò</label>
                            <input type="text" class="form-control" value="Nhân viên bán hàng" disabled>
                            <small class="text-muted">Liên hệ quản lý để thay đổi vai trò</small>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-3">Đổi mật khẩu</h6>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" placeholder="Nhập mật khẩu hiện tại">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" placeholder="Mật khẩu mới">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" placeholder="Xác nhận mật khẩu mới">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
