/**
 * SanPhamCreate - State Management cho trang Tạo mới sản phẩm
 * 
 * Pattern: IIFE với Object namespace
 * Không gắn DOM, có thể test độc lập và tái sử dụng
 */

window.SanPhamCreate = (function() {
    'use strict';

    // ============================================================
    // PRIVATE STATE - Trạng thái nội bộ của ứng dụng
    // ============================================================
    const state = {
        basicInfo: {
            name: '',
            category_id: null,
            brand: ''
        },
        attributes: [
            // VD: { name: 'Màu sắc', values: [{id: 1, text: 'Đỏ'}, {id: 2, text: 'Xanh'}] }
        ],
        units: [
            // VD: { name: 'Thùng', rate: 24, barcode: '', price: 24000 }
        ],
        variants: [
            // VD: { variant_name: 'Đỏ - M', attribute_ids: [1, 3], sku: '', price: 0, cost: 0, stock: 0 }
        ]
    };

    // ============================================================
    // HELPER FUNCTIONS - Các hàm tiện ích thuần thuật toán
    // ============================================================

    /**
     * So sánh 2 mảng sau khi sắp xếp
     * Dùng để so sánh 2 mảng attribute_ids có khớp nhau hay không
     * 
     * @param {Array} a - Mảng thứ nhất
     * @param {Array} b - Mảng thứ hai
     * @returns {boolean} - true nếu 2 mảng bằng nhau
     */
    function arraysEqual(a, b) {
        if (a === b) return true;
        if (a == null || b == null) return false;
        if (a.length !== b.length) return false;
        
        const sortedA = [...a].sort(function(x, y) { return x - y; });
        const sortedB = [...b].sort(function(x, y) { return x - y; });
        
        for (let i = 0; i < sortedA.length; i++) {
            if (sortedA[i] !== sortedB[i]) return false;
        }
        return true;
    }

    /**
     * Tính Cartesian Product của nhiều mảng
     * Ví dụ: [[1,2],[3,4]] → [[1,3],[1,4],[2,3],[2,4]]
     * 
     * @param {Array<Array>} arrays - Mảng chứa các mảng con cần nhân
     * @returns {Array<Array>} - Mảng kết quả chứa tất cả các tổ hợp
     */
    function cartesianProduct(arrays) {
        if (!arrays || arrays.length === 0) {
            return [[]];
        }
        
        return arrays.reduce(function(acc, currentArray) {
            const result = [];
            for (const accItem of acc) {
                for (const item of currentArray) {
                    result.push([...accItem, item]);
                }
            }
            return result;
        }, [[]]);
    }

    /**
     * Tạo tên hiển thị cho variant từ mảng attribute_ids
     * Ghép text của các thuộc tính bằng dấu " - "
     * 
     * @param {Array<number>} attrIds - Mảng chứa id của các thuộc tính
     * @returns {string} - Tên variant (VD: "Đỏ - M")
     */
    function buildVariantName(attrIds) {
        if (!attrIds || attrIds.length === 0) {
            return '';
        }
        
        const names = [];
        for (const attr of state.attributes) {
            if (attr && attr.values) {
                for (const val of attr.values) {
                    if (attrIds.includes(val.id)) {
                        names.push(val.text);
                        break;
                    }
                }
            }
        }
        
        return names.join(' - ');
    }

    /**
     * Tìm variant cũ trong state.variants có cùng attribute_ids
     * Dùng để giữ lại dữ liệu khi regenerate variants
     * 
     * @param {Array<number>} targetAttrIds - Mảng attribute_ids cần tìm
     * @returns {Object|null} - Variant cũ nếu tìm thấy, null nếu không
     */
    function findOldVariantByAttrIds(targetAttrIds) {
        for (const variant of state.variants) {
            if (arraysEqual(variant.attribute_ids, targetAttrIds)) {
                return variant;
            }
        }
        return null;
    }

    /**
     * Kiểm tra xem có attribute nào hợp lệ không
     * Hợp lệ = attributes không rỗng VÀ có ít nhất 1 giá trị
     * 
     * @returns {boolean}
     */
    function hasValidAttributes() {
        if (!state.attributes || state.attributes.length === 0) {
            return false;
        }
        
        for (const attr of state.attributes) {
            if (attr && attr.values && attr.values.length > 0) {
                return true;
            }
        }
        
        return false;
    }

    // ============================================================
    // CORE FUNCTIONS - Các hàm chính của state management
    // ============================================================

    /**
     * TASK 1: Cartesian Product với giữ dữ liệu cũ
     * 
     * Hàm này chạy khi attributes thay đổi:
     * 1. Nếu không có attributes hợp lệ → tạo 1 variant mặc định
     * 2. Nếu có attributes → tính Cartesian product để sinh tất cả tổ hợp
     * 3. Với mỗi variant mới, tìm variant cũ cùng attribute_ids để giữ lại data
     * 
     * Ví dụ: attributes = [{name: 'Màu', values: [Đỏ, Xanh]}, {name: 'Size', values: [S, M]}]
     * Kết quả: [Đỏ-S, Đỏ-M, Xanh-S, Xanh-M]
     */
    function regenerateVariants() {
        // Bước 1: Kiểm tra edge case - không có attributes hợp lệ
        if (!hasValidAttributes()) {
            // Nếu attributes rỗng hoặc tất cả đều không có giá trị
            // → tạo 1 variant mặc định trống
            state.variants = [{
                variant_name: '',
                attribute_ids: [],
                sku: '',
                price: 0,
                cost: 0,
                stock: 0
            }];
            return;
        }
        
        // Bước 2: Thu thập mảng giá trị từ mỗi attribute
        // Chỉ lấy attributes có ít nhất 1 giá trị
        const valueArrays = [];
        for (const attr of state.attributes) {
            if (attr && attr.values && attr.values.length > 0) {
                valueArrays.push(attr.values.map(v => v.id));
            }
        }
        
        // Bước 3: Tính Cartesian product
        // Kết quả là mảng các mảng, mỗi mảng chứa 1 tổ hợp id
        const combinations = cartesianProduct(valueArrays);
        
        // Bước 4: Sinh variants mới, giữ lại data cũ nếu có
        const newVariants = [];
        for (const attrIds of combinations) {
            // Tìm variant cũ có cùng attribute_ids
            const oldVariant = findOldVariantByAttrIds(attrIds);
            
            if (oldVariant) {
                // Tìm thấy → giữ lại tất cả data cũ
                newVariants.push({
                    variant_name: oldVariant.variant_name || buildVariantName(attrIds),
                    attribute_ids: [...attrIds],
                    sku: oldVariant.sku || '',
                    price: oldVariant.price || 0,
                    cost: oldVariant.cost || 0,
                    stock: oldVariant.stock || 0
                });
            } else {
                // Không tìm thấy → tạo variant mới với giá trị rỗng
                newVariants.push({
                    variant_name: buildVariantName(attrIds),
                    attribute_ids: [...attrIds],
                    sku: '',
                    price: 0,
                    cost: 0,
                    stock: 0
                });
            }
        }
        
        state.variants = newVariants;
    }

    /**
     * TASK 3: Bulk Apply - Áp dụng giá trị cho tất cả variants
     * 
     * @param {string} field - Tên trường cần cập nhật ('price' | 'cost' | 'stock')
     * @param {number} value - Giá trị mới
     */
    function bulkApply(field, value) {
        const allowedFields = ['price', 'cost', 'stock'];
        
        if (!allowedFields.includes(field)) {
            console.warn('bulkApply: Invalid field - ' + field);
            return;
        }
        
        for (const variant of state.variants) {
            variant[field] = value;
        }
    }

    /**
     * TASK 4a: Build Payload - Xây dựng dữ liệu gửi lên API
     * 
     * Format khớp với backend SanPhamController::store():
     * - bien_the[] là danh sách variants
     * - bien_the[][units][] là đơn vị quy đổi lồng trong từng variant
     * 
     * @returns {Object} - Payload theo format backend yêu cầu
     */
    function buildPayload() {
        // Xác định loại biến thể dựa trên attributes
        const isSingleUnit = state.attributes.length === 0 || 
                            state.attributes.every(a => !a.values || a.values.length === 0);
        const loai_bien_the = isSingleUnit ? 'don_vi' : 'thuoc_tinh';
        
        return {
            ten_san_pham: state.basicInfo.name,
            id_danh_muc: state.basicInfo.category_id,
            thuong_hieu: state.basicInfo.brand || '',
            mo_ta: '',
            trang_thai: 1,
            loai_bien_the: loai_bien_the,
            bien_the: state.variants.map(function(v) {
                return {
                    ten_bien_the: v.variant_name || '',
                    ma_hang: v.sku || '',
                    ma_vach: '',
                    gia_von: parseFloat(v.cost) || 0,
                    gia_ban: parseFloat(v.price) || 0,
                    so_luong_ton: parseInt(v.stock) || 0,
                    dinh_muc_toi_thieu: 0,
                    thuoc_tinh_ids: JSON.stringify(v.attribute_ids || []),
                    units: state.units.map(function(u) {
                        return {
                            ten_don_vi: u.name || '',
                            ty_le_quy_doi: parseInt(u.rate) || 1,
                            gia_von_quy_doi: 0,
                            gia_ban_quy_doi: parseFloat(u.price) || 0,
                            ma_vach: u.barcode || null
                        };
                    })
                };
            })
        };
    }

    /**
     * TASK 4b: Validate and Submit - Kiểm tra dữ liệu và chuẩn bị submit
     * 
     * @returns {Object} - { success: boolean, errors: string[], payload?: Object }
     */
    function validateAndSubmit() {
        const errors = [];
        
        // Validate thông tin cơ bản
        if (!state.basicInfo.name || state.basicInfo.name.trim() === '') {
            errors.push('Tên sản phẩm không được để trống');
        }
        
        if (!state.basicInfo.category_id) {
            errors.push('Vui lòng chọn danh mục');
        }
        
        // Validate đơn vị quy đổi
        for (let i = 0; i < state.units.length; i++) {
            const u = state.units[i];
            const unitLabel = 'Đơn vị ' + (i + 1) + (u.name ? ' (' + u.name + ')' : '');
            
            if (!u.name || u.name.trim() === '') {
                errors.push(unitLabel + ': Tên đơn vị không được để trống');
            }
            
            if (!u.rate || parseInt(u.rate) < 1) {
                errors.push(unitLabel + ': Tỷ lệ quy đổi phải >= 1');
            }
            
            const priceValue = parseFloat(u.price);
            if (u.price === '' || u.price === null || u.price === undefined || isNaN(priceValue) || priceValue < 0) {
                errors.push(unitLabel + ': Giá bán quy đổi phải >= 0');
            }
        }
        
        // Nếu có lỗi, trả về false kèm danh sách lỗi
        if (errors.length > 0) {
            return { 
                success: false, 
                errors: errors 
            };
        }
        
        // Validate thành công, trả về payload
        return { 
            success: true, 
            payload: buildPayload() 
        };
    }

    // ============================================================
    // PUBLIC API - Các hàm để tương tác với state từ bên ngoài
    // ============================================================

    /**
     * Lấy toàn bộ state hiện tại
     * @returns {Object} - Copy của state
     */
    function getState() {
        return JSON.parse(JSON.stringify(state));
    }

    /**
     * Lấy state thô (reference)
     * @returns {Object} - Reference đến state
     */
    function getStateRef() {
        return state;
    }

    /**
     * Cập nhật thông tin cơ bản
     * @param {Object} info - { name?, category_id?, brand? }
     */
    function setBasicInfo(info) {
        if (info.name !== undefined) state.basicInfo.name = info.name;
        if (info.category_id !== undefined) state.basicInfo.category_id = info.category_id;
        if (info.brand !== undefined) state.basicInfo.brand = info.brand;
    }

    /**
     * Thêm một attribute mới
     * @param {Object} attr - { name: string, values: Array }
     */
    function addAttribute(attr) {
        state.attributes.push({
            name: attr.name || '',
            values: attr.values || []
        });
        regenerateVariants();
    }

    /**
     * Cập nhật tên attribute
     * @param {number} idx - Index của attribute
     * @param {string} name - Tên mới
     */
    function updateAttributeName(idx, name) {
        if (state.attributes[idx]) {
            state.attributes[idx].name = name;
        }
    }

    /**
     * Xóa một attribute
     * @param {number} idx - Index của attribute cần xóa
     */
    function removeAttribute(idx) {
        if (idx >= 0 && idx < state.attributes.length) {
            state.attributes.splice(idx, 1);
            regenerateVariants();
        }
    }

    /**
     * Thêm giá trị cho attribute
     * @param {number} attrIdx - Index của attribute
     * @param {string} text - Text hiển thị
     */
    function addAttributeValue(attrIdx, text) {
        if (!state.attributes[attrIdx]) return;
        
        const values = state.attributes[attrIdx].values;
        // Tạo id tạm (dựa trên thời gian + index để tránh trùng lặp)
        const id = Date.now() + (values.length * 1000);
        
        values.push({ id: id, text: text });
        regenerateVariants();
    }

    /**
     * Xóa giá trị của attribute
     * @param {number} attrIdx - Index của attribute
     * @param {number} valueIdx - Index của giá trị
     */
    function removeAttributeValue(attrIdx, valueIdx) {
        if (!state.attributes[attrIdx]) return;
        
        const values = state.attributes[attrIdx].values;
        if (valueIdx >= 0 && valueIdx < values.length) {
            values.splice(valueIdx, 1);
            regenerateVariants();
        }
    }

    /**
     * Thêm đơn vị quy đổi
     * @param {Object} unit - { name, rate, barcode, price }
     */
    function addUnit(unit) {
        state.units.push({
            name: unit.name || '',
            rate: unit.rate || 1,
            barcode: unit.barcode || '',
            price: unit.price || 0
        });
    }

    /**
     * Cập nhật đơn vị quy đổi
     * @param {number} idx - Index của đơn vị
     * @param {Object} data - { name?, rate?, barcode?, price? }
     */
    function updateUnit(idx, data) {
        if (!state.units[idx]) return;
        
        if (data.name !== undefined) state.units[idx].name = data.name;
        if (data.rate !== undefined) state.units[idx].rate = data.rate;
        if (data.barcode !== undefined) state.units[idx].barcode = data.barcode;
        if (data.price !== undefined) state.units[idx].price = data.price;
    }

    /**
     * Xóa đơn vị quy đổi
     * @param {number} idx - Index của đơn vị
     */
    function removeUnit(idx) {
        if (idx >= 0 && idx < state.units.length) {
            state.units.splice(idx, 1);
        }
    }

    /**
     * Cập nhật variant
     * @param {number} idx - Index của variant
     * @param {Object} data - { variant_name?, sku?, price?, cost?, stock? }
     */
    function updateVariant(idx, data) {
        if (!state.variants[idx]) return;
        
        if (data.variant_name !== undefined) state.variants[idx].variant_name = data.variant_name;
        if (data.sku !== undefined) state.variants[idx].sku = data.sku;
        if (data.price !== undefined) state.variants[idx].price = data.price;
        if (data.cost !== undefined) state.variants[idx].cost = data.cost;
        if (data.stock !== undefined) state.variants[idx].stock = data.stock;
    }

    /**
     * Reset toàn bộ state về ban đầu
     */
    function reset() {
        state.basicInfo = { name: '', category_id: null, brand: '' };
        state.attributes = [];
        state.units = [];
        state.variants = [{
            variant_name: '',
            attribute_ids: [],
            sku: '',
            price: 0,
            cost: 0,
            stock: 0
        }];
    }

    // ============================================================
    // DOM BINDING - Gắn state vào form tạo sản phẩm trên view
    // ============================================================

    /**
     * Tìm modal tạo sản phẩm. Cho phép override id qua data-attribute trên body.
     * Trả về null nếu không có modal (vd: trang không phải san-pham).
     */
    function findCreateModal() {
        // Ưu tiên 1: id chuẩn #addProductModal
        let modal = document.getElementById('addProductModal');
        if (modal) return modal;

        // Ưu tiên 2: id custom qua body[data-create-modal-id]
        const customId = document.body && document.body.dataset.createModalId;
        if (customId) {
            modal = document.getElementById(customId);
            if (modal) return modal;
        }

        return null;
    }

    /**
     * Bind DOM inputs vào state, validate trước khi submit form.
     *
     * Lưu ý: file san-pham.js cũ đã quản lý variants/units trong modal bằng
     * DOM manipulation. Hàm này CHỈ chịu trách nhiệm:
     *   1. Sync basic info (ten_san_pham, id_danh_muc, thuong_hieu)
     *   2. Validate trước khi submit form
     *   3. Inject hidden inputs bien_the[]/units[] theo format backend
     *
     * Nếu sau này muốn san-pham.js gọi sang SanPhamCreate API (addAttribute,
     * bulkApply, ...) thì có thể mở rộng thêm.
     */
    function bindCreateForm() {
        const modal = findCreateModal();
        if (!modal) return false;

        const form = modal.querySelector('form');
        if (!form) return false;

        // ---- Basic Info inputs (nếu có) ----
        const nameInput = modal.querySelector('input[name="ten_san_pham"]');
        const categorySelect = modal.querySelector('select[name="id_danh_muc"]');
        const brandInput = modal.querySelector('input[name="thuong_hieu"]');

        if (nameInput) {
            nameInput.addEventListener('input', function(e) {
                setBasicInfo({ name: e.target.value });
            });
            // Set giá trị ban đầu nếu có (vd: old value từ validation fail)
            if (nameInput.value) setBasicInfo({ name: nameInput.value });
        }
        if (categorySelect) {
            categorySelect.addEventListener('change', function(e) {
                const v = parseInt(e.target.value);
                setBasicInfo({ category_id: isNaN(v) ? null : v });
            });
            if (categorySelect.value) {
                const v = parseInt(categorySelect.value);
                if (!isNaN(v)) setBasicInfo({ category_id: v });
            }
        }
        if (brandInput) {
            brandInput.addEventListener('input', function(e) {
                setBasicInfo({ brand: e.target.value });
            });
            if (brandInput.value) setBasicInfo({ brand: brandInput.value });
        }

        // ---- Submit handler ----
        // Lấy danh sách variant cards hiện có trong DOM (do san-pham.js render)
        // và đắp vào state để buildPayload có dữ liệu.
        form.addEventListener('submit', function(e) {
            syncVariantsFromDom(modal);

            const result = validateAndSubmit();
            if (!result.success) {
                e.preventDefault();
                alert(result.errors.join('\n'));
                return false;
            }

            injectPayloadIntoForm(form, result.payload);
            return true;
        });

        return true;
    }

    /**
     * Đọc các variant card đã render trong DOM (san-pham.js quản lý) và đắp vào state.variants.
     * Tương thích với cấu trúc hiện tại: .variant-card có data-variant-idx và input name="bien_the[i][...]".
     */
    function syncVariantsFromDom(modal) {
        const form = modal.querySelector('form');
        if (!form) return;

        const cards = form.querySelectorAll('.variant-card');
        const variants = [];

        cards.forEach(function(card) {
            function getVal(selector) {
                const el = card.querySelector(selector);
                return el ? el.value : '';
            }
            const tenBienThe = getVal('input[name*="[ten_bien_the]"]');
            const maHang = getVal('input[name*="[ma_hang]"]');
            const giaVon = getVal('input[name*="[gia_von]"]');
            const giaBan = getVal('input[name*="[gia_ban]"]');
            const soLuong = getVal('input[name*="[so_luong_ton]"]');
            const thuocTinhIdsRaw = getVal('input[name*="[thuoc_tinh_ids]"]');

            let attrIds = [];
            try {
                attrIds = JSON.parse(thuocTinhIdsRaw || '[]');
                if (!Array.isArray(attrIds)) attrIds = [];
            } catch (_) {
                attrIds = [];
            }

            variants.push({
                variant_name: tenBienThe,
                attribute_ids: attrIds,
                sku: maHang,
                cost: parseFloat(giaVon) || 0,
                price: parseFloat(giaBan) || 0,
                stock: parseInt(soLuong) || 0
            });
        });

        // Nếu form chưa có variant card nào (chưa thêm biến thể), giữ nguyên state cũ
        if (variants.length > 0) {
            state.variants = variants;
        }
    }

    /**
     * Inject payload dưới dạng hidden inputs vào form, theo format backend đọc.
     */
    function injectPayloadIntoForm(form, payload) {
        // Xóa hidden inputs cũ do inject trước
        form.querySelectorAll('input[data-injected="payload"]').forEach(function(el) {
            el.remove();
        });

        function addHidden(name, value) {
            if (value === undefined || value === null) return;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            input.dataset.injected = 'payload';
            form.appendChild(input);
        }

        // Basic info (chỉ inject nếu input tương ứng không tồn tại trong form)
        if (!form.querySelector('input[name="id_danh_muc"]') && payload.id_danh_muc) {
            addHidden('id_danh_muc', payload.id_danh_muc);
        }
        if (!form.querySelector('input[name="thuong_hieu"]') && payload.thuong_hieu) {
            addHidden('thuong_hieu', payload.thuong_hieu);
        }
        if (!form.querySelector('input[name="loai_bien_the"]')) {
            addHidden('loai_bien_the', payload.loai_bien_the || 'thuoc_tinh');
        }

        // Variants
        (payload.bien_the || []).forEach(function(bt, i) {
            addHidden('bien_the[' + i + '][ten_bien_the]', bt.ten_bien_the || '');
            addHidden('bien_the[' + i + '][ma_hang]', bt.ma_hang || '');
            addHidden('bien_the[' + i + '][gia_von]', bt.gia_von || 0);
            addHidden('bien_the[' + i + '][gia_ban]', bt.gia_ban || 0);
            addHidden('bien_the[' + i + '][so_luong_ton]', bt.so_luong_ton || 0);
            addHidden('bien_the[' + i + '][thuoc_tinh_ids]', bt.thuoc_tinh_ids || '[]');

            // Units lồng trong variant
            (bt.units || []).forEach(function(u, j) {
                addHidden('bien_the[' + i + '][units][' + j + '][ten_don_vi]', u.ten_don_vi || '');
                addHidden('bien_the[' + i + '][units][' + j + '][ty_le_quy_doi]', u.ty_le_quy_doi || 1);
                addHidden('bien_the[' + i + '][units][' + j + '][gia_ban_quy_doi]', u.gia_ban_quy_doi || 0);
                addHidden('bien_the[' + i + '][units][' + j + '][ma_vach]', u.ma_vach || '');
            });
        });
    }

    // Tự động bind khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindCreateForm);
    } else {
        bindCreateForm();
    }

    // ============================================================
    // EXPORT PUBLIC API
    // ============================================================
    return {
        // Getters
        getState: getState,
        getStateRef: getStateRef,
        
        // Basic Info
        setBasicInfo: setBasicInfo,
        
        // Attributes
        addAttribute: addAttribute,
        updateAttributeName: updateAttributeName,
        removeAttribute: removeAttribute,
        addAttributeValue: addAttributeValue,
        removeAttributeValue: removeAttributeValue,
        
        // Units
        addUnit: addUnit,
        updateUnit: updateUnit,
        removeUnit: removeUnit,
        
        // Variants
        regenerateVariants: regenerateVariants,
        updateVariant: updateVariant,
        
        // Bulk operations
        bulkApply: bulkApply,
        
        // Submit
        buildPayload: buildPayload,
        validateAndSubmit: validateAndSubmit,
        
        // Utility
        reset: reset,

        // DOM binding
        bindCreateForm: bindCreateForm,
        injectPayloadIntoForm: injectPayloadIntoForm,
        syncVariantsFromDom: syncVariantsFromDom,
        findCreateModal: findCreateModal,

        // Expose helpers for testing
        _cartesianProduct: cartesianProduct,
        _arraysEqual: arraysEqual,
        _buildVariantName: buildVariantName
    };
})();
