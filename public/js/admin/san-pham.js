// ============================================================
// SAN-PHAM: Toggle section (shared with index + edit pages)
// ============================================================
function toggleSection(headerEl) {
    const card = headerEl.closest('.section-card');
    if (!card) return;
    const body = card.querySelector('.section-body');
    if (!body) return;
    const isOpen = body.classList.contains('open');
    body.classList.toggle('open', !isOpen);
    headerEl.classList.toggle('open', !isOpen);
}

// ============================================================
// BULK ACTIONS (san-pham index)
// ============================================================
(function() {
    var selectAllCheckbox = document.getElementById('selectAllCheckbox');
    var productCheckboxes = document.querySelectorAll('.product-checkbox');
    var bulkActionButtons = document.getElementById('bulkActionButtons');
    var selectedCount = document.getElementById('selectedCount');
    var bulkActionForm = document.getElementById('bulkActionForm');
    var bulkActionInput = document.getElementById('bulkActionInput');
    var selectedIdsContainer = document.getElementById('selectedIdsContainer');

    function updateBulkUI() {
        var checked = Array.from(productCheckboxes).filter(function(cb) { return cb.checked; });
        if (checked.length > 0) {
            if (bulkActionButtons) bulkActionButtons.classList.remove('d-none');
            if (selectedCount) selectedCount.textContent = checked.length + ' da chon';
        } else {
            if (bulkActionButtons) bulkActionButtons.classList.add('d-none');
        }
    }

    productCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', updateBulkUI);
    });

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            productCheckboxes.forEach(function(cb) { cb.checked = this.checked; }, this);
            updateBulkUI();
        });
    }

    window.submitBulkAction = function(action) {
        var checked = Array.from(productCheckboxes).filter(function(cb) { return cb.checked; });
        if (checked.length === 0) return;

        var messages = {
            'delete': 'Bạn có chắc muốn xóa ' + checked.length + ' sản phẩm đã chọn?',
            'activate': 'Bật trạng thái cho ' + checked.length + ' sản phẩm?',
            'deactivate': 'Tắt trạng thái cho ' + checked.length + ' sản phẩm?'
        };

        if (!confirm(messages[action] || 'Xác nhận?')) return;

        if (selectedIdsContainer) selectedIdsContainer.innerHTML = '';
        checked.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            if (selectedIdsContainer) selectedIdsContainer.appendChild(input);
        });
        if (bulkActionInput) bulkActionInput.value = action;
        if (bulkActionForm) bulkActionForm.submit();
    };

    window.toggleVariants = function(productId) {
        var btn = document.getElementById('expandBtn' + productId);
        var detailRow = document.getElementById('productDetailRow' + productId);
        var isExpanded = false;

        if (btn && btn.classList.contains('expanded')) {
            isExpanded = true;
        } else if (detailRow) {
            var detailDisplay = window.getComputedStyle(detailRow).display;
            isExpanded = detailDisplay !== 'none';
        }

        if (isExpanded) {
            if (detailRow) detailRow.style.display = 'none';
            if (btn) {
                btn.classList.remove('expanded');
                var icon = btn.querySelector('i');
                if (icon) icon.style.transform = '';
            }
        } else {
            if (detailRow) {
                detailRow.style.display = '';
                window.loadProductStats && window.loadProductStats(productId);
            }
            if (btn) {
                btn.classList.add('expanded');
                var icon = btn.querySelector('i');
                if (icon) icon.style.transform = 'rotate(90deg)';
            }
        }
    };
})();

    function buildStatsHtml(productId, data) {
        var product = data.product || {};
        var summary = data.summary || {};
        var topVariants = data.top_variants || [];
        var recentOrders = data.recent_orders || [];

        var rangeLabel = '30 ngày';
        if (summary.from && summary.to) {
            rangeLabel = summary.from === summary.to ? summary.from : summary.from + ' - ' + summary.to;
        }

        // Tính toán "Giá trị tồn kho" = Tồn kho * Giá vốn (data.summary.inventory_value)
        // Fallback: nếu backend chưa trả, dùng tổng tồn kho * 70% giá bán trung bình
        var inventoryValue = summary.inventory_value;
        if (inventoryValue === undefined || inventoryValue === null) {
            var approxCost = Math.round((summary.average_price || 0) * 0.7);
            inventoryValue = (product.tong_ton_kho || 0) * approxCost;
        }

        // ============================================================
        // URL trang chi tiết (Progressive Disclosure: Quick View → Detail)
        // Dùng cho nút "Xem chi tiết đầy đủ" của từng panel + hash cho tab
        // ============================================================
        var detailPageUrl = '/admin/san-pham/' + productId;

        // ============================================================
        // YÊU CẦU 2: 4 Thẻ Thống Kê (Top Cards) - Text-left, có icon SVG
        // ============================================================
        var cardsHtml = '<div class="row g-3 mb-3 stats-cards-row">';

        // Card 1: Đơn hàng (Shopping Bag icon)
        cardsHtml += '<div class="col-md-3 col-sm-6">' +
            '<div class="stat-card text-left shadow-sm">' +
                '<div class="stat-card-icon stat-icon-blue">' +
                    // Heroicons: shopping-bag
                    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="22" height="22">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />' +
                    '</svg>' +
                '</div>' +
                '<div class="stat-card-label">Đơn hàng</div>' +
                '<div class="stat-card-value">' + (summary.total_orders ?? 0) + '</div>' +
            '</div>' +
        '</div>';

        // Card 2: Số lượng bán (Cube icon)
        cardsHtml += '<div class="col-md-3 col-sm-6">' +
            '<div class="stat-card text-left shadow-sm">' +
                '<div class="stat-card-icon stat-icon-orange">' +
                    // Heroicons: cube
                    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="22" height="22">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />' +
                    '</svg>' +
                '</div>' +
                '<div class="stat-card-label">Số lượng bán</div>' +
                '<div class="stat-card-value">' + (summary.total_quantity ?? 0) + '</div>' +
            '</div>' +
        '</div>';

        // Card 3: Doanh thu (Currency icon)
        cardsHtml += '<div class="col-md-3 col-sm-6">' +
            '<div class="stat-card text-left shadow-sm">' +
                '<div class="stat-card-icon stat-icon-green">' +
                    // Heroicons: banknotes / currency-dollar
                    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="22" height="22">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />' +
                    '</svg>' +
                '</div>' +
                '<div class="stat-card-label">Doanh thu</div>' +
                '<div class="stat-card-value">' + formatMoney(summary.total_revenue ?? 0) + 'đ</div>' +
            '</div>' +
        '</div>';

        // Card 4: Giá trị tồn kho (Archive box icon) - THAY THẾ "Giá TB / đơn vị"
        cardsHtml += '<div class="col-md-3 col-sm-6">' +
            '<div class="stat-card text-left shadow-sm">' +
                '<div class="stat-card-icon stat-icon-purple">' +
                    // Heroicons: archive-box (tồn kho)
                    '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="22" height="22">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />' +
                    '</svg>' +
                '</div>' +
                '<div class="stat-card-label">Giá trị tồn kho</div>' +
                '<div class="stat-card-value">' + formatMoney(inventoryValue) + 'đ</div>' +
                '<div class="stat-card-growth">' +
                    '<span class="text-gray-500">Tồn: ' + (product.tong_ton_kho ?? 0) + ' sp</span>' +
                    '<span class="text-gray-400 ms-1">trên kho</span>' +
                '</div>' +
            '</div>' +
        '</div>';

        cardsHtml += '</div>'; // đóng row stats-cards-row

        // ============================================================
        // Header mini (dòng thời gian + meta)
        // ============================================================
        var headerHtml = '<div class="d-flex justify-content-between align-items-center mb-3 px-1">' +
            '<div class="text-muted small"><i class="far fa-calendar-alt me-1"></i>Dữ liệu bán hàng: ' + rangeLabel + '</div>' +
            '<div class="text-muted small">' + (product.bien_the_count ?? 0) + ' biến thể · ' + (product.tong_ton_kho ?? 0) + ' tồn kho</div>' +
        '</div>';

        // ============================================================
        // YÊU CẦU 4: 2 Khối Bottom (Top biến thể & Đơn hàng gần nhất)
        // Chia layout col-span-4 (Top biến thể) và col-span-8 (Đơn hàng gần nhất)
        // Dùng Bootstrap grid 12: col-md-4 và col-md-8
        // ============================================================

        // ---- Top biến thể (col-md-4) - có Progress Bar ----
        // Tính max để làm thanh progress tỉ lệ (chỉ khi có dữ liệu thật)
        var maxQty = 0;
        if (topVariants.length > 0) {
            topVariants.forEach(function(item) {
                var q = parseInt(item.quantity || 0);
                if (q > maxQty) maxQty = q;
            });
        }

        var topVariantsHtml = '<div class="col-md-4">' +
            '<div class="bg-white rounded shadow-sm p-3 h-100 panel-block">' +
                '<div class="d-flex justify-content-between align-items-center mb-3">' +
                    '<h6 class="fw-semibold mb-0 panel-title"><i class="fas fa-fire text-warning me-2"></i>Top biến thể bán chạy</h6>' +
                    '<span class="badge bg-light text-dark border">' + topVariants.length + '</span>' +
                '</div>';

        if (topVariants.length === 0) {
            topVariantsHtml += '<div class="p-4 text-center text-sm text-gray-500">Chưa có dữ liệu bán hàng.</div>';
        } else {
            topVariantsHtml += '<ul class="list-unstyled mb-0 top-variants-list">';
            topVariants.slice(0, 5).forEach(function(item, idx) {
                var qty = parseInt(item.quantity || 0);
                var pct = maxQty > 0 ? Math.round((qty / maxQty) * 100) : 0;
                topVariantsHtml += '<li class="top-variant-item">' +
                    '<div class="d-flex justify-content-between align-items-start mb-1">' +
                        '<div class="flex-grow-1 me-2">' +
                            '<div class="fw-medium variant-name">' +
                                (idx === 0 ? '<span class="rank-badge rank-1">1</span>' : '<span class="rank-badge">' + (idx + 1) + '</span>') +
                                (item.variant_name || '-') +
                            '</div>' +
                        '</div>' +
                        '<div class="text-end">' +
                            '<div class="fw-semibold text-primary small">' + formatMoney(item.revenue ?? 0) + 'đ</div>' +
                            '<div class="text-muted" style="font-size:0.7rem;">' + qty + ' sp</div>' +
                        '</div>' +
                    '</div>' +
                    // YÊU CẦU 4: Progress Bar cao 4px, màu xanh bg-blue-500
                    '<div class="progress variant-progress" style="height:4px;">' +
                        '<div class="progress-bar bg-blue-500" role="progressbar" style="width:' + pct + '%; background-color:#3b82f6;" aria-valuenow="' + pct + '" aria-valuemin="0" aria-valuemax="100"></div>' +
                    '</div>' +
                '</li>';
            });
            topVariantsHtml += '</ul>';
        }

        topVariantsHtml += '</div></div>'; // đóng panel + col

        // ---- Đơn hàng gần nhất (col-md-8) - Mini Table/List ----
        var statusBadge = function(status) {
            // Map status về tiếng Việt + class
            var map = {
                'hoan_thanh':   { label: 'Hoàn thành', cls: 'badge-status badge-success' },
                'hoanthanh':    { label: 'Hoàn thành', cls: 'badge-status badge-success' },
                'completed':    { label: 'Hoàn thành', cls: 'badge-status badge-success' },
                'dang_xu_ly':   { label: 'Đang xử lý', cls: 'badge-status badge-warning' },
                'dangxuly':     { label: 'Đang xử lý', cls: 'badge-status badge-warning' },
                'processing':   { label: 'Đang xử lý', cls: 'badge-status badge-warning' },
                'huy':          { label: 'Đã hủy',     cls: 'badge-status badge-danger' },
                'cancelled':    { label: 'Đã hủy',     cls: 'badge-status badge-danger' },
                'tra_hang':     { label: 'Trả hàng',   cls: 'badge-status badge-secondary' },
            };
            var key = (status || '').toString().toLowerCase();
            return map[key] || { label: status || '—', cls: 'badge-status badge-secondary' };
        };

        var recentOrdersHtml = '<div class="col-md-8">' +
            '<div class="bg-white rounded shadow-sm p-3 h-100 panel-block" id="tab-don-hang-' + productId + '">' +
                '<div class="d-flex justify-content-between align-items-center mb-3">' +
                    '<h6 class="fw-semibold mb-0 panel-title"><i class="fas fa-receipt text-success me-2"></i>Đơn hàng gần nhất</h6>' +
                    // ============================================================
                    // PROGRESSIVE DISCLOSURE: "Xem tất cả" → chuyển sang trang
                    // chi tiết (#tab-don-hang) thay vì chỉ là href="#" stub.
                    // Backend LIMIT 5 nên panel này chỉ hiển thị 5 đơn mới nhất;
                    // click để xem lịch sử đầy đủ trên trang chi tiết.
                    // ============================================================
                    '<a href="' + detailPageUrl + '#tab-don-hang" class="small text-decoration-none view-all-link" title="Xem tất cả đơn hàng trên trang chi tiết">' +
                        'Xem tất cả <i class="fas fa-arrow-right ms-1"></i>' +
                    '</a>' +
                '</div>' +
                '<div class="table-responsive recent-orders-table">' +
                    '<table class="table table-sm align-middle mb-0">' +
                        '<thead>' +
                            '<tr>' +
                                '<th class="border-0 text-muted" style="font-size:0.72rem;font-weight:600;">Mã đơn</th>' +
                                '<th class="border-0 text-muted" style="font-size:0.72rem;font-weight:600;">Thời gian</th>' +
                                '<th class="border-0 text-muted text-center" style="font-size:0.72rem;font-weight:600;">SL</th>' +
                                '<th class="border-0 text-muted text-end" style="font-size:0.72rem;font-weight:600;">Tổng tiền</th>' +
                                '<th class="border-0 text-muted text-end" style="font-size:0.72rem;font-weight:600;">Trạng thái</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>';

        if (recentOrders.length === 0) {
            recentOrdersHtml += '<tr><td colspan="5" class="p-4 text-center text-sm text-gray-500">Chưa có giao dịch nào.</td></tr>';
        } else {
            recentOrders.slice(0, 5).forEach(function(order) {
                var orderDate = order.order_date ? new Date(order.order_date).toLocaleString('vi-VN', {
                    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                }) : '-';
                var st = statusBadge(order.status);
                var detailUrl = '/admin/hoa-don/' + order.order_id;
                recentOrdersHtml += '<tr class="recent-order-row" data-href="' + detailUrl + '" style="cursor:pointer;" title="Xem chi tiết hóa đơn">' +
                    '<td>' +
                        '<span class="fw-semibold text-primary">' +
                            (order.ma_hoa_don || ('#' + order.order_id)) +
                        '</span>' +
                    '</td>' +
                    '<td class="text-muted small">' + orderDate + '</td>' +
                    '<td class="text-center"><span class="qty-badge">x' + (order.quantity ?? 0) + '</span></td>' +
                    '<td class="text-end fw-semibold">' + formatMoney(order.revenue ?? 0) + 'đ</td>' +
                    '<td class="text-end"><span class="' + st.cls + '">' + st.label + '</span></td>' +
                '</tr>';
            });
        }

        recentOrdersHtml += '</tbody></table></div></div></div>'; // đóng table, panel, col

        var bottomHtml = '<div class="row g-3 mt-1 stats-bottom-row">' + topVariantsHtml + recentOrdersHtml + '</div>';

        return headerHtml + cardsHtml + bottomHtml;
    }

    function buildVariantsHtml(data) {
        var variants = data.allVariants || [];
        if (!variants.length) {
            return '<div class="text-muted small py-4">Không có biến thể.</div>';
        }

        var html = '<div class="row g-3">';
        variants.forEach(function(v) {
            var stock = v.so_luong_ton ?? 0;
            var minStock = v.dinh_muc_toi_thieu ?? 0;
            var status = 'Còn hàng';
            var badgeClass = 'bg-success';
            if (!v.trang_thai) {
                status = 'Ngừng';
                badgeClass = 'bg-danger';
            } else if (stock <= 0) {
                status = 'Hết hàng';
                badgeClass = 'bg-secondary';
            } else if (stock <= minStock) {
                status = 'Sắp hết';
                badgeClass = 'bg-warning text-dark';
            }

            html += '<div class="col-12"><div class="bg-white rounded shadow-sm p-3">';
            html += '<div class="d-flex justify-content-between align-items-start mb-2">';
            html += '<div><div class="fw-semibold">' + (v.ten_bien_the || v.ten_don_vi || 'Biến thể') + '</div>';
            html += '<div class="text-muted small">#' + (v.ma_hang || v.ma_vach || v.id) + '</div></div>';
            html += '<span class="badge ' + badgeClass + '">' + status + '</span>';
            html += '</div>';
            html += '<div class="row gx-2 gy-2 small text-muted">';
            html += '<div class="col-sm-3"><div class="fw-medium">Giá bán</div>' + formatMoney(v.gia_ban ?? 0) + ' đ</div>';
            html += '<div class="col-sm-3"><div class="fw-medium">Tồn kho</div>' + stock + '</div>';
            html += '<div class="col-sm-3"><div class="fw-medium">Định mức</div>' + minStock + '</div>';
            html += '<div class="col-sm-3"><div class="fw-medium">Đơn vị</div>' + (v.ten_don_vi || '-') + '</div>';
            html += '</div>';

            var units = v.units || [];
            if (units.length) {
                html += '<div class="mt-3">';
                html += '<div class="text-muted small fw-semibold mb-2">Đơn vị quy đổi</div>';
                html += '<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Đơn vị</th><th>Tỷ lệ</th><th>Giá bán</th><th>Tồn</th></tr></thead><tbody>';
                units.forEach(function(unit) {
                    html += '<tr>';
                    html += '<td>' + (unit.ten_don_vi || '-') + '</td>';
                    html += '<td>' + (unit.so_luong_san_pham_trong_don_vi ?? unit.ty_le_quy_doi ?? '-') + '</td>';
                    html += '<td>' + formatMoney(unit.gia_ban_quy_doi ?? 0) + ' đ</td>';
                    html += '<td>' + (unit.so_luong_ton ?? 0) + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div>';
                html += '</div>';
            } else {
                html += '<div class="mt-3 text-muted small">Không có đơn vị quy đổi.</div>';
            }
            html += '</div></div>';
        });
        html += '</div>';
        return html;
    }

    function buildStockHtml(productId, data) {
        var currentVariant = data.variant || {};
        var variants = data.allVariants || [];
        // Ưu tiên dữ liệu đầy đủ cho Quick View (đã include variant_id),
        // fallback về theKho/loHang cũ cho tương thích ngược.
        var theKhoAll = data.theKhoAll || [];
        var loHangAll = data.loHangAll || [];
        var theKho = data.theKho || [];
        var loHang = data.loHang || [];

        // Nếu server đã trả về dữ liệu đầy đủ (theKhoAll/loHangAll) thì
        // dùng nó để render, vì mỗi dòng có variant_id giúp frontend lọc
        // ngay khi user đổi biến thể mà không cần gọi lại API.
        var useFullData = (theKhoAll.length > 0 || loHangAll.length > 0);

        var html = '';

        // ID biến thể đang chọn mặc định (variant hiện tại, fallback về
        // variant đầu tiên nếu không có).
        var initialVariantId = String((currentVariant && currentVariant.id) || (variants[0] && variants[0].id) || '');
        // ID rất đặc biệt để tag scope an toàn giữa nhiều QuickView
        // (mỗi sản phẩm 1 ID riêng, tránh xung đột DOM/JS).
        var scopeId = 'qv-' + productId + '-' + Date.now();

        // Khối root chỉ để nhóm các phần tử + phục vụ click handler
        // (window.__qvStockClickVariant dùng closest('.quickview-stock-root')
        // để tìm scope). KHÔNG dùng Alpine.js để tránh xung đột với DOM
        // toggle thủ công (Alpine x-show có thể ghi đè style.display).
        html += '<div class="quickview-stock-root" id="' + scopeId + '" ' +
            'data-product-id="' + productId + '" ' +
            'data-initial-variant="' + initialVariantId + '">';

        // BƯỚC 1: NÚT CHỌN BIẾN THỂ
        // ----------------------------------------
        // Cơ chế hoạt động:
        //   - Mỗi nút có data-variant-id + inline onclick gọi
        //     window.__qvStockClickVariant (handler vanilla JS, không phụ
        //     thuộc Alpine).
        //   - Ta vẫn wrap trong x-data="{ selectedVariantId: ... }" để
        //     có thể dùng Alpine x-show cho row filtering (progressive
        //     enhancement) — nếu Alpine chưa load, onclick vẫn hoạt động
        //     bình thường nhờ DOM toggle 'display:none' inline.
        if (variants.length > 1) {
            html += '<div class="mb-3 d-flex flex-wrap gap-2 qv-variant-buttons">';
            variants.forEach(function (v) {
                var vid = String(v.id);
                var label = v.ten_bien_the || v.ten_don_vi || ('Biến thể ' + v.id);
                // Nút biến thể đầu tiên (initial selection) → btn-dark active.
                var isFirst = (vid === initialVariantId);
                var btnClass = isFirst
                    ? 'qv-variant-btn btn btn-sm btn-dark text-white'
                    : 'qv-variant-btn btn btn-sm btn-outline-secondary';
                html += '<button type="button" ' +
                    'class="' + btnClass + '" ' +
                    'data-variant-id="' + vid + '" ' +
                    'onclick="window.__qvStockClickVariant && window.__qvStockClickVariant(this, ' + vid + ', ' + productId + ');">' +
                    '<i class="fas fa-circle me-1" style="font-size:0.5rem; vertical-align:middle;"></i>' + label +
                    '</button>';
            });
            html += '</div>';
        }

        // BƯỚC 2: 2 BẢNG (Thẻ kho mini + Lô hàng)
        // ----------------------------------------
        html += '<div class="row g-3">';

        // ----- Bảng Thẻ kho mini -----
        html += '<div class="col-md-6">';
        html += '<div class="bg-white rounded shadow-sm p-3">';
        html += '<h6 class="fw-semibold mb-2">Thẻ kho mini</h6>';

        if (useFullData) {
            // Render tất cả dòng kèm data-variant-id để fallback filter
            // bằng DOM. Alpine x-show sẽ được thêm vào để bind theo state.
            if (!theKhoAll.length) {
                html += '<div class="text-muted small py-3 qv-empty-state" data-empty-for="theKho">Chưa có lịch sử kho.</div>';
            } else {
                // Tính số thứ tự theo từng variant (mỗi variant reset về 1)
                // để khi filter hiển thị, số thứ tự vẫn liền mạch, dễ đọc.
                var idxByVariant = {};
                theKhoAll.forEach(function (item) {
                    var key = String(item.variant_id);
                    idxByVariant[key] = (idxByVariant[key] || 0) + 1;
                });
                var seenPerVariant = {};
                html += '<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Phiếu</th><th>Thời gian</th><th>Loại</th><th>Số lượng</th><th>Còn lại</th></tr></thead><tbody>';
                theKhoAll.forEach(function (item, index) {
                    var isFirst = (String(item.variant_id) === initialVariantId);
                    var variantKey = String(item.variant_id);
                    seenPerVariant[variantKey] = (seenPerVariant[variantKey] || 0) + 1;
                    // Row chỉ hiển thị khi data-variant-id khớp variant
                    // đang chọn. Áp dụng 2 cơ chế filter:
                    //   1. Inline style="display:none" cho rows KHÔNG khớp
                    //      (ban đầu selectedVariantId = initialVariantId).
                    //   2. window.__qvStockClickVariant sẽ toggle display
                    //      khi user đổi variant.
                    html += '<tr data-variant-id="' + item.variant_id + '" data-table-for="theKho" ' +
                        'style="' + (isFirst ? '' : 'display:none;') + '">' +
                        '<td>' + seenPerVariant[variantKey] + '</td>' +
                        '<td>' + (item.maPhieu || '-') + '</td>' +
                        '<td>' + (item.thoiGian ? new Date(item.thoiGian).toLocaleString('vi-VN') : '-') + '</td>' +
                        '<td>' + (item.loaiPhieu || '-') + '</td>' +
                        '<td>' + (item.soLuong ?? 0) + '</td>' +
                        '<td>' + (item.soLuongConLai ?? 0) + '</td>' +
                        '</tr>';
                });
                html += '</tbody></table>';
                html += '<div class="text-muted small py-3 qv-empty-state" data-empty-for="theKho" data-empty-variant="' + initialVariantId + '" style="display:none;">Chưa có lịch sử kho cho biến thể này.</div>';
                html += '</div>';
            }
        } else {
            // Fallback: chỉ có dữ liệu 1 variant (cũ)
            if (!theKho.length) {
                html += '<div class="text-muted small py-3 qv-empty-state" data-empty-for="theKho">Chưa có lịch sử kho.</div>';
            } else {
                html += '<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Phiếu</th><th>Thời gian</th><th>Loại</th><th>Số lượng</th><th>Còn lại</th></tr></thead><tbody>';
                theKho.forEach(function (item, index) {
                    html += '<tr data-variant-id="' + initialVariantId + '" data-table-for="theKho">' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + (item.maPhieu || '-') + '</td>' +
                        '<td>' + (item.thoiGian ? new Date(item.thoiGian).toLocaleString('vi-VN') : '-') + '</td>' +
                        '<td>' + (item.loaiPhieu || '-') + '</td>' +
                        '<td>' + (item.soLuong ?? 0) + '</td>' +
                        '<td>' + (item.soLuongConLai ?? 0) + '</td>' +
                        '</tr>';
                });
                html += '</tbody></table></div>';
            }
        }

        html += '</div></div>';

        // ----- Bảng Lô hàng -----
        html += '<div class="col-md-6">';
        html += '<div class="bg-white rounded shadow-sm p-3">';
        html += '<h6 class="fw-semibold mb-2">Lô hàng</h6>';

        if (useFullData) {
            if (!loHangAll.length) {
                html += '<div class="text-muted small py-3 qv-empty-state" data-empty-for="loHang">Không có lô hàng tồn kho.</div>';
            } else {
                // Số thứ tự theo từng variant (xem giải thích ở bảng Thẻ kho)
                var loIdxByVariant = {};
                var loSeenPerVariant = {};
                loHangAll.forEach(function (item) {
                    var key = String(item.variant_id);
                    loIdxByVariant[key] = (loIdxByVariant[key] || 0) + 1;
                });
                html += '<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Mã lô</th><th>Hạn dùng</th><th>Số lượng</th><th>Giá</th></tr></thead><tbody>';
                loHangAll.forEach(function (item, index) {
                    var isFirst = (String(item.variant_id) === initialVariantId);
                    var variantKey = String(item.variant_id);
                    loSeenPerVariant[variantKey] = (loSeenPerVariant[variantKey] || 0) + 1;
                    html += '<tr data-variant-id="' + item.variant_id + '" data-table-for="loHang" ' +
                        'style="' + (isFirst ? '' : 'display:none;') + '">' +
                        '<td>' + loSeenPerVariant[variantKey] + '</td>' +
                        '<td>' + (item.maLo || '-') + '</td>' +
                        '<td>' + (item.hanSuDung ? new Date(item.hanSuDung).toLocaleDateString('vi-VN') : '-') + '</td>' +
                        '<td>' + (item.soLuongConLai ?? 0) + '</td>' +
                        '<td>' + formatMoney(item.giaNhap ?? 0) + ' đ</td>' +
                        '</tr>';
                });
                html += '</tbody></table>';
                html += '<div class="text-muted small py-3 qv-empty-state" data-empty-for="loHang" data-empty-variant="' + initialVariantId + '" style="display:none;">Không có lô hàng tồn kho cho biến thể này.</div>';
                html += '</div>';
            }
        } else {
            if (!loHang.length) {
                html += '<div class="text-muted small py-3 qv-empty-state" data-empty-for="loHang">Không có lô hàng tồn kho.</div>';
            } else {
                html += '<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Mã lô</th><th>Hạn dùng</th><th>Số lượng</th><th>Giá</th></tr></thead><tbody>';
                loHang.forEach(function (item, index) {
                    html += '<tr data-variant-id="' + initialVariantId + '" data-table-for="loHang">' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + (item.maLo || '-') + '</td>' +
                        '<td>' + (item.hanSuDung ? new Date(item.hanSuDung).toLocaleDateString('vi-VN') : '-') + '</td>' +
                        '<td>' + (item.soLuongConLai ?? 0) + '</td>' +
                        '<td>' + formatMoney(item.giaNhap ?? 0) + ' đ</td>' +
                        '</tr>';
                });
                html += '</tbody></table></div>';
            }
        }

        html += '</div></div>';

        html += '</div>'; // close row
        html += '</div>'; // close quickview-stock-root

        return html;
    }

    // ============================================================
    // CLICK HANDLER - Chọn variant trong Tab Kho của Quick View
    // Được gọi từ inline onclick của mỗi nút chọn variant.
    // Nhiệm vụ:
    //   1. Toggle class active/inactive cho nút
    //   2. Ẩn/hiện từng <tr> theo data-variant-id
    //   3. Toggle empty-state (nếu không có dòng nào khớp biến thể)
    // ============================================================
    window.__qvStockClickVariant = function (btn, vid, productId) {
        var root = btn.closest('.quickview-stock-root');
        if (!root) return;

        // 1. Toggle active class cho tất cả nút
        var btns = root.querySelectorAll('.qv-variant-btn');
        btns.forEach(function (b) {
            b.classList.remove('btn-dark', 'text-white');
            b.classList.add('btn-outline-secondary');
        });
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-dark', 'text-white');

        // 2. Ẩn/hiện dòng trong cả 2 bảng theo data-variant-id
        var rows = root.querySelectorAll('tr[data-variant-id]');
        var matchCounts = { theKho: 0, loHang: 0 };
        var targetVid = String(vid);
        rows.forEach(function (tr) {
            var trVid = String(tr.getAttribute('data-variant-id'));
            var matches = (trVid === targetVid);
            tr.style.display = matches ? '' : 'none';
            if (matches) {
                var tableFor = tr.getAttribute('data-table-for');
                if (matchCounts[tableFor] !== undefined) matchCounts[tableFor]++;
            }
        });

        // 3. Toggle empty-state riêng cho từng bảng
        var emptyStates = root.querySelectorAll('.qv-empty-state[data-empty-for]');
        emptyStates.forEach(function (el) {
            var kind = el.getAttribute('data-empty-for');
            if (el.hasAttribute('data-empty-variant')) {
                // Empty-state "theo variant" → ẩn/hiện theo match count
                el.style.display = (matchCounts[kind] === 0) ? '' : 'none';
            } else {
                // Empty-state tổng (toàn bộ bảng rỗng ngay từ đầu) → ẩn
                el.style.display = 'none';
            }
        });

        // 4. Cập nhật data-initial-variant để lần vào lại tab lưu state
        root.setAttribute('data-initial-variant', targetVid);
        // Đẩy sự kiện ra ngoài để các listener khác (nếu có) cũng biết
        root.dispatchEvent(new CustomEvent('qv:variant-changed', {
            detail: { variantId: targetVid, productId: productId },
            bubbles: true
        }));
    };

    window.loadProductStats = async function(productId, forceReload) {
        var detailRow = document.getElementById('productDetailRow' + productId);
        if (!detailRow) return;
        var panel = detailRow.querySelector('.product-detail-panel');
        if (!panel) return;
        var content = panel.querySelector('.product-summary-tab');
        if (!content) return;
        if (panel.dataset.loadedSummary === '1' && !forceReload) return;

        panel.dataset.loadedSummary = '0';
        content.innerHTML = '<div class="d-flex justify-content-center align-items-center py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Đang tải thống kê...</div>';

        try {
            var res = await fetch('/admin/api/san-pham/' + productId + '/thong-ke?days=30');
            if (!res.ok) throw new Error('Không tải được dữ liệu.');
            var json = await res.json();
            if (!json.success) throw new Error(json.message || 'Lỗi API');
            content.innerHTML = buildStatsHtml(productId, json.data);
            panel.dataset.loadedSummary = '1';
        } catch (e) {
            content.innerHTML = '<div class="text-danger">Lỗi tải thống kê: ' + (e.message || 'Không xác định') + '</div>';
        }
    };

    window.loadProductDetail = async function(productId, variantId) {
        window.productDetailCache = window.productDetailCache || {};
        var cached = window.productDetailCache[productId];
        var requestedVariant = variantId ? String(variantId) : null;

        // Cache key strategy:
        //  - Caller yêu cầu variant_id cụ thể → cache theo variantId đó.
        //  - Caller KHÔNG truyền variant_id (Quick View tab Kho) → cache
        //    kết quả variant_id=null (chứa theKhoAll, loHangAll) và dùng
        //    lại cho lần sau nếu chưa có variant_id khác ghi đè.
        if (cached) {
            if (!requestedVariant) {
                // Cached data không có variant_id (bản đầy đủ) → trả luôn
                if (!cached.variantId) return cached.data;
            } else if (String(cached.variantId) === requestedVariant) {
                return cached.data;
            }
        }

        var url = '/admin/api/san-pham/' + productId;
        if (requestedVariant) {
            url += '?variant_id=' + encodeURIComponent(requestedVariant);
        }

        var res;
        try {
            res = await fetch(url);
        } catch (networkErr) {
            throw new Error('Lỗi mạng: ' + networkErr.message);
        }
        if (!res.ok) {
            let bodyText = '';
            try { bodyText = await res.text(); } catch (e) {}
            throw new Error('Không tải được dữ liệu sản phẩm.');
        }
        var json = await res.json();
        if (!json.success) throw new Error(json.message || 'Lỗi API');

        window.productDetailCache[productId] = {
            variantId: requestedVariant || '', // '' = phiên bản đầy đủ
            data: json.data,
        };
        return json.data;
    };

    window.loadProductVariants = async function(productId) {
        var detailRow = document.getElementById('productDetailRow' + productId);
        if (!detailRow) return;
        var panel = detailRow.querySelector('.product-detail-panel');
        if (!panel) return;
        var content = panel.querySelector('.product-variants-tab');
        if (!content) return;

        panel.dataset.loadedVariants = '0';
        content.innerHTML = '<div class="d-flex justify-content-center align-items-center py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Đang tải danh sách biến thể...</div>';

        try {
            var data = await window.loadProductDetail(productId);
            content.innerHTML = buildVariantsHtml(data);
            panel.dataset.loadedVariants = '1';
        } catch (e) {
            content.innerHTML = '<div class="text-danger">Lỗi tải biến thể: ' + (e.message || 'Không xác định') + '</div>';
        }
    };

    window.loadProductStock = async function(productId, variantId) {
        var detailRow = document.getElementById('productDetailRow' + productId);
        if (!detailRow) return;
        var panel = detailRow.querySelector('.product-detail-panel');
        if (!panel) return;
        var content = panel.querySelector('.product-stock-tab');
        if (!content) return;

        // Quick View tab "Kho" hiển thị dữ liệu của TẤT CẢ biến thể
        // (xem buildStockHtml). Luôn re-render khi gọi để đảm bảo
        // state Alpine.js + nút active + filter table được khởi tạo
        // tươi, tránh bug khi user chuyển tab tới lui.
        panel.dataset.loadedStock = '0';
        content.innerHTML = '<div class="d-flex justify-content-center align-items-center py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Đang tải thông tin kho...</div>';

        try {
            // Truyền variant_id nếu có để server trả dữ liệu tập trung cho
            // variant đó (theKho/loHang) — nhưng vẫn trả kèm theKhoAll/...
            // cho frontend dùng khi user đổi biến thể nhanh tại client.
            var data = await window.loadProductDetail(productId, variantId);
            content.innerHTML = buildStockHtml(productId, data);
            panel.dataset.loadedStock = '1';
        } catch (e) {
            content.innerHTML = '<div class="text-danger">Lỗi tải kho: ' + (e.message || 'Không xác định') + '</div>';
        }
    };

    window.switchProductTab = async function(productId, tabKey, variantId) {
        var detailRow = document.getElementById('productDetailRow' + productId);
        if (!detailRow) return;
        var panel = detailRow.querySelector('.product-detail-panel');
        if (!panel) return;
        var buttons = panel.querySelectorAll('[data-tab-key]');
        buttons.forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.tabKey === tabKey);
        });

        var summaryTab = panel.querySelector('.product-summary-tab');
        var variantsTab = panel.querySelector('.product-variants-tab');
        var stockTab = panel.querySelector('.product-stock-tab');
        if (summaryTab) summaryTab.classList.toggle('d-none', tabKey !== 'summary');
        if (variantsTab) variantsTab.classList.toggle('d-none', tabKey !== 'variants');
        if (stockTab) stockTab.classList.toggle('d-none', tabKey !== 'stock');

        if (tabKey === 'summary') {
            await window.loadProductStats(productId, true);
        } else if (tabKey === 'variants') {
            await window.loadProductVariants(productId);
        } else if (tabKey === 'stock') {
            // Quick View tab "Kho" hiển thị dữ liệu của TẤT CẢ biến
            // thể với nút chọn variant (active state + filter table).
            // → KHÔNG truyền variantId: cần fetch với variant_id=null
            // để server trả theKhoAll/loHangAll.
            // → Không dùng cache (đã xử lý trong loadProductDetail).
            panel.dataset.loadedStock = '0';
            await window.loadProductStock(productId);
        }
    };

    // ============================================================
