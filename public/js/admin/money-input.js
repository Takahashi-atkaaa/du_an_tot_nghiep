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
        // #region agent log
        fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:53',message:'bindInput called',data:{alreadyBound:!!el.__moneyBound,elementName:el.name,elementValue:el.value},timestamp:Date.now(),hypothesisId:'B'})}).catch(()=>{});
        // #endregion
        if (el.__moneyBound) return; // Tránh bind 2 lần (VD: sau khi DOM thay đổi)
        el.__moneyBound = true;

        var decimals = getDecimals(el);

        // Khi gõ: format liên tục
        el.addEventListener('input', function (e) {
            // #region agent log
            var cursorPos = el.selectionStart; var cursorEnd = el.selectionEnd;
            fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:62',message:'input event - before format',data:{elementName:el.name,valueBefore:el.value,cursorStart:cursorPos,cursorEnd:cursorEnd,decimals:decimals},timestamp:Date.now(),hypothesisId:'A',runId:'post-fix'})}).catch(()=>{});
            // #endregion
            
            var oldValue = el.value;
            var oldCursorPos = el.selectionStart;
            
            var num = parseMoneyVN(el.value);
            if (num === 0 && el.value.trim() === '') {
                el.value = '';
            } else {
                var newValue = formatMoneyVN(num, decimals);
                
                // Tính số ký tự phân cách trước cursor trong chuỗi cũ
                var beforeCursor = oldValue.substring(0, oldCursorPos);
                var separatorsBeforeOld = (beforeCursor.match(/[.,]/g) || []).length;
                
                // Tính số ký tự phân cách trong chuỗi mới
                var digitsBeforeCursor = beforeCursor.replace(/\D/g, '').length;
                var newBeforeCursor = newValue.substring(0, newValue.length);
                var digitsCount = 0;
                var newCursorPos = 0;
                
                for (var i = 0; i < newValue.length; i++) {
                    if (/\d/.test(newValue[i])) {
                        digitsCount++;
                    }
                    if (digitsCount >= digitsBeforeCursor) {
                        newCursorPos = i + 1;
                        break;
                    }
                }
                
                el.value = newValue;
                
                // Restore cursor position
                if (newCursorPos > 0 && newCursorPos <= newValue.length) {
                    el.setSelectionRange(newCursorPos, newCursorPos);
                }
            }
            // #region agent log
            fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:95',message:'input event - after format',data:{elementName:el.name,valueAfter:el.value,newCursorPos:el.selectionStart},timestamp:Date.now(),hypothesisId:'A',runId:'post-fix'})}).catch(()=>{});
            // #endregion
        });

        // Khi focus: bỏ dấu chấm để dễ sửa
        el.addEventListener('focus', function () {
            // #region agent log
            fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:75',message:'focus event',data:{elementName:el.name,valueBefore:el.value},timestamp:Date.now(),hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            var num = parseMoneyVN(el.value);
            el.value = num === 0 ? '' : String(num);
            // #region agent log
            fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:79',message:'focus event - after unformat',data:{elementName:el.name,valueAfter:el.value},timestamp:Date.now(),hypothesisId:'D'})}).catch(()=>{});
            // #endregion
        });

        // Khi blur: format lại lần cuối
        el.addEventListener('blur', function () {
            // #region agent log
            fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:86',message:'blur event',data:{elementName:el.name,valueBefore:el.value},timestamp:Date.now(),hypothesisId:'D'})}).catch(()=>{});
            // #endregion
            var num = parseMoneyVN(el.value);
            el.value = num === 0 ? '' : formatMoneyVN(num, decimals);
            // #region agent log
            fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:90',message:'blur event - after format',data:{elementName:el.name,valueAfter:el.value},timestamp:Date.now(),hypothesisId:'D'})}).catch(()=>{});
            // #endregion
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
        // #region agent log
        fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:109',message:'MoneyInput init called',data:{},timestamp:Date.now(),hypothesisId:'B'})}).catch(()=>{});
        // #endregion
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
                            // #region agent log
                            fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:121',message:'MutationObserver detected money-input added',data:{nodeName:node.name},timestamp:Date.now(),hypothesisId:'B'})}).catch(()=>{});
                            // #endregion
                            bindInput(node);
                            needScan = true;
                        } else if (node.querySelectorAll && node.querySelector('.money-input')) {
                            // #region agent log
                            fetch('http://127.0.0.1:7249/ingest/61cdda37-75e2-47ee-9a7a-608a4741bbba',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'51a869'},body:JSON.stringify({sessionId:'51a869',location:'money-input.js:127',message:'MutationObserver detected container with money-input',data:{},timestamp:Date.now(),hypothesisId:'B'})}).catch(()=>{});
                            // #endregion
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