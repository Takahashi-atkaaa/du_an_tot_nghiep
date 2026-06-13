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
                    {{ $nguoiDung->ho_ten }}{{ $nguoiDung->vai_tro ? ' - ' . $nguoiDung->vai_tro : '' }}
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
</div>
