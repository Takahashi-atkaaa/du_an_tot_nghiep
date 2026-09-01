/* ================================================================
   money-input.js — Utility tự động format ô nhập tiền tệ VNĐ
   ----------------------------------------------------------------
   - Đánh dấu ô nhập tiền bằng class:    class="form-control money-input"
   - Hỗ trợ số thập phân:                data-money-decimals="2"
   - Tự động:
       + Chặn ký tự không phải số khi gõ.
       + Format dấu chấm (.) phân cách hàng nghìn theo chuẩn vi-VN.
       + Dấu phẩy (,) là phân cách thập phân.
       + Bỏ format khi focus để user dễ sửa.
       + Có thể tắt format khi gõ bằng data-money-format-on-input="false".
       + Trước khi submit form: gửi raw number (format chuẩn) lên server.
   - Tái sử dụng cho mọi trang Blade + JS thuần (không qua Vue).
   ================================================================ */
(function () {
    'use strict';

    // ===== Helpers =====

    /**
     * Parse chuỗi VN format thành số
     * VD: "1.000.000" => 1000000
     *     "12,50" => 12.5
     *     "1.234,56" => 1234.56
     */
    function parseMoneyVN(str, decimals) {
        if (str === null || str === undefined || str === '') return null;

        var strVal = String(str).trim();
        if (strVal === '') return null;

        // Tiền nguyên chỉ dùng dấu chấm làm phân cách hàng nghìn.
        if (!decimals || decimals <= 0) {
            var integerPart = strVal.replace(/\D/g, '');
            return integerPart === '' ? 0 : Number(integerPart);
        }

        // Với phần trăm, dấu phẩy là dấu thập phân; dấu chấm là phân cách nghìn.
        var commaIndex = strVal.lastIndexOf(',');
        if (commaIndex >= 0) {
            var commaInteger = strVal.slice(0, commaIndex).replace(/\D/g, '');
            var commaFraction = strVal.slice(commaIndex + 1)
                .replace(/\D/g, '')
                .slice(0, decimals);
            return Number((commaInteger || '0') + (commaFraction ? '.' + commaFraction : ''));
        }

        // Chấp nhận cả số thập phân chuẩn quốc tế khi giá trị được điền sẵn.
        var dotIndex = strVal.lastIndexOf('.');
        if (dotIndex >= 0 && strVal.length - dotIndex - 1 <= decimals) {
            var dotInteger = strVal.slice(0, dotIndex).replace(/\D/g, '');
            var dotFraction = strVal.slice(dotIndex + 1).replace(/\D/g, '');
            return Number((dotInteger || '0') + (dotFraction ? '.' + dotFraction : ''));
        }

        var raw = strVal.replace(/\D/g, '');
        return raw === '' ? 0 : Number(raw);
    }

    /**
     * Format số thành chuỗi VN
     * VD: 1000000 => "1.000.000"
     *     12.5 (decimals=2) => "12,50"
     *     1234.56 (decimals=2) => "1.234,56"
     */
    function formatMoneyVN(num, decimals) {
        if (num === null || num === undefined || num === '') return '';

        var n = Number(num);
        if (isNaN(n)) return '';

        if (decimals && decimals > 0) {
            // Có số thập phân
            // Sử dụng toLocaleString với chuẩn VN
            return n.toLocaleString('vi-VN', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        // Không có thập phân: làm tròn và format
        return Math.round(n).toLocaleString('vi-VN');
    }

    /**
     * Lấy số chữ số thập phân từ attribute
     */
    function getDecimals(el) {
        var raw = el.getAttribute('data-money-decimals');
        var d = parseInt(raw, 10);
        return isNaN(d) ? 0 : d;
    }

    /**
     * Đếm số chữ số trước vị trí cursor
     * Dùng để restore cursor sau khi format
     */
    function countDigitsBeforeCursor(str, cursorPos) {
        var before = str.substring(0, cursorPos);
        var digits = before.replace(/\D/g, '');
        return digits.length;
    }

    /**
     * Tìm vị trí cursor mới sau khi format
     * Dựa vào số chữ số đã đếm
     */
    function findCursorPosition(formattedStr, digitCount) {
        if (digitCount <= 0) return 0;
        var count = 0;
        for (var i = 0; i < formattedStr.length; i++) {
            if (/\d/.test(formattedStr[i])) {
                count++;
                if (count >= digitCount) {
                    return i + 1;
                }
            }
        }
        return formattedStr.length;
    }

    function formatEditableMoney(str, decimals) {
        if (str === '') return '';

        if (decimals > 0 && str.indexOf(',') >= 0) {
            var commaIndex = str.indexOf(',');
            var integerPart = str.slice(0, commaIndex).replace(/\D/g, '') || '0';
            var fractionPart = str.slice(commaIndex + 1)
                .replace(/\D/g, '')
                .slice(0, decimals);

            return Number(integerPart).toLocaleString('vi-VN') + ',' + fractionPart;
        }

        var raw = str.replace(/\D/g, '');
        return raw === '' ? '' : Number(raw).toLocaleString('vi-VN');
    }

    // ===== Bind cho 1 input =====
    function unbindInput(el) {
        var handlers = el.__moneyHandlers;
        if (!handlers) return;

        el.removeEventListener('input', handlers.input);
        el.removeEventListener('focus', handlers.focus);
        el.removeEventListener('blur', handlers.blur);
        delete el.__moneyHandlers;
        el.__moneyBound = false;
    }

    function bindInput(el) {
        if (el.__moneyBound) return; // Tránh bind 2 lần
        // Một số form đổi data-money-decimals và reset cờ cũ trước khi bind.
        // Gỡ listener cũ để hai bộ định dạng không cùng xử lý một lần nhập.
        unbindInput(el);
        el.__moneyBound = true;

        var decimals = getDecimals(el);
        var formatOnInput = el.getAttribute('data-money-format-on-input') !== 'false';

        // Khi gõ, luôn làm sạch dữ liệu trước khi parse.
        var inputHandler = function () {
            var oldValue = el.value;
            var oldCursorPos = el.selectionStart == null ? oldValue.length : el.selectionStart;
            var cleaned = oldValue;

            if (decimals > 0) {
                cleaned = cleaned.replace(/[^\d,]/g, '');
                var parts = cleaned.split(',');
                if (parts.length > 2) {
                    cleaned = parts[0] + ',' + parts.slice(1).join('');
                }
                if (parts.length > 1) {
                    cleaned = parts[0] + ',' + parts[1].slice(0, decimals);
                }
            } else {
                cleaned = cleaned.replace(/\D/g, '');
            }

            var invalidBeforeCursor = (oldValue.slice(0, oldCursorPos).match(/[^\d,]/g) || []).length;
            var cursorAfterClean = Math.max(0, Math.min(cleaned.length, oldCursorPos - invalidBeforeCursor));

            if (cleaned !== oldValue) {
                el.value = cleaned;
            }

            // Giá trị khuyến mãi cần được nhập tự do; format khi blur sẽ tránh con trỏ nhảy.
            if (!formatOnInput || cleaned === '') {
                if (cleaned !== oldValue && document.activeElement === el) {
                    el.setSelectionRange(cursorAfterClean, cursorAfterClean);
                }
                return;
            }

            var formatted = formatEditableMoney(cleaned, decimals);
            var digitsBeforeCursor = countDigitsBeforeCursor(cleaned, cursorAfterClean);
            var commaIndex = cleaned.indexOf(',');
            el.value = formatted;

            if (document.activeElement === el) {
                var newCursorPos = findCursorPosition(formatted, digitsBeforeCursor);
                if (commaIndex >= 0 && cursorAfterClean > commaIndex) {
                    var integerDigits = cleaned.slice(0, commaIndex).replace(/\D/g, '').length;
                    var fractionDigits = Math.max(0, digitsBeforeCursor - integerDigits);
                    newCursorPos = formatted.indexOf(',') + 1 + fractionDigits;
                }
                el.setSelectionRange(newCursorPos, newCursorPos);
            }
        };

        // Khi focus: bỏ format nghìn (giữ lại dấu phẩy nếu có decimals)
        var focusHandler = function () {
            var currentValue = el.value;
            if (currentValue === '') return;
            var num = parseMoneyVN(currentValue, decimals);
            if (num === null) return;

            if (decimals > 0) {
                el.value = String(num).replace('.', ',');
            } else {
                el.value = String(num);
            }
        };

        // Khi blur: format lại đầy đủ
        var blurHandler = function () {
            var currentValue = el.value;
            if (currentValue === '') return;
            var num = parseMoneyVN(currentValue, decimals);
            if (num === null) {
                el.value = '';
                return;
            }
            el.value = formatMoneyVN(num, decimals);
        };

        el.addEventListener('input', inputHandler);
        el.addEventListener('focus', focusHandler);
        el.addEventListener('blur', blurHandler);
        el.__moneyHandlers = {
            input: inputHandler,
            focus: focusHandler,
            blur: blurHandler,
        };
    }

    // ===== Submit form: gửi raw number =====
    function bindForm(form) {
        if (form.__moneyFormBound) return;
        form.__moneyFormBound = true;

        form.addEventListener('submit', function () {
            var inputs = form.querySelectorAll('.money-input');
            inputs.forEach(function (input) {
                var currentValue = input.value;
                if (currentValue === '') return; // Giữ nguyên rỗng

                var decimals = getDecimals(input);
                var num = parseMoneyVN(currentValue, decimals);

                if (num === null) {
                    input.value = '';
                } else {
                    // Gửi số thuần túy lên server
                    // decimals > 0: gửi dạng "1000.50" (dấu chấm chuẩn quốc tế)
                    // decimals = 0: gửi dạng "1000000"
                    input.value = String(num);
                }
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
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) return;
                        if (node.classList && node.classList.contains('money-input')) {
                            bindInput(node);
                        } else if (node.querySelectorAll && node.querySelector('.money-input')) {
                            scan(node);
                        }
                    });
                });
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
