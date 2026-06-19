<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
        <input type="text" name="ho_ten"
            value="{{ old('ho_ten', $nguoiDung->ho_ten) }}"
            class="form-control @error('ho_ten') is-invalid @enderror"
            placeholder="Nhập họ tên">
        @error('ho_ten')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email"
            value="{{ old('email', $nguoiDung->email) }}"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="example@smartmart.vn">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
        <input type="text" name="sdt"
            value="{{ old('sdt', $nguoiDung->sdt) }}"
            class="form-control @error('sdt') is-invalid @enderror"
            placeholder="0912345678">
        @error('sdt')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Giới tính <span class="text-danger">*</span></label>
        <select name="gioi_tinh"
            class="form-select @error('gioi_tinh') is-invalid @enderror">
            <option value="">Chọn giới tính</option>
            <option value="Nam" @selected(old('gioi_tinh', $nguoiDung->gioi_tinh) === 'Nam')>Nam</option>
            <option value="Nữ" @selected(old('gioi_tinh', $nguoiDung->gioi_tinh) === 'Nữ')>Nữ</option>
            <option value="Khác" @selected(old('gioi_tinh', $nguoiDung->gioi_tinh) === 'Khác')>Khác</option>
        </select>
        @error('gioi_tinh')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">CCCD <span class="text-danger">*</span></label>
        <input type="text" name="cccd"
            value="{{ old('cccd', $nguoiDung->cccd) }}"
            class="form-control @error('cccd') is-invalid @enderror"
            placeholder="Nhập số CCCD">
        @error('cccd')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @if(! $isEdit)
        <div class="col-md-6">
            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
            <input type="password" name="mat_khau"
                autocomplete="new-password"
                class="form-control @error('mat_khau') is-invalid @enderror"
                placeholder="Nhập mật khẩu">
            @error('mat_khau')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div class="col-md-6">
        <label class="form-label">Ảnh đại diện</label>
        <input type="file" name="anh_dai_dien"
            class="form-control @error('anh_dai_dien') is-invalid @enderror">

        @if($isEdit && $nguoiDung->anh_dai_dien)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $nguoiDung->anh_dai_dien) }}"
                    width="100"
                    class="img-thumbnail">
            </div>
        @endif

        @error('anh_dai_dien')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">CCCD mặt trước</label>
        <input type="file" name="anh_cccd_mat_truoc"
            class="form-control @error('anh_cccd_mat_truoc') is-invalid @enderror">

        @if($isEdit && $nguoiDung->anh_cccd_mat_truoc)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $nguoiDung->anh_cccd_mat_truoc) }}"
                    width="150"
                    class="img-thumbnail">
            </div>
        @endif

        @error('anh_cccd_mat_truoc')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">CCCD mặt sau</label>
        <input type="file" name="anh_cccd_mat_sau"
            class="form-control @error('anh_cccd_mat_sau') is-invalid @enderror">

        @if($isEdit && $nguoiDung->anh_cccd_mat_sau)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $nguoiDung->anh_cccd_mat_sau) }}"
                    width="150"
                    class="img-thumbnail">
            </div>
        @endif

        @error('anh_cccd_mat_sau')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
        <select name="id_vai_tro"
            class="form-select @error('id_vai_tro') is-invalid @enderror">
            <option value="">Chọn vai trò</option>
            @foreach($vaiTros as $vaiTro)
                <option value="{{ $vaiTro->id }}"
                    @selected((string) old('id_vai_tro', $nguoiDung->id_vai_tro) === (string) $vaiTro->id)>
                    {{ $vaiTro->ten_vai_tro }}
                </option>
            @endforeach
        </select>
        @error('id_vai_tro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
        <select name="trang_thai"
            class="form-select @error('trang_thai') is-invalid @enderror">
            <option value="1"
                @selected(old('trang_thai', (string) $nguoiDung->trang_thai) === '1')>
                Hoạt động
            </option>
            <option value="0"
                @selected(old('trang_thai', (string) $nguoiDung->trang_thai) === '0')>
                Ngưng hoạt động
            </option>
        </select>
        @error('trang_thai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
