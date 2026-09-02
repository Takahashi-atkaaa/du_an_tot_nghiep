<!-- ===================== IMPORT MODAL (partial) ===================== -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#198754 0%,#157347 100%);color:white;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="importProductModalLabel">
                        <i class="fas fa-file-import me-2"></i>Nhập dữ liệu sản phẩm
                    </h5>
                    <small class="text-white-50">Từ file Excel (.xlsx, .xls) hoặc CSV (.csv)</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('san-pham.import') }}" method="POST" enctype="multipart/form-data" id="importProductForm">
                @csrf
                <input type="hidden" name="_action" value="import">
                <div class="modal-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h5 class="mb-1"><i class="fas fa-file-excel text-success me-2"></i>Chọn file Excel để import</h5>
                            <p class="text-muted small mb-0">Hỗ trợ .xlsx, .xls và .csv; tối đa 1000 dòng, 5MB.</p>
                        </div>
                        <a href="{{ route('san-pham.export-template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-1"></i>Tải file mẫu
                        </a>
                    </div>

                    <div class="alert alert-info py-2 small">
                        <div class="fw-semibold mb-1"><i class="fas fa-circle-info me-1"></i>Quy ước dữ liệu</div>
                        <ul class="mb-0 ps-3">
                            <li>Bắt buộc: Tên sản phẩm, Danh mục và Giá bán.</li>
                            <li>Mã hàng/mã vạch để trống sẽ được tự sinh; giữ hai cột này ở dạng Text.</li>
                            <li>Giá vốn và Tồn kho chỉ để tham khảo khi tạo sản phẩm, không tạo giao dịch kho.</li>
                            <li>Đơn vị trống sẽ dùng <strong>Cái</strong>. URL ảnh chỉ nhận <code>http://</code> hoặc <code>https://</code>.</li>
                            <li>Dòng <code>unit</code> cần Mã biến thể cha và Tỷ lệ quy đổi lớn hơn 1.</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label for="importFileInput" class="form-label fw-semibold">Chọn file Excel/CSV</label>
                        <input type="file" class="form-control" id="importFileInput" name="excel_file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                        <div class="form-text">Dung lượng tối đa 5MB; chỉ đọc sheet đầu tiên.</div>
                    </div>

                    <!-- Preview section -->
                    <div id="importPreviewSection" class="d-none">
                        <hr>
                        <h6 class="mb-2"><i class="fas fa-eye me-1"></i>Xem trước dữ liệu (5 dòng đầu tiên)</h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-bordered table-hover mb-0" id="importPreviewTable">
                                <thead class="table-light"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="small text-muted mt-1">Chỉ hiển thị tối đa 5 dòng đầu để kiểm tra nhanh.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success" id="btnImportSubmit">
                        <i class="fas fa-upload me-1"></i>Import sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
