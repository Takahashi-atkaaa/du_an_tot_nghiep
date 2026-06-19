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
                <ul class="list-group" id="donViList">
                    @foreach($donVis as $donVi)
                        <li class="list-group-item d-flex justify-content-between align-items-center" id="donvi-item-{{ $donVi->id }}">
                            <div class="donvi-view">
                                <strong>{{ $donVi->ten_don_vi }}</strong>
                                <div class="text-muted">Số lượng: {{ $donVi->so_luong_san_pham_trong_don_vi }}</div>
                            </div>
                            <div class="donvi-edit d-none">
                                <form method="POST" action="{{ url('admin/cai-dat/san-pham/don-vi/'.$donVi->id) }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="ten_don_vi" class="form-control form-control-sm" value="{{ $donVi->ten_don_vi }}" required style="width:120px;">
                                    <input type="number" name="so_luong_san_pham_trong_don_vi" class="form-control form-control-sm" value="{{ $donVi->so_luong_san_pham_trong_don_vi }}" min="1" style="width:80px;">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditDonVi({{ $donVi->id }})"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                            <div class="donvi-actions">
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="toggleEditDonVi({{ $donVi->id }})"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="{{ url('admin/cai-dat/san-pham/don-vi/'.$donVi->id) }}" class="d-inline" onsubmit="return confirm('Xóa đơn vị này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
                    <div class="mb-2">
                        <label class="form-label">Loại</label>
                        <select name="loai" class="form-select" id="loaiThuocTinhSelect">
                            <option value="cha">Thuộc tính cha (VD: Màu sắc, Size)</option>
                            <option value="con">Thuộc tính con (VD: Đen, Trắng, M, L)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tên thuộc tính</label>
                        <input name="ten_thuoc_tinh" type="text" class="form-control" required>
                    </div>
                    <div class="mb-2" id="thuocTinhChaSelectWrapper" style="display:none;">
                        <label class="form-label">Thuộc tính cha</label>
                        <select name="thuoc_tinh_cha_id" class="form-select">
                            <option value="">-- Chọn thuộc tính cha --</option>
                            @foreach($thuocTinhChas as $tt)
                                <option value="{{ $tt->id }}">{{ $tt->ten_thuoc_tinh }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-footer bg-white p-0">
                        <button class="btn btn-primary mt-2" type="submit"><i class="fas fa-plus me-2"></i>Thêm</button>
                    </div>
                </form>

                <hr>
                <ul class="list-group" id="thuocTinhList">
                    @foreach($thuocTinhChas as $tt)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light fw-bold" id="tt-item-{{ $tt->id }}">
                            <div>
                                <i class="fas fa-layer-group me-1 text-primary"></i>{{ $tt->ten_thuoc_tinh }}
                                <span class="badge bg-primary ms-2">{{ $tt->thuocTinhCons->count() }}</span>
                            </div>
                            <div class="tt-actions">
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="toggleEditTt({{ $tt->id }})"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="{{ url('admin/cai-dat/san-pham/thuoc-tinh/'.$tt->id) }}" class="d-inline" onsubmit="return confirm('Xóa thuộc tính và các thuộc tính con?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </li>
                        @foreach($tt->thuocTinhCons as $con)
                            <li class="list-group-item d-flex justify-content-between align-items-center ps-5" id="tt-item-{{ $con->id }}">
                                <div class="tt-view">
                                    <span class="text-muted">└─</span> {{ $con->ten_thuoc_tinh }}
                                </div>
                                <div class="tt-edit d-none">
                                    <form method="POST" action="{{ url('admin/cai-dat/san-pham/thuoc-tinh/'.$con->id) }}" class="d-flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="ten_thuoc_tinh" class="form-control form-control-sm" value="{{ $con->ten_thuoc_tinh }}" required style="width:180px;">
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditTt({{ $con->id }})"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                                <div class="tt-actions">
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="toggleEditTt({{ $con->id }})"><i class="fas fa-edit"></i></button>
                                    <form method="POST" action="{{ url('admin/cai-dat/san-pham/thuoc-tinh/'.$con->id) }}" class="d-inline" onsubmit="return confirm('Xóa thuộc tính con này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('loaiThuocTinhSelect').addEventListener('change', function() {
        const wrapper = document.getElementById('thuocTinhChaSelectWrapper');
        wrapper.style.display = this.value === 'con' ? 'block' : 'none';
    });

    function toggleEditDonVi(id) {
        const item = document.getElementById('donvi-item-' + id);
        const viewEl = item.querySelector('.donvi-view');
        const editEl = item.querySelector('.donvi-edit');
        const actionsEl = item.querySelector('.donvi-actions');

        if (editEl.classList.contains('d-none')) {
            viewEl.classList.add('d-none');
            actionsEl.classList.add('d-none');
            editEl.classList.remove('d-none');
        } else {
            viewEl.classList.remove('d-none');
            actionsEl.classList.remove('d-none');
            editEl.classList.add('d-none');
        }
    }

    function toggleEditTt(id) {
        const item = document.getElementById('tt-item-' + id);
        const viewEl = item.querySelector('.tt-view');
        const editEl = item.querySelector('.tt-edit');
        const actionsEl = item.querySelector('.tt-actions');

        if (editEl.classList.contains('d-none')) {
            viewEl.classList.add('d-none');
            actionsEl.classList.add('d-none');
            editEl.classList.remove('d-none');
        } else {
            viewEl.classList.remove('d-none');
            actionsEl.classList.remove('d-none');
            editEl.classList.add('d-none');
        }
    }
</script>
@endpush