// RECENT ORDERS ROW → MỞ TRANG CHI TIẾT HÓA ĐƠN
// Click bất kỳ đâu trên dòng đơn hàng gần nhất → chuyển sang /admin/hoa-don/{id}
// Dùng event delegation để hoạt động với cả row được render sau bằng AJAX.
// ============================================================
(function() {
    document.addEventListener('click', function(e) {
        var row = e.target.closest('.recent-order-row[data-href]');
        if (!row) return;

        // Nếu click vào link/button bên trong (mở tab mới) thì bỏ qua
        if (e.target.closest('a, button, input, select, textarea')) return;

        var url = row.getAttribute('data-href');
        if (!url) return;

        // Mở trong tab mới nếu giữ Ctrl/Cmd, ngược lại chuyển tab hiện tại
        if (e.ctrlKey || e.metaKey) {
            window.open(url, '_blank');
        } else {
            window.location.href = url;
        }
    });

    // Hiệu ứng phím: Enter trên dòng đơn hàng → cũng mở
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        var active = document.activeElement;
        if (!active || !active.classList || !active.classList.contains('recent-order-row')) return;
        e.preventDefault();
        var url = active.getAttribute('data-href');
        if (url) window.location.href = url;
    });
})();

function formatMoney(num) {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString('vi-VN');
}

