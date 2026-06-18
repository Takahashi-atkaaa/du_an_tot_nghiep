@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thùng rác Nhà cung cấp')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-1">Thùng rác - Nhà cung cấp</h4>
    <a href="{{ url('/admin/kho-hang/nha-cung-cap') }}" class="btn btn-secondary">Quay lại danh sách</a>
</div>

<div class="card table-admin">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Đã xóa lúc</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->ten_nha_cung_cap }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->so_dien_thoai }}</td>
                            <td>{{ $item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <form action="{{ url('/admin/kho-hang/nha-cung-cap/'.$item->id.'/restore') }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Khôi phục</button>
                                </form>
                                <form action="{{ url('/admin/kho-hang/nha-cung-cap/'.$item->id.'/force') }}" method="POST" style="display:inline-block; margin-left:6px;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa vĩnh viễn nhà cung cấp này?')">Xóa vĩnh viễn</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Thùng rác trống.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>

@endsection
