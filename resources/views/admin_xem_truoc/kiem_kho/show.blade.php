@extends('admin_xem_truoc.layouts.admin')
@section('title', 'Chi tiết phiếu kiểm kho')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/kiem-kho.css') }}">
@endsection

@section('content')
<div class="container-fluid py-4" x-data="kiemKhoShow({{ $phieu->id }})">
    <!-- Header -->
    <div class="kk-page-header d-flex justify-content-between align-items-start">
        <div>
            <h2><i class="fas fa-clipboard-check"></i> Phiếu kiểm kho {{ $phieu->ma_kiem_kho }}</h2>
            <div class="subtitle">Ngày kiểm: {{ $phieu->ngay_kiem?->format('d/m/Y') }} - Phạm vi: {{ $phieu->pham_vi_label }}</div>
        </div>
        <div>
            <span class="kk-status {{ $phieu->trang_thai }}">{{ $phieu->trang_thai_label }}</span>
        </div>
    </div>

    <!-- Action bar -->
    <div class="kk-action-bar">
        <a href="{{ route('kiem-kho.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>

        @if($phieu->co_the_dem && userHasPermission('kiem_kho_dem'))
            <a href="{{ route('kiem-kho.dem', $phieu->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-clipboard-check"></i> Kiểm đếm
            </a>
        @endif

        @if($phieu->co_the_sua && userHasPermission('kiem_kho_tao'))
            <a href="{{ route('kiem-kho.edit', $phieu->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Sửa
            </a>
        @endif

        @if($phieu->co_the_huy && userHasPermission('kiem_kho_huy'))
            <button type="button" class="btn btn-danger btn-sm" @click="huyPhieu">
                <i class="fas fa-ban"></i> Hủy phiếu
            </button>
        @endif

        <a href="{{ route('kiem-kho.print', $phieu->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-print"></i> In
        </a>
    </div>

    <!-- Thông tin phiếu -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Mã phiếu:</strong> {{ $phieu->ma_kiem_kho }}</div>
                <div class="col-md-3"><strong>Ngày kiểm:</strong> {{ $phieu->ngay_kiem?->format('d/m/Y') }}</div>
                <div class="col-md-3"><strong>Người tạo:</strong> {{ $phieu->nguoiTao?->ho_ten ?? '-' }}</div>
                <div class="col-md-3"><strong>Người kiểm:</strong> {{ $phieu->nguoiKiem?->ho_ten ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>Người duyệt:</strong> {{ $phieu->nguoiDuyet?->ho_ten ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>Bắt đầu:</strong> {{ $phieu->bat_dau_luc?->format('d/m/Y H:i') ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>Hoàn tất đếm:</strong> {{ $phieu->hoan_tat_dem_luc?->format('d/m/Y H:i') ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>Hoàn thành:</strong> {{ $phieu->hoan_thanh_luc?->format('d/m/Y H:i') ?? '-' }}</div>
            </div>
            @if($phieu->ghi_chu)
                <div class="mt-2"><strong>Ghi chú:</strong> {{ $phieu->ghi_chu }}</div>
            @endif
            @if($phieu->ly_do_tu_choi)
                <div class="mt-2 alert alert-warning"><strong>Lý do từ chối:</strong> {{ $phieu->ly_do_tu_choi }}</div>
            @endif
            @if($phieu->ly_do_huy)
                <div class="mt-2 alert alert-danger"><strong>Lý do hủy:</strong> {{ $phieu->ly_do_huy }}</div>
            @endif
        </div>
    </div>

    <!-- Thống kê -->
    <div class="row g-3 mb-3">
        <div class="col-md-2">
            <div class="kk-stat-card bg-blue">
                <div class="label">Tổng SP</div>
                <div class="value">{{ $thongKe['tong_so_san_pham'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="kk-stat-card bg-green">
                <div class="label">Đúng</div>
                <div class="value">{{ $thongKe['so_sp_dung'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="kk-stat-card bg-red">
                <div class="label">Thiếu</div>
                <div class="value">{{ $thongKe['so_sp_thieu'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="kk-stat-card bg-yellow">
                <div class="label">Thừa</div>
                <div class="value">{{ $thongKe['so_sp_thua'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="kk-stat-card">
                <div class="label">Chưa đếm</div>
                <div class="value">{{ $thongKe['so_sp_chua_dem'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="kk-stat-card">
                <div class="label">Tổng giá trị lệch</div>
                <div class="value text-danger" style="font-size: 18px;">{{ number_format((float)$thongKe['tong_gia_tri_lech'], 0, ',', '.') }}đ</div>
            </div>
        </div>
    </div>

    <!-- Bảng chi tiết + filter -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control form-control-sm" style="max-width: 300px;" x-model="search" placeholder="Tìm theo tên, mã vạch, mã hàng...">
                <select class="form-select form-select-sm" style="max-width: 200px;" x-model="filter">
                    <option value="">Tất cả</option>
                    <option value="chua_dem">Chưa đếm</option>
                    <option value="thieu">Thiếu</option>
                    <option value="du">Đủ</option>
                    <option value="thua">Thừa</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th class="text-center">HSD gần nhất</th>
                            <th class="text-center">Tồn HT</th>
                            <th class="text-center">Thực tế</th>
                            <th class="text-center">Chênh lệch</th>
                            <th class="text-end">Giá trị lệch</th>
                            <th>Phân loại</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($phieu->chiTietKiemKho as $i => $ct)
                        @php
                            $searchKey = strtolower(trim(($ct->ten_san_pham ?? '') . ' ' . ($ct->ma_vach ?? '') . ' ' . ($ct->ma_hang ?? '')));
                        @endphp
                        <tr class="kk-row-{{ $ct->loai_chenh_lech }}"
                            x-show="(filter === '' || '{{ $ct->loai_chenh_lech }}' === filter) && (search === '' || @js($searchKey).includes(search.toLowerCase()))">
                            <td>{{ $i + 1 }}</td>
                            <td><code>{{ $ct->ma_hang ?? $ct->ma_vach ?? '#' . $ct->id }}</code></td>
                            <td>
                                <strong>{{ $ct->ten_san_pham }}</strong>
                                @if($ct->ten_bien_the || $ct->ten_don_vi)
                                    <br><small class="text-muted">{{ $ct->ten_bien_the ?? $ct->ten_don_vi }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $ct->han_su_dung_gan_nhat?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-center"><strong>{{ $ct->so_luong_he_thong }}</strong></td>
                            <td class="text-center">
                                @if($ct->so_luong_thuc_te !== null)
                                    <strong>{{ $ct->so_luong_thuc_te }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($ct->so_luong_lech != 0)
                                    <strong class="{{ $ct->so_luong_lech < 0 ? 'text-danger' : 'text-warning' }}">
                                        {{ $ct->so_luong_lech > 0 ? '+' : '' }}{{ $ct->so_luong_lech }}
                                    </strong>
                                @else
                                    0
                                @endif
                            </td>
                            <td class="text-end">{{ number_format((float)$ct->gia_tri_lech, 0, ',', '.') }}đ</td>
                            <td><span class="kk-status {{ $ct->loai_chenh_lech }}">{{ $ct->loai_chenh_lech_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function kiemKhoShow(id) {
    return {
        search: '',
        filter: '',
        async huyPhieu() {
            const lyDo = prompt('Lý do hủy (tối thiểu 5 ký tự):');
            if (!lyDo || lyDo.length < 5) {
                toastr.warning('Vui lòng nhập lý do hủy');
                return;
            }
            try {
                await axios.post(`/admin/api/kiem-kho/${id}/huy`, { ly_do: lyDo });
                toastr.success('Đã hủy phiếu');
                location.reload();
            } catch (e) {
                toastr.error(e.response?.data?.message || 'Có lỗi xảy ra');
            }
        }
    }
}
</script>
@endsection
