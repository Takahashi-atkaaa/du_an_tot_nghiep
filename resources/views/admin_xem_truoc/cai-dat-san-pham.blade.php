@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Cài đặt sản phẩm - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Cài đặt sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/cai-dat') }}">Cài đặt</a></li>
                <li class="breadcrumb-item active">Cài đặt sản phẩm</li>
            </ol>
        </nav>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-balance-scale me-2"></i>Đơn vị tính</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('admin/cai-dat/san-pham/don-vi') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tên đơn vị</label>
                        <input name="ten_don_vi" type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số lượng trong đơn vị</label>
                        <input name="so_luong_san_pham_trong_don_vi" type="number" class="form-control" value="1" min="1">
                    </div>
                    <div class="card-footer bg-white p-0">
                        <button class="btn btn-primary mt-2" type="submit"><i class="fas fa-plus me-2"></i>Thêm</button>
                    </div>
                </form>

                <hr>
                <ul class="list-group">
                    @foreach($donVis as $donVi)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $donVi->ten_don_vi }}</strong>
                                <div class="text-muted">Số lượng: {{ $donVi->so_luong_san_pham_trong_don_vi }}</div>
                            </div>
                            <div>
                                <form method="POST" action="{{ url('admin/cai-dat/san-pham/don-vi/'.$donVi->id) }}" onsubmit="return confirm('Xóa đơn vị này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card table-admin">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Thuộc tính sản phẩm</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('admin/cai-dat/san-pham/thuoc-tinh') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tên thuộc tính</label>
                        <input name="ten_thuoc_tinh" type="text" class="form-control" required>
                    </div>
                    <div class="card-footer bg-white p-0">
                        <button class="btn btn-primary mt-2" type="submit"><i class="fas fa-plus me-2"></i>Thêm</button>
                    </div>
                </form>

                <hr>
                <ul class="list-group">
                    @foreach($thuocTinhs as $tt)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                {{ $tt->ten_thuoc_tinh }}
                            </div>
                            <div>
                                <form method="POST" action="{{ url('admin/cai-dat/san-pham/thuoc-tinh/'.$tt->id) }}" onsubmit="return confirm('Xóa thuộc tính này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