;

// ============================================================
// DELETE PRODUCT (from detail panel) - by URL
// ============================================================
window.deleteProductByUrl = async function(deleteUrl, productId, productName) {
    // Validate URL
    if (!deleteUrl || deleteUrl.includes('undefined') || deleteUrl.includes('null')) {
        alert('Lỗi: Không tìm thấy URL xóa sản phẩm. Vui lòng tải lại trang và thử lại.');
        console.error('deleteProductByUrl called with invalid URL:', deleteUrl);
        return;
    }

    var confirmMsg = 'Bạn có chắc muốn xóa sản phẩm "' + (productName || '') + '"?\n\nSản phẩm sẽ được chuyển vào thùng rác và có thể khôi phục.';
    if (!confirm(confirmMsg)) {
        return;
    }

    console.log('DELETE request to:', deleteUrl);

    // Tạo loading state
    var deleteBtn = event?.target?.closest?.('button') || document.activeElement;
    var originalBtnContent = '';
    if (deleteBtn && deleteBtn.innerHTML) {
        originalBtnContent = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }

    try {
        var res = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || document.querySelector('input[name=_token]')?.value || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        console.log('DELETE response status:', res.status);

        var data;
        try {
            data = await res.json();
        } catch (parseErr) {
            data = { success: res.ok, message: res.ok ? 'Đã xóa sản phẩm.' : 'Không thể xóa sản phẩm.' };
        }

        if (res.ok && data.success) {
            // Xóa dòng sản phẩm khỏi bảng mà không reload
            if (productId) {
                var productRow = document.querySelector('tr[data-id="' + productId + '"]');
                var detailRow = document.getElementById('productDetailRow' + productId);

                if (detailRow) {
                    detailRow.remove();
                }
                if (productRow) {
                    productRow.remove();
                }

                // Cập nhật tổng số sản phẩm
                var totalProducts = document.getElementById('totalProducts');
                if (totalProducts) {
                    var currentTotal = parseInt(totalProducts.textContent) || 0;
                    totalProducts.textContent = Math.max(0, currentTotal - 1);
                }
            }

            // Hiển thị thông báo thành công
            alert(data.message || 'Đã xóa sản phẩm thành công.');
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa sản phẩm.') + ' (HTTP ' + res.status + ')');
        }
    } catch (e) {
        console.error('DELETE error:', e);
        alert('Lỗi kết nối: ' + e.message);
    } finally {
        // Khôi phục button
        if (deleteBtn && originalBtnContent) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalBtnContent;
        }
    }
};

// ============================================================
// DELETE PRODUCT (from detail panel) - by ID (legacy)
// ============================================================
window.deleteProduct = async function(productId, productName) {
    // Validate productId
    if (!productId || productId === 'null' || productId === 'undefined') {
        alert('Lỗi: Không tìm thấy ID sản phẩm. Vui lòng tải lại trang và thử lại.');
        console.error('deleteProduct called with invalid productId:', productId);
        return;
    }

    var deleteUrl = '/admin/san-pham/' + productId;
    window.deleteProductByUrl(deleteUrl, productId, productName);
};

// ============================================================
// IMPORT PREVIEW
// ============================================================
(function() {
    var input = document.getElementById('importFileInput');
    if (!input) return;
    input.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;

        var previewSection = document.getElementById('importPreviewSection');
        var previewTable = document.getElementById('importPreviewTable');
        if (!previewTable) return;
        var thead = previewTable.querySelector('thead');
        var tbody = previewTable.querySelector('tbody');

        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                var data = new Uint8Array(e.target.result);
                var workbook = XLSX.read(data, { type: 'array' });
                var firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                var jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

                if (jsonData.length < 2) {
                    alert('File không có dữ liệu hoặc chỉ có header.');
                    return;
                }

                var headers = jsonData[0];
                var rows = jsonData.slice(1, 6);

                thead.innerHTML = '<tr>' + headers.map(function(h) { return '<th>' + (h || '') + '</th>'; }).join('') + '</tr>';
                tbody.innerHTML = rows.map(function(row) {
                    return '<tr>' + headers.map(function(_, i) { return '<td>' + (row[i] !== undefined ? row[i] : '') + '</td>'; }).join('') + '</tr>';
                }).join('');

                if (previewSection) previewSection.classList.remove('d-none');
            } catch(err) {
                alert('Không thể đọc file. Vui lòng đảm bảo file đúng định dạng.');
                console.error(err);
            }
        };
        reader.readAsArrayBuffer(file);
    });
})();

