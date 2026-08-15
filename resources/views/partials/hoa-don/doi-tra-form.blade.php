@php
use Illuminate\Support\Str;

$currentRoute = request()->route()?->getName() ?? '';
$isAdmin = Str::startsWith($currentRoute, 'admin.');
$actionRoute = $isAdmin ? route('admin.hoa-don.xu-ly-doi-tra', $hoaDon->id) : route('nhan-vien.hoa-don.xu-ly-doi-tra', $hoaDon->id);
$showRoute = $isAdmin ? route('admin.hoa-don.show', $hoaDon->id) : route('nhan-vien.hoa-don.chi-tiet', $hoaDon->id);
$requestToken = old('request_token', (string) Str::uuid());
$nguoiBanMacDinh = old(
    'id_nguoi_dung',
    optional($danhSachNguoiBan->firstWhere('id', auth()->id()))->id
        ?? optional($danhSachNguoiBan->firstWhere('id', $hoaDon->id_nguoi_dung ?? null))->id
);
@endphp

<form action="{{ $actionRoute }}" method="POST" id="doiTraForm">
    @csrf
    <input type="hidden" name="request_token" value="{{ $requestToken }}">

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="id_nguoi_dung" class="form-label fw-bold">Người bán</label>
                    <select name="id_nguoi_dung" id="id_nguoi_dung" class="form-select @error('id_nguoi_dung') is-invalid @enderror" required>
                        <option value="">Chọn người bán phụ trách</option>
                        @foreach($danhSachNguoiBan as $nguoiBan)
                            <option value="{{ $nguoiBan->id }}" @selected((string) $nguoiBanMacDinh === (string) $nguoiBan->id)>
                                {{ $nguoiBan->ho_ten_kem_vai_tro }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_nguoi_dung')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-warning text-dark fw-bold">
            <i class="fas fa-undo me-2"></i> Xử lý đổi/trả theo hóa đơn gốc
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">Đã mua</th>
                            <th class="text-center">Đã trả/đổi</th>
                            <th class="text-center">Còn xử lý</th>
                            <th class="text-end">Giá gốc</th>
                            <th width="500">Nghiệp vụ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chiTiet as $index => $item)
                            @if(($item->so_luong_con_lai ?? 0) > 0)
                                @php
                                    $replacement = ($item->replacement_options ?? collect())->first();
                                    $exchangeVariantId = $replacement->id ?? $item->id_bien_the_san_pham;
                                    $exchangeStock = (int) ($replacement->so_luong_ton ?? $item->so_luong_ton ?? 0);
                                @endphp
                                <tr class="return-item-row"
                                    data-price="{{ $item->gia_ban }}"
                                    data-max="{{ $item->so_luong_con_lai }}"
                                    data-index="{{ $index }}">
                                    <td>
                                        <div class="fw-bold">{{ $item->ten_hien_thi_san_pham ?: $item->ten_san_pham }}</div>
                                        @if($item->ma_vach)
                                            <div class="small text-muted">Mã vạch: {{ $item->ma_vach }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->so_luong }}</td>
                                    <td class="text-center">{{ $item->da_doi_tra ?? 0 }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $item->so_luong_con_lai }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($item->gia_ban, 0, ',', '.') }}đ</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][id_chi_tiet_hoa_don]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $index }}][hang_loi]" class="hang-loi-hidden" value="{{ old("items.$index.hang_loi", 0) ? 1 : 0 }}">

                                        <div class="d-flex flex-column gap-3">
                                            <div class="d-flex flex-wrap gap-3">
                                                <label class="mb-0">
                                                    <input type="radio" name="items[{{ $index }}][action]" value="none" class="action-radio" @checked(old("items.$index.action", 'none') === 'none')>
                                                    Giữ nguyên
                                                </label>
                                                <label class="mb-0">
                                                    <input type="radio" name="items[{{ $index }}][action]" value="return" class="action-radio" @checked(old("items.$index.action") === 'return')>
                                                    Trả hàng
                                                </label>
                                                <label class="mb-0">
                                                    <input type="radio" name="items[{{ $index }}][action]" value="exchange" class="action-radio" @checked(old("items.$index.action") === 'exchange')>
                                                    Đổi hàng
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input
                                                    class="form-check-input hang-loi-checkbox"
                                                    type="checkbox"
                                                    id="hang-loi-{{ $index }}"
                                                    @checked(old("items.$index.hang_loi"))
                                                >
                                                <label class="form-check-label" for="hang-loi-{{ $index }}">
                                                    Hàng lỗi
                                                </label>
                                            </div>

                                            <div class="action-panel d-none border rounded bg-light p-3">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-1">Số lượng</label>
                                                        <input
                                                            type="number"
                                                            name="items[{{ $index }}][so_luong]"
                                                            class="form-control form-control-sm qty-input"
                                                            min="1"
                                                            max="{{ $item->so_luong_con_lai }}"
                                                            value="{{ old("items.$index.so_luong", 1) }}"
                                                            disabled
                                                        >
                                                    </div>
                                                    <div class="col-md-8 replacement-wrapper d-none">
                                                        <label class="form-label mb-1">Biến thể thay thế</label>
                                                        <input
                                                            type="hidden"
                                                            name="items[{{ $index }}][id_bien_the_thay_the]"
                                                            class="replacement-select"
                                                            value="{{ $exchangeVariantId }}"
                                                            data-stock="{{ $exchangeStock }}"
                                                            disabled
                                                        >
                                                        <div class="form-control form-control-sm bg-white">
                                                            {{ $item->ten_hien_thi_san_pham ?: $item->ten_san_pham }}
                                                        </div>
                                                        <div class="small text-muted mt-1">
                                                            Đổi hàng chỉ áp dụng cho hàng lỗi và chỉ được đổi đúng cùng biến thể đã mua.
                                                        </div>
                                                        <div class="small {{ $exchangeStock > 0 ? 'text-success' : 'text-danger' }}">
                                                            Tồn khả dụng của cùng biến thể: <strong>{{ number_format($exchangeStock, 0, ',', '.') }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Không còn sản phẩm nào đủ điều kiện đổi/trả.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-info">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Lý do</label>
                    <textarea name="ly_do" class="form-control" rows="4" placeholder="Ví dụ: Hàng lỗi - bung chỉ, lỗi khóa, méo mó, không hoạt động...">{{ old('ly_do') }}</textarea>
                    <div class="small text-muted mt-2">
                        Nếu chọn đổi hàng, hệ thống sẽ tự xem đây là hàng lỗi và không nhập lại kho bán được.
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="mb-2">
                        <span class="text-muted">Tổng giá trị trả hàng</span>
                        <div id="tongTraHang" class="fs-4 fw-bold text-danger">0đ</div>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Tổng dòng đổi hàng</span>
                        <div id="tongDoiHang" class="fs-5 fw-bold text-primary">0</div>
                    </div>
                    <div class="small text-muted mb-3">
                        Trả hàng tốt: nhập lại kho. Trả hàng lỗi: không nhập lại kho. Đổi hàng: chỉ cho hàng lỗi và chỉ trừ tồn của cùng biến thể giao lại cho khách.
                    </div>

                    <a href="{{ $showRoute }}" class="btn btn-secondary me-2">Quay lại</a>
                    <button type="submit" class="btn btn-info text-white fw-bold" id="btnSubmit">
                        <i class="fas fa-check-circle me-1"></i> Xác nhận xử lý
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = Array.from(document.querySelectorAll('.return-item-row'));
        const form = document.getElementById('doiTraForm');
        const tongTraHang = document.getElementById('tongTraHang');
        const tongDoiHang = document.getElementById('tongDoiHang');
        const formatMoney = (value) => new Intl.NumberFormat('vi-VN').format(value || 0) + 'đ';

        function syncHangLoi(row) {
            const action = row.querySelector('.action-radio:checked')?.value || 'none';
            const checkbox = row.querySelector('.hang-loi-checkbox');
            const hidden = row.querySelector('.hang-loi-hidden');

            if (action === 'exchange') {
                checkbox.checked = true;
                checkbox.disabled = true;
            } else if (action === 'none') {
                checkbox.checked = false;
                checkbox.disabled = false;
            } else {
                checkbox.disabled = false;
            }

            hidden.value = checkbox.checked ? '1' : '0';
        }

        function toggleRow(row) {
            const action = row.querySelector('.action-radio:checked')?.value || 'none';
            const panel = row.querySelector('.action-panel');
            const qtyInput = row.querySelector('.qty-input');
            const replacementWrapper = row.querySelector('.replacement-wrapper');
            const replacementSelect = row.querySelector('.replacement-select');

            syncHangLoi(row);

            if (action === 'none') {
                panel.classList.add('d-none');
                qtyInput.disabled = true;
                replacementSelect.disabled = true;
                replacementWrapper.classList.add('d-none');
                return;
            }

            panel.classList.remove('d-none');
            qtyInput.disabled = false;
            replacementWrapper.classList.toggle('d-none', action !== 'exchange');
            replacementSelect.disabled = action !== 'exchange';
        }

        function validateRow(row) {
            const action = row.querySelector('.action-radio:checked')?.value || 'none';
            if (action === 'none') {
                return true;
            }

            const max = parseInt(row.dataset.max, 10) || 0;
            const qtyInput = row.querySelector('.qty-input');
            const qty = parseInt(qtyInput.value, 10) || 0;

            if (qty < 1 || qty > max) {
                return false;
            }

            if (action === 'exchange') {
                const replacementSelect = row.querySelector('.replacement-select');
                const stock = parseInt(replacementSelect?.dataset.stock || '0', 10);
                const checkbox = row.querySelector('.hang-loi-checkbox');

                if (!checkbox.checked || !replacementSelect.value || qty > stock) {
                    return false;
                }
            }

            return true;
        }

        function recalcTotals() {
            let traHang = 0;
            let doiHang = 0;

            rows.forEach((row) => {
                const action = row.querySelector('.action-radio:checked')?.value || 'none';
                const qty = parseInt(row.querySelector('.qty-input')?.value || '0', 10) || 0;
                const price = parseFloat(row.dataset.price || '0') || 0;

                if (action === 'return') {
                    traHang += qty * price;
                }

                if (action === 'exchange') {
                    doiHang += qty;
                }
            });

            tongTraHang.textContent = formatMoney(traHang);
            tongDoiHang.textContent = new Intl.NumberFormat('vi-VN').format(doiHang);
        }

        rows.forEach((row) => {
            row.querySelectorAll('.action-radio').forEach((radio) => {
                radio.addEventListener('change', () => {
                    toggleRow(row);
                    recalcTotals();
                });
            });

            row.querySelector('.hang-loi-checkbox')?.addEventListener('change', () => {
                syncHangLoi(row);
            });

            row.querySelector('.qty-input')?.addEventListener('input', () => {
                const max = parseInt(row.dataset.max, 10) || 0;
                const input = row.querySelector('.qty-input');
                let value = parseInt(input.value || '0', 10) || 0;
                if (value < 1) value = 1;
                if (value > max) value = max;
                input.value = value;
                recalcTotals();
            });

            toggleRow(row);
        });

        recalcTotals();

        let isSubmittingMixedDoiTra = false;

        form.addEventListener('submit', function (event) {
            if (isSubmittingMixedDoiTra) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            const activeRows = rows.filter((row) => (row.querySelector('.action-radio:checked')?.value || 'none') !== 'none');

            if (activeRows.length === 0) {
                alert('Vui lòng chọn ít nhất một dòng để đổi hoặc trả hàng.');
                return;
            }

            const invalidRow = activeRows.find((row) => !validateRow(row));
            if (invalidRow) {
                alert('Có dòng đổi/trả chưa hợp lệ. Vui lòng kiểm tra lại số lượng, trạng thái hàng lỗi và tồn kho của đúng biến thể.');
                return;
            }

            const submitButton = document.getElementById('btnSubmit');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';
            }

            isSubmittingMixedDoiTra = true;
            form.submit();
        }, true);

        form.addEventListener('submit', function (event) {
            const activeRows = rows.filter((row) => (row.querySelector('.action-radio:checked')?.value || 'none') !== 'none');

            if (activeRows.length === 0) {
                event.preventDefault();
                alert('Vui lòng chọn ít nhất một dòng để đổi hoặc trả hàng.');
                return;
            }

            const actionTypes = new Set(activeRows.map((row) => row.querySelector('.action-radio:checked')?.value));
            if (actionTypes.size > 1) {
                event.preventDefault();
                alert('Mỗi lần xử lý chỉ được chọn một loại nghiệp vụ: trả hàng hoặc đổi hàng.');
                return;
            }

            const invalidRow = activeRows.find((row) => !validateRow(row));
            if (invalidRow) {
                event.preventDefault();
                alert('Có dòng đổi/trả chưa hợp lệ. Vui lòng kiểm tra lại số lượng, trạng thái hàng lỗi và tồn kho của đúng biến thể.');
                return;
            }

            const submitButton = document.getElementById('btnSubmit');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...';
        });
    });
</script>
@endsection
