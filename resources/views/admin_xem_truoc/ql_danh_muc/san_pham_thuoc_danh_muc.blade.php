@extends('admin_xem_truoc.layouts.admin')


@section('title', 'Danh mục sản phẩm sản phẩm thuộc danh mục- SmartMart')

@section('content')

<div class="container-fluid">

    {{-- Tiêu đề --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                Sản phẩm thuộc danh mục
            </h4>

            <p class="text-muted mb-0">
                Danh mục: <strong>{{ $danhmuc->ten_danh_muc }}</strong>
            </p>
        </div>

        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    {{-- Danh sách sản phẩm --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                Danh sách sản phẩm
            </h5>
        </div>

        <div class="card-body">

            @if($sanpham->count() > 0)

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá bán</th>
                                <th>Định mức tối thiểu</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($sanpham as $index => $sp)
                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        @if($sp->hinh_anh)
                                            <img
                                                src="{{ asset('storage/' . $sp->hinh_anh) }}"
                                                alt="{{ $sp->ten_san_pham }}"
                                                width="60"
                                                height="60"
                                                style="object-fit: cover; border-radius: 8px;"
                                            >
                                        @else
                                            <span class="text-muted">
                                                Không có ảnh
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $sp->ten_san_pham }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ number_format($sp->gia_ban, 0, ',', '.') }} đ
                                    </td>

                                    <td>
                                        {{ $sp->dinh_muc_toi_thieu }}
                                    </td>

                                    <td>
                                        @if($sp->trang_thai)
                                            <span class="badge bg-success">
                                                Đang bán
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                Ngừng bán
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ '/admin/san-pham/' . $sp->id }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @else

                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>

                    <h5>Chưa có sản phẩm</h5>

                    <p class="text-muted mb-0">
                        Danh mục này hiện chưa có sản phẩm nào.
                    </p>
                </div>

            @endif

        </div>
    </div>

</div>


@endsection