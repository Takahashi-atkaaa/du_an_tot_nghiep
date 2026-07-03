@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Chi tiết sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Chi tiết sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/san-pham') }}">Sản phẩm</a></li>
                <li class="breadcrumb-item active">{{ $product->ten_san_pham }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/san-pham/edit/' . $product->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Chỉnh sửa
        </a>
        <a href="{{ url('admin/san-pham') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Thông tin sản phẩm</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    @if($product->variants->count() > 0 && $product->variants->first()->hinh_anh)
                        <img src="{{ asset($product->variants->first()->hinh_anh) }}"
                             alt="{{ $product->ten_san_pham }}"
                             class="rounded-3 shadow-sm"
                             style="width: 100%; max-height: 260px; object-fit: cover;">
                    @else
                        <div class="rounded-3 shadow-sm d-flex align-items-center justify-content-center bg-light"
                             style="width: 100%; height: 200px;">
                            <i class="fas fa-image fa-4x text-muted"></i>
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted text-uppercase fw-semibold">Tên sản phẩm</label>
                    <p class="mb-0 fw-semibold fs-6">{{ $product->ten_san_pham }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted text-uppercase fw-semibold">Danh mục</label>
                    <p class="mb-0">
                        @if($product->danhMuc)
                            <span class="badge bg-info">{{ $product->danhMuc->ten_danh_muc }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted text-uppercase fw-semibold">Thương hiệu</label>
                    <p class="mb-0">{{ $product->thuong_hieu ?: '<span class="text-muted">-</span>' }}</p>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted text-uppercase fw-semibold">Trạng thái</label>
                    <p class="mb-0">
                        @if($product->trang_thai)
                            <span class="badge bg-success">Đang bán</span>
                        @else
                            <span class="badge bg-secondary">Ngừng bán</span>
                        @endif
                    </p>
                </div>

                <div class="mb-0">
                    <label class="form-label small text-muted text-uppercase fw-semibold">Mô tả</label>
                    <p class="mb-0 text-muted small">{{ $product->mo_ta ?: 'Không có mô tả' }}</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-cubes me-2 text-success"></i>Tổng quan kho</h5>
            </div>
            <div class="card-body p-0">
                <div class="row g-0 text-center">
                    <div class="col-6 border-end border-bottom py-3">
                        <div class="text-primary fw-bold fs-4">{{ $product->variants->count() }}</div>
                        <div class="small text-muted text-uppercase">Biến thể</div>
                    </div>
                    <div class="col-6 border-bottom py-3">
                        @php
                            $tongTon = $product->variants->sum('so_luong_ton');
                        @endphp
                        <div class="text-success fw-bold fs-4">{{ number_format($tongTon) }}</div>
                        <div class="small text-muted text-uppercase">Tổng tồn</div>
                    </div>
                    <div class="col-6 border-end py-3">
                        <div class="text-warning fw-bold fs-4">
                            {{ number_format($product->variants->sum(function($v){ return $v->gia_von * $v->so_luong_ton; }), 0, ',', '.') }}
                        </div>
                        <div class="small text-muted text-uppercase">Tổng vốn</div>
                    </div>
                    <div class="col-6 py-3">
                        <div class="text-danger fw-bold fs-4">
                            {{ number_format($product->variants->sum(function($v){ return $v->gia_ban * $v->so_luong_ton; }), 0, ',', '.') }}
                        </div>
                        <div class="small text-muted text-uppercase">Tổng giá trị</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2 text-danger"></i>Danh sách biến thể</h5>
                <a href="{{ url('admin/san-pham/edit/' . $product->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i>Thêm biến thể
                </a>
            </div>
            <div class="card-body p-0">
                @if($product->variants->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase small fw-semibold" style="width:40px;">#</th>
                                    <th class="text-uppercase small fw-semibold">Biến thể</th>
                                    <th class="text-uppercase small fw-semibold text-center">Mã vạch</th>
                                    <th class="text-uppercase small fw-semibold text-end">Giá vốn</th>
                                    <th class="text-uppercase small fw-semibold text-end">Giá bán</th>
                                    <th class="text-uppercase small fw-semibold text-center">Tồn kho</th>
                                    <th class="text-uppercase small fw-semibold">Thuộc tính</th>
                                    <th class="text-uppercase small fw-semibold text-center">Đơn vị</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->variants as $index => $variant)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($variant->hinh_anh)
                                                    <img src="{{ asset($variant->hinh_anh) }}"
                                                         alt="{{ $variant->ten_bien_the }}"
                                                         class="rounded"
                                                         style="width:40px;height:40px;object-fit:cover;">
                                                @else
                                                    <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                                         style="width:40px;height:40px;">
                                                        <i class="fas fa-box text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="fw-semibold small">{{ $variant->ten_bien_the ?: 'Mặc định' }}</span>
                                                    @if($variant->ma_hang)
                                                        <div class="text-muted" style="font-size:0.72rem;">MH: {{ $variant->ma_hang }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="small text-muted font-monospace">{{ $variant->ma_vach ?: '-' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-semibold text-secondary">{{ number_format((float)$variant->gia_von, 0, ',', '.') }} đ</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-primary">{{ number_format((float)$variant->gia_ban, 0, ',', '.') }} đ</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $dinhMuc = $variant->dinh_muc_toi_thieu ?? 0;
                                                $tonKho = $variant->so_luong_ton ?? 0;
                                            @endphp
                                            @if($tonKho <= 0)
                                                <span class="badge bg-secondary">Hết</span>
                                            @elseif($tonKho <= $dinhMuc)
                                                <span class="badge bg-warning text-dark">Sắp hết</span>
                                            @else
                                                <span class="badge bg-success">Còn</span>
                                            @endif
                                            <span class="d-block small text-muted mt-1">{{ number_format($tonKho) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $thuocTinhIds = $variant->thuoc_tinh_ids ?? [];
                                                $thuocTinhs = \App\Models\ThuocTinhSanPham::whereIn('id', $thuocTinhIds)->get();
                                            @endphp
                                            @if($thuocTinhs->count() > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($thuocTinhs as $tt)
                                                        <span class="badge bg-light text-dark border" style="font-size:0.68rem;">
                                                            {{ $tt->ten_thuoc_tinh }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($variant->units->count() > 0)
                                                <button class="btn btn-sm btn-outline-secondary"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#units-{{ $variant->id }}"
                                                        aria-expanded="false">
                                                    <i class="fas fa-list-ul me-1"></i>{{ $variant->units->count() }}
                                                </button>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($variant->units->count() > 0)
                                        <tr class="bg-light">
                                            <td colspan="8" class="p-0 border-0">
                                                <div class="collapse" id="units-{{ $variant->id }}">
                                                    <div class="p-3">
                                                        <div class="small text-muted fw-semibold text-uppercase mb-2">
                                                            <i class="fas fa-exchange-alt me-1"></i>Đơn vị quy đổi của "{{ $variant->ten_bien_the }}"
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th class="text-uppercase small">Đơn vị</th>
                                                                        <th class="text-uppercase small text-center">Tỷ lệ QĐ</th>
                                                                        <th class="text-uppercase small text-end">Giá vốn QĐ</th>
                                                                        <th class="text-uppercase small text-end">Giá bán QĐ</th>
                                                                        <th class="text-uppercase small">Mã vạch</th>
                                                                        <th class="text-uppercase small text-center">Mặc định</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($variant->units as $unit)
                                                                        <tr>
                                                                            <td>
                                                                                <span class="fw-semibold">{{ $unit->ten_don_vi }}</span>
                                                                                @if($unit->ma_hang)
                                                                                    <span class="text-muted ms-1" style="font-size:0.72rem;">({{ $unit->ma_hang }})</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span class="badge bg-secondary">1 : {{ $unit->ty_le_quy_doi }}</span>
                                                                            </td>
                                                                            <td class="text-end text-muted">
                                                                                {{ number_format((float)$unit->gia_von_quy_doi, 0, ',', '.') }} đ
                                                                            </td>
                                                                            <td class="text-end fw-semibold text-primary">
                                                                                {{ number_format((float)$unit->gia_ban_quy_doi, 0, ',', '.') }} đ
                                                                            </td>
                                                                            <td>
                                                                                <span class="small font-monospace text-muted">{{ $unit->ma_vach ?: '-' }}</span>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                @if($unit->la_don_vi_mac_dinh)
                                                                                    <i class="fas fa-check-circle text-success"></i>
                                                                                @else
                                                                                    <i class="far fa-circle text-muted"></i>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-layer-group fa-3x mb-3"></i>
                        <p class="mb-0">Chưa có biến thể nào.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-warning"></i>Lịch sử tồn kho</h5>
            </div>
            <div class="card-body p-0">
                @if(count($theKho) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase small fw-semibold">Mã phiếu</th>
                                    <th class="text-uppercase small fw-semibold">Loại</th>
                                    <th class="text-uppercase small fw-semibold">Nhà cung cấp</th>
                                    <th class="text-uppercase small fw-semibold text-end">Số lượng</th>
                                    <th class="text-uppercase small fw-semibold text-end">Giá nhập</th>
                                    <th class="text-uppercase small fw-semibold">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($theKho as $item)
                                    <tr>
                                        <td>
                                            <span class="font-monospace small">{{ $item->ma_phieu }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $loaiPhieu = $item->loai_phieu ?? '';
                                                $loaiLabel = match(true) {
                                                    str_contains(strtolower($loaiPhieu), 'nhap') => '<span class="badge bg-success">Nhập</span>',
                                                    str_contains(strtolower($loaiPhieu), 'xuat') => '<span class="badge bg-danger">Xuất</span>',
                                                    str_contains(strtolower($loaiPhieu), 'tra') => '<span class="badge bg-warning text-dark">Trả</span>',
                                                    default => '<span class="badge bg-secondary">'.$loaiPhieu.'</span>'
                                                };
                                            @endphp
                                            {!! $loaiLabel !!}
                                        </td>
                                        <td>
                                            <span class="small text-muted">{{ $item->nha_cung_cap ?: '-' }}</span>
                                        </td>
                                        <td class="text-end fw-semibold {{ ($item->so_luong ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $item->so_luong > 0 ? '+' : '' }}{{ number_format($item->so_luong) }}
                                        </td>
                                        <td class="text-end text-muted small">
                                            {{ number_format((float)($item->gia ?? 0), 0, ',', '.') }} đ
                                        </td>
                                        <td>
                                            <span class="small text-muted">
                                                {{ \Carbon\Carbon::parse($item->thoi_gian)->format('d/m/Y H:i') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p class="mb-0">Chưa có lịch sử tồn kho.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-boxes-stacked me-2 text-info"></i>Thông tin lô hàng</h5>
            </div>
            <div class="card-body p-0">
                @if(count($loHang) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase small fw-semibold">Mã lô</th>
                                    <th class="text-uppercase small fw-semibold text-end">Số lượng còn</th>
                                    <th class="text-uppercase small fw-semibold">Hạn sử dụng</th>
                                    <th class="text-uppercase small fw-semibold">Trạng thái HSD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loHang as $lo)
                                    @php
                                        $hanSuDung = $lo->han_su_dung ? \Carbon\Carbon::parse($lo->han_su_dung) : null;
                                        $now = \Carbon\Carbon::now();
                                        $daysLeft = $hanSuDung ? $now->diffInDays($hanSuDung, false) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="font-monospace small fw-semibold">{{ $lo->ma_lo ?: '-' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ ($lo->so_luong ?? 0) <= 0 ? 'text-secondary' : 'text-success' }}">
                                                {{ number_format((float)($lo->so_luong ?? 0)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($hanSuDung)
                                                <span class="small">{{ $hanSuDung->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$hanSuDung)
                                                <span class="badge bg-secondary">Không có HSD</span>
                                            @elseif($daysLeft < 0)
                                                <span class="badge bg-danger">Đã hết hạn</span>
                                            @elseif($daysLeft <= 30)
                                                <span class="badge bg-warning text-dark">Còn {{ $daysLeft }} ngày</span>
                                            @else
                                                <span class="badge bg-success">Còn {{ $daysLeft }} ngày</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-boxes-stacked fa-3x mb-3"></i>
                        <p class="mb-0">Chưa có thông tin lô hàng.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/san-pham.css') }}">
@endsection
