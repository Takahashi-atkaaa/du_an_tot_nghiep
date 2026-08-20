@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Import Phiếu xuất - SmartMart')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-file-import me-2 text-danger"></i>Import Phiếu xuất từ Excel</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang') }}">Kho hàng</a></li>
                <li class="breadcrumb-item"><a href="{{ url('admin/kho-hang/phieu-xuat') }}">Phiếu xuất</a></li>
                <li class="breadcrumb-item active">Import Excel</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
</div>

<div id="alert-container-import-xuat"></div>

<div class="row">
    <div class="col-lg-8">
        <div class="card table-admin mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-upload me-2 text-danger"></i>Thông tin phiếu xuất</h5>
            </div>
            <div class="card-body">
                <form id="form-import-phieu-xuat">
                    <div class="alert alert-warning mb-3 py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Hệ thống sẽ tự động trừ kho theo nguyên tắc <strong>FEFO</strong> — ưu tiên lô có HSD gần nhất.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Loại xuất <span class="text-danger">*</span></label>
                            <select name="loai_xuat" class="form-select" id="import-loai-xuat" required>
                                <option value="tieu_huy">Tiêu hủy</option>
                                <option value="tra_hang_nha_cung_cap">Trả hàng NCC</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nhà cung cấp</label>
                            <select name="id_nha_cung_cap" id="import-xuat-id-ncc" class="form-select">
                                <option value="">-- Chọn NCC --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lý do</label>
                            <input type="text" name="ly_do" id="import-ly-do" class="form-control" placeholder="Lý do xuất hàng...">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="ghi_chu" id="import-xuat-ghi-chu" class="form-control" placeholder="Ghi chú...">
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" id="import-file-xuat" accept=".xlsx,.xls,.csv" class="form-control" required>
                        <small class="text-muted">Hỗ trợ định dạng .xlsx, .xls, .csv</small>
                    </div>

                    <div class="drop-zone-xuat border rounded p-4 text-center" id="drop-zone-xuat" style="border-style: dashed; background: #f8f9fa; cursor:pointer;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                        <p class="mb-0 text-muted">Kéo thả file Excel vào đây</p>
                        <small class="text-muted">hoặc nhấn "Chọn file" ở trên</small>
                    </div>

                    <div id="import-file-preview-xuat" class="mt-2 d-none">
                        <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                            <i class="fas fa-file-excel me-2 text-success"></i>
                            <span id="import-file-name-xuat"></span>
                            <button type="button" class="btn-close ms-auto" onclick="xoaFileImportXuat()"></button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ url('admin/kho-hang/phieu-xuat') }}" class="btn btn-secondary">Hủy</a>
                        <button type="button" class="btn btn-outline-primary" id="btn-download-template-xuat">
                            <i class="fas fa-download me-1"></i>Tải file mẫu
                        </button>
                        <button type="submit" class="btn btn-danger" id="btn-submit-import-xuat" disabled>
                            <i class="fas fa-upload me-1"></i>Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Hướng dẫn --}}
        <div class="card table-admin mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Hướng dẫn</h6>
            </div>
            <div class="card-body small">
                <p class="mb-2"><strong>Cấu trúc file Excel/CSV:</strong></p>
                <table class="table table-sm table-bordered mb-3">
                    <thead class="table-light">
                        <tr><th>Ma_vach</th><th>So_luong</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>SP001</code></td><td><code>5</code></td></tr>
                    </tbody>
                </table>
                <ul class="mb-0 ps-3">
                    <li><strong>Ma_vach:</strong> Mã vạch sản phẩm (bắt buộc)</li>
                    <li><strong>So_luong:</strong> Số lượng xuất, số nguyên > 0 (bắt buộc)</li>
                    <li>Hệ thống tự động trừ kho theo <strong>FEFO</strong></li>
                    <li>Dòng bắt đầu bằng <code>#</code> sẽ bị bỏ qua (dòng chú thích)</li>
                    <li>Dòng đầu tiên nếu là tên cột sẽ tự động bỏ qua</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let importFileXuat = null;

