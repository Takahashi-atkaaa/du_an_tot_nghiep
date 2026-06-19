@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Nhà cung cấp - SmartMart')

@section('content')
{{-- Tiêu đề trang --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Nhà cung cấp</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item active">Nhà cung cấp</li>
            </ol>
        </nav>
    </div>

    <div class="d-flex gap-2 align-items-center">
        @php $trashCount = \App\Models\NhaCungCap::onlyTrashed()->count(); @endphp

        <a href="{{ url('/admin/kho-hang/nha-cung-cap/thung-rac') }}" class="btn btn-outline-danger">
            <i class="fas fa-trash me-1"></i> Thùng rác
            @if($trashCount)
                <span class="badge bg-danger rounded-pill ms-2">{{ $trashCount }}</span>
            @endif
        </a>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#supplierModal">
            <i class="fas fa-plus me-2"></i>Thêm nhà cung cấp
        </button>
    </div>
</div>

{{-- Thông báo --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Hiển thị lỗi validate --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php $q = $q ?? ''; @endphp

{{-- Tìm kiếm nhà cung cấp --}}
<div class="row mb-3">
    <div class="col-md-9">
        <form method="GET" action="{{ url('/admin/kho-hang/nha-cung-cap') }}" class="d-flex align-items-center">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text"
                       name="q"
                       class="form-control"
                       placeholder="Tìm kiếm nhà cung cấp theo tên, email hoặc số điện thoại"
                       value="{{ $q }}">
            </div>

            <div class="d-flex gap-2 ms-2">
                <button type="submit" class="btn btn-primary px-4">Tìm kiếm</button>
                <a href="{{ url('/admin/kho-hang/nha-cung-cap') }}" class="btn btn-light border px-4">Làm mới</a>
            </div>
        </form>
    </div>
</div>

{{-- Danh sách nhà cung cấp --}}
<div class="card table-admin mb-4 mt-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Người đại diện</th>
                        <th>Ngày tạo</th>
                        <th style="width: 230px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->ten_nha_cung_cap }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->so_dien_thoai }}</td>
                            <td>{{ $item->nguoi_dai_dien }}</td>
                            <td>{{ optional($item->created_at)->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    {{-- Lịch sử giao dịch --}}
                                    <a href="{{ url('/admin/kho-hang/nha-cung-cap/'.$item->id.'/lich-su-giao-dich') }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Lịch sử
                                    </a>

                                    {{-- Sửa --}}
                                    <button class="btn btn-sm btn-outline-secondary btn-edit"
                                            data-id="{{ $item->id }}">
                                        Sửa
                                    </button>

                                    {{-- Xóa --}}
                                    <form action="{{ url('/admin/kho-hang/nha-cung-cap/'.$item->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa?')">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Chưa có nhà cung cấp nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Phân trang --}}
<div class="d-flex justify-content-end mt-3">
    {{ $items->links() }}
</div>

{{-- Modal thêm nhà cung cấp --}}
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ url('/admin/kho-hang/nha-cung-cap') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Thêm nhà cung cấp
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp</label>
                        <input type="text" name="ten_nha_cung_cap" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="so_dien_thoai" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Người đại diện</label>
                        <input type="text" name="nguoi_dai_dien" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal sửa nhà cung cấp --}}
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSupplierForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Sửa nhà cung cấp
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp</label>
                        <input type="text" name="ten_nha_cung_cap" class="form-control" id="edit_ten" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="edit_email">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="so_dien_thoai" class="form-control" id="edit_sdt" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Người đại diện</label>
                        <input type="text" name="nguoi_dai_dien" class="form-control" id="edit_nguoi">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sửa nhà cung cấp
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.id;

            fetch('/admin/kho-hang/nha-cung-cap/' + id + '/edit')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_ten').value = data.ten_nha_cung_cap || '';
                    document.getElementById('edit_email').value = data.email || '';
                    document.getElementById('edit_sdt').value = data.so_dien_thoai || '';
                    document.getElementById('edit_nguoi').value = data.nguoi_dai_dien || '';

                    document.getElementById('editSupplierForm').action =
                        '/admin/kho-hang/nha-cung-cap/' + id;

                    new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
                })
                .catch(() => alert('Không tải được dữ liệu'));
        });
    });
});
</script>
@endsection