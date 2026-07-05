@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Sua San pham - SmartMart')

@section('content')

<h1>Trang thêm sản phẩm</h1>


<form action="{{ route('san-pham.store') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

<div class="mb-3">
    <label>Tên sản phẩm <span class="text-danger">*</span></label>

    <input type="text"
           class="form-control @error('ten_san_pham') is-invalid @enderror"
           name="ten_san_pham"
           value="{{ old('ten_san_pham') }}">

    @error('ten_san_pham')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="mb-3">
    <label>Danh mục <span class="text-danger">*</span></label>

    <select name="id_danh_muc"
            class="form-control @error('id_danh_muc') is-invalid @enderror">

        <option value="">--Chọn danh mục--</option>

        @foreach($danhMucs as $item)

            <option value="{{ $item->id }}"
                {{ old('id_danh_muc') == $item->id ? 'selected' : '' }}>

                {{ $item->ten_danh_muc }}

            </option>

        @endforeach

    </select>

    @error('id_danh_muc')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

<div class="mb-3">

    <label>Giá bán <span class="text-danger">*</span></label>

    <input type="number"
           name="gia_ban"
           value="{{ old('gia_ban') }}"
           class="form-control @error('gia_ban') is-invalid @enderror">

    @error('gia_ban')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

</div>

<div class="mb-3">

    <label>Định mức tối thiểu <span class="text-danger">*</span></label>

    <input type="number"
           name="dinh_muc_toi_thieu"
           value="{{ old('dinh_muc_toi_thieu') }}"
           class="form-control @error('dinh_muc_toi_thieu') is-invalid @enderror">

    @error('dinh_muc_toi_thieu')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

</div>

<div class="mb-3">

    <label>Hình ảnh</label>

    <input type="file"
           name="hinh_anh"
           class="form-control @error('hinh_anh') is-invalid @enderror">

    @error('hinh_anh')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

</div>

<div class="mb-3">

    <label>Mô tả</label>

    <textarea name="mo_ta"
              class="form-control">{{ old('mo_ta') }}</textarea>

</div>

    <button class="btn btn-success">
        Thêm sản phẩm
    </button>



{{-- Danh sách biến thể --}}
<div class="card mt-4">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h5 class="mb-0">Danh sách biến thể</h5>

        <button
            type="button"
            class="btn btn-success"
            id="themBienThe">

            <i class="fas fa-plus"></i>
            Thêm biến thể

        </button>

    </div>

    <div class="card-body">

        <table class="table table-bordered" id="tableBienThe">

            <thead>

                <tr>
                    <th>Tên biến thể</th>
                    <th>Hệ số quy đổi</th>
                    <th>Trạng thái</th>
                    <th width="120">Thao tác</th>
                </tr>

            </thead>

            <tbody>

                @if(old('bien_the'))

                    @foreach(old('bien_the') as $i => $bienThe)

                        <tr>

                            <td>

                                <input
                                    type="text"
                                    class="form-control @error("bien_the.$i.ten_bien_the") is-invalid @enderror"
                                    name="bien_the[{{ $i }}][ten_bien_the]"
                                    value="{{ $bienThe['ten_bien_the'] ?? '' }}">

                                @error("bien_the.$i.ten_bien_the")
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </td>

                            <td>

                                <input
                                    type="number"
                                    class="form-control @error("bien_the.$i.he_so_quy_doi") is-invalid @enderror"
                                    name="bien_the[{{ $i }}][he_so_quy_doi]"
                                    value="{{ $bienThe['he_so_quy_doi'] ?? 1 }}">

                                @error("bien_the.$i.he_so_quy_doi")
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </td>

                            <td>

                                <select
                                    class="form-control"
                                    name="bien_the[{{ $i }}][trang_thai]">

                                    <option value="1"
                                        {{ ($bienThe['trang_thai'] ?? 1) == 1 ? 'selected' : '' }}>
                                        Hoạt động
                                    </option>

                                    <option value="0"
                                        {{ ($bienThe['trang_thai'] ?? 1) == 0 ? 'selected' : '' }}>
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

                @endif

            </tbody>

        </table>

    </div>

</div>

<div class="mt-3">
    <button type="submit" class="btn btn-primary">
        Thêm sản phẩm
    </button>
</div>

</form>




<script>

let index = 0;

document.getElementById('themBienThe').onclick = function(){

    let html = `
        <tr>

            <td>

                <input
                    type="text"
                    class="form-control"
                    name="bien_the[${index}][ten_bien_the]"
                    placeholder="Ví dụ: Hộp">

            </td>

            <td>

                <input
                    type="number"
                    class="form-control"
                    value="1"
                    min="1"
                    name="bien_the[${index}][he_so_quy_doi]">

            </td>

            <td>

                <select
                    class="form-control"
                    name="bien_the[${index}][trang_thai]">

                    <option value="1">
                        Hoạt động
                    </option>

                    <option value="0">
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
    `;

    document.querySelector('#tableBienThe tbody')
            .insertAdjacentHTML('beforeend',html);

    index++;

}

document.addEventListener('click',function(e){

    if(e.target.classList.contains('xoa')){

        e.target.closest('tr').remove();

    }

});

</script>
@endsection
