@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý khách hàng - SmartMart')

@section('content')
<style>
    .soft-panel {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 0.75rem 2rem rgba(15, 23, 42, 0.08);
    }
    .customer-avatar {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #0d6efd, #20c997);
        flex-shrink: 0;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.04);
    }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý khách hàng</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Khách hàng</li>
            </ol>
        </nav>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('khach-hang.trash') }}"
           class="btn btn-outline-danger">
            <i class="fas fa-trash-restore me-2"></i>
            Thùng rác
        </a>

        <a href="{{ route('khach-hang.create') }}"
           class="btn btn-primary px-4">
            <i class="fas fa-plus me-2"></i>
            Thêm khách hàng
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success soft-panel border-0">{{ session('success') }}</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100 soft-panel">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-users"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng khách hàng</h6>
                    <h3 class="mb-0">{{ number_format($tongKhachHang) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100 soft-panel">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-circle-check"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đang hoạt động</h6>
                    <h3 class="mb-0">{{ number_format($dangHoatDong) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100 soft-panel">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-coins"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng điểm tích lũy</h6>
                    <h3 class="mb-0">{{ number_format($tongDiemTichLuy) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100 soft-panel">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-sack-dollar"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng công nợ</h6>
                    <h3 class="mb-0 text-danger">{{ number_format($tongCongNo, 0, ',', '.') }} đ</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card soft-panel mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('khach-hang.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label class="form-label fw-semibold">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="keyword" value="{{ $keyword }}" class="form-control" placeholder="Tìm theo tên khách hàng hoặc số điện thoại">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" @selected($trangThai !== null && (string) $trangThai === '1')>Đang hoạt động</option>
                        <option value="0" @selected($trangThai !== null && (string) $trangThai === '0')>Ngừng hoạt động</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <button class="btn btn-outline-primary w-100" type="submit">
                        <i class="fas fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card soft-panel">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">STT</th>
                        <th>Mã khách hàng</th>
                        <th>Họ và tên</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Địa chỉ</th>
                        {{-- <th>Điểm tích lũy</th>
                        <th>Tổng chi tiêu</th>
                        <th>Công nợ</th> --}}
                        <th>Trạng thái</th>
                        <th class="text-end" style="width: 190px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($khachHangs as $index => $khachHang)
                        @php
                            $initials = collect(explode(' ', trim($khachHang->ten_khach_hang)))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                        @endphp
                        <tr>
                            <td>{{ $khachHangs->firstItem() + $index }}</td>
                            <td><strong>KH{{ str_pad((string) $khachHang->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="customer-avatar">{{ $initials ?: 'KH' }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $khachHang->ten_khach_hang }}</div>
                                        <small class="text-muted">{{ $khachHang->phi_chu ?: 'Khách hàng thường xuyên' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $khachHang->so_dien_thoai }}</td>
                            <td>{{ $khachHang->email ?: '-' }}</td>
                            <td class="text-muted">{{ $khachHang->dia_chi ?: '-' }}</td>
                            {{-- <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    {{ number_format($khachHang->diem_tich_luy ?? 0) }} điểm
                                </span>
                            </td>
                            <td><strong>{{ number_format($khachHang->tong_chi_tieu ?? 0, 0, ',', '.') }} đ</strong></td>
                            <td>
                                @if((float) $khachHang->cong_no > 0)
                                    <strong class="text-danger">{{ number_format($khachHang->cong_no, 0, ',', '.') }} đ</strong>
                                @else
                                    <span class="text-success fw-semibold">0 đ</span>
                                @endif
                            </td> --}}
                            <td>
                                <span class="badge rounded-pill {{ $khachHang->trang_thai ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                    {{ $khachHang->trang_thai ? 'Hoạt động' : 'Ngừng hoạt động' }}
                                </span>
                            </td>
                            <td class="text-end">
                                {{-- <a href="{{ route('khach-hang.show', $khachHang) }}#lich-su-tich-diem" class="btn btn-sm btn-outline-info btn-action" title="Xem lịch sử tích điểm">
                                    <i class="fas fa-star"></i>
                                </a> --}}
                                <a href="{{ route('khach-hang.show', $khachHang) }}" class="btn btn-sm btn-outline-primary btn-action" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('khach-hang.edit', $khachHang) }}" class="btn btn-sm btn-outline-warning btn-action" title="Sửa">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-action" title="Xóa mềm" data-bs-toggle="modal" data-bs-target="#deleteCustomerModal" data-delete-url="{{ route('khach-hang.destroy', $khachHang) }}" data-customer-name="{{ $khachHang->ten_khach_hang }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="fw-semibold">Chưa có khách hàng phù hợp</h5>
                                    <p class="text-muted mb-0">Dữ liệu mẫu sẽ hiển thị ở đây khi chưa có bản ghi trong cơ sở dữ liệu.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted">
                Hiển thị {{ $khachHangs->firstItem() ?? 0 }}-{{ $khachHangs->lastItem() ?? 0 }} của {{ $khachHangs->total() }} khách hàng
            </div>
            {{ $khachHangs->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-triangle-exclamation text-danger me-2"></i>Xác nhận xóa mềm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Bạn có chắc muốn xóa mềm khách hàng <strong id="deleteCustomerName"></strong> không? Dữ liệu vẫn có thể khôi phục sau này.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" id="deleteCustomerForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa mềm</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const deleteCustomerModal = document.getElementById('deleteCustomerModal');
    if (deleteCustomerModal) {
        deleteCustomerModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const form = document.getElementById('deleteCustomerForm');
            const name = document.getElementById('deleteCustomerName');

            form.action = button.getAttribute('data-delete-url');
            name.textContent = button.getAttribute('data-customer-name');
        });
    }
</script>
@endsection