// ============================================================
// DELETE VARIANT (global, accessible from HTML)
// ============================================================
window.deleteVariant = async function(variantId, productId) {
    if (!confirm('Bạn có chắc muốn xóa biến thể này?')) return;
    try {
        var res = await fetch('/admin/api/san-pham/variant/' + variantId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                'Accept': 'application/json',
            },
        });
        var json = await res.json();
        if (json.success) {
            window.location.reload();
        } else {
            alert(json.message || 'Không thể xóa biến thể.');
        }
    } catch(e) {
        alert('Lỗi: ' + e.message);
    }
};

// ============================================================
// SAN-PHAM SUA: Variant + Units management
// ============================================================
(function() {
    var bienTheListEdit = document.getElementById('bienTheListEdit');
    var bienTheEmptyHintEdit = document.getElementById('bienTheEmptyHintEdit');
    var btnThemBienTheEdit = document.getElementById('btnThemBienTheEdit');

    function updateEmptyHintEdit() {
        if (!bienTheListEdit || !bienTheEmptyHintEdit) return;
        bienTheEmptyHintEdit.style.display = bienTheListEdit.querySelectorAll('.variant-card-edit').length === 0 ? '' : 'none';
    }

    window.toggleVariantEdit = function(btn) {
        var card = btn.closest('.variant-card-edit');
        var body = card.querySelector('.variant-edit-body');
        var icon = btn.querySelector('i');
        if (body.style.display === 'none') {
            body.style.display = '';
            icon.className = 'fas fa-chevron-up';
        } else {
            body.style.display = 'none';
            icon.className = 'fas fa-chevron-down';
        }
    };

    window.removeVariantEdit = function(btn) {
        var card = btn.closest('.variant-card-edit');
        if (confirm('Bạn có chắc chắn muốn xóa biến thể này?')) {
            card.remove();
            updateEmptyHintEdit();
        }
    };

    window.addUnitEdit = function(btn) {
        var tbody = btn.closest('.unit-section').querySelector('.unit-tbody-edit');
        var variantCard = btn.closest('.variant-card-edit');
        var variantIdx = variantCard.getAttribute('data-variant-index');
        var unitRows = tbody.querySelectorAll('.unit-row-edit');
        var unitIdx = unitRows.length + 1;

        var emptyRow = tbody.querySelector('.unit-empty-row');
        if (emptyRow) emptyRow.remove();

        var unitHtml = '<tr class="unit-row-edit">' +
            '<td>' +
                '<input type="hidden" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][id]" value="0">' +
                '<input type="text" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][ten_don_vi]" class="form-control form-control-sm" placeholder="VD: Thùng" value="">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][so_luong_san_pham_trong_don_vi]" class="form-control form-control-sm" placeholder="VD: 24" min="1" value="1">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][gia_von_quy_doi]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" value="">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][gia_ban_quy_doi]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" value="">' +
            '</td>' +
            '<td>' +
                '<input type="text" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][ma_vach]" class="form-control form-control-sm" placeholder="Mã vạch" value="">' +
            '</td>' +
            '<td class="text-center align-middle">' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeUnitEdit(this)" title="Xóa đơn vị"><i class="fas fa-minus"></i></button>' +
            '</td>' +
        '</tr>';

        tbody.insertAdjacentHTML('beforeend', unitHtml);
    };

    window.removeUnitEdit = function(btn) {
        var row = btn.closest('tr');
        var tbody = row.closest('tbody');
        row.remove();
        var remaining = tbody.querySelectorAll('.unit-row-edit');
        if (remaining.length === 0) {
            tbody.insertAdjacentHTML('beforeend', '<tr class="unit-row-edit unit-empty-row"><td colspan="6" class="text-center text-muted py-2 small">Chưa có đơn vị nào. Nhấn "Thêm đơn vị" để thêm.</td></tr>');
        }
    };

    if (btnThemBienTheEdit) {
        btnThemBienTheEdit.addEventListener('click', function() {
            if (typeof window.variantCounterEdit === 'undefined') window.variantCounterEdit = 0;
            window.variantCounterEdit++;

            if (bienTheEmptyHintEdit) {
                bienTheEmptyHintEdit.style.display = 'none';
            }

            var variantHtml = '<div class="variant-card-edit mb-3 border rounded p-3 bg-white" data-variant-index="' + window.variantCounterEdit + '">' +
                '<div class="d-flex justify-content-between align-items-center mb-3">' +
                    '<h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-cube me-1"></i>Biến thể #NEW-' + window.variantCounterEdit + '</h6>' +
                    '<div>' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="toggleVariantEdit(this)"><i class="fas fa-chevron-up"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariantEdit(this)"><i class="fas fa-trash"></i></button>' +
                    '</div>' +
                '</div>' +
                '<div class="variant-edit-body">' +
                    '<input type="hidden" name="bien_the[' + window.variantCounterEdit + '][id]" value="0">' +
                    '<div class="row g-3 mb-3">' +
                        '<div class="col-4">' +
                            '<label class="form-label small fw-medium">Tên biến thể</label>' +
                            '<input type="text" name="bien_the[' + window.variantCounterEdit + '][ten_bien_the]" class="form-control form-control-sm" placeholder="VD: Bia lon 330ml" value="">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<label class="form-label small fw-medium">Giá vốn</label>' +
                            '<input type="number" name="bien_the[' + window.variantCounterEdit + '][gia_von]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" value="0">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<label class="form-label small fw-medium">Giá bán</label>' +
                            '<input type="number" name="bien_the[' + window.variantCounterEdit + '][gia_ban]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" value="0">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<label class="form-label small fw-medium">Tồn kho</label>' +
                            '<input type="number" name="bien_the[' + window.variantCounterEdit + '][so_luong_ton]" class="form-control form-control-sm" placeholder="0" min="0" value="0">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<label class="form-label small fw-medium">Mã vạch</label>' +
                            '<input type="text" name="bien_the[' + window.variantCounterEdit + '][ma_vach]" class="form-control form-control-sm" placeholder="Mã vạch" value="">' +
                        '</div>' +
                        '<div class="col-4">' +
                            '<label class="form-label small fw-medium">Thuộc tính (ID, cách nhau dấu phẩy)</label>' +
                            '<input type="text" name="bien_the[' + window.variantCounterEdit + '][thuoc_tinh_ids]" class="form-control form-control-sm" placeholder="VD: 1,2,3" value="">' +
                        '</div>' +
                        '<div class="col-3">' +
                            '<label class="form-label small fw-medium">Hình ảnh</label>' +
                            '<input type="file" name="bien_the[' + window.variantCounterEdit + '][hinh_anh]" class="form-control form-control-sm" accept="image/*">' +
                        '</div>' +
                        '<div class="col-5">' +
                            '<label class="form-label small fw-medium">Trạng thái</label>' +
                            '<div class="form-check form-switch mt-1">' +
                                '<input type="hidden" name="bien_the[' + window.variantCounterEdit + '][trang_thai]" value="0">' +
                                '<input type="checkbox" name="bien_the[' + window.variantCounterEdit + '][trang_thai]" class="form-check-input" id="variantStatusEditNew' + window.variantCounterEdit + '" value="1" checked>' +
                                '<label class="form-check-label small" for="variantStatusEditNew' + window.variantCounterEdit + '">Đang bán</label>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="unit-section mt-2 p-2 rounded" style="background:#f8f9fa;border:1px solid #e9ecef;">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<h6 class="mb-0 small fw-semibold text-secondary"><i class="fas fa-balance-scale me-1"></i> Đơn vị quy đổi</h6>' +
                            '<button type="button" class="btn btn-sm btn-outline-primary" onclick="addUnitEdit(this)"><i class="fas fa-plus me-1"></i>Thêm đơn vị</button>' +
                        '</div>' +
                        '<div class="table-responsive">' +
                            '<table class="table table-sm table-bordered mb-0 bg-white">' +
                                '<thead class="table-light">' +
                                    '<tr>' +
                                        '<th style="width:35%;">Tên đơn vị</th>' +
                                        '<th style="width:12%;">Tỷ lệ QĐ</th>' +
                                        '<th style="width:15%;">Giá vốn QĐ</th>' +
                                        '<th style="width:15%;">Giá bán QĐ</th>' +
                                        '<th style="width:15%;">Mã vạch</th>' +
                                        '<th style="width:8%;"></th>' +
                                    '</tr>' +
                                '</thead>' +
                                '<tbody class="unit-tbody-edit">' +
                                    '<tr class="unit-row-edit unit-empty-row">' +
                                        '<td colspan="6" class="text-center text-muted py-2 small">Chưa có đơn vị nào. Nhấn "Thêm đơn vị" để thêm.</td>' +
                                    '</tr>' +
                                '</tbody>' +
                            '</table>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

            if (bienTheListEdit) {
                bienTheListEdit.insertAdjacentHTML('beforeend', variantHtml);
            }
        });
    }

    var editTrangThaiSwitch = document.getElementById('editTrangThaiSwitch');
    if (editTrangThaiSwitch) {
        editTrangThaiSwitch.addEventListener('change', function() {
            var label = this.nextElementSibling;
            if (label) {
                label.textContent = this.checked ? ' Sản phẩm đang được bán' : ' Sản phẩm ngừng bán';
            }
        });
    }

    var productEditForm = document.getElementById('productEditForm');
    if (productEditForm) {
        productEditForm.addEventListener('submit', function(e) {
            var tenSanPham = productEditForm.querySelector('input[name="ten_san_pham"]');
            var idDanhMuc = productEditForm.querySelector('select[name="id_danh_muc"]');

            var isValid = true;
            var errors = [];

            if (!tenSanPham || !tenSanPham.value.trim()) {
                errors.push('Tên sản phẩm là bắt buộc.');
                isValid = false;
            }
            if (!idDanhMuc || !idDanhMuc.value) {
                errors.push('Vui lòng chọn danh mục.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng kiểm tra lại:\n' + errors.join('\n'));
            }
        });
    }

    updateEmptyHintEdit();
})();

