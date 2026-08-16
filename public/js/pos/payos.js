/* ============================================================
 *  PayOS Module – POS
 *  File: public/js/pos/payos.js
 *  Phụ thuộc (do pos_moi.blade.php cung cấp):
 *      - bootstrap (Modal)
 *      - showToast(msg, type)
 *      - closePaidInvoiceTab()  (gọi sau khi tạo link PayOS thành công)
 *      - loadProducts()         (làm mới danh sách sản phẩm)
 *      - document.querySelector('meta[name="csrf-token"]').content
 *      - <meta name="payos-create-url">  ...> route('payos.create')
 *      - <meta name="payos-pending-url"> ...> route('nhan-vien.ban-hang.don-cho-thanh-toan')
 * ============================================================ */

(function () {
    'use strict';

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]').content;
    const urlCreate = () => document.querySelector('meta[name="payos-create-url"]').content;
    const urlPending = () => document.querySelector('meta[name="payos-pending-url"]').content;

    /* ----------------------------------------------------------
     * 1. Tạo link thanh toán PayOS cho hóa đơn vừa thanh toán
     * -------------------------------------------------------- */
    async function redirectToPayOS(hoaDonId) {
    try {
        const res = await fetch(urlCreate(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ hoa_don_id: hoaDonId }),
        });

        const data = await res.json();

        if (!res.ok || !data.success || !data.checkout_url) {
            showToast(data.message || 'Không tạo được link PayOS!', 'error');
            return;
        }

        closePaidInvoiceTab();
        loadProducts();
        window.open(data.checkout_url, '_blank');
    } catch (error) {
        console.error(error);
        showToast('Lỗi khi tạo link PayOS!', 'error');
    }
}

    /* ----------------------------------------------------------
     * 2. Mở modal "Đơn chờ thanh toán PayOS"
     * -------------------------------------------------------- */
    async function openDonChoPayOS() {
        const modalEl = document.getElementById('donChoPayOSModal');
        if (!modalEl) return;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        await loadDonChoPayOS();
    }

    /* ----------------------------------------------------------
     * 3. Tải danh sách đơn đang chờ thanh toán PayOS
     * -------------------------------------------------------- */
    async function loadDonChoPayOS() {
        const box = document.getElementById('donChoPayOSList');
        box.innerHTML = `<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>`;

        try {
            const res = await fetch(urlPending(), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });
            const json = await res.json();

            if (!res.ok || !json.success) {
                box.innerHTML = `<div class="text-center text-danger py-4">Không tải được danh sách.</div>`;
                return;
            }

            const items = json.data || [];
            if (items.length === 0) {
                box.innerHTML = `<div class="text-center text-muted py-5"><i class="fas fa-inbox"></i><p class="mt-2 mb-0">Không có đơn nào đang chờ thanh toán.</p></div>`;
                return;
            }

            const fmt = (n) => new Intl.NumberFormat('vi-VN').format(Number(n || 0)) + ' đ';

            box.innerHTML = items.map((it) => {
                const ten = it.ten_khach_hang ? it.ten_khach_hang : 'Khách lẻ';
                const sdt = it.so_dien_thoai ? ' - ' + it.so_dien_thoai : '';
                const reopenBtn = it.has_payos
                    ? `<button class="btn btn-warning btn-sm" onclick="reopenPayOSQR(${it.hoa_don_id}, '${(it.ma_hoa_don || '#' + it.hoa_don_id).replace(/'/g, "\\'")}')">
                           <i class="fas fa-qrcode"></i> Mở lại QR
                       </button>`
                    : `<span class="badge bg-secondary">Chưa có QR PayOS</span>`;
                return `
                    <div class="d-flex justify-content-between align-items-center border-bottom px-3 py-2">
                        <div>
                            <div class="fw-bold">#${it.hoa_don_id} - ${ten}${sdt}</div>
                            <small class="text-muted">${it.ma_hoa_don || ''}</small>
                            <div class="text-success fw-bold">${fmt(it.khach_can_tra)}</div>
                        </div>
                        <div class="text-end">
                            ${reopenBtn}
                        </div>
                    </div>
                `;
            }).join('');
        } catch (err) {
            console.error(err);
            box.innerHTML = `<div class="text-center text-danger py-4">Lỗi kết nối máy chủ.</div>`;
        }
    }

    /* ----------------------------------------------------------
     * 4. Mở lại QR PayOS cho 1 hóa đơn cụ thể
     * -------------------------------------------------------- */
    async function reopenPayOSQR(hoaDonId, maHoaDon) {
        try {
            const res = await fetch(urlCreate(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ hoa_don_id: hoaDonId }),
            });

            const data = await res.json();

            if (!res.ok || !data.success || !data.checkout_url) {
                showToast(data.message || 'Không mở được QR PayOS!', 'error');
                return;
            }

            window.open(data.checkout_url, '_blank');
            showToast('Đã mở lại QR PayOS cho ' + maHoaDon, 'success');
        } catch (err) {
            console.error(err);
            showToast('Lỗi khi mở lại QR PayOS!', 'error');
        }
    }

    /* ----------------------------------------------------------
     *  Expose ra window để dùng từ inline onclick trong Blade
     * -------------------------------------------------------- */
    window.redirectToPayOS = redirectToPayOS;
    window.openDonChoPayOS = openDonChoPayOS;
    window.loadDonChoPayOS = loadDonChoPayOS;
    window.reopenPayOSQR = reopenPayOSQR;
})();
