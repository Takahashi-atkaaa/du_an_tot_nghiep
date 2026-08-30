@extends('admin_xem_truoc.layouts.admin')
@section('title', 'Tạo phiếu kiểm kho')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/kiem-kho.css') }}">
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="kk-page-header">
        <h2><i class="fas fa-plus-circle"></i> Tạo phiếu kiểm kho</h2>
        <div class="subtitle">Hệ thống sẽ snapshot tồn kho tại thời điểm tạo phiếu và không thay đổi</div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('kiem-kho.store') }}" x-data="kiemKhoForm()">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Người kiểm <span class="text-danger">*</span></label>
                        <select name="id_nguoi_kiem" class="form-select @error('id_nguoi_kiem') is-invalid @enderror" required>
                            <option value="">-- Chọn người kiểm --</option>
                            @foreach($dsNguoiDung as $nd)
                                <option value="{{ $nd->id }}" {{ old('id_nguoi_kiem') == $nd->id ? 'selected' : '' }}>{{ $nd->ho_ten }} ({{ $nd->email }})</option>
                            @endforeach
                        </select>
                        @error('id_nguoi_kiem') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ngày kiểm</label>
                        <input type="date" name="ngay_kiem" class="form-control" value="{{ old('ngay_kiem', date('Y-m-d')) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Phạm vi kiểm <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input type="radio" name="pham_vi" value="toan_bo" id="pv_1" x-model="phamVi" {{ old('pham_vi', 'toan_bo') == 'toan_bo' ? 'checked' : '' }}>
                                <label for="pv_1">Toàn bộ sản phẩm</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="pham_vi" value="theo_danh_muc" id="pv_2" x-model="phamVi" {{ old('pham_vi') == 'theo_danh_muc' ? 'checked' : '' }}>
                                <label for="pv_2">Theo danh mục</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="pham_vi" value="chon_san_pham" id="pv_3" x-model="phamVi" {{ old('pham_vi') == 'chon_san_pham' ? 'checked' : '' }}>
                                <label for="pv_3">Chọn sản phẩm</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12" x-show="phamVi === 'theo_danh_muc'" x-cloak>
                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select name="id_danh_muc" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($dsDanhMuc as $dm)
                                <option value="{{ $dm->id }}" {{ old('id_danh_muc') == $dm->id ? 'selected' : '' }}>{{ $dm->ten_danh_muc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12" x-show="phamVi === 'chon_san_pham'" x-cloak>
                        <label class="form-label">Tìm sản phẩm <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" x-model="search" @input.debounce.300ms="timVariant" placeholder="Nhập tên, mã vạch, mã hàng...">
                            <button class="btn btn-outline-secondary" type="button" @click="timVariant">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="mt-2 border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                            <template x-for="v in suggestions" :key="v.id">
                                <div class="form-check">
                                    <input type="checkbox" :value="v.id" x-model="selected" class="form-check-input" :id="'v-' + v.id">
                                    <label class="form-check-label" :for="'v-' + v.id">
                                        <span x-text="v.ten_hien_thi"></span>
                                        <small class="text-muted">
                                            (<span x-text="v.ma_vach || 'N/A'"></span>,
                                            tồn: <strong x-text="v.so_luong_ton"></strong>)
                                        </small>
                                    </label>
                                </div>
                            </template>
                            <div x-show="suggestions.length === 0" class="text-muted text-center py-3">Nhập từ khóa để tìm...</div>
                        </div>
                        <input type="hidden" name="variant_ids" :value="JSON.stringify(selected)">
                        <small class="text-muted">Đã chọn: <strong x-text="selected.length"></strong> sản phẩm</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="ghi_chu" class="form-control" rows="3" placeholder="Ghi chú thêm...">{{ old('ghi_chu') }}</textarea>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between">
                    <a href="{{ route('kiem-kho.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Tạo phiếu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function kiemKhoForm() {
    return {
        phamVi: '{{ old('pham_vi', 'toan_bo') }}',
        search: '',
        suggestions: [],
        selected: {!! json_encode(old('variant_ids') ?? []) !!},

        async timVariant() {
            if (this.search.trim().length < 2) {
                this.suggestions = [];
                return;
            }
            try {
                const res = await axios.get('{{ route('admin.api.kiem-kho.tim-variant') }}', {
                    params: { q: this.search }
                });
                this.suggestions = res.data.data || [];
            } catch (e) {
                toastr.error('Không thể tải danh sách sản phẩm');
            }
        }
    }
}
</script>
@endsection