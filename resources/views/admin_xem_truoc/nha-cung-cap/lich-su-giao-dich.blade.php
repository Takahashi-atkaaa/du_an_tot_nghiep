@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Lịch sử giao dịch nhà cung cấp')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Lịch sử giao dịch nhà cung cấp</h4>
        <p class="text-muted mb-0">
            Nhà cung cấp: <strong>{{ $nhaCungCap->ten_nha_cung_cap }}</strong>
        </p>
    </div>

    <a href="{{ url('/admin/kho-hang/nha-cung-cap') }}" class="btn btn-outline-secondary">
        Quay lại
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card p-3">
            <p class="text-muted mb-1">Tổng số phiếu nhập</p>
            <h3>{{ $tongSoPhieu }}</h3>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3">
            <p class="text-muted mb-1">Tổng giá trị hàng nhập</p>
            <h3>{{ number_format($tongGiaTri, 0, ',', '.') }} đ</h3>
        </div>
    </div>
</div>

<form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label">Từ ngày</label>
        <input type="date" name="tu_ngay" class="form-control" value="{{ request('tu_ngay') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Đến ngày</label>
        <input type="date" name="den_ngay" class="form-control" value="{{ request('den_ngay') }}">
    </div>

    <div class="col-md-4 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary">Lọc</button>
        <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="card table-admin">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID phiếu</th>
                        <th>Loại phiếu</th>
                        <th>Ngày tạo</th>
                        <th>Tổng số lượng</th>
                        <th>Tổng giá trị</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($phieuNhap as $phieu)
                        <tr>
                            <td>#{{ $phieu->id }}</td>
                            <td>{{ $phieu->loai_phieu }}</td>
                            <td>{{ $phieu->created_at ? \Carbon\Carbon::parse($phieu->created_at)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $phieu->tong_so_luong }}</td>
                            <td>{{ number_format($phieu->tong_gia_tri, 0, ',', '.') }} đ</td>
                            <td>{{ $phieu->ghi_chu ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                Chưa có lịch sử nhập hàng từ nhà cung cấp này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $phieuNhap->links() }}
        </div>
    </div>
</div>
@endsection