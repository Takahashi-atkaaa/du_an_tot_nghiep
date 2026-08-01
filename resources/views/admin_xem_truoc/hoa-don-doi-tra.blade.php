@extends('admin_xem_truoc.layouts.admin')

@section('title', 'Đổi / Trả Hàng')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Xử lý Đổi / Trả hàng - Hóa đơn #HD{{ str_pad($hoaDon->id, 4, '0', STR_PAD_LEFT) }}</h4>

    <form action="{{ route('admin.hoa-don.xu-ly-doi-tra', $hoaDon->id) }}" method="POST" id="doiTraForm">
        @csrf
        
        <!-- Khu vực 1: Hàng khách trả lại -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark fw-bold">
                <i class="fas fa-undo me-2"></i> Ký gửi trả hàng / Đổi hàng
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đã mua</th>
                            <th>Có thể trả/đổi</th>
                            <th>Đơn giá</th>
                            <th width="350">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chiTiet as $item)
                            @php
                                $key = $item->id_chi_tiet_phieu ?: $item->id_san_pham;
                                $daTraSp = $daTra[$key] ?? 0;
                                $maxTra = $item->so_luong - $daTraSp;
                            @endphp
                            @if($maxTra > 0)
                                <tr class="item-tra-row" data-id="{{ $item->id }}" data-price="{{ $item->gia_ban }}">
                                    <td>
                                        <span class="fw-bold">{{ $item->ten_san_pham }}</span>
                                        @if($item->ten_don_vi || $item->ten_bien_the)
                                            <br><small class="text-muted">({{ $item->ten_don_vi ?: $item->ten_bien_the }})</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->so_luong }}</td>
                                    <td><span class="badge bg-info">{{ $maxTra }}</span></td>
                                    <td>{{ number_format($item->gia_ban, 0, ',', '.') }}đ</td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex gap-3">
                                                <label><input type="radio" name="action_{{ $item->id }}" value="none" class="action-radio" checked> Giữ nguyên</label>
                                                <label><input type="radio" name="action_{{ $item->id }}" value="return" class="action-radio text-danger"> <span class="text-danger fw-bold">Trả hàng</span></label>
                                                <label><input type="radio" name="action_{{ $item->id }}" value="exchange" class="action-radio text-primary"> <span class="text-primary fw-bold">Đổi hàng</span></label>
                                            </div>
                                            <div class="action-options d-none p-2 bg-light border rounded">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <label class="mb-0 text-nowrap">Số lượng:</label>
                                                    <input type="number" class="form-control form-control-sm qty-input" name="items_tra[{{ $item->id }}][so_luong]" value="1" min="1" max="{{ $maxTra }}" disabled>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input is-loi-check" type="checkbox" name="items_tra[{{ $item->id }}][is_loi]" value="1" disabled>
                                                    <label class="form-check-label text-danger">
                                                        Hàng bị lỗi (Không hoàn kho)
                                                    </label>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 mb-2 loi-qty-row" style="display:none;">
                                                    <label class="mb-0 text-nowrap">Số lượng hàng lỗi:</label>
                                                    <input type="number" class="form-control form-control-sm loi-qty-input" name="items_tra[{{ $item->id }}][so_luong_loi]" value="0" min="0" max="{{ $maxTra }}" disabled>
                                                </div>
                                                <input type="hidden" name="items_tra[{{ $item->id }}][id]" value="{{ $item->id }}" disabled class="item-id-hidden">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Khu vực 2: Chọn hàng mới (Chỉ hiện khi có item chọn Đổi hàng) -->
        <div class="card mb-4" id="khuVucDoiHang" style="display: none;">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-exchange-alt me-2"></i> Sản phẩm đổi mới cho khách
            </div>
            <div class="card-body">
                <div class="mb-3 position-relative">
                    <input type="text" id="searchProduct" class="form-control form-control-lg" placeholder="Tìm kiếm sản phẩm cần đổi (Tên, mã vạch)..." autocomplete="off">
                    <div id="searchResults" class="list-group position-absolute w-100 shadow" style="z-index: 1000; max-height: 300px; overflow-y: auto; display: none;"></div>
                </div>

                <table class="table table-bordered mt-3" id="tableDoiHang">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm mới</th>
                            <th width="150">Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                            <th width="80">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Items appended by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Khu vực 3: Thanh toán -->
        <div class="card border-info">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ghi chú đổi/trả:</label>
                        <textarea name="ly_do" class="form-control" rows="3" placeholder="Lý do đổi trả..."></textarea>
                    </div>
                    <div class="col-md-6 text-end">
                        <h5 class="mb-2">Tổng tiền hàng khách trả: <span id="lblTongTra" class="text-danger">0đ</span></h5>
                        <h5 class="mb-3" id="rowTongDoi" style="display:none;">Tổng tiền hàng đổi mới: <span id="lblTongDoi" class="text-primary">0đ</span></h5>
                        <hr>
                        <h4 class="mb-3">
                            <span id="lblChenhLechText">Cần thu thêm:</span>
                            <span id="lblChenhLechTotal" class="text-success fw-bold">0đ</span>
                        </h4>
                        
                        <a href="{{ route('admin.hoa-don.show', $hoaDon->id) }}" class="btn btn-secondary me-2">Hủy bỏ</a>
                        <button type="submit" class="btn btn-info fw-bold text-white" id="btnSubmit">
                            <i class="fas fa-check-circle me-1"></i> Xác nhận Đổi / Trả hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        
        let tongTienTra = 0;
        let tongTienDoi = 0;
        let exchangedItems = {};

        // 1. Xử lý Radio Hành động Trả/Đổi
        document.querySelectorAll('.action-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                const row = this.closest('.item-tra-row');
                const optionsDiv = row.querySelector('.action-options');
                const inputs = optionsDiv.querySelectorAll('input');
                
                if (this.value === 'none') {
                    optionsDiv.classList.add('d-none');
                    inputs.forEach(inp => inp.disabled = true);
                } else {
                    optionsDiv.classList.remove('d-none');
                    inputs.forEach(inp => inp.disabled = false);
                }
                
                checkHienThiKhuVucDoi();
                tinhTien();
            });
        });

        // Lắng nghe thay đổi số lượng hàng trả
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('.item-tra-row');
                updateLoiQtyRow(row);
                validateTraRow(row);
                tinhTien();
            });
        });

        document.querySelectorAll('.is-loi-check').forEach(check => {
            check.addEventListener('change', function() {
                const row = this.closest('.item-tra-row');
                updateLoiQtyRow(row);
                validateTraRow(row);
                tinhTien();
            });
        });

        document.querySelectorAll('.loi-qty-input').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('.item-tra-row');
                validateTraRow(row);
                tinhTien();
            });
        });

        function checkHienThiKhuVucDoi() {
            const hasExchange = Array.from(document.querySelectorAll('.action-radio:checked')).some(r => r.value === 'exchange');
            document.getElementById('khuVucDoiHang').style.display = hasExchange ? 'block' : 'none';
            document.getElementById('rowTongDoi').style.display = hasExchange ? 'block' : 'none';
        }

        function updateLoiQtyRow(row) {
            const loiCheckbox = row.querySelector('.is-loi-check');
            const loiQtyRow = row.querySelector('.loi-qty-row');
            const loiQtyInput = row.querySelector('.loi-qty-input');
            const qtyInput = row.querySelector('.qty-input');
            if (!loiCheckbox || !loiQtyRow || !loiQtyInput || !qtyInput) return;

            loiQtyInput.max = qtyInput.value || loiQtyInput.max;

            if (loiCheckbox.checked) {
                loiQtyRow.style.display = 'flex';
                loiQtyInput.disabled = false;
                if (loiQtyInput.value === '') {
                    loiQtyInput.value = 0;
                }
            } else {
                loiQtyRow.style.display = 'none';
                loiQtyInput.disabled = true;
                loiQtyInput.value = 0;
            }
        }

        function validateTraRow(row) {
            const qtyInput = row.querySelector('.qty-input');
            const loiQtyInput = row.querySelector('.loi-qty-input');
            if (!qtyInput || !loiQtyInput) return true;

            const qty = parseInt(qtyInput.value, 10) || 0;
            let loiQty = parseInt(loiQtyInput.value, 10);
            if (Number.isNaN(loiQty)) {
                loiQty = 0;
            }

            if (loiQty < 0) {
                loiQtyInput.value = 0;
                alert('Số lượng hàng lỗi không được âm.');
                return false;
            }

            if (loiQty > qty) {
                loiQtyInput.value = qty;
                alert('Số lượng hàng lỗi không được lớn hơn tổng số lượng trả.');
                return false;
            }

            return true;
        }

        // 2. Xử lý Tìm kiếm Sản phẩm Đổi
        const searchInput = document.getElementById('searchProduct');
        const searchResults = document.getElementById('searchResults');
        
        let searchTimeout;
        
        function fetchProducts(q) {
            const url = `{{ route('admin.hoa-don.search-product', [], false) }}?q=${q}`;
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length === 0) {
                        searchResults.innerHTML = '<div class="list-group-item text-muted">Không tìm thấy sản phẩm</div>';
                    } else {
                        data.forEach(item => {
                            const isOutOfStock = item.so_luong_ton <= 0;
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center' + (isOutOfStock ? ' disabled bg-light' : '');
                            if (isOutOfStock) {
                                btn.disabled = true;
                                btn.style.cursor = 'not-allowed';
                                btn.style.opacity = '0.6';
                            }
                            
                            const variantName = item.ten_don_vi ? item.ten_don_vi : item.ten_bien_the;
                            const nameDisplay = variantName ? `${item.ten_san_pham} (${variantName})` : item.ten_san_pham;
                            
                            const stockDisplay = isOutOfStock ? '<span class="text-danger fw-bold">Hết hàng</span>' : `Kho: ${item.so_luong_ton}`;
                            
                            btn.innerHTML = `
                                <div>
                                    <div class="fw-bold ${isOutOfStock ? 'text-muted' : ''}">${nameDisplay}</div>
                                    <small class="text-muted">${stockDisplay} | Mã: ${item.ma_vach ?? 'N/A'}</small>
                                </div>
                                <span class="badge ${isOutOfStock ? 'bg-secondary' : 'bg-primary'} rounded-pill">${formatMoney(item.gia_ban)}</span>
                            `;
                            
                            if (!isOutOfStock) {
                                btn.onclick = () => addDoiItem(item, nameDisplay);
                            }
                            searchResults.appendChild(btn);
                        });
                    }
                    searchResults.style.display = 'block';
                })
                .catch(err => {
                    console.error("Lỗi khi tìm kiếm sản phẩm:", err);
                    searchResults.innerHTML = '<div class="list-group-item text-danger">Có lỗi xảy ra khi tìm kiếm</div>';
                    searchResults.style.display = 'block';
                });
        }

        searchInput.addEventListener('focus', function() {
            fetchProducts(this.value.trim());
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            searchTimeout = setTimeout(() => fetchProducts(q), 300);
        });

        document.addEventListener('click', e => {
            if (!searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.style.display = 'none';
            }
        });

        // 3. Thêm Sản phẩm Đổi vào Bảng
        const tbodyDoiHang = document.querySelector('#tableDoiHang tbody');
        
        function addDoiItem(item, nameDisplay) {
            searchResults.style.display = 'none';
            searchInput.value = '';
            
            if (item.so_luong_ton <= 0) {
                alert('Sản phẩm này đã hết hàng trong kho!');
                return;
            }

            if (exchangedItems[item.id]) {
                const tr = tbodyDoiHang.querySelector(`tr[data-vid="${item.id}"]`);
                const input = tr.querySelector('.doi-qty');
                if (parseInt(input.value) < item.so_luong_ton) {
                    input.value = parseInt(input.value) + 1;
                    input.dispatchEvent(new Event('input'));
                } else {
                    alert('Số lượng vượt quá tồn kho!');
                }
                return;
            }

            exchangedItems[item.id] = item;
            
            const tr = document.createElement('tr');
            tr.dataset.vid = item.id;
            tr.innerHTML = `
                <td>
                    <div class="fw-bold">${nameDisplay}</div>
                    <small class="text-muted">Tồn kho: <span class="ton-kho">${item.so_luong_ton}</span></small>
                </td>
                <td>
                    <input type="number" name="items_doi[${item.id}][so_luong]" class="form-control doi-qty" value="1" min="1" max="${item.so_luong_ton}" required>
                    <input type="hidden" name="items_doi[${item.id}][variant_id]" value="${item.id}">
                </td>
                <td>${formatMoney(item.gia_ban)}</td>
                <td class="doi-thanh-tien fw-bold">${formatMoney(item.gia_ban)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-doi"><i class="fas fa-trash"></i></button>
                </td>
            `;
            
            tbodyDoiHang.appendChild(tr);
            
            tr.querySelector('.doi-qty').addEventListener('input', function() {
                let val = parseInt(this.value);
                if (val > item.so_luong_ton) {
                    alert('Chỉ còn ' + item.so_luong_ton + ' sản phẩm trong kho!');
                    this.value = item.so_luong_ton;
                    val = item.so_luong_ton;
                }
                if (val < 1) this.value = 1;
                
                const tt = parseInt(this.value) * item.gia_ban;
                tr.querySelector('.doi-thanh-tien').innerText = formatMoney(tt);
                tinhTien();
            });
            
            tr.querySelector('.btn-remove-doi').addEventListener('click', function() {
                delete exchangedItems[item.id];
                tr.remove();
                tinhTien();
            });
            
            tinhTien();
        }

        // 4. Tính toán Tài chính
        function tinhTien() {
            tongTienTra = 0;
            document.querySelectorAll('.item-tra-row').forEach(row => {
                const radio = row.querySelector('.action-radio:checked');
                if (radio && radio.value !== 'none') {
                    const price = parseFloat(row.dataset.price);
                    const qty = parseInt(row.querySelector('.qty-input').value) || 0;
                    tongTienTra += price * qty;
                }
            });

            tongTienDoi = 0;
            const hasExchange = Array.from(document.querySelectorAll('.action-radio:checked')).some(r => r.value === 'exchange');
            if (hasExchange) {
                document.querySelectorAll('#tableDoiHang tbody tr').forEach(tr => {
                    const vid = tr.dataset.vid;
                    const item = exchangedItems[vid];
                    const qty = parseInt(tr.querySelector('.doi-qty').value) || 0;
                    tongTienDoi += qty * item.gia_ban;
                });
            }

            document.getElementById('lblTongTra').innerText = formatMoney(tongTienTra);
            document.getElementById('lblTongDoi').innerText = formatMoney(tongTienDoi);

            const chenhLech = tongTienDoi - tongTienTra;
            const lblText = document.getElementById('lblChenhLechText');
            const lblTotal = document.getElementById('lblChenhLechTotal');

            if (chenhLech > 0) {
                lblText.innerText = 'Khách cần thanh toán thêm:';
                lblTotal.innerText = formatMoney(chenhLech);
                lblTotal.className = 'text-success fw-bold';
            } else if (chenhLech < 0) {
                lblText.innerText = 'Cửa hàng hoàn lại khách:';
                lblTotal.innerText = formatMoney(Math.abs(chenhLech));
                lblTotal.className = 'text-danger fw-bold';
            } else {
                lblText.innerText = 'Chênh lệch:';
                lblTotal.innerText = '0đ';
                lblTotal.className = 'text-secondary fw-bold';
            }
        }

        // Validate form submit
        document.getElementById('doiTraForm').addEventListener('submit', function(e) {
            const hasAction = Array.from(document.querySelectorAll('.action-radio:checked')).some(r => r.value !== 'none');
            if (!hasAction) {
                e.preventDefault();
                alert('Vui lòng chọn Hành động (Trả hàng hoặc Đổi hàng) cho ít nhất 1 sản phẩm!');
                return;
            }

            const valid = Array.from(document.querySelectorAll('.item-tra-row')).every(validateTraRow);
            if (!valid) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection
