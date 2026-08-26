@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý hàng lỗi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Quản lý hàng lỗi</h4>
            <div class="text-muted">Hàng lỗi được ghi nhận riêng, không nhập lại tồn bán được và chờ tiêu hủy.</div>
        </div>
    </div>

    @if(isset($migrationMessage) && $migrationMessage)
        <div class="alert alert-warning">
            {{ $migrationMessage }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="cho_tieu_huy" @selected(request('trang_thai') === 'cho_tieu_huy')>Chờ tiêu hủy</option>
                        <option value="da_tieu_huy" @selected(request('trang_thai') === 'da_tieu_huy')>Đã tiêu hủy</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.hang-loi.index') }}" class="btn btn-outline-secondary">Làm mới</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Hóa đơn</th>
                            <th>Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th>Loại phát sinh</th>
                            <th>Lý do</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hangLois as $hangLoi)
                            <tr>
                                <td>{{ $hangLoi->id }}</td>
                                <td>
                                    <div class="fw-semibold">#HD{{ str_pad($hangLoi->doiTra?->id_hoa_don ?? 0, 4, '0', STR_PAD_LEFT) }}</div>
                                    <div class="small text-muted">Đổi/trả #{{ $hangLoi->doiTra?->id }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $hangLoi->ten_san_pham_bien_the_hien_thi }}</div>
                                    <div class="small text-muted">Người xử lý: {{ $hangLoi->nguoi_xu_ly_doi_tra_hien_thi }}</div>
                                </td>
                                <td class="text-center">{{ $hangLoi->so_luong }}</td>
                                <td>{{ $hangLoi->doiTra?->Loai === 'doi_tra' ? 'Đổi hàng lỗi' : 'Trả hàng lỗi' }}</td>
                                <td>{{ $hangLoi->ly_do ?: 'Không có' }}</td>
                                <td>
                                    @if($hangLoi->trang_thai === 'cho_tieu_huy')
                                        <span class="badge bg-warning text-dark">Chờ tiêu hủy</span>
                                    @else
                                        <span class="badge bg-success">Đã tiêu hủy</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">Ghi nhận: {{ optional($hangLoi->created_at)->format('d/m/Y H:i') }}</div>
                                    <div class="small text-muted">
                                        Tiêu hủy: {{ $hangLoi->ngay_tieu_huy ? $hangLoi->ngay_tieu_huy->format('d/m/Y H:i') : 'Chưa xác nhận' }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($hangLoi->trang_thai === 'cho_tieu_huy')
                                        <form action="{{ route('admin.hang-loi.xac-nhan-tieu-huy', $hangLoi->id) }}" method="POST" onsubmit="return confirm('Xác nhận tiêu hủy hàng lỗi này?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Xác nhận tiêu hủy</button>
                                        </form>
                                    @else
                                        <div class="small text-muted">{{ $hangLoi->nguoiDungTieuHuy?->ho_ten ?? 'Đã xác nhận' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Chưa có hàng lỗi nào được ghi nhận.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $hangLois->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
