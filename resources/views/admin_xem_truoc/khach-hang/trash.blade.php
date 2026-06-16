@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Thùng rác khách hàng')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Thùng rác khách hàng</h4>

        <a href="{{ route('khach-hang.index') }}"
           class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Ngày xóa</th>
                <th width="220">Thao tác</th>
            </tr>
        </thead>

        <tbody>
            @forelse($khachHangs as $khachHang)
                <tr>
                    <td>{{ $khachHang->id }}</td>
                    <td>{{ $khachHang->ten_khach_hang }}</td>
                    <td>{{ $khachHang->so_dien_thoai }}</td>
                    <td>{{ $khachHang->email }}</td>
                    <td>{{ $khachHang->deleted_at }}</td>

                    <td>
                        <form action="{{ route('khach-hang.restore',$khachHang->id) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            @method('PUT')

                            <button class="btn btn-success btn-sm">
                                Khôi phục
                            </button>
                        </form>

                        <form action="{{ route('khach-hang.force-delete',$khachHang->id) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Xóa vĩnh viễn khách hàng này?')">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-dark btn-sm">
                                Xóa vĩnh viễn
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        Không có dữ liệu trong thùng rác
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $khachHangs->links() }}

</div>
@endsection