$(function () {
    taiNhaCungCapXuat();

    $('#btn-download-template-xuat').click(function () {
        window.open('/admin/api/phieu-xuat/download-template', '_blank');
    });

    $('#import-file-xuat').change(function () {
        const file = this.files[0];
        if (file) {
            importFileXuat = file;
            $('#import-file-name-xuat').text(file.name);
            $('#import-file-preview-xuat').removeClass('d-none');
            $('#btn-submit-import-xuat').prop('disabled', false);
        }
    });

    $('#drop-zone-xuat').click(function () {
        $('#import-file-xuat').click();
    });

    $('#drop-zone-xuat').on('dragover', function (e) {
        e.preventDefault();
        $(this).css('background-color', '#fff5f5');
    });

    $('#drop-zone-xuat').on('dragleave', function () {
        $(this).css('background-color', '#f8f9fa');
    });

    $('#drop-zone-xuat').on('drop', function (e) {
        e.preventDefault();
        $(this).css('background-color', '#f8f9fa');
        const file = e.originalEvent.dataTransfer.files[0];
        if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
            importFileXuat = file;
            $('#import-file-xuat')[0].files = e.originalEvent.dataTransfer.files;
            $('#import-file-name-xuat').text(file.name);
            $('#import-file-preview-xuat').removeClass('d-none');
            $('#btn-submit-import-xuat').prop('disabled', false);
        } else {
            hienThongBaoImportXuat('danger', 'Vui lòng chọn file Excel (.xlsx, .xls, .csv)');
        }
    });

    $('#form-import-phieu-xuat').submit(function (e) {
        e.preventDefault();
        if (!importFileXuat) {
            hienThongBaoImportXuat('danger', 'Vui lòng chọn file Excel.');
            return;
        }

        const formData = new FormData();
        formData.append('file', importFileXuat);
        formData.append('loai_xuat', $('#import-loai-xuat').val());
        formData.append('id_nha_cung_cap', $('#import-xuat-id-ncc').val() || '');
        formData.append('ly_do', $('#import-ly-do').val() || '');
        formData.append('ghi_chu', $('#import-xuat-ghi-chu').val() || '');

        $('#btn-submit-import-xuat').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang import...');

        $.ajax({
            url: '/admin/api/phieu-xuat/import',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    hienThongBaoImportXuat('success', res.message);
                    importFileXuat = null;
                    $('#form-import-phieu-xuat')[0].reset();
                    $('#import-file-preview-xuat').addClass('d-none');
                    setTimeout(() => {
                        window.location.href = '/admin/kho-hang/phieu-xuat';
                    }, 1500);
                } else {
                    hienThongBaoImportXuat('danger', res.message);
                    if (res.errors && res.errors.length) {
                        console.error('Import errors:', res.errors);
                    }
                }
            },
            error: function (x) {
                const msg = x.responseJSON?.message || 'Import thất bại.';
                hienThongBaoImportXuat('danger', msg);
            },
            complete: function () {
                $('#btn-submit-import-xuat').prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Import');
            }
        });
    });
});

function taiNhaCungCapXuat() {
    $.get('/admin/api/lo-hang/nha-cung-cap', res => {
        const list = Array.isArray(res) ? res : (res?.data || []);
        const opts = list.map(n => `<option value="${n.id}">${n.ten_nha_cung_cap}</option>`).join('');
        $('#import-xuat-id-ncc').html('<option value="">-- Chọn NCC --</option>' + opts);
    });
}

function hienThongBaoImportXuat(type, message) {
    $('#alert-container-import-xuat').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    setTimeout(() => $('.alert').fadeOut(), 5000);
}

function xoaFileImportXuat() {
    importFileXuat = null;
    $('#import-file-xuat').val('');
    $('#import-file-preview-xuat').addClass('d-none');
    $('#btn-submit-import-xuat').prop('disabled', true);
}
</script>
@endsection
