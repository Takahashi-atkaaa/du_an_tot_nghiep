@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý nhân sự - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Xem danh sách nhân sự</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Nhân sự</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('nguoi-dung.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Thêm nhân sự
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat blue h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-users"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Tổng nhân viên</h6>
                    <h3 class="mb-0">{{ number_format($tongNhanVien) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat green h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-user-check"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đang làm việc</h6>
                    <h3 class="mb-0">{{ number_format($dangLamViec) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat orange h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-clock"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Nghỉ phép</h6>
                    <h3 class="mb-0">{{ number_format($nghiPhep) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat red h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon me-3"><i class="fas fa-user-times"></i></div>
                <div>
                    <h6 class="text-muted mb-1">Đã nghỉ việc</h6>
                    <h3 class="mb-0">{{ number_format($daNghiViec) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card table-admin mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('nguoi-dung.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Từ khóa</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="keyword" value="{{ $keyword }}" class="form-control" placeholder="Tìm theo họ tên, email, số điện thoại, vai trò">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vai trò</label>
                    <select name="vai_tro" class="form-select">
                        <option value="">Tất cả vai trò</option>
                        @foreach($vaiTros as $vaiTroItem)
                            <option value="{{ $vaiTroItem }}" @selected($vaiTro === $vaiTroItem)>{{ $vaiTroItem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" @selected($trangThai !== null && (string) $trangThai === '1')>Hoạt động</option>
                        <option value="0" @selected($trangThai !== null && (string) $trangThai === '0')>Ngưng</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fas fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Ảnh</th>
                        <th>Họ tên</th>
                        <th>Giới tính</th>
                        <th>CCCD</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th class="text-end" style="width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nguoiDungs as $nguoiDung)
                        <tr>
                            <td>
                                <strong>
                                    ND{{ str_pad((string) $nguoiDung->id, 4, '0', STR_PAD_LEFT) }}
                                </strong>
                            </td>

                            <td>
                                @if($nguoiDung->anh_dai_dien)
                                    <img
                                        src="{{ asset('storage/' . $nguoiDung->anh_dai_dien) }}"
                                        alt="{{ $nguoiDung->ho_ten }}"
                                        width="50"
                                        height="50"
                                        class="rounded-circle border"
                                        style="object-fit: cover;"
                                    >
                                @else
                                    <div
                                        class="rounded-circle bg-light border d-flex align-items-center justify-content-center"
                                        style="width:50px;height:50px;"
                                    >
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $nguoiDung->ho_ten }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $nguoiDung->sdt }}
                                </small>
                            </td>

                            <td>
                                {{ $nguoiDung->gioi_tinh ?? '-' }}
                            </td>

                            <td>
                                {{ $nguoiDung->cccd ?? '-' }}
                            </td>

                            <td>
                                {{ $nguoiDung->email }}
                            </td>

                            <td>
                                <span class="badge bg-secondary">
                                    {{ $nguoiDung->vai_tro }}
                                </span>
                            </td>

                            <td>
                                <span class="status-badge {{ $nguoiDung->trang_thai ? 'status-active' : 'status-inactive' }}">
                                    {{ $nguoiDung->trang_thai ? 'Hoạt động' : 'Ngưng hoạt động' }}
                                </span>
                            </td>

                            <td class="text-end">
                                <a href="{{ route('nguoi-dung.show', $nguoiDung) }}"
                                   class="btn btn-sm btn-outline-info btn-action"
                                   title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('nguoi-dung.edit', $nguoiDung) }}"
                                   class="btn btn-sm btn-outline-primary btn-action"
                                   title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                Không có nhân sự nào phù hợp.
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
                Hiển thị {{ $nguoiDungs->firstItem() ?? 0 }}
                - {{ $nguoiDungs->lastItem() ?? 0 }}
                của {{ $nguoiDungs->total() }} nhân sự
            </div>

            {{ $nguoiDungs->links() }}
        </div>
    </div>
</div>
@endsection