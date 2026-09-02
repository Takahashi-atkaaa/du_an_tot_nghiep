@extends('admin_xem_truoc.layouts.admin')
@section('title', 'Kiểm đếm - ' . $phieu->ma_kiem_kho)
@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/kiem-kho.css') }}">
@endsection

@section('content')
<div class="container-fluid py-3" x-data="kiemKhoDem({{ $phieu->id }})">
    <!-- Action bar -->
    <div class="kk-action-bar">
        <a href="{{ route('kiem-kho.show', $phieu->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>

        <div class="flex-grow-1">
            <strong>{{ $phieu->ma_kiem_kho }}</strong>
            <span class="kk-status {{ $phieu->trang_thai }}">{{ $phieu->trang_thai_label }}</span>
        </div>

        <input type="text" class="form-control form-control-sm" style="max-width: 250px;" x-model="search" placeholder="Tìm sản phẩm...">
        <select class="form-select form-select-sm" style="max-width: 150px;" x-model="filterLoai">
            <option value="">Tất cả</option>
            <option value="chua_dem">Chưa đếm</option>
            <option value="thieu">Thiếu</option>
            <option value="du">Đủ</option>
            <option value="thua">Thừa</option>
        </select>

        <button type="button" class="btn btn-info btn-sm" @click="saveAll" :disabled="savingAll">
            <i class="fas fa-save"></i> Lưu tất cả
        </button>

        @if($phieu->co_the_hoan_tat_dem && userHasPermission('kiem_kho_dem'))
            <button type="button" class="btn btn-success btn-sm" @click="hoanTatDem">
                <i class="fas fa-check-double"></i> Hoàn tất đếm
            </button>
        @endif
    </div>

    <!-- Thống kê realtime -->
    <div class="row g-2 mb-3">
        <div class="col-md-2"><div class="kk-stat-card bg-blue"><div class="label">Tổng SP</div><div class="value" x-text="thongKe.tong_so_san_pham"></div></div></div>
        <div class="col-md-2"><div class="kk-stat-card bg-green"><div class="label">Đúng</div><div class="value" x-text="thongKe.so_sp_dung"></div></div></div>
        <div class="col-md-2"><div class="kk-stat-card bg-red"><div class="label">Thiếu</div><div class="value" x-text="thongKe.so_sp_thieu"></div></div></div>
        <div class="col-md-2"><div class="kk-stat-card bg-yellow"><div class="label">Thừa</div><div class="value" x-text="thongKe.so_sp_thua"></div></div></div>
        <div class="col-md-2"><div class="kk-stat-card"><div class="label">Chưa đếm</div><div class="value" x-text="thongKe.so_sp_chua_dem"></div></div></div>
        <div class="col-md-2"><div class="kk-stat-card"><div class="label">Tổng lệch</div><div class="value" :class="thongKe.tong_sl_lech < 0 ? 'text-danger' : 'text-warning'" x-text="(thongKe.tong_sl_lech || 0)"></div></div></div>
    </div>

    <!-- Bảng đếm -->
    <div class="kk-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Mã SP</th>
                    <th>Sản phẩm</th>
                    <th class="text-center">Tồn HT</th>
                    <th class="text-center" style="width: 140px;">Thực tế</th>
                    <th class="text-center">Chênh lệch</th>
                    <th>Lý do</th>
                    <th class="text-center">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @foreach($phieu->chiTietKiemKho as $i => $ct)
                @php
                    $searchKey = strtolower(trim(($ct->ten_san_pham ?? '') . ' ' . ($ct->ma_vach ?? '') . ' ' . ($ct->ma_hang ?? '')));
                @endphp
                <tr class="kk-row-{{ $ct->loai_chenh_lech }}"
                    x-show="(filterLoai === '' || '{{ $ct->loai_chenh_lech }}' === filterLoai) && (search === '' || @js($searchKey).includes(search.toLowerCase()))">
                    <td>{{ $i + 1 }}</td>
                    <td><code>{{ $ct->ma_hang ?? $ct->ma_vach ?? '#' . $ct->id }}</code></td>
                    <td>
                        <strong>{{ $ct->ten_san_pham }}</strong>
                        @if($ct->ten_bien_the || $ct->ten_don_vi)
                            <br><small class="text-muted">{{ $ct->ten_bien_the ?? $ct->ten_don_vi }}</small>
                        @endif
                    </td>
                    <td class="text-center"><strong>{{ $ct->so_luong_he_thong }}</strong></td>
                    <td class="text-center">
                        <input type="number"
                               min="0"
                               class="kk-input-qty"
                               x-model="items[{{ $ct->id }}].so_luong_thuc_te"
                               @change.debounce.500ms="capNhat({{ $ct->id }})"
                               :class="{ 'is-edited': items[{{ $ct->id }}].edited }">
                    </td>
                    <td class="text-center">
                        <strong :class="items[{{ $ct->id }}].chenh_lech < 0 ? 'text-danger' : (items[{{ $ct->id }}].chenh_lech > 0 ? 'text-warning' : 'text-success')">
                            <span x-text="formatLech(items[{{ $ct->id }}].chenh_lech)"></span>
                        </strong>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" x-model="items[{{ $ct->id }}].ly_do" placeholder="Lý do (nếu có)" @change.debounce.500ms="capNhat({{ $ct->id }})">
                    </td>
                    <td class="text-center">
                        <span class="kk-status" :class="loaiChenhLechClass(items[{{ $ct->id }}].chenh_lech, items[{{ $ct->id }}].da_dem)" x-text="loaiChenhLechLabel(items[{{ $ct->id }}].chenh_lech, items[{{ $ct->id }}].da_dem)"></span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3 text-end text-muted">
        <small>Mã phiếu: <strong>{{ $phieu->ma_kiem_kho }}</strong> - Tự động lưu sau khi nhập 0.5s</small>
    </div>
