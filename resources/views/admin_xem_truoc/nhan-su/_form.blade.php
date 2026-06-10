<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
        <input type="text" name="ho_ten" value="{{ old('ho_ten', $nguoiDung->ho_ten) }}" class="form-control @error('ho_ten') is-invalid @enderror" placeholder="Nhập họ tên">
        @error('ho_ten')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" value="{{ old('email', $nguoiDung->email) }}" class="form-control @error('email') is-invalid @enderror" placeholder="example@smartmart.vn">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
        <input type="text" name="sdt" value="{{ old('sdt', $nguoiDung->sdt) }}" class="form-control @error('sdt') is-invalid @enderror" placeholder="0912345678">
        @error('sdt')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    @if(! $isEdit)
        <div class="col-md-6">
            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
            <input type="password" name="mat_khau" autocomplete="new-password" class="form-control @error('mat_khau') is-invalid @enderror" placeholder="Nhập mật khẩu">
            @error('mat_khau')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif
    <div class="col-md-6">
        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
        <select name="vai_tro" class="form-select @error('vai_tro') is-invalid @enderror">
            <option value="">Chọn vai trò</option>
            @foreach($vaiTros as $vaiTro)
                <option value="{{ $vaiTro }}" @selected(old('vai_tro', $nguoiDung->vai_tro) === $vaiTro)>{{ $vaiTro }}</option>
            @endforeach
        </select>
        @error('vai_tro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
        <select name="trang_thai" class="form-select @error('trang_thai') is-invalid @enderror">
            <option value="1" @selected(old('trang_thai', (string) $nguoiDung->trang_thai) === '1')>Hoạt động</option>
            <option value="0" @selected(old('trang_thai', (string) $nguoiDung->trang_thai) === '0')>Ngưng hoạt động</option>
        </select>
        @error('trang_thai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>