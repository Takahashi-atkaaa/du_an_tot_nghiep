@extends('admin_xem_truoc.layouts.admin')
@section('title', 'Thùng rác kiểm kho')

@section('content')
<div class="container-fluid py-4">
    <div class="kk-page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-trash"></i> Thùng rác kiểm kho</h2>
        </div>
        <a href="{{ route('kiem-kho.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="kk-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Ngày xóa</th>
                    <th>Người kiểm</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phieus as $p)
                <tr>
                    <td><strong>{{ $p->ma_kiem_kho }}</strong></td>
                    <td>{{ $p->deleted_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $p->nguoiKiem?->ho_ten ?? '-' }}</td>
                    <td class="text-end">
                        @if(userHasPermission('kiem_kho_huy'))
                            <form action="{{ route('kiem-kho.restore', $p->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-undo"></i> Khôi phục
                                </button>
                            </form>
                            <form action="{{ route('kiem-kho.force-delete', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa vĩnh viễn?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-times"></i> Xóa vĩnh viễn
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-5 text-muted">Thùng rác trống.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $phieus->links() }}</div>
</div>
@endsection
