<div class="row g-3">
	<div class="col-md-6">
		<label class="form-label">Tên ca <span class="text-danger">*</span></label>
		@php
			$caOptions = ['Ca sáng','Ca chiều','Ca tối','Ca đêm'];
			$selected = old('ten_ca', $caLamViec->ten_ca ?? null);
		@endphp
		<select name="ten_ca" class="form-select @error('ten_ca') is-invalid @enderror">
			<option value="">Chọn ca</option>
			@foreach($caOptions as $opt)
				<option value="{{ $opt }}" @selected($selected === $opt)>{{ $opt }}</option>
			@endforeach
		</select>
		@error('ten_ca')
			<div class="invalid-feedback">{{ $message }}</div>
		@enderror
	</div>

	<div class="col-md-3">
		<label class="form-label">Giờ bắt đầu <span class="text-danger">*</span></label>
		<input
			type="time"
			name="gio_bat_dau"
			value="{{ old('gio_bat_dau', $caLamViec->gio_bat_dau ? \Illuminate\Support\Carbon::parse($caLamViec->gio_bat_dau)->format('H:i') : '') }}"
			class="form-control @error('gio_bat_dau') is-invalid @enderror"
		>
		@error('gio_bat_dau')
			<div class="invalid-feedback">{{ $message }}</div>
		@enderror
	</div>

	<div class="col-md-3">
		<label class="form-label">Giờ kết thúc <span class="text-danger">*</span></label>
		<input
			type="time"
			name="gio_ket_thuc"
			value="{{ old('gio_ket_thuc', $caLamViec->gio_ket_thuc ? \Illuminate\Support\Carbon::parse($caLamViec->gio_ket_thuc)->format('H:i') : '') }}"
			class="form-control @error('gio_ket_thuc') is-invalid @enderror"
		>
		@error('gio_ket_thuc')
			<div class="invalid-feedback">{{ $message }}</div>
		@enderror
	</div>

	<div class="col-md-6">
		<label class="form-label">Số phút đi làm trễ tối đa <span class="text-danger">*</span></label>
		<input
			type="number"
			min="0"
			max="1440"
			name="so_phut_di_lam_tre_toi_da"
			value="{{ old('so_phut_di_lam_tre_toi_da', $caLamViec->so_phut_di_lam_tre_toi_da ?? 15) }}"
			class="form-control @error('so_phut_di_lam_tre_toi_da') is-invalid @enderror"
			placeholder="15"
		>
		@error('so_phut_di_lam_tre_toi_da')
			<div class="invalid-feedback">{{ $message }}</div>
		@enderror
	</div>
</div>
