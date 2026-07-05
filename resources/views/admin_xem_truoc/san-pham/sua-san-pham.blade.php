@extends('admin_xem_truoc.layouts.admin')

@section('title','Sửa sản phẩm')

@section('content')

<h1>Sửa sản phẩm</h1>

<form action="{{ route('san-pham.update',$sanPham->id) }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Tên sản phẩm</label>

        <input type="text"
               class="form-control @error('ten_san_pham') is-invalid @enderror"
               name="ten_san_pham"
               value="{{ old('ten_san_pham',$sanPham->ten_san_pham) }}">

        @error('ten_san_pham')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    <div class="mb-3">

        <label>Danh mục</label>

        <select
            name="id_danh_muc"
            class="form-control">

            @foreach($danhMucs as $item)

                <option
                    value="{{ $item->id }}"
                    {{ old('id_danh_muc',$sanPham->id_danh_muc)==$item->id ? 'selected':'' }}>

                    {{ $item->ten_danh_muc }}

                </option>

            @endforeach

        </select>

    </div>

    <div class="mb-3">

        <label>Giá bán</label>

        <input
            type="number"
            class="form-control"
            name="gia_ban"
            value="{{ old('gia_ban',$sanPham->gia_ban) }}">

    </div>

    <div class="mb-3">

        <label>Định mức tối thiểu</label>

        <input
            type="number"
            class="form-control"
            name="dinh_muc_toi_thieu"
            value="{{ old('dinh_muc_toi_thieu',$sanPham->dinh_muc_toi_thieu) }}">

    </div>

    <div class="mb-3">

        <label>Hình ảnh</label>

        @if($sanPham->hinh_anh)

            <img
                src="{{ asset('uploads/san_pham/'.$sanPham->hinh_anh) }}"
                width="120"
                class="mb-2">

        @endif

        <input
            type="file"
            class="form-control"
            name="hinh_anh">

    </div>

    <div class="mb-3">

        <label>Mô tả</label>

        <textarea
            class="form-control"
            name="mo_ta">{{ old('mo_ta',$sanPham->mo_ta) }}</textarea>

    </div>

    {{--=================== BIẾN THỂ ===================--}}

    <div class="card">

        <div class="card-header d-flex justify-content-between">

            <h5>Danh sách biến thể</h5>

            <button
                type="button"
                class="btn btn-success"
                id="themBienThe">

                Thêm biến thể

            </button>

        </div>

        <div class="card-body">

            <table
                class="table table-bordered"
                id="tableBienThe">

                <thead>

                <tr>

                    <th>Tên biến thể</th>

                    <th>Hệ số quy đổi</th>

                    <th>Trạng thái</th>

                    <th width="100">Thao tác</th>

                </tr>

                </thead>

                <tbody>

                @php

                    $bienThes = old('bien_the',$sanPham->bienTheSanPhams);

                @endphp

                @foreach($bienThes as $i=>$bienThe)

                    <tr>

                        <td>

                            <input
                                type="text"
                                class="form-control"
                                name="bien_the[{{$i}}][ten_bien_the]"
                                value="{{ is_array($bienThe)?$bienThe['ten_bien_the']:$bienThe->ten_bien_the }}">

                        </td>

                        <td>

                            <input
                                type="number"
                                class="form-control"
                                name="bien_the[{{$i}}][he_so_quy_doi]"
                                value="{{ is_array($bienThe)?$bienThe['he_so_quy_doi']:$bienThe->he_so_quy_doi }}">

                        </td>

                        <td>

                            <select
                                class="form-control"
                                name="bien_the[{{$i}}][trang_thai]">

                                <option
                                    value="1"
                                    {{ (is_array($bienThe)?$bienThe['trang_thai']:$bienThe->trang_thai)==1?'selected':'' }}>

                                    Hoạt động

                                </option>

                                <option
                                    value="0"
                                    {{ (is_array($bienThe)?$bienThe['trang_thai']:$bienThe->trang_thai)==0?'selected':'' }}>

                                    Ngừng

                                </option>

                            </select>

                        </td>

                        <td class="text-center">

                            <button
                                type="button"
                                class="btn btn-danger btn-sm xoa">

                                Xóa

                            </button>

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-3">

        <button class="btn btn-primary">

            Cập nhật sản phẩm

        </button>

    </div>

</form>

<script>

let index = {{ count($sanPham->bienTheSanPhams) }};

document.getElementById('themBienThe').onclick=function(){

    let html=`

<tr>

<td>

<input
type="text"
class="form-control"
name="bien_the[${index}][ten_bien_the]">

</td>

<td>

<input
type="number"
class="form-control"
value="1"
name="bien_the[${index}][he_so_quy_doi]">

</td>

<td>

<select
class="form-control"
name="bien_the[${index}][trang_thai]">

<option value="1">Hoạt động</option>

<option value="0">Ngừng</option>

</select>

</td>

<td class="text-center">

<button
type="button"
class="btn btn-danger btn-sm xoa">

Xóa

</button>

</td>

</tr>

`;

document.querySelector("#tableBienThe tbody")
.insertAdjacentHTML("beforeend",html);

index++;

}

document.addEventListener("click",function(e){

if(e.target.classList.contains("xoa")){

e.target.closest("tr").remove();

}

});

</script>

@endsection