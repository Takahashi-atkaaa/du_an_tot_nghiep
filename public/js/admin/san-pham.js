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
            'delete': 'Ban co chac muon xoa ' + checked.length + ' san pham da chon?',
            'activate': 'Bat trang thai cho ' + checked.length + ' san pham?',
            'deactivate': 'Tat trang thai cho ' + checked.length + ' san pham?'
        };

        if (!confirm(messages[action] || 'Xac nhan?')) return;

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
                    console.error('Khong the khoi dong QR scanner', err);
                    alert('Khong the khoi dong camera de quet ma vach. Vui long kiem tra quyen truy cap camera.');
                });
            } else {
                alert('Khong tim thay camera phu hop de quet ma vach.');
            }
        }).catch(function(err) {
            console.error('Loi lay camera', err);
            alert('Khong the truy cap camera. Vui long kiem tra quyen truy cap thiet bi.');
        });
    };

    window.stopQrScanner = function() {
        if (!qrScannerActive || !html5QrCode) return;
        html5QrCode.stop().then(function() {
            html5QrCode.clear();
            qrScannerActive = false;
        }).catch(function(err) {
            console.error('Loi dung QR scanner', err);
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
            var row = e.target.closest('.variant-row, .unit-row, tr[data-id]');
            if (!row) return;
            var id = row.dataset.id || row.dataset.productId;
            if (id) window.openProductDrawer(id);
        });
    }

    window.openProductDrawer = async function(id) {
        var modal = new bootstrap.Offcanvas(drawer);
        drawerBody.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="min-height:300px;">' +
            '<div class="text-center">' +
                '<div class="spinner-border text-primary mb-3" role="status"></div>' +
                '<p class="text-muted mb-0">Dang tai...</p>' +
            '</div>' +
        '</div>';
        if (drawerEditBtn) drawerEditBtn.href = '/admin/san-pham/' + id + '/edit';
        window.currentDrawerProductId = id;
        modal.show();

        try {
            var res = await fetch('/admin/api/san-pham/' + id);
            var json = await res.json();
            if (!json.success) {
                drawerBody.innerHTML = '<div class="p-4 text-center text-danger">' + (json.message || 'Khong tim thay san pham.') + '</div>';
                return;
            }
            renderDrawerContent(json.data);
        } catch(e) {
            drawerBody.innerHTML = '<div class="p-4 text-center text-danger">Loi tai du lieu: ' + e.message + '</div>';
        }
    };

    window.confirmDeleteFromDrawer = function() {
        if (!window.currentDrawerProductId) return;
        if (!confirm('Ban co chac muon xoa san pham nay?')) return;
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
            'nhap': '<span class="badge bg-success">Nhap</span>',
            'xuat': '<span class="badge bg-danger">Xuat</span>',
            'ban': '<span class="badge bg-primary">Ban</span>',
            'tra': '<span class="badge bg-warning text-dark">Tra</span>',
        };
        return map[loai?.toLowerCase()] || '<span class="badge bg-secondary">' + (loai || '-') + '</span>';
    }

    function renderDrawerContent(data) {
        var sp = data.product || {};
        var variant = data.variant || {};
        var units = data.units || [];
        var theKho = data.theKho || [];
        var loHang = data.loHang || [];

        var trangThaiVariant = !variant.trang_thai
            ? '<span class="badge bg-danger">Ngung ban</span>'
            : ((variant.so_luong_ton ?? 0) <= 0
                ? '<span class="badge bg-secondary">Het hang</span>'
                : ((variant.dinh_muc_toi_thieu && (variant.so_luong_ton ?? 0) <= variant.dinh_muc_toi_thieu)
                    ? '<span class="badge bg-warning text-dark">Sap het hang</span>'
                    : '<span class="badge bg-success">Con hang</span>'));

        var thuocTinhLabels = '';
        if (variant.thuoc_tinh_ids && Array.isArray(variant.thuoc_tinh_ids)) {
            var thuocTinhChas = window.thuocTinhChasData || [];
            variant.thuoc_tinh_ids.forEach(function(ttId) {
                var found = thuocTinhChas.find(function(t) { return String(t.id) === String(ttId); });
                if (found) {
                    thuocTinhLabels += '<span class="badge bg-info text-dark me-1">' + found.ten_thuoc_tinh + '</span>';
                }
            });
        }

        var variantHinhAnh = variant.hinh_anh
            ? '<img src="/' + variant.hinh_anh + '" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">'
            : '<div style="width:32px;height:32px;border-radius:4px;background:#eee;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image text-muted" style="font-size:0.6rem;"></i></div>';

        var mainHinhAnh = variant.hinh_anh
            ? '<img src="/' + variant.hinh_anh + '" class="img-fluid rounded" alt="' + (sp.ten_san_pham || '') + '" style="max-height:220px; object-fit:contain; background:#f8f9fa;">'
            : '<div class="text-center text-muted py-5 bg-light rounded"><i class="fas fa-image fa-3x"></i><p class="mt-2 mb-0">Khong co anh</p></div>';

        var unitsHtml = '';
        if (units.length > 0) {
            unitsHtml = '<div class="mb-3">' +
                '<h6 class="fw-bold mb-2"><i class="fas fa-balance-scale me-1"></i>Don vi quy doi <span class="fw-normal text-muted small">(' + units.length + ')</span></h6>' +
                '<table class="table table-sm table-bordered mb-0" style="font-size:0.82rem;">' +
                    '<thead class="table-light">' +
                        '<tr>' +
                            '<th>Ten don vi</th>' +
                            '<th class="text-center">Ty le QD</th>' +
                            '<th class="text-end">Gia von</th>' +
                            '<th class="text-end">Gia ban</th>' +
                            '<th class="text-end">Gia ban si</th>' +
                            '<th>Ma vach</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';
            units.forEach(function(unit) {
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

        var theKhoHtml = '';
        if (theKho.length > 0) {
            theKhoHtml = '<div class="table-scroll-wrap">' +
                '<table class="table table-sm table-hover mb-0">' +
                    '<thead class="table-light">' +
                        '<tr>' +
                            '<th>Ma phieu</th>' +
                            '<th>Thoi gian</th>' +
                            '<th>Loai</th>' +
                            '<th>Lo</th>' +
                            '<th class="text-end">Gia nhap</th>' +
                            '<th class="text-center">SL</th>' +
                            '<th class="text-center">SL con lai</th>' +
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
            theKhoHtml = '<p class="text-muted text-center py-3 mb-0 small">Chua co du lieu the kho.</p>';
        }

        var loHangHtml = '';
        if (loHang.length > 0) {
            loHangHtml = '<table class="table table-sm table-hover mb-0">' +
                '<thead class="table-light">' +
                    '<tr>' +
                        '<th>So lo</th>' +
                        '<th>Han su dung</th>' +
                        '<th class="text-end">SL nhap</th>' +
                        '<th class="text-end">SL con lai</th>' +
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
            loHangHtml = '<p class="text-muted text-center py-3 mb-0 small">Chua co lo hang.</p>';
        }

        drawerBody.innerHTML = '<div class="p-3">' +
            '<div class="row g-3 mb-3">' +
                '<div class="col-4">' + mainHinhAnh + '</div>' +
                '<div class="col-8">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                        '<div>' +
                            '<h5 class="fw-bold mb-1">' + (sp.ten_san_pham || '-') + '</h5>' +
                            '<p class="text-muted small mb-1">#' + (variant.ma_hang || variant.ma_vach || variant.id || sp.id || '-') + '</p>' +
                            trangThaiVariant +
                        '</div>' +
                        '<div class="text-end">' +
                            '<p class="fw-bold text-primary mb-0" style="font-size:1.4rem;">' + formatMoney(variant.gia_ban) + ' d</p>' +
                            '<p class="text-muted small mb-0">Gia von: ' + formatMoney(variant.gia_von) + ' d</p>' +
                        '</div>' +
                    '</div>' +
                    '<hr>' +
                    '<div class="row g-2 small">' +
                        '<div class="col-6"><strong>Danh muc:</strong> ' + (sp.danh_muc?.ten_danh_muc || '-') + '</div>' +
                        '<div class="col-6"><strong>Thuong hieu:</strong> ' + (sp.thuong_hieu || '-') + '</div>' +
                        '<div class="col-6"><strong>Ten bien the:</strong> ' + (variant.ten_bien_the || '-') + '</div>' +
                        '<div class="col-6"><strong>Ma vach:</strong> ' + (variant.ma_vach || '-') + '</div>' +
                        '<div class="col-6"><strong>Ton kho:</strong> ' + (variant.so_luong_ton ?? 0) + '</div>' +
                        '<div class="col-6"><strong>Dinh muc:</strong> ' + (variant.dinh_muc_toi_thieu ?? 0) + '</div>' +
                    '</div>' +
                    (thuocTinhLabels ? '<div class="mt-2">' + thuocTinhLabels + '</div>' : '') +
                '</div>' +
            '</div>' +

            (sp.mo_ta ? '<div class="mb-3"><h6 class="fw-bold mb-2"><i class="fas fa-align-left me-1"></i>Mo ta</h6><div class="bg-light rounded p-2 small text-muted" style="white-space:pre-line;">' + sp.mo_ta + '</div></div>' : '') +

            unitsHtml +

            '<div class="mb-3">' +
                '<h6 class="fw-bold mb-2"><i class="fas fa-history me-1"></i>The kho <span class="fw-normal text-muted small">(' + theKho.length + ')</span></h6>' +
                theKhoHtml +
            '</div>' +

            '<div class="mb-3">' +
                '<h6 class="fw-bold mb-2"><i class="fas fa-boxes-stacked me-1"></i>Lo - Han su dung <span class="fw-normal text-muted small">(' + loHang.length + ')</span></h6>' +
                loHangHtml +
            '</div>' +

            '<div class="text-muted small border-top pt-2">' +
                '<i class="fas fa-clock me-1"></i>Tao: ' + formatDate(sp.created_at) + ' | Cap nhat: ' + formatDate(sp.updated_at) +
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
                    alert('File khong co du lieu hoac chi co header.');
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
                alert('Khong the doc file. Vui long dam bao file dung dinh dang.');
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
    if (!confirm('Ban co chac muon xoa bien the nay?')) return;
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
            alert(json.message || 'Khong the xoa bien the.');
        }
    } catch(e) {
        alert('Loi: ' + e.message);
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
        if (confirm('Ban co chan chan muon xoa bien the nay?')) {
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
                '<input type="text" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][ten_don_vi]" class="form-control form-control-sm" placeholder="VD: Thung" value="">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][ty_le_quy_doi]" class="form-control form-control-sm" placeholder="VD: 24" min="1" value="1">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][gia_von_quy_doi]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" value="">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][gia_ban_quy_doi]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" value="">' +
            '</td>' +
            '<td>' +
                '<input type="text" name="bien_the[' + variantIdx + '][units][' + unitIdx + '][ma_vach]" class="form-control form-control-sm" placeholder="Ma vach" value="">' +
            '</td>' +
            '<td class="text-center align-middle">' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeUnitEdit(this)" title="Xoa don vi"><i class="fas fa-minus"></i></button>' +
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
            tbody.insertAdjacentHTML('beforeend', '<tr class="unit-row-edit unit-empty-row"><td colspan="6" class="text-center text-muted py-2 small">Chua co don vi nao. Nhan "Them don vi" de them.</td></tr>');
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
                    '<h6 class="mb-0 fw-semibold text-primary"><i class="fas fa-cube me-1"></i>Bien the #NEW-' + window.variantCounterEdit + '</h6>' +
                    '<div>' +
                        '<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="toggleVariantEdit(this)"><i class="fas fa-chevron-up"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariantEdit(this)"><i class="fas fa-trash"></i></button>' +
                    '</div>' +
                '</div>' +
                '<div class="variant-edit-body">' +
                    '<input type="hidden" name="bien_the[' + window.variantCounterEdit + '][id]" value="0">' +
                    '<div class="row g-3 mb-3">' +
                        '<div class="col-4">' +
                            '<label class="form-label small fw-medium">Ten bien the</label>' +
                            '<input type="text" name="bien_the[' + window.variantCounterEdit + '][ten_bien_the]" class="form-control form-control-sm" placeholder="VD: Bia lon 330ml" value="">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<label class="form-label small fw-medium">Gia von</label>' +
                            '<input type="number" name="bien_the[' + window.variantCounterEdit + '][gia_von]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" value="0">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<label class="form-label small fw-medium">Gia ban</label>' +
                            '<input type="number" name="bien_the[' + window.variantCounterEdit + '][gia_ban]" class="form-control form-control-sm" placeholder="0" min="0" step="0.01" value="0">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<label class="form-label small fw-medium">Ton kho</label>' +
                            '<input type="number" name="bien_the[' + window.variantCounterEdit + '][so_luong_ton]" class="form-control form-control-sm" placeholder="0" min="0" value="0">' +
                        '</div>' +
                        '<div class="col-2">' +
                            '<label class="form-label small fw-medium">Ma vach</label>' +
                            '<input type="text" name="bien_the[' + window.variantCounterEdit + '][ma_vach]" class="form-control form-control-sm" placeholder="Ma vach" value="">' +
                        '</div>' +
                        '<div class="col-4">' +
                            '<label class="form-label small fw-medium">Thuoc tinh (ID, cach nhau dau phay)</label>' +
                            '<input type="text" name="bien_the[' + window.variantCounterEdit + '][thuoc_tinh_ids]" class="form-control form-control-sm" placeholder="VD: 1,2,3" value="">' +
                        '</div>' +
                        '<div class="col-3">' +
                            '<label class="form-label small fw-medium">Hinh anh</label>' +
                            '<input type="file" name="bien_the[' + window.variantCounterEdit + '][hinh_anh]" class="form-control form-control-sm" accept="image/*">' +
                        '</div>' +
                        '<div class="col-5">' +
                            '<label class="form-label small fw-medium">Trang thai</label>' +
                            '<div class="form-check form-switch mt-1">' +
                                '<input type="hidden" name="bien_the[' + window.variantCounterEdit + '][trang_thai]" value="0">' +
                                '<input type="checkbox" name="bien_the[' + window.variantCounterEdit + '][trang_thai]" class="form-check-input" id="variantStatusEditNew' + window.variantCounterEdit + '" value="1" checked>' +
                                '<label class="form-check-label small" for="variantStatusEditNew' + window.variantCounterEdit + '">Dang ban</label>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="unit-section mt-2 p-2 rounded" style="background:#f8f9fa;border:1px solid #e9ecef;">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<h6 class="mb-0 small fw-semibold text-secondary"><i class="fas fa-balance-scale me-1"></i> Don vi quy doi</h6>' +
                            '<button type="button" class="btn btn-sm btn-outline-primary" onclick="addUnitEdit(this)"><i class="fas fa-plus me-1"></i>Them don vi</button>' +
                        '</div>' +
                        '<div class="table-responsive">' +
                            '<table class="table table-sm table-bordered mb-0 bg-white">' +
                                '<thead class="table-light">' +
                                    '<tr>' +
                                        '<th style="width:35%;">Ten don vi</th>' +
                                        '<th style="width:12%;">Ty le QD</th>' +
                                        '<th style="width:15%;">Gia von QD</th>' +
                                        '<th style="width:15%;">Gia ban QD</th>' +
                                        '<th style="width:15%;">Ma vach</th>' +
                                        '<th style="width:8%;"></th>' +
                                    '</tr>' +
                                '</thead>' +
                                '<tbody class="unit-tbody-edit">' +
                                    '<tr class="unit-row-edit unit-empty-row">' +
                                        '<td colspan="6" class="text-center text-muted py-2 small">Chua co don vi nao. Nhan "Them don vi" de them.</td>' +
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
                label.textContent = this.checked ? ' San pham dang duoc ban' : ' San pham ngung ban';
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
                errors.push('Ten san pham la bat buoc.');
                isValid = false;
            }
            if (!idDanhMuc || !idDanhMuc.value) {
                errors.push('Vui long chon danh muc.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert('Vui long kiem tra lai:\n' + errors.join('\n'));
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
