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
})();

// ============================================================
// QR SCANNER (san-pham index)
// ============================================================
(function() {
    var startQrScanBtn = document.getElementById('startQrScanBtn');
    var stopQrScanBtn = document.getElementById('stopQrScanBtn');
    var qrScannerModal = document.getElementById('qrScannerModal');
    var searchKeywordInput = document.getElementById('searchKeywordInput');
    var qrScannerElementId = 'qrScanner';
    var html5QrCode = null;
    var qrScannerActive = false;

    window.startQrScanner = function() {
        if (qrScannerActive) return;

        html5QrCode = new Html5Qrcode(qrScannerElementId);
        var config = { fps: 10, qrbox: 250 };

        Html5Qrcode.getCameras().then(function(cameras) {
            if (cameras && cameras.length) {
                var cameraId = cameras[0].id;
                html5QrCode.start(cameraId, config, function(qrCodeMessage) {
                    if (searchKeywordInput) {
                        searchKeywordInput.value = qrCodeMessage;
                    }
                    var modalInstance = bootstrap.Modal.getInstance(qrScannerModal);
                    if (modalInstance) modalInstance.hide();
                    window.stopQrScanner();
                    var form = document.querySelector('form[action*="admin/san-pham"]');
                    if (form) form.submit();
                }, function(errorMessage) {
                    console.debug('QR scan error', errorMessage);
                }).then(function() {
                    qrScannerActive = true;
                }).catch(function(err) {
                    console.error('Không thể khởi động QR scanner', err);
                    alert('Không thể khởi động camera để quét mã vạch. Vui lòng kiểm tra quyền truy cập camera.');
                });
            } else {
                alert('Không tìm thấy camera phù hợp để quét mã vạch.');
            }
        }).catch(function(err) {
            console.error('Lỗi lấy camera', err);
            alert('Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập thiết bị.');
        });
    };

    window.stopQrScanner = function() {
        if (!qrScannerActive || !html5QrCode) return;
        html5QrCode.stop().then(function() {
            html5QrCode.clear();
            qrScannerActive = false;
        }).catch(function(err) {
            console.error('Lỗi dừng QR scanner', err);
        });
    };

    if (startQrScanBtn) {
        startQrScanBtn.addEventListener('click', function() {
            var modal = new bootstrap.Modal(qrScannerModal);
            modal.show();
            window.startQrScanner();
        });
    }

    if (stopQrScanBtn) {
        stopQrScanBtn.addEventListener('click', function() {
            var modal = bootstrap.Modal.getInstance(qrScannerModal);
            if (modal) modal.hide();
            window.stopQrScanner();
        });
    }

    if (qrScannerModal) {
        qrScannerModal.addEventListener('hidden.bs.modal', function() {
            window.stopQrScanner();
        });
    }
})();

