@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Quản lý Sản phẩm - SmartMart')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Sản phẩm</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active">Sản phẩm</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus me-2"></i>Thêm sản phẩm
    </button>
    <button class="btn btn-outline-secondary ms-2" id="startQrScanBtn">
        <i class="fas fa-barcode me-2"></i>Quét mã vạch
    </button>
    <a href="{{ url('admin/san-pham/trash') }}" class="btn btn-outline-danger ms-2">
        <i class="fas fa-trash me-2"></i>Thùng rác
    </a>
</div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- Filter & Search -->
<div class="card table-admin mb-4">
    <div class="card-body">
        <form action="{{ url('admin/san-pham') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchKeywordInput" class="form-control" name="keyword" value="{{ $keyword ?? '' }}" placeholder="Tìm kiếm sản phẩm...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="danh_muc">
                        <option value="">Tất cả danh mục</option>
                        @foreach($danhMucs as $danhMuc)
                            <option value="{{ $danhMuc->id }}" {{ (string) $danhMuc->id === (string) ($danhMucId ?? '') ? 'selected' : '' }}>{{ $danhMuc->ten_danh_muc }}</option>
                        @endforeach
                    </select>

                </div>
                <div class="col-md-3">
                    <select class="form-select" name="trang_thai">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ $trangThai === '1' || $trangThai === 1 ? 'selected' : '' }}>Đang bán</option>
                        <option value="0" {{ $trangThai === '0' || $trangThai === 0 ? 'selected' : '' }}>Ngừng bán</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-filter me-2"></i>Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quét mã vạch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrScanner" style="width:100%; min-height:400px;"></div>
                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-secondary" id="stopQrScanBtn">Dừng quét</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Bar -->
<form id="bulkActionForm" action="{{ url('admin/san-pham/bulk-action') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput">
    <div id="selectedIdsContainer"></div>
</form>

