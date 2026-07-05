@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quan ly San pham - SmartMart')

@section('content')
 <h1>Trang sản phẩm</h1>

 <a href="{{url('san-pham/them-san-pham')}}" class="btn btn-primary">Thêm sản phẩm</a>

 <table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá bán</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
<tbody>

@foreach($danhSachSanPham as $sanPham)

<tr>

    <td>{{ $sanPham->id }}</td>

    <td>

        @if($sanPham->hinh_anh)

            <img src="{{ asset('uploads/san_pham/'.$sanPham->hinh_anh) }}"
                 alt="{{ $sanPham->ten_san_pham }}"
                 width="80"
                 height="80"
                 style="object-fit: cover; border-radius:8px;">

        @else

            <span class="text-muted">Chưa có ảnh</span>

        @endif

    </td>

    <td>{{ $sanPham->ten_san_pham }}</td>

    <td>{{ $sanPham->danhMuc->ten_danh_muc ?? 'Chưa có' }}</td>

    <td>{{ number_format($sanPham->gia_ban) }} đ</td>

    <td>

        <a href="{{ route('san-pham.edit',$sanPham->id) }}"
           class="btn btn-warning btn-sm">

            Sửa

        </a>

    </td>

</tr>

@endforeach

</tbody>
    </tbody>
</table>


@endsection
