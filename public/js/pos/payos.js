/* ============================================================
 *  PayOS Module – POS
 *  File: public/js/pos/payos.js
 *  Phụ thuộc (do pos_moi.blade.php cung cấp):
 *      - trang "QR đang chờ" riêng
 *      - showToast(msg, type)
 *      - closePaidInvoiceTab()  (gọi sau khi tạo link PayOS thành công)
 *      - loadProducts()         (làm mới danh sách sản phẩm)
 *      - document.querySelector('meta[name="csrf-token"]').content
 *      - <meta name="payos-create-url">  ...> route('payos.create')
 * ============================================================ */

(function () {
    'use strict';

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]').content;
    const urlCreate = () => document.querySelector('meta[name="payos-create-url"]').content;

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
     * 2. Đi tới trang "QR đang chờ"
     * -------------------------------------------------------- */
    function openDonChoPayOS() {
        window.location.href = '/ban-hang/don-cho-thanh-toan';
    }

    /* ----------------------------------------------------------
     *  Expose ra window để dùng từ inline onclick trong Blade
     * -------------------------------------------------------- */
    window.redirectToPayOS = redirectToPayOS;
    window.openDonChoPayOS = openDonChoPayOS;
})();
