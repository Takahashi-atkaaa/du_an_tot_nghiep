<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nhân viên <span class="text-danger">*</span></label>
        <select
            name="id_nguoi_dung"
            class="form-select @error('id_nguoi_dung') is-invalid @enderror"
        >
            <option value="">Chọn nhân viên</option>
            @foreach($nguoiDungs as $nguoiDung)
                <option
                    value="{{ $nguoiDung->id }}"
                    @selected((string) old('id_nguoi_dung', $chiaCaLamViec->id_nguoi_dung ?? '') === (string) $nguoiDung->id)
                >
                    {{ $nguoiDung->ho_ten }}{{ optional($nguoiDung->vaiTro)->ten_vai_tro ? ' - ' . optional($nguoiDung->vaiTro)->ten_vai_tro : '' }}
                </option>
            @endforeach
        </select>
        @error('id_nguoi_dung')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Ca làm việc <span class="text-danger">*</span></label>
        <select
            name="id_ca_lam_viec"
            class="form-select @error('id_ca_lam_viec') is-invalid @enderror"
        >
            <option value="">Chọn ca làm việc</option>
            @foreach($caLamViecs as $caLamViec)
                <option
                    value="{{ $caLamViec->id }}"
                    @selected((string) old('id_ca_lam_viec', $chiaCaLamViec->id_ca_lam_viec ?? '') === (string) $caLamViec->id)
                >
                    {{ $caLamViec->ten_ca }} ({{ \Illuminate\Support\Carbon::parse($caLamViec->gio_bat_dau)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($caLamViec->gio_ket_thuc)->format('H:i') }})
                </option>
            @endforeach
        </select>
        @error('id_ca_lam_viec')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Ngày làm việc <span class="text-danger">*</span></label>
        <input
            type="date"
            name="ngay"
            value="{{ old('ngay', isset($chiaCaLamViec->ngay) ? \Illuminate\Support\Carbon::parse($chiaCaLamViec->ngay)->format('Y-m-d') : '') }}"
            class="form-control @error('ngay') is-invalid @enderror"
        >
        @error('ngay')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Vai trò trong ca <span class="text-danger">*</span></label>
        <select
            name="vai_tro_trong_ca"
            class="form-select @error('vai_tro_trong_ca') is-invalid @enderror"
        >
            <option value="nhan_vien" @selected(old('vai_tro_trong_ca', $chiaCaLamViec->vai_tro_trong_ca ?? 'nhan_vien') === 'nhan_vien')>
                Nhân viên
            </option>
            <option value="truong_ca" @selected(old('vai_tro_trong_ca', $chiaCaLamViec->vai_tro_trong_ca ?? '') === 'truong_ca')>
                Trưởng ca
            </option>
        </select>
        @error('vai_tro_trong_ca')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