// ============================================================
// SAN-PHAM CAI-DAT: Toggle edit forms
// ============================================================
(function() {
    var loaiSelect = document.getElementById('loaiThuocTinhSelect');
    if (loaiSelect) {
        loaiSelect.addEventListener('change', function() {
            var wrapper = document.getElementById('thuocTinhChaSelectWrapper');
            if (wrapper) wrapper.style.display = this.value === 'con' ? 'block' : 'none';
        });
    }

    window.toggleEditDonVi = function(id) {
        var item = document.getElementById('donvi-item-' + id);
        if (!item) return;
        var viewEl = item.querySelector('.donvi-view');
        var editEl = item.querySelector('.donvi-edit');
        var actionsEl = item.querySelector('.donvi-actions');

        if (editEl.classList.contains('d-none')) {
            if (viewEl) viewEl.classList.add('d-none');
            if (actionsEl) actionsEl.classList.add('d-none');
            editEl.classList.remove('d-none');
        } else {
            if (viewEl) viewEl.classList.remove('d-none');
            if (actionsEl) actionsEl.classList.remove('d-none');
            editEl.classList.add('d-none');
        }
    };

    window.toggleEditTt = function(id) {
        var item = document.getElementById('tt-item-' + id);
        if (!item) return;
        var viewEl = item.querySelector('.tt-view');
        var editEl = item.querySelector('.tt-edit');
        var actionsEl = item.querySelector('.tt-actions');

        if (editEl.classList.contains('d-none')) {
            if (viewEl) viewEl.classList.add('d-none');
            if (actionsEl) actionsEl.classList.add('d-none');
            editEl.classList.remove('d-none');
        } else {
            if (viewEl) viewEl.classList.remove('d-none');
            if (actionsEl) actionsEl.classList.remove('d-none');
            editEl.classList.add('d-none');
        }
    };
})();