<!-- Products Table -->
<div class="card table-admin">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                <label class="form-check-label text-muted" for="selectAllCheckbox">Chọn tất cả</label>
            </div>
            <div id="bulkActionButtons" class="d-none">
                <span class="text-muted me-3" id="selectedCount">0 đã chọn</span>
                <button type="button" class="btn btn-sm btn-success" onclick="submitBulkAction('activate')"><i class="fas fa-check me-1"></i>Bật trạng thái</button>
                <button type="button" class="btn btn-sm btn-warning" onclick="submitBulkAction('deactivate')"><i class="fas fa-ban me-1"></i>Tắt trạng thái</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="submitBulkAction('delete')"><i class="fas fa-trash me-1"></i>Xóa</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Ảnh</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Đơn vị</th>
                        <th>Định mức</th>
                        <th>Giá bán</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th style="width: 180px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanPhams as $sanPham)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" value="{{ $sanPham->id }}">
                            </td>
                            <td>
                                @if($sanPham->hinh_anh)
                                    <img src="{{ asset($sanPham->hinh_anh) }}" class="rounded" alt="{{ $sanPham->ten_san_pham }}" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <img src="https://via.placeholder.com/60" class="rounded" alt="No image">
                                @endif
                            </td>
                            <td>{{ $sanPham->ma_hang ?? $sanPham->ma_vach }}</td>
                            <td>{{ $sanPham->ten_san_pham }}</td>
                            <td>{{ $sanPham->danhMuc->ten_danh_muc ?? '-' }}</td>
                            <td>{{ $sanPham->donVi->ten_don_vi ?? '-' }}</td>
                            <td>{{ $sanPham->dinh_muc_toi_thieu ?? 0 }}</td>
                            <td>{{ number_format($sanPham->gia_ban, 0, ',', '.') }} đ</td>
                            <td>{{ $sanPham->so_luong_ton_kho }}</td>
                            <td>
                                @if(! $sanPham->trang_thai)
                                    <span class="badge bg-danger">Ngừng bán</span>
                                @elseif($sanPham->so_luong_ton_kho <= 0)
                                    <span class="badge bg-secondary">Hết hàng</span>
                                @elseif($sanPham->so_luong_ton_kho <= ($sanPham->dinh_muc_toi_thieu ?? 0))
                                    <span class="badge bg-warning text-dark">Sắp hết hàng</span>
                                @else
                                    <span class="badge bg-success">Đang bán</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ url('admin/san-pham/'.$sanPham->id) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                                <a href="{{ url('admin/san-pham/'.$sanPham->id.'/edit') }}" class="btn btn-sm btn-outline-secondary ms-1">Sửa</a>
                                <form action="{{ url('admin/san-pham/'.$sanPham->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Hiện chưa có sản phẩm nào. Vui lòng thêm sản phẩm mới.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted">
                    Hiển thị {{ $sanPhams->firstItem() ?? 0 }} - {{ $sanPhams->lastItem() ?? 0 }} trên {{ $sanPhams->total() }} sản phẩm
                </span>
            </div>
            <nav>
                {{ $sanPhams->links('pagination::bootstrap-5') }}
            </nav>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('admin/san-pham') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã sản phẩm</label>
                            <input type="text" class="form-control" placeholder="Auto generate" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="ma_vach" class="form-control" placeholder="8934567890123" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="ten_san_pham" class="form-control" placeholder="Nhập tên sản phẩm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_danh_muc" required>
                                <option selected disabled>Chọn danh mục</option>
                                @foreach($danhMucs as $danhMuc)
                                    <option value="{{ $danhMuc->id }}">{{ $danhMuc->ten_danh_muc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thương hiệu</label>
                            <input type="text" name="thuong_hieu" class="form-control" placeholder="Nhập thương hiệu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Đơn vị cơ bản <span class="text-danger">*</span></label>
                            <input type="text" name="don_vi_co_ban" class="form-control" placeholder="Hộp" value="Hộp" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thuộc tính</label>
                            <select class="form-select" name="id_thuoc_tinh">
                                <option value="">Chọn thuộc tính</option>
                                @foreach($thuocTinhs as $thuocTinh)
                                    <option value="{{ $thuocTinh->id }}">{{ $thuocTinh->ten_thuoc_tinh }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giá vốn</label>
                            <div class="input-group">
                                <input type="number" name="gia_von" class="form-control" placeholder="0">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="gia_ban" class="form-control" placeholder="0" required>
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tồn kho</label>
                            <input type="number" name="so_luong_ton_kho" class="form-control" placeholder="0" min="0" value="{{ old('so_luong_ton_kho', 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Định mức tối thiểu</label>
                            <input type="number" name="dinh_muc_toi_thieu" class="form-control" placeholder="0" min="0" value="{{ old('dinh_muc_toi_thieu', 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="mo_ta" rows="3" placeholder="Mô tả sản phẩm...">{{ old('mo_ta') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Hình ảnh</label>
                            <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <div class="card border p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1">Hàng cùng loại</h6>
                                        <p class="text-muted mb-0">Thêm đơn vị bán khác để hệ thống tạo các sản phẩm cùng loại với mã hàng và mã vạch riêng.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" id="addVariantBtn">+ Thêm đơn vị</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="variantTable">
                                        <thead>
                                            <tr>
                                                <th>Đơn vị</th>
                                                <th>Quy đổi</th>
                                                <th>Giá bán</th>
                                                <th>Mã vạch</th>
                                                <th style="width: 100px;">Xóa</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu
                    </button>
                </div>
            </form>
            <template id="variantRowTemplate">
                <tr>
                    <td><input type="text" name="bien_the[__INDEX__][ten_bien_the]" class="form-control" placeholder="Thùng"></td>
                    <td><input type="number" name="bien_the[__INDEX__][so_luong_san_pham_trong_don_vi]" class="form-control" value="1" min="1"></td>
                    <td>
                        <div class="input-group">
                            <input type="number" name="bien_the[__INDEX__][gia_ban_bien]" class="form-control" placeholder="0">
                            <span class="input-group-text">đ</span>
                        </div>
                    </td>
                    <td><input type="text" name="bien_the[__INDEX__][ma_vach]" class="form-control" placeholder="Mã vạch"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-variant">Xóa</button>
                    </td>
                </tr>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addVariantBtn = document.getElementById('addVariantBtn');
        const variantBody = document.querySelector('#variantTable tbody');
        const template = document.getElementById('variantRowTemplate').innerHTML;

        addVariantBtn.addEventListener('click', function () {
            const index = variantBody.children.length;
            const rowHtml = template.replace(/__INDEX__/g, index);
            variantBody.insertAdjacentHTML('beforeend', rowHtml);
        });

        variantBody.addEventListener('click', function (event) {
            if (event.target.matches('.remove-variant')) {
                const row = event.target.closest('tr');
                row.remove();
                Array.from(variantBody.querySelectorAll('tr')).forEach((tr, idx) => {
                    tr.querySelectorAll('input').forEach(function (input) {
                        input.name = input.name.replace(/bien_the\[\d+\]/, 'bien_the[' + idx + ']');
                    });
                });
            }
        });
    });
</script>

@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.7/minified/html5-qrcode.min.js"></script>
@endpush

@section('page_scripts')
<script>
    // Bulk Actions
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const bulkActionButtons = document.getElementById('bulkActionButtons');
    const selectedCount = document.getElementById('selectedCount');
    const bulkActionForm = document.getElementById('bulkActionForm');
    const bulkActionInput = document.getElementById('bulkActionInput');
    const selectedIdsContainer = document.getElementById('selectedIdsContainer');

    function updateBulkUI() {
        const checked = Array.from(productCheckboxes).filter(cb => cb.checked);
        if (checked.length > 0) {
            bulkActionButtons.classList.remove('d-none');
            selectedCount.textContent = checked.length + ' đã chọn';
        } else {
            bulkActionButtons.classList.add('d-none');
        }
    }

    productCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkUI));

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            productCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkUI();
        });
    }

    function submitBulkAction(action) {
        const checked = Array.from(productCheckboxes).filter(cb => cb.checked);
        if (checked.length === 0) return;

        const messages = {
            delete: 'Bạn có chắc muốn xóa ' + checked.length + ' sản phẩm đã chọn?',
            activate: 'Bật trạng thái cho ' + checked.length + ' sản phẩm?',
            deactivate: 'Tắt trạng thái cho ' + checked.length + ' sản phẩm?',
        };

        if (!confirm(messages[action] || 'Xác nhận?')) return;

        selectedIdsContainer.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            selectedIdsContainer.appendChild(input);
        });
        bulkActionInput.value = action;
        bulkActionForm.submit();
    }
