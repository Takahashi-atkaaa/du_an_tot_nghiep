@extends('admin_xem_truoc.layouts.admin')
@section('title', 'Danh sách phiếu kiểm kho')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/kiem-kho.css') }}">
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="kk-page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-clipboard-check"></i> Quản lý kiểm kho</h2>
            <div class="subtitle">Theo dõi và quản lý các phiếu kiểm kho hàng hóa</div>
        </div>
        <div>
            @if(userHasPermission('kiem_kho_tao'))
                <a href="{{ route('kiem-kho.create') }}" class="btn btn-light">
                    <i class="fas fa-plus"></i> Tạo phiếu mới
                </a>
            @endif
            <a href="{{ route('kiem-kho.bao-cao') }}" class="btn btn-outline-light">
                <i class="fas fa-chart-bar"></i> Báo cáo
            </a>
            @if(userHasPermission('kiem_kho_huy'))
                <a href="{{ route('kiem-kho.trash') }}" class="btn btn-outline-light">
                    <i class="fas fa-trash"></i> Thùng rác
                </a>
            @endif
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="kk-filter-panel mb-3" x-data="{ open: true }">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Bộ lọc</h5>
            <button type="button" class="btn btn-sm btn-link" @click="open = !open">
                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
        </div>
        <form method="GET" action="{{ route('kiem-kho.index') }}" x-show="open">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Mã phiếu</label>
                    <input type="text" name="ma_phieu" class="form-control" value="{{ request('ma_phieu') }}" placeholder="KK00001...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($dsTrangThai as $key => $label)
                            <option value="{{ $key }}" {{ request('trang_thai') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Người kiểm</label>
                    <select name="id_nguoi_kiem" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($dsNguoiKiem as $nd)
                            <option value="{{ $nd->id }}" {{ request('id_nguoi_kiem') == $nd->id ? 'selected' : '' }}>{{ $nd->ho_ten }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="tu_ngay" class="form-control" value="{{ request('tu_ngay') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="den_ngay" class="form-control" value="{{ request('den_ngay') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>

    <!-- Danh sách -->
    <div class="kk-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Ngày kiểm</th>
                    <th>Người kiểm</th>
                    <th>Phạm vi</th>
                    <th class="text-center">SP</th>
                    <th class="text-end">Tổng lệch</th>
                    <th class="text-end">Giá trị lệch</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($phieus as $p)
                <tr>
                    <td><strong>{{ $p->ma_kiem_kho }}</strong></td>
                    <td>{{ $p->ngay_kiem?->format('d/m/Y') }}</td>
                    <td>{{ $p->nguoiKiem?->ho_ten ?? '-' }}</td>
                    <td>{{ $p->pham_vi_label }}</td>
                    <td class="text-center">{{ $p->tong_so_san_pham }}</td>
                    <td class="text-end">
                        @if($p->tong_sl_lech > 0)
                            <span class="text-warning">+{{ $p->tong_sl_lech }}</span>
                        @elseif($p->tong_sl_lech < 0)
                            <span class="text-danger">{{ $p->tong_sl_lech }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format((float)$p->tong_gia_tri_lech, 0, ',', '.') }}đ</td>
                    <td><span class="kk-status {{ $p->trang_thai }}">{{ $p->trang_thai_label }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('kiem-kho.show', $p->id) }}" class="btn btn-sm btn-outline-primary" title="Xem">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($p->co_the_dem && userHasPermission('kiem_kho_dem'))
                            <a href="{{ route('kiem-kho.dem', $p->id) }}" class="btn btn-sm btn-outline-info" title="Kiểm đếm">
                                <i class="fas fa-clipboard-check"></i>
                            </a>
                        @endif
                        <a href="{{ route('kiem-kho.print', $p->id) }}" class="btn btn-sm btn-outline-secondary" title="In">
                            <i class="fas fa-print"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        Chưa có phiếu kiểm kho nào.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $phieus->links() }}
    </div>
</div>
@endsection

@section('scripts')
@endsection
