@extends('nhan_vien_view.layouts.nhan_vien')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-primary">
                    <i class="fas fa-users"></i> Danh sách khách hàng
                </h3>
                <small class="text-muted">
                    Quản lý thông tin khách hàng và điểm tích lũy
                </small>
            </div>

            <a href="{{ route('nhan-vien.khach-hang.create') }}"
               class="btn btn-success px-4">
                <i class="fas fa-plus-circle me-1"></i>
                Thêm khách hàng
            </a>
        </div>
    </div>

    {{-- Search --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text"
                               name="keyword"
                               class="form-control form-control-lg"
                               value="{{ $keyword }}"
                               placeholder="🔍 Nhập tên khách hàng, số điện thoại hoặc email...">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search"></i>
                            Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-primary">
                        <tr>
                            <th>Khách hàng</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th>Điểm tích lũy</th>
                            <th>Tổng chi tiêu</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($khachHangs as $kh)
                        <tr>

                            <td>
                                <div class="fw-semibold">
                                    {{ $kh->ten_khach_hang }}
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-info">
                                    {{ $kh->so_dien_thoai }}
                                </span>
                            </td>

                            <td>
                                {{ $kh->email }}
                            </td>

                            <td>
                                <span class="badge bg-warning text-dark px-3 py-2">
                                    {{ $kh->diem_tich_luy }} điểm
                                </span>
                            </td>

                            <td>
                                <span class="fw-bold text-success">
                                    {{ number_format($kh->tong_chi_tieu) }} đ
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('nhan-vien.khach-hang.show',$kh) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                    Xem
                                </a>
                            </td>

                        </tr>
                    @empty

                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486740.png"
                                     width="80"
                                     class="mb-3">

                                <h5>Không có dữ liệu</h5>

                                <p class="text-muted mb-0">
                                    Chưa tìm thấy khách hàng nào
                                </p>
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
        {{ $khachHangs->links() }}
    </div>

</div>

@endsection