</script>

<script>
    // QR Scanner
    const startQrScanBtn = document.getElementById('startQrScanBtn');
    const stopQrScanBtn = document.getElementById('stopQrScanBtn');
    const qrScannerModal = document.getElementById('qrScannerModal');
    const searchKeywordInput = document.getElementById('searchKeywordInput');
    const qrScannerElementId = 'qrScanner';
    let html5QrCode = null;
    let qrScannerActive = false;

    function startQrScanner() {
        if (qrScannerActive) return;

        html5QrCode = new Html5Qrcode(qrScannerElementId);
        const config = { fps: 10, qrbox: 250 };

        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                const cameraId = cameras[0].id;
                html5QrCode.start(cameraId, config, qrCodeMessage => {
                    if (searchKeywordInput) {
                        searchKeywordInput.value = qrCodeMessage;
                    }
                    bootstrap.Modal.getInstance(qrScannerModal).hide();
                    stopQrScanner();
                    document.querySelector('form[action="{{ url('admin/san-pham') }}"]').submit();
                }, errorMessage => {
                    console.debug('QR scan error', errorMessage);
                }).then(() => {
                    qrScannerActive = true;
                }).catch(err => {
                    console.error('Không thể khởi động QR scanner', err);
                    alert('Không thể khởi động camera để quét mã vạch. Vui lòng kiểm tra quyền truy cập camera.');
                });
            } else {
                alert('Không tìm thấy camera phù hợp để quét mã vạch.');
            }
        }).catch(err => {
            console.error('Lỗi lấy camera', err);
            alert('Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập thiết bị.');
        });
    }

    function stopQrScanner() {
        if (!qrScannerActive || !html5QrCode) return;
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
            qrScannerActive = false;
        }).catch(err => {
            console.error('Lỗi dừng QR scanner', err);
        });
    }

    if (startQrScanBtn) {
        startQrScanBtn.addEventListener('click', function () {
            const modal = new bootstrap.Modal(qrScannerModal);
            modal.show();
            startQrScanner();
        });
    }

    if (stopQrScanBtn) {
        stopQrScanBtn.addEventListener('click', function () {
            const modal = bootstrap.Modal.getInstance(qrScannerModal);
            if (modal) modal.hide();
            stopQrScanner();
        });
    }

    if (qrScannerModal) {
        qrScannerModal.addEventListener('hidden.bs.modal', function () {
            stopQrScanner();
        });
    }
</script>
@endsection