</div>
@endsection

@section('scripts')
<script>
function kiemKhoDem(id) {
    return {
        search: '',
        filterLoai: '',
        savingAll: false,
        thongKe: {!! json_encode($thongKe ?? []) !!},
        items: {!! $itemsJson ?? '{}' !!},
        async capNhat(itemId) {
            const it = this.items[itemId];
            if (it.so_luong_thuc_te === null || it.so_luong_thuc_te === undefined || it.so_luong_thuc_te === '') return;
            if (it.so_luong_thuc_te < 0) {
                toastr.error('Số lượng thực tế phải >= 0');
                return;
            }
            // Recompute locally
            it.chenh_lech = parseInt(it.so_luong_thuc_te) - it.so_luong_he_thong;
            it.da_dem = true;
            it.edited = true;

            try {
                const res = await axios.post(`/admin/api/kiem-kho/${id}/items/${itemId}`, {
                    chi_tiet_id: itemId,
                    so_luong_thuc_te: parseInt(it.so_luong_thuc_te),
                    ly_do: it.ly_do
                });
                if (res.data.success) {
                    it.edited = false;
                    if (res.data.data?.thong_ke) {
                        this.thongKe = res.data.data.thong_ke;
                    }
                }
            } catch (e) {
                toastr.error(e.response?.data?.message || 'Lỗi khi lưu');
            }
        },
        formatLech(v) {
            if (v == null) return '-';
            return v > 0 ? '+' + v : v;
        },
        loaiChenhLechClass(lech, daDem) {
            if (!daDem) return 'cancelled';
            if (lech < 0) return 'rejected';
            if (lech > 0) return 'pending';
            return 'completed';
        },
        loaiChenhLechLabel(lech, daDem) {
            if (!daDem) return 'Chưa đếm';
            if (lech < 0) return 'Thiếu';
            if (lech > 0) return 'Thừa';
            return 'Đủ';
        },
        async saveAll() {
            this.savingAll = true;
            const items = Object.entries(this.items).map(([id, it]) => ({
                chi_tiet_id: parseInt(id),
                so_luong_thuc_te: it.so_luong_thuc_te,
                ly_do: it.ly_do
            })).filter(it => it.so_luong_thuc_te !== null && it.so_luong_thuc_te !== undefined && it.so_luong_thuc_te !== '');
            try {
                const res = await axios.post(`/admin/api/kiem-kho/${id}/items/bulk`, { items });
                if (res.data.success) {
                    toastr.success(res.data.message);
                    if (res.data.data?.thong_ke) this.thongKe = res.data.data.thong_ke;
                }
            } catch (e) {
                toastr.error('Lỗi khi lưu');
            } finally {
                this.savingAll = false;
            }
        },
        async hoanTatDem() {
            const chuaDem = Object.values(this.items).filter(it => !it.da_dem).length;
            if (chuaDem > 0) {
                if (!confirm(`Còn ${chuaDem} sản phẩm chưa đếm. Bạn vẫn muốn hoàn tất?`)) return;
            } else {
                if (!confirm('Xác nhận hoàn tất kiểm đếm?')) return;
            }
            try {
                const res = await axios.post(`/admin/api/kiem-kho/${id}/hoan-tat-kiem`);
                if (res.data.success) {
                    toastr.success(res.data.message);
                    setTimeout(() => location.reload(), 800);
                }
            } catch (e) {
                toastr.error(e.response?.data?.message || 'Lỗi');
            }
        }
    }
}
</script>
@endsection
