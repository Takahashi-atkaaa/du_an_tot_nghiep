/* ================================================================
   money-input.js — Utility tự động format ô nhập tiền tệ VNĐ
   ----------------------------------------------------------------
   - Đánh dấu ô nhập tiền bằng class:    class="form-control money-input"
   - Hỗ trợ số thập phân:                data-money-decimals="2"
   - Tự động:
       + Chặn ký tự không phải số khi gõ.
       + Format dấu chấm (.) phân cách hàng nghìn theo chuẩn vi-VN.
       + Bỏ dấu chấm khi focus để user dễ sửa.
       + Trước khi submit form: gửi raw number (bỏ dấu chấm) lên server.
   - Tái sử dụng cho mọi trang Blade + JS thuần (không qua Vue).
   ================================================================ */
(function () {
    'use strict';

    // ===== Helpers =====
    function parseMoneyVN(str) {
        if (str === null || str === undefined) return 0;
        // Loại bỏ mọi ký tự không phải số (kể cả dấu chấm, phẩy, khoảng trắng).
        var raw = String(str).replace(/\D/g, '');
        if (raw === '') return 0;
        return parseInt(raw, 10);
    }

    function formatMoneyVN(num, decimals) {
        var n = Number(num) || 0;
        if (decimals && decimals > 0) {
            // Giữ đúng số chữ số thập phân (mặc định 2 cho %).
            var factor = Math.pow(10, decimals);
            n = Math.round(n * factor) / factor;
            return n.toLocaleString('vi-VN', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }
        // Mặc định: VNĐ nguyên, không thập phân.
        return Math.round(n).toLocaleString('vi-VN');
    }

    function getDecimals(el) {
        var raw = el.getAttribute('data-money-decimals');
        var d = parseInt(raw, 10);
        return isNaN(d) ? 0 : d;
    }

    // ===== Bind cho 1 input =====
    function bindInput(el) {
        if (el.__moneyBound) return; // Tránh bind 2 lần (VD: sau khi DOM thay đổi)
        el.__moneyBound = true;

        var decimals = getDecimals(el);

        // Khi gõ: format liên tục
        el.addEventListener('input', function () {
            var num = parseMoneyVN(el.value);
            if (num === 0 && el.value.trim() === '') {
                el.value = '';
            } else {
                el.value = formatMoneyVN(num, decimals);
            }
        });

        // Khi focus: bỏ dấu chấm để dễ sửa
        el.addEventListener('focus', function () {
            var num = parseMoneyVN(el.value);
            el.value = num === 0 ? '' : String(num);
        });

        // Khi blur: format lại lần cuối
        el.addEventListener('blur', function () {
            var num = parseMoneyVN(el.value);
            el.value = num === 0 ? '' : formatMoneyVN(num, decimals);
        });
    }

    // ===== Submit form: gửi raw number =====
    function bindForm(form) {
        if (form.__moneyFormBound) return;
        form.__moneyFormBound = true;

        form.addEventListener('submit', function () {
            var inputs = form.querySelectorAll('.money-input');
            inputs.forEach(function (input) {
                var num = parseMoneyVN(input.value);
                input.value = String(num); // Raw integer — không có dấu chấm
            });
        }, true); // capture: chạy trước handler khác
    }

    // ===== Scan & bind =====
    function scan(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('.money-input').forEach(bindInput);
        scope.querySelectorAll('form').forEach(function (form) {
            if (form.querySelector('.money-input')) bindForm(form);
        });
    }

    // ===== Expose cho code khác dùng =====
    window.MoneyInput = {
        format: formatMoneyVN,
        parse: parseMoneyVN,
        scan: scan,
        bind: bindInput
    };

    // ===== Auto-init khi DOM ready =====
    function init() {
        scan(document);

        // Theo dõi DOM thay đổi (cho nội dung load bằng AJAX / template engine)
        // Tránh observe toàn bộ <body> vì sẽ chạy quá nhiều; chỉ observe subtree
        // của <main class="main-content"> và các vùng chính.
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function (mutations) {
                var needScan = false;
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) return;
                        if (node.classList && node.classList.contains('money-input')) {
                            bindInput(node);
                            needScan = true;
                        } else if (node.querySelectorAll && node.querySelector('.money-input')) {
                            scan(node);
                            needScan = true;
                        }
                    });
                });
                if (needScan) scan(document);
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();