// ============================================================
// UNIT CONVERSION DROPDOWN (san-pham index) - Dynamic Switch
// ============================================================
(function() {
    window.activeUnits = window.activeUnits || {};

    function formatMoneyVN(num) {
        if (num === null || num === undefined || num === '') return '0';
        return Number(num).toLocaleString('vi-VN');
    }

    function recalcRow(row, unitObj) {
        if (!row) return;
        var isChild = row.classList.contains('variant-child-row');
        var baseDonvi = row.getAttribute('data-base-donvi') || '';
        var baseGia = parseFloat(row.getAttribute('data-base-gia')) || 0;
        var baseTonKho = parseFloat(row.getAttribute('data-base-tonkho')) || 0;
        var baseMaHang = row.getAttribute('data-base-mahang') || '';
        var baseMaVach = row.getAttribute('data-base-mavach') || '';
        var baseTrangThai = row.getAttribute('data-base-trangthai') === '1';

        var showDonvi, showGia, showTonKho, showMaHang, showMaVach, showTrangThai;
        if (!unitObj) {
            showDonvi = baseDonvi || '—';
            showGia = baseGia;
            showTonKho = baseTonKho;
            showMaHang = baseMaHang || '—';
            showMaVach = baseMaVach || '—';
            showTrangThai = baseTrangThai;
        } else {
            var tyLe = parseFloat(unitObj.ty_le) || 1;
            showDonvi = unitObj.ten_don_vi || '—';
            showGia = parseFloat(unitObj.gia_ban) || 0;
            showTonKho = Math.floor(baseTonKho / tyLe);
            showMaHang = unitObj.ma_hang || '—';
            showMaVach = unitObj.ma_vach || '—';
            showTrangThai = baseTrangThai;
        }

        var elDonvi = row.querySelector('.js-donvi');
        if (elDonvi) elDonvi.textContent = showDonvi;

        var elGia = row.querySelector('.js-giaban');
        if (elGia) elGia.textContent = formatMoneyVN(showGia) + ' d';

        var elTonKho = row.querySelector('.js-tonkho');
        if (elTonKho) {
            elTonKho.textContent = showTonKho;
            var tonKhoClass = 'js-tonkho small ';
            if (showTonKho <= 0) tonKhoClass += 'text-danger';
            else if (showTonKho <= (isChild ? 3 : 10)) tonKhoClass += 'text-warning';
            else tonKhoClass += 'text-muted';
            elTonKho.className = tonKhoClass;
        }

        var elMaHang = row.querySelector('.js-mahang');
        if (elMaHang) elMaHang.textContent = 'MH: ' + (showMaHang || '—');

        var elMaVach = row.querySelector('.js-mavach');
        if (elMaVach) elMaVach.textContent = showMaVach && showMaVach !== '—' ? '#' + showMaVach : '—';

        var elTrangThai = row.querySelector('.js-trangthai');
        if (elTrangThai) {
            var badge = '';
            if (!showTrangThai) badge = '<span class="badge bg-danger">Ngừng kinh doanh</span>';
            else if (showTonKho <= 0) badge = '<span class="badge bg-secondary">Hết hàng</span>';
            else if (showTonKho <= (isChild ? 3 : 10)) badge = '<span class="badge bg-warning text-dark">Sắp hết</span>';
            else badge = '<span class="badge bg-success">Còn hàng</span>';
            elTrangThai.innerHTML = badge;
        }

        if (unitObj) row.classList.add('is-unit-switched');
        else row.classList.remove('is-unit-switched');
    }

    window.selectUnitView = function(row, unitObj) {
        if (!row || !unitObj) return;
        var targetId = row.dataset.variantId || row.dataset.productId || row.getAttribute('data-id') || row.id;
        if (!targetId) return;
        window.activeUnits[targetId] = unitObj;
        recalcRow(row, unitObj);
    };

    window.resetUnitView = function(row) {
        if (!row) return;
        var targetId = row.dataset.variantId || row.dataset.productId || row.getAttribute('data-id') || row.id;
        if (targetId) delete window.activeUnits[targetId];
        recalcRow(row, null);
    };

    window.getActiveUnitView = function(row) {
        if (!row) return null;
        var targetId = row.dataset.variantId || row.dataset.productId || row.getAttribute('data-id') || row.id;
        if (!targetId) return null;
        return window.activeUnits[targetId] || null;
    };

    window.toggleUnitDropdown = function(element) {
        if (!element) return;
        var container = element.closest('.unit-dropdown-container');
        if (!container) return;
        var menu = container.querySelector('.unit-dropdown-menu');
        if (!menu) return;
        var isOpen = menu.style.display === 'block';
        document.querySelectorAll('.unit-dropdown-menu').forEach(function(m) { m.style.display = 'none'; });
        document.querySelectorAll('.unit-dropdown-toggle').forEach(function(b) { b.classList.remove('active'); });
        if (!isOpen) {
            menu.style.display = 'block';
            if (element.classList) element.classList.add('active');
        }
    };

    window.selectUnitFromDropdown = function(liEl) {
        if (!liEl) return;
        var row = liEl.closest('tr');
        var raw = liEl.getAttribute('data-unit-obj');
        if (!raw) return;
        var unitObj;
        try { unitObj = JSON.parse(raw); } catch(e) { return; }
        window.selectUnitView(row, unitObj);
        var container = liEl.closest('.unit-dropdown-container');
        if (container) {
            var menu = container.querySelector('.unit-dropdown-menu');
            if (menu) menu.style.display = 'none';
            var btn = container.querySelector('.unit-dropdown-toggle');
            if (btn) btn.classList.add('active');
        }
    };

    window.selectBaseUnit = function(liEl) {
        if (!liEl) return;
        var row = liEl.closest('tr');
        window.resetUnitView(row);
        var container = liEl.closest('.unit-dropdown-container');
        if (container) {
            var menu = container.querySelector('.unit-dropdown-menu');
            if (menu) menu.style.display = 'none';
            var btn = container.querySelector('.unit-dropdown-toggle');
            if (btn) btn.classList.remove('active');
        }
    };

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.unit-dropdown-container')) {
            document.querySelectorAll('.unit-dropdown-menu').forEach(function(m) { m.style.display = 'none'; });
            document.querySelectorAll('.unit-dropdown-toggle').forEach(function(b) { b.classList.remove('active'); });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.unit-dropdown-menu').forEach(function(m) { m.style.display = 'none'; });
            document.querySelectorAll('.unit-dropdown-toggle').forEach(function(b) { b.classList.remove('active'); });
        }
    });
})();

