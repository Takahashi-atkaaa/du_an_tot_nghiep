@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quan ly ca lam viec - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Danh sách ca làm việc</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/ca-lam-viec') }}">Ca làm việc</a></li>
            </ol>
        </nav>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-column gap-2">
        <a href="{{ route('ca-lam-viec.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm ca làm việc
        </a>
        <a href="{{ route('chia-ca-lam-viec.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-calendar-alt me-2"></i>Chia ca làm việc
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card table-admin">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tên ca</th>
                        <th>Giờ bắt đầu</th>
                        <th>Giờ kết thúc</th>
                        <th>Đi trễ tối đa</th>
                        <th class="text-end" style="width: 160px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($caLamViecs as $caLamViec)
                        <tr>
                            <td>{{ $caLamViec->ten_ca }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($caLamViec->gio_bat_dau)->format('H:i') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($caLamViec->gio_ket_thuc)->format('H:i') }}</td>
                            <td>{{ $caLamViec->so_phut_di_lam_tre_toi_da }} phút</td>
                            <td class="text-end">
                                <a href="{{ route('ca-lam-viec.edit', $caLamViec) }}" class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('ca-lam-viec.destroy', $caLamViec) }}" method="POST" class="d-inline" onsubmit="return confirm('Hủy ca làm việc này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Hủy">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Chưa có ca làm việc nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="text-muted">
                Hiển thị {{ $caLamViecs->firstItem() ?? 0 }}-{{ $caLamViecs->lastItem() ?? 0 }} của {{ $caLamViecs->total() }} ca làm việc
            </div>
            {{ $caLamViecs->links() }}
        </div>
    </div>
</div>
@endsection