// ============================================================
// PRODUCT DETAIL DRAWER (san-pham index)
// ============================================================
(function() {
    var drawer = document.getElementById('productDetailDrawer');
    var drawerBody = document.getElementById('drawerBody');
    var drawerEditBtn = document.getElementById('drawerEditBtn');

    var _drawerController = null;
    var _drawerRequestId = 0;

    window.toggleVariants = function(productId) {
        var btn = document.getElementById('expandBtn' + productId);
        var rows = document.querySelectorAll('[id^="variantRow' + productId + '_"]');
        var isExpanded = btn && btn.classList.contains('expanded');

        if (isExpanded) {
            rows.forEach(function(row) { row.style.display = 'none'; });
            if (btn) {
                btn.classList.remove('expanded');
                var icon = btn.querySelector('i');
                if (icon) icon.style.transform = '';
            }
        } else {
            rows.forEach(function(row) { row.style.display = ''; });
            if (btn) {
                btn.classList.add('expanded');
                var icon = btn.querySelector('i');
                if (icon) icon.style.transform = 'rotate(90deg)';
            }
        }
    };

    var productTableBody = document.getElementById('productTableBody');
    if (productTableBody) {
        productTableBody.addEventListener('click', function(e) {
            var row = e.target.closest('.product-parent-row, .variant-child-row, .unit-child-row, tr[data-id]');
            if (!row) return;
            var productId = row.dataset.productId || row.dataset.id;
            var targetId = row.dataset.targetId || row.dataset.variantId || productId;
            var rowType = row.dataset.rowType || 'goc';
            var unitId = row.dataset.unitId || '';
            var isMaster = row.dataset.isMaster || '0';
            if (productId) window.openProductDrawer(productId, targetId, rowType, unitId, isMaster);
        });
    }

    window.openProductDrawer = async function(productId, targetId, rowType, unitId, isMaster) {
        if (targetId === undefined) { targetId = productId; rowType = 'goc'; }
        if (isMaster === undefined) { isMaster = '0'; }
        console.log('[Drawer] openProductDrawer called', { productId, targetId, rowType, unitId, isMaster });

        var modal = new bootstrap.Offcanvas(drawer);
        drawerBody.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="min-height:300px;">' +
            '<div class="text-center">' +
                '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                '<p class="text-muted mb-0">Đang tải...</p>' +
            '</div>' +
        '</div>';
        if (drawerEditBtn) drawerEditBtn.href = '/admin/san-pham/' + productId + '/edit';
        window.currentDrawerProductId = productId;
        modal.show();

        // Abort any in-flight request before starting a new one
        if (_drawerController) _drawerController.abort();
        _drawerController = new AbortController();
        var myRequestId = ++_drawerRequestId;

        try {
            var apiUrl = '/admin/api/san-pham/' + productId;
            var queryParts = [];
            if (targetId && String(targetId) !== String(productId)) {
                queryParts.push('variant_id=' + encodeURIComponent(targetId));
            }
            if (unitId) {
                queryParts.push('unit_id=' + encodeURIComponent(unitId));
            }
            if (isMaster === '1') {
                queryParts.push('is_master=1');
            }
            if (queryParts.length) {
                apiUrl += '?' + queryParts.join('&');
            }
            var res = await fetch(apiUrl, { signal: _drawerController.signal });
            var json = await res.json();
            if (myRequestId !== _drawerRequestId) return;
            if (!json.success) {
                drawerBody.innerHTML = '<div class="p-4 text-center text-danger">' + (json.message || 'Khong tim thay san pham.') + '</div>';
                return;
            }
            renderDrawerContent(json.data, targetId, rowType);
        } catch(e) {
            if (e.name === 'AbortError') return;
            drawerBody.innerHTML = '<div class="p-4 text-center text-danger">Lỗi tải dữ liệu: ' + e.message + '</div>';
        }
    };

    window.confirmDeleteFromDrawer = function() {
        if (!window.currentDrawerProductId) return;
        if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/san-pham/' + window.currentDrawerProductId;
        form.innerHTML = '<input name="_token" value="' + (document.querySelector('meta[name=csrf-token]')?.content || '') + '"><input name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    };

    function formatMoney(num) {
        if (num === null || num === undefined) return '0';
        return Number(num).toLocaleString('vi-VN');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function loaiPhieuLabel(loai) {
        var map = {
            'nhap': '<span class="badge bg-success">Nhập</span>',
            'xuat': '<span class="badge bg-danger">Xuất</span>',
            'ban': '<span class="badge bg-primary">Bán</span>',
            'tra': '<span class="badge bg-warning text-dark">Trả</span>',
        };
        return map[loai?.toLowerCase()] || '<span class="badge bg-secondary">' + (loai || '-') + '</span>';
    }

    function renderDrawerContent(data, targetId, rowType) {
        var sp = data.product || {};
        var allVariants = data.allVariants || [];
        var variant = data.variant || {};
        var units = data.units || [];
        var theKho = data.theKho || [];
        var loHang = data.loHang || [];
        var hasMultipleVariants = data.hasMultipleVariants || false;
        var masterSummary = data.masterSummary || null;
        var isMaster = data.isMaster || false;

        // === Tìm đúng variant để hiển thị trên Header ===
        var displayVariant = variant;

        if (targetId && String(targetId) !== String(sp.id)) {
            var found = allVariants.find(function(v) { return String(v.id) === String(targetId); });
            if (found) displayVariant = found;
        }

        // Lấy units đúng của variant đang hiển thị
        var displayUnits = displayVariant && displayVariant.units
            ? displayVariant.units
            : units;

        // Tìm đơn vị tương ứng với variant đang hiển thị
        var displayUnit = null;

        if (data.selectedUnit && data.selectedUnit.id) {
            displayUnit = data.selectedUnit;
        }

        if (!displayUnit && displayVariant && displayVariant.don_vi_id) {
            displayUnit = displayUnits.find(function(u) { return String(u.id) === String(displayVariant.don_vi_id); });
        }

        // Xác định hiển thị dựa trên la_don_vi và isMaster
        var isLaDonVi = displayVariant && displayVariant.la_don_vi;
        var tenDonViHienThi;
        var tenBienTheHienThi;

        if (isLaDonVi) {
            tenDonViHienThi = displayVariant.ten_don_vi || '-';
            tenBienTheHienThi = '-';
        } else {
            tenDonViHienThi = displayUnit ? displayUnit.ten_don_vi : (units.length > 0 ? (units[0].ten_don_vi || '-') : '-');
            tenBienTheHienThi = displayVariant.ten_bien_the || '-';
        }

        var selectedUnitBadge = '';
        if (rowType === 'quy_doi' && displayUnit) {
            tenDonViHienThi = displayUnit.ten_don_vi;
            selectedUnitBadge = '<span class="badge bg-info ms-2" style="font-size:0.65rem;"><i class="fas fa-cube me-1"></i>Đơn vị quy đổi</span>';
        }

        // === Xác định giá trị hiển thị dựa trên isMaster ===
        var maVachHienThi;
        var giaBanHienThi;
        var giaVonHienThi;
        var tonKhoHienThi;

        if (isMaster) {
            // TRẠNG THÁI A: MASTER PRODUCT (Dòng Cha) - Hiển thị thông tin tổng hợp
            maVachHienThi = '<span class="text-muted fst-italic">Nhiều SKU</span>';
            giaBanHienThi = masterSummary && masterSummary.gia_ban_min !== null
                ? (masterSummary.gia_ban_min === masterSummary.gia_ban_max
                    ? formatMoney(masterSummary.gia_ban_min)
                    : formatMoney(masterSummary.gia_ban_min) + ' - ' + formatMoney(masterSummary.gia_ban_max))
                : '-';
            giaVonHienThi = masterSummary && masterSummary.gia_von_min !== null
                ? (masterSummary.gia_von_min === masterSummary.gia_von_max
                    ? formatMoney(masterSummary.gia_von_min)
                    : formatMoney(masterSummary.gia_von_min) + ' - ' + formatMoney(masterSummary.gia_von_max))
                : '-';
            tonKhoHienThi = masterSummary ? masterSummary.tong_ton_kho : 0;
            tenBienTheHienThi = '<span class="text-muted fst-italic">Nhiều biến thể</span>';
            tenDonViHienThi = '<span class="text-muted fst-italic">Nhiều đơn vị</span>';
        } else {
            // TRẠNG THÁI B: VARIANT CỤ THỂ - Hiển thị thông tin variant
            maVachHienThi = displayUnit ? (displayUnit.ma_vach || '-') : (displayVariant.ma_vach || '-');
            giaBanHienThi = displayUnit ? displayUnit.gia_ban_quy_doi : displayVariant.gia_ban;
            giaVonHienThi = displayUnit ? displayUnit.gia_von_quy_doi : displayVariant.gia_von;
            tonKhoHienThi = displayUnit ? displayUnit.so_luong_ton : displayVariant.so_luong_ton;
        }

        var trangThaiVariant = !displayVariant.trang_thai
            ? '<span class="badge bg-danger">Ngừng bán</span>'
            : ((tonKhoHienThi ?? 0) <= 0
                ? '<span class="badge bg-secondary">Hết hàng</span>'
                : ((displayVariant.dinh_muc_toi_thieu && (tonKhoHienThi ?? 0) <= displayVariant.dinh_muc_toi_thieu)
                    ? '<span class="badge bg-warning text-dark">Sắp hết hàng</span>'
                    : '<span class="badge bg-success">Còn hàng</span>'));

        var thuocTinhLabels = '';
        if (displayVariant.thuoc_tinh_ids && Array.isArray(displayVariant.thuoc_tinh_ids)) {
            var thuocTinhChas = window.thuocTinhChasData || [];
            displayVariant.thuoc_tinh_ids.forEach(function(ttId) {
                var found = thuocTinhChas.find(function(t) { return String(t.id) === String(ttId); });
                if (found) {
                    thuocTinhLabels += '<span class="badge bg-info text-dark me-1">' + found.ten_thuoc_tinh + '</span>';
                }
            });
        }

        var mainHinhAnh = displayVariant.hinh_anh
            ? '<img src="/' + displayVariant.hinh_anh + '" class="img-fluid rounded" alt="' + (sp.ten_san_pham || '') + '" style="max-height:220px; object-fit:contain; background:#f8f9fa;">'
            : '<div class="text-center text-muted py-5 bg-light rounded"><i class="fas fa-image fa-3x"></i><p class="mt-2 mb-0">Không có ảnh</p></div>';

        // === Bảng đơn vị quy đổi (chỉ hiển thị khi KHÔNG phải Master) ===
        var unitsHtml = '';
        if (!isMaster && units.length > 0) {
            unitsHtml = '<div class="mb-3">' +
                '<h6 class="fw-bold mb-2"><i class="fas fa-balance-scale me-1"></i>Đơn vị quy đổi <span class="fw-normal text-muted small">(' + displayUnits.length + ')</span></h6>' +
                '<table class="table table-sm table-bordered mb-0" style="font-size:0.82rem;">' +
                    '<thead class="table-light">' +
                        '<tr>' +
                            '<th>Tên đơn vị</th>' +
                            '<th class="text-center">Tỷ lệ QĐ</th>' +
                            '<th class="text-end">Giá vốn</th>' +
                            '<th class="text-end">Giá bán</th>' +
                            '<th class="text-end">Giá bán sỉ</th>' +
                            '<th>Mã vạch</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';
            displayUnits.forEach(function(unit) {
                unitsHtml += '<tr>' +
                    '<td class="small">' + (unit.ten_don_vi || '-') + '</td>' +
                    '<td class="text-center small">' + (unit.ty_le_quy_doi || '-') + '</td>' +
                    '<td class="text-end small">' + formatMoney(unit.gia_von_quy_doi) + ' d</td>' +
                    '<td class="text-end small fw-bold text-primary">' + formatMoney(unit.gia_ban_quy_doi) + ' d</td>' +
                    '<td class="text-end small">' + formatMoney(unit.gia_ban_si) + ' d</td>' +
                    '<td class="small text-muted">' + (unit.ma_vach || '-') + '</td>' +
                '</tr>';
            });
            unitsHtml += '</tbody></table></div>';
        }

        // === Bảng Thẻ kho (chỉ hiển thị khi KHÔNG phải Master) ===
        var theKhoHtml = '';
        if (!isMaster) {
            if (theKho.length > 0) {
                theKhoHtml = '<div class="table-scroll-wrap">' +
                    '<table class="table table-sm table-hover mb-0">' +
                        '<thead class="table-light">' +
                            '<tr>' +
                                '<th>Mã phiếu</th>' +
                                '<th>Thời gian</th>' +
                                '<th>Loại</th>' +
                                '<th>Lô</th>' +
                                '<th class="text-end">Giá nhập</th>' +
                                '<th class="text-center">SL</th>' +
                                '<th class="text-center">SL còn lại</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>';
                theKho.forEach(function(item) {
                    theKhoHtml += '<tr>' +
                        '<td class="small">' + (item.maPhieu || '-') + '</td>' +
                        '<td class="small">' + formatDate(item.thoiGian) + '</td>' +
                        '<td>' + loaiPhieuLabel(item.loaiPhieu) + '</td>' +
                        '<td class="small">' + (item.maLo || '-') + '</td>' +
                        '<td class="small text-end">' + formatMoney(item.gia) + ' d</td>' +
                        '<td class="small text-center">' + (item.soLuong ?? '-') + '</td>' +
                        '<td class="small text-center">' + (item.soLuongConLai ?? '-') + '</td>' +
                    '</tr>';
                });
                theKhoHtml += '</tbody></table></div>';
            } else {
                theKhoHtml = '<p class="text-muted text-center py-3 mb-0 small">Chưa có dữ liệu thẻ kho.</p>';
            }
        }

        // === Bảng Lô hàng (chỉ hiển thị khi KHÔNG phải Master) ===
        var loHangHtml = '';
        if (!isMaster) {
            if (loHang.length > 0) {
                loHangHtml = '<table class="table table-sm table-hover mb-0">' +
                    '<thead class="table-light">' +
                        '<tr>' +
                            '<th>Số lô</th>' +
                            '<th>Hạn sử dụng</th>' +
                            '<th class="text-end">SL nhập</th>' +
                            '<th class="text-end">SL còn lại</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';
                loHang.forEach(function(item) {
                    var isExpired = item.hanSuDung && new Date(item.hanSuDung) < new Date();
                    loHangHtml += '<tr>' +
                        '<td class="small">' + (item.maLo || '-') + '</td>' +
                        '<td class="small ' + (isExpired ? 'text-danger' : '') + '">' + formatDate(item.hanSuDung) + ' ' + (isExpired ? '<i class="fas fa-exclamation-circle"></i>' : '') + '</td>' +
                        '<td class="small text-end">' + (item.so_luong ?? '-') + '</td>' +
                        '<td class="small text-end">' + (item.soLuongConLai ?? '-') + '</td>' +
                    '</tr>';
                });
                loHangHtml += '</tbody></table>';
            } else {
                loHangHtml = '<p class="text-muted text-center py-3 mb-0 small">Chưa có lô hàng.</p>';
            }
        }

        // === Bảng biến thể (CHỉ hiển thị khi LÀ Master) ===
        var variantsTableHtml = '';
        if (isMaster && hasMultipleVariants) {
            variantsTableHtml = '<div class="mb-3">' +
                '<h6 class="fw-bold mb-2"><i class="fas fa-boxes me-1"></i>Danh sách biến thể <span class="badge bg-secondary ms-1" style="font-size:0.7rem;">' + allVariants.length + '</span></h6>' +
                '<div class="table-responsive" style="max-height:250px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;">' +
                '<table class="table table-sm table-bordered table-hover mb-0" style="font-size:0.8rem;">' +
                    '<thead class="table-light sticky-top">' +
                        '<tr>' +
                            '<th>Tên biến thể</th>' +
                            '<th>Mã hàng</th>' +
                            '<th>Mã vạch</th>' +
                            '<th class="text-end">Giá vốn</th>' +
                            '<th class="text-end">Giá bán</th>' +
                            '<th class="text-end">Tồn kho</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';
            allVariants.forEach(function(vt) {
                var vtTonKho = vt.so_luong_ton || 0;
                var vtTonBadge = vtTonKho > 0 ? 'bg-success' : 'bg-secondary';
                var thuocTinhLabels = '';
                if (vt.thuoc_tinh_ids && Array.isArray(vt.thuoc_tinh_ids)) {
                    vt.thuoc_tinh_ids.forEach(function(ttId) {
                        var found = (window.thuocTinhChasData || []).find(function(t) { return String(t.id) === String(ttId); });
                        if (found) {
                            thuocTinhLabels += '<span class="badge bg-light text-dark border" style="font-size:0.6rem;">' + found.ten_thuoc_tinh + '</span> ';
                        }
                    });
                }
                var vtTen = vt.ten_bien_the
                    ? vt.ten_bien_the + (thuocTinhLabels ? '<br>' + thuocTinhLabels : '')
                    : (thuocTinhLabels ? thuocTinhLabels : '<span class="text-muted fst-italic">Mặc định</span>');

                variantsTableHtml += '<tr>' +
                    '<td class="small">' + vtTen + '</td>' +
                    '<td class="small text-muted">' + (vt.ma_hang || '-') + '</td>' +
                    '<td class="small text-muted">' + (vt.ma_vach || '-') + '</td>' +
                    '<td class="text-end small">' + formatMoney(vt.gia_von) + ' đ</td>' +
                    '<td class="text-end small fw-bold text-primary">' + formatMoney(vt.gia_ban) + ' đ</td>' +
                    '<td class="text-end"><span class="badge ' + vtTonBadge + '" style="font-size:0.65rem;">' + vtTonKho + '</span></td>' +
                '</tr>';
            });
            variantsTableHtml += '</tbody></table></div></div>';
        }

        // === Render HTML ===
        drawerBody.innerHTML = '<div class="p-3">' +
            '<div class="row g-3 mb-3">' +
                '<div class="col-4">' + mainHinhAnh + '</div>' +
                '<div class="col-8">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                        '<div>' +
                            '<h5 class="fw-bold mb-1">' + (sp.ten_san_pham || '-') + selectedUnitBadge + '</h5>' +
                            '<p class="text-muted small mb-1">#' + (displayVariant.ma_hang || displayVariant.ma_vach || displayVariant.id || sp.id || '-') + '</p>' +
                            trangThaiVariant +
                        '</div>' +
                        '<div class="text-end">' +
                            '<p class="fw-bold text-primary mb-0" style="font-size:1.4rem;">' + (typeof giaBanHienThi === 'string' ? giaBanHienThi : formatMoney(giaBanHienThi)) + ' đ</p>' +
                            '<p class="text-muted small mb-0">Giá vốn: ' + (typeof giaVonHienThi === 'string' ? giaVonHienThi : formatMoney(giaVonHienThi)) + ' đ</p>' +
                        '</div>' +
                    '</div>' +
                    '<hr>' +
                    '<div class="row g-2 small">' +
                        '<div class="col-6"><strong>Danh mục:</strong> ' + (sp.danh_muc?.ten_danh_muc || '-') + '</div>' +
                        '<div class="col-6"><strong>Thương hiệu:</strong> ' + (sp.thuong_hieu || '-') + '</div>' +
                        '<div class="col-6"><strong>Biến thể:</strong> ' + tenBienTheHienThi + '</div>' +
                        '<div class="col-6"><strong>Đơn vị:</strong> ' + tenDonViHienThi + '</div>' +
                        '<div class="col-6"><strong>Mã vạch:</strong> ' + maVachHienThi + '</div>' +
                        '<div class="col-6"><strong>Tồn kho:</strong> ' + (tonKhoHienThi ?? 0) + '</div>' +
                        (!isMaster ? '<div class="col-6"><strong>Định mức:</strong> ' + (displayVariant.dinh_muc_toi_thieu ?? 0) + '</div>' : '') +
                    '</div>' +
                    (!isMaster && thuocTinhLabels ? '<div class="mt-2">' + thuocTinhLabels + '</div>' : '') +
                '</div>' +
            '</div>' +

            (sp.mo_ta ? '<div class="mb-3"><h6 class="fw-bold mb-2"><i class="fas fa-align-left me-1"></i>Mô tả</h6><div class="bg-light rounded p-2 small text-muted" style="white-space:pre-line;">' + sp.mo_ta + '</div></div>' : '') +

            // === Bảng biến thể: CHỉ hiển thị khi LÀ MASTER ===
            variantsTableHtml +

            // === Bảng đơn vị quy đổi: CHỉ hiển thị khi KHÔNG phải Master ===
            unitsHtml +

            // === Bảng Thẻ kho: CHỉ hiển thị khi KHÔNG phải Master ===
            (!isMaster ? '<div class="mb-3">' : '') +
            (!isMaster ? '<h6 class="fw-bold mb-2"><i class="fas fa-history me-1"></i>Thẻ kho <span class="fw-normal text-muted small">(' + theKho.length + ')</span></h6>' : '') +
            (!isMaster ? theKhoHtml : '') +
            (!isMaster ? '</div>' : '') +

            // === Bảng Lô hàng: CHỉ hiển thị khi KHÔNG phải Master ===
            (!isMaster ? '<div class="mb-3">' : '') +
            (!isMaster ? '<h6 class="fw-bold mb-2"><i class="fas fa-boxes-stacked me-1"></i>Lô - Hạn sử dụng <span class="fw-normal text-muted small">(' + loHang.length + ')</span></h6>' : '') +
            (!isMaster ? loHangHtml : '') +
            (!isMaster ? '</div>' : '') +

            '<div class="text-muted small border-top pt-2">' +
                '<i class="fas fa-clock me-1"></i>Tạo: ' + formatDate(sp.created_at) + ' | Cập nhật: ' + formatDate(sp.updated_at) +
            '</div>' +
        '</div>';
    }
})();

// ============================================================
// EXPORT EXCEL
// ============================================================
(function() {
    var btn = document.getElementById('btnExportExcel');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var params = new URLSearchParams(window.location.search);
        window.location.href = '/admin/san-pham/export?' + params.toString();
    });
})();

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
            window.openProductDrawer(productId || window.currentDrawerProductId);
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
