/* Phase 2: Thêm Grid "Hàng cùng loại" với Cartesian product + merge state theo key
 * - Block 1: Thông tin cơ bản + Giá mặc định
 * - Block 2: Đơn vị tính (collapse) + Thuộc tính (collapse)
 * - Block 3: Grid sinh tự động (mỗi row = 1 variant; thuộc tính là cột, đơn vị là cột)
 * - Payload: mỗi row -> bien_the[i]; thuoc_tinh_ids lưu value ids
 */
(function () {
    const { createApp, reactive, ref, watch, computed, onMounted } = Vue;
    const DATA = window.__CREATE_PRODUCT_DATA__ || { danhMucs: [], csrfToken: '' };
    const uid = () => 'id_' + Math.random().toString(36).slice(2, 10);

    // ============================================================
    // DIRECTIVE v-money: format hiển thị tiền tệ VNĐ cho input
    // - input: chỉ giữ chữ số, format dấu chấm ngàn ngay khi gõ.
    // - focus: bỏ dấu chấm để dễ sửa.
    // - blur: format lại, đồng thời đẩy raw number về v-model.
    // - Tương thích với v-model.number (model vẫn là Number khi submit payload).
    // ============================================================
    function vMoneyParse(str) {
        if (str === null || str === undefined) return 0;
        var raw = String(str).replace(/\D/g, '');
        return raw === '' ? 0 : parseInt(raw, 10);
    }
    function vMoneyFormat(num, decimals) {
        var n = Number(num) || 0;
        if (decimals > 0) {
            var factor = Math.pow(10, decimals);
            n = Math.round(n * factor) / factor;
            return n.toLocaleString('vi-VN', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }
        return Math.round(n).toLocaleString('vi-VN');
    }
    function vMoneyDirective(el, binding) {
        if (el.__vMoneyBound) return;
        el.__vMoneyBound = true;

        var decimals = 0;
        if (binding && binding.value && typeof binding.value === 'object') {
            decimals = parseInt(binding.value.decimals, 10) || 0;
        } else if (el.hasAttribute('data-money-decimals')) {
            decimals = parseInt(el.getAttribute('data-money-decimals'), 10) || 0;
        }

        // Lưu raw digits vào __moneyRaw để tránh xung đột với v-model.number.
        // Vue's v-model.number đọc el.value; nếu el.value có dấu chấm -> parseFloat sai.
        // Vì vậy ta để el.value = raw digits lúc input, format lại display ở nextTick.
        Object.defineProperty(el, '__moneyRaw', {
            value: el.value,
            writable: true,
            configurable: true
        });

        // Format giá trị khởi tạo (nếu có sẵn từ v-model)
        if (el.value !== '' && el.value !== null && el.value !== undefined) {
            var initNum = vMoneyParse(el.value);
            el.__moneyRaw = String(initNum);
            el.value = vMoneyFormat(initNum, decimals);
        }

        el.addEventListener('input', function () {
            // Lấy raw digits từ những gì user vừa gõ
            var rawDigits = vMoneyParse(el.value);
            el.__moneyRaw = (rawDigits === 0 && el.value.trim() === '') ? '' : String(rawDigits);
            // Trong cùng tick, Vue v-model.number sẽ đọc el.value để parseFloat.
            // Để Vue parse đúng, ta để el.value là raw digits (không dấu chấm).
            el.value = el.__moneyRaw;
            // Format lại display sau khi Vue đã cập nhật model.
            Vue.nextTick(function () {
                if (document.activeElement === el) return; // đang focus -> không format đè
                var v = parseInt(el.__moneyRaw, 10) || 0;
                if (v > 0) el.value = vMoneyFormat(v, decimals);
            });
        });

        el.addEventListener('focus', function () {
            var v = parseInt(el.__moneyRaw, 10) || 0;
            el.value = v === 0 ? '' : String(v);
        });

        el.addEventListener('blur', function () {
            var v = parseInt(el.__moneyRaw, 10) || 0;
            el.value = v === 0 ? '' : vMoneyFormat(v, decimals);
        });
    }

    // ============ APP ============
    const app = createApp({
        setup() {
            // ------- STATE -------
            const isEditMode = DATA.editMode === true;

            const basicInfo = reactive({
                code: '',
                ten_san_pham: '',
                id_danh_muc: '',
                brand: '',
                mo_ta: '',
                trang_thai: true,
                defaultPrice: 0,
                defaultCost: 0,
                defaultStock: 0,
                defaultMinStock: 0,
                image: null,
                imagePreview: ''
            });

            const unitConfig = reactive({
                baseUnit: '',
                basePrice: 0,
                conversionUnits: []
            });

            // availableAttributes: tất cả thuộc tính cha từ DB để gợi ý trong dropdown
            // VD: [{ id: 5, name: 'Màu sắc', values: [{id:1, label:'Đỏ'},{id:2, label:'Xanh'}] }]
            const availableAttributes = ref(DATA.availableAttributes || []);

            // availableUnits: tất cả đơn vị chuẩn từ bảng danh_muc_don_vi
            // VD: [{ id: 1, name: 'Thùng 24', qty: 24 }, { id: 2, name: 'Thùng 12', qty: 12 }]
            const availableUnits = ref(DATA.availableUnits || []);

            // allUnitOptions: availableUnits + giá trị hiện tại của baseUnit nếu không có trong list
            // Đảm bảo selected value luôn là option hợp lệ dù không có trong DB
            const allUnitOptions = computed(() => {
                const base = availableUnits.value;
                const current = unitConfig.baseUnit.trim();
                if (current && !base.find(u => u.ten_don_vi === current)) {
                    return [...base, { id: '__custom__', ten_don_vi: current }];
                }
                return base;
            });

            // baseUnitInput: text field cho phép nhập đơn vị mới "on-the-fly"
            // Ưu tiên dùng giá trị này thay cho select khi có dữ liệu.
            const baseUnitInput = ref('');

            // Sync ngược: khi user gõ vào baseUnitInput → cập nhật unitConfig.baseUnit
            // để giữ nguyên logic grid hiện tại.
            watch(baseUnitInput, (val) => {
                unitConfig.baseUnit = val;
            });

            const attributesConfig = reactive({
                groups: [] // [{ id, name, values: [{id, label, _isNew}], valueInput, _dropdownOpen, _highlightedIdx }]
            });

            const sectionOpen = reactive({
                info: true,
                units: false,
                attributes: false,
                grid: true
            });

            // ------- STATE: lỗi validation từ server & trạng thái submit -------
            const errors = ref({});      // { fieldName: [msg1, msg2, ...] }
            const generalError = ref(''); // lỗi không gắn với field cụ thể
            const submitting = ref(false);
            const submitHadError = ref(false); // true sau khi Lưu thất bại (để giữ nút disabled)
            const formLoaded = ref(false); // true khi Vue đã khởi tạo xong (initFromProduct hoặc regenerateGrid)

            // hasImage: true khi user da chon file (co trong basicInfo.image)
            const hasImage = computed(() => basicInfo.image !== null);

            // ============================================================
            // YÊU CẦU 2: KIỂM TRA TRÙNG LẶP NHÓM THUỘC TÍNH (FRONTEND)
            // ============================================================
            // Phát hiện khi người dùng tạo 2 nhóm giống nhau (VD: 2 nhóm "Kích thước")
            const duplicateAttrGroups = computed(() => {
                const groups = attributesConfig.groups.filter(g => g.name.trim());
                const seen = new Map();
                const duplicates = [];

                groups.forEach((g, idx) => {
                    const key = g.name.trim();
                    if (seen.has(key)) {
                        duplicates.push({
                            groupName: key,
                            indices: [seen.get(key), idx + 1]
                        });
                    } else {
                        seen.set(key, idx + 1);
                    }
                });

                return duplicates;
            });

            // Computed: thông báo trùng nhóm thuộc tính
            const duplicateAttrGroupWarning = computed(() => {
                const dups = duplicateAttrGroups.value;
                if (dups.length === 0) return '';
                return `Cảnh báo: Có nhóm thuộc tính bị trùng tên! Vui lòng xóa bớt nhóm trùng lặp.`;
            });

            // ============================================================
            // YÊU CẦU 2: KIỂM TRA TRÙNG LẶP BIẾN THỂ (FRONTEND VUE.JS)
            // ============================================================
            // Computed property: phát hiện biến thể trùng lặp trong gridData
            const hasDuplicateVariants = computed(() => {
                if (gridData.value.length < 2) return false;

                // Trích xuất "Attribute Signature" từ mỗi dòng
                // Signature = chuỗi (attrValueIds đã sort) + '_' + (ten_don_vi hoặc ty_le)
                // FIX: trước đây chỉ gom attrValueIds, làm 2 dòng cùng thuộc tính
                // nhưng khác đơn vị (VD: Đen-38 ở "cái" và Đen-38 ở "Bao 6") bị
                // tính là trùng. Giờ thêm ten_don_vi / ty_le vào key để phân biệt.
                const signatures = gridData.value.map(row => {
                    if (!row.attrValueIds || row.attrValueIds.length === 0) {
                        return ''; // Dòng không có thuộc tính
                    }
                    // Clone và sort để đảm bảo "M-Đỏ" = "Đỏ-M"
                    const sortedIds = [...row.attrValueIds].map(id => String(id)).sort();
                    const attrPart = sortedIds.join('-');
                    // Phân biệt đơn vị: ten_don_vi (unitName) hoặc tyLe
                    const donViPart = row.unitName || (row.tyLe != null ? String(row.tyLe) : '');
                    return `${attrPart}__${donViPart}`;
                });

                // So sánh độ dài: nếu có trùng lặp thì unique sẽ ngắn hơn
                const uniqueSignatures = [...new Set(signatures.filter(sig => sig !== ''))];
                // Chỉ kiểm tra các signature không rỗng
                const nonEmptySignatures = signatures.filter(sig => sig !== '');
                const hasDuplicate = uniqueSignatures.length < nonEmptySignatures.length;

                if (hasDuplicate) {
                    console.warn('[Duplicate Detection] Phát hiện biến thể trùng lặp:', {
                        total: nonEmptySignatures.length,
                        unique: uniqueSignatures.length,
                        signatures: nonEmptySignatures
                    });
                }

                return hasDuplicate;
            });

            // Computed: lấy danh sách các dòng bị trùng (để hiển thị)
            const duplicateVariantIndices = computed(() => {
                if (!hasDuplicateVariants.value) return [];

                const signatures = gridData.value.map((row, idx) => ({
                    idx: idx,
                    sig: row.attrValueIds && row.attrValueIds.length > 0
                        ? (() => {
                            const sortedIds = [...row.attrValueIds].map(id => String(id)).sort();
                            const attrPart = sortedIds.join('-');
                            // FIX: thêm ten_don_vi / ty_le vào key để phân biệt
                            // đơn vị, tránh báo trùng sai khi kết hợp thuộc tính + đơn vị quy đổi
                            const donViPart = row.unitName || (row.tyLe != null ? String(row.tyLe) : '');
                            return `${attrPart}__${donViPart}`;
                        })()
                        : ''
                }));

                const seen = new Map();
                const duplicates = [];

                signatures.forEach(item => {
                    if (item.sig === '') return; // Bỏ qua dòng không có thuộc tính
                    if (seen.has(item.sig)) {
                        duplicates.push(item.idx + 1); // 1-indexed
                    } else {
                        seen.set(item.sig, true);
                    }
                });

                return [...new Set(duplicates)]; // Loại bỏ trùng lặp trong danh sách
            });

            // Computed: thông báo cảnh báo chi tiết
            const duplicateWarningMessage = computed(() => {
                if (!hasDuplicateVariants.value) return '';
                const indices = duplicateVariantIndices.value;
                if (indices.length === 0) return '';
                return `Cảnh báo: Có biến thể đang bị trùng lặp kích thước/màu sắc tại dòng ${indices.join(', ')}!`;
            });

            function clearErrors() {
                errors.value = {};
                generalError.value = '';
            }

            function initFromProduct(prod) {
                try {
                    basicInfo.code = prod.basicInfo?.code ?? '';
                    basicInfo.ten_san_pham = prod.basicInfo?.ten_san_pham ?? '';
                    basicInfo.id_danh_muc = prod.basicInfo?.id_danh_muc ? String(prod.basicInfo.id_danh_muc) : '';
                    basicInfo.brand = prod.basicInfo?.brand ?? '';
                    basicInfo.mo_ta = prod.basicInfo?.mo_ta ?? '';
                    basicInfo.trang_thai = prod.basicInfo?.trang_thai ?? true;
                    basicInfo.defaultPrice = prod.basicInfo?.defaultPrice ?? 0;
                    basicInfo.defaultCost = prod.basicInfo?.defaultCost ?? 0;
                    basicInfo.defaultMinStock = prod.basicInfo?.defaultMinStock ?? 0;
                    basicInfo.imagePreview = prod.basicInfo?.imagePreview ?? '';

                    unitConfig.baseUnit = prod.unitConfig?.baseUnit ?? '';
                    baseUnitInput.value = unitConfig.baseUnit;
                    unitConfig.basePrice = prod.unitConfig?.basePrice ?? (prod.basicInfo?.defaultPrice ?? 0);
                    unitConfig.conversionUnits = (prod.unitConfig?.conversionUnits || []).map(u => ({
                        id: u.id || uid(),
                        name: u.name || '',
                        name_input: '', // cho phép gõ đơn vị mới on-the-fly
                        rate: u.ty_le_quy_doi ?? u.rate ?? 1,
                        price: u.gia_ban_quy_doi ?? u.price ?? 0
                    }));

                    attributesConfig.groups = (prod.attributesConfig?.groups || []).map(g => ({
                        id: g.id || uid(),
                        name: g.name || '',
                        values: (g.values || []).map(v => ({
                            id: v.id ?? (g.id + '_' + (v.label || v)),
                            label: v.label ?? v,
                            _isNew: !!v._isNew // true = user gõ tay, chưa có trong DB
                        })),
                        valueInput: '',
                        _dropdownOpen: false,
                        _highlightedIdx: -1
                    }));

                    // Build lookup from attribute value id -> group name and label
                    const attrLabelMap = {};
                    attributesConfig.groups.forEach(g => {
                        g.values.forEach(v => {
                            attrLabelMap[v.id] = { groupName: g.name, label: v.label };
                        });
                    });

                    gridData.value = (prod.bien_the || []).map(bt => {
                        const attrValueIds = Array.isArray(bt.thuoc_tinh_ids) ? bt.thuoc_tinh_ids : (bt.thuoc_tinh_ids ? String(bt.thuoc_tinh_ids).split(',').map(x => x.trim()).filter(Boolean) : []);
                        const attrLabels = {};
                        attrValueIds.forEach(id => {
                            const found = attrLabelMap[id];
                            if (found) {
                                attrLabels[found.groupName] = found.label;
                            }
                        });

                        // ten_bien_the lưu tên đơn vị gốc của variant (không phải unit đầu tiên trong don_vi_quy_doi)
                        const rowUnitName = bt.ten_bien_the || prod.unitConfig?.baseUnit || '';

                        const unitKey = rowUnitName === prod.unitConfig?.baseUnit
                            ? 'base'
                            : ('cv_' + (bt.units && bt.units.length > 0 ? (bt.units[0].id || uid()) : uid()));

                        return {
                            key: buildRowKey(attrLabels, { key: unitKey, name: rowUnitName }),
                            existingId: bt.id ?? null,
                            attrLabels: attrLabels,
                            attrValueIds: attrValueIds,
                            unitKey: unitKey,
                            unitName: rowUnitName,
                            tyLe: 1, // variant row luôn là đơn vị gốc, tyLe = 1
                            isBase: rowUnitName === prod.unitConfig?.baseUnit,
                            tenBienThe: bt.ten_bien_the ?? '',
                            maHang: bt.ma_hang ?? '',
                            maVach: bt.ma_vach ?? '',
                            giaVon: parseFloat(bt.gia_von) || 0,
                            giaBan: parseFloat(bt.gia_ban) || 0,
                            dinhMucToiThieu: bt.dinh_muc_toi_thieu ?? 0,
                            soLuong: bt.so_luong_ton ?? 0,
                            touched: {},
                            // Ảnh biến thể
                            hinhAnh: bt.hinh_anh || '',
                            fileHinhAnh: null,
                            // conversionUnits: đơn vị quy đổi gốc từ DB (dùng cho payload)
                            conversionUnits: (bt.units || []).map(u => ({
                                id: u.id,
                                ten_don_vi: u.ten_don_vi,
                                so_luong_san_pham_trong_don_vi: u.so_luong_san_pham_trong_don_vi,
                                gia_von_quy_doi: u.gia_von_quy_doi,
                                gia_ban_quy_doi: u.gia_ban_quy_doi,
                                ma_hang: u.ma_hang,
                                ma_vach: u.ma_vach
                            })),
                            savedUnits: (bt.units || []).map(u => ({
                                id: u.id,
                                ten_don_vi: u.ten_don_vi,
                                so_luong_san_pham_trong_don_vi: u.so_luong_san_pham_trong_don_vi,
                                gia_von_quy_doi: u.gia_von_quy_doi,
                                gia_ban_quy_doi: u.gia_ban_quy_doi,
                                ma_hang: u.ma_hang,
                                ma_vach: u.ma_vach
                            }))
                        };
                    });
                    _initDone = true; // Guard: init complete, watchers may now regenerate grid
                } catch (e) {
                    console.error('initFromProduct failed', e);
                }
            }

            // ------- DICTIONARY: dịch thông báo Laravel sang tiếng Việt dễ hiểu -------
            function translateError(msg) {
                if (!msg) return msg;
                const m = String(msg);
                if (/has already been taken/i.test(m)) {
                    if (/ma[_ ]?vach|barcode/i.test(m)) return 'Mã vạch này đã tồn tại trên hệ thống.';
                    if (/ma[_ ]?hang|sku/i.test(m)) return 'Mã hàng này đã tồn tại trên hệ thống.';
                    if (/ten[_ ]?san[_ ]?pham|name/i.test(m)) return 'Tên sản phẩm này đã tồn tại trên hệ thống.';
                    return 'Giá trị này đã tồn tại trên hệ thống.';
                }
                if (/is required/i.test(m)) return 'Trường này là bắt buộc.';
                if (/must be a string/i.test(m)) return 'Vui lòng nhập chuỗi ký tự hợp lệ.';
                if (/must be an integer/i.test(m)) return 'Vui lòng nhập số nguyên hợp lệ.';
                if (/must be a number/i.test(m)) return 'Vui lòng nhập số hợp lệ.';
                if (/must be at least/i.test(m)) return m.replace(/must be at least (\d+)/i, 'Giá trị tối thiểu là $1');
                if (/may not be greater than/i.test(m)) return 'Giá trị vượt quá giới hạn cho phép.';
                if (/does not exist/i.test(m)) return 'Giá trị không tồn tại trong hệ thống.';
                if (/must be an image/i.test(m)) return 'Tệp tải lên phải là hình ảnh.';
                if (/must be a file of type/i.test(m)) return 'Định dạng tệp không được hỗ trợ.';
                if (/must not be greater than/i.test(m)) return 'Tệp tải lên vượt quá dung lượng cho phép.';
                return m; // fallback: giữ nguyên message từ server
            }

            // Chuyển object errors của Laravel thành tiếng Việt
            function localizeErrors(errsObj) {
                const out = {};
                Object.keys(errsObj || {}).forEach(field => {
                    const arr = errsObj[field] || [];
                    out[field] = arr.map(translateError);
                });
                return out;
            }

            // Grid sinh ra từ watcher
            const gridData = ref([]);   // [{ key, attrLabels:{name->valueLabel}, attrValueIds:[id,...], unitName, tyLe, maHang, maVach, giaVon, giaBan, soLuong }]
            let _initDone = false; // guard: prevent regenerateGrid before initFromProduct runs (watchers fire on mount before init)
            const attrValueIdByLabel = ref({}); // label -> id (lookup nhanh)

            // ------- COMPUTED: danh sách đơn vị thực tế (base + conversions hợp lệ) -------
            const effectiveUnits = computed(() => {
                const list = [];
                if (unitConfig.baseUnit.trim()) {
                    list.push({
                        key: 'base',
                        name: unitConfig.baseUnit.trim(),
                        tyLe: 1,
                        price: parseFloat(unitConfig.basePrice) || parseFloat(basicInfo.defaultPrice) || 0,
                        isBase: true
                    });
                }
                unitConfig.conversionUnits.forEach((u, i) => {
                    if (!u.name.trim()) return;
                    list.push({
                        key: 'cv_' + u.id,
                        name: u.name.trim(),
                        tyLe: parseInt(u.rate) || 1,
                        price: parseFloat(u.price) || 0,
                        isBase: false
                    });
                });
                return list;
            });

            // ------- COMPUTED: groups thuộc tính hợp lệ -------
            const effectiveAttrGroups = computed(() => {
                return attributesConfig.groups
                    .filter(g => g.name.trim() && g.values.length > 0)
                    .map(g => ({
                        id: g.id,
                        name: g.name.trim(),
                        values: g.values.map(v => {
                            // Giữ nguyên cấu trúc: id (số hoặc null), label, _isNew
                            if (typeof v === 'string') {
                                return { id: g.id + '_' + v, label: v, _isNew: true };
                            }
                            return { id: v.id, label: v.label, _isNew: !!v._isNew };
                        })
                    }));
            });

            // ------- CARTESIAN PRODUCT -------
            function cartesian(groups) {
                if (groups.length === 0) return [{}]; // 1 phần tử rỗng
                return groups.reduce((acc, g) => {
                    const result = [];
                    acc.forEach(prefix => {
                        g.values.forEach(v => {
                            result.push({
                                ...prefix,
                                [g.name]: v.label,
                                [`__id_${g.name}`]: v.id,
                                [`__isNew_${g.name}`]: !!v._isNew,
                                __ids: (prefix.__ids || []).concat([v.id]).filter(Boolean)
                            });
                        });
                    });
                    return result;
                }, [{}]);
            }

            // ------- BUILD KEY: định danh bền vững cho mỗi row -------
            // attrCombo: { name: label, __id_name: id|null, __isNew_name: bool, __ids: [...] }
            // key = `u:{unitKey}::a:{__isNew_Màu Sắc}=false|__id_Màu Sắc=1|__isNew_Size=false|__id_Size=2`
            function buildRowKey(attrCombo, unit) {
                const attrPart = Object.keys(attrCombo)
                    .filter(k => k.startsWith('__'))
                    .filter(k => k !== '__ids')
                    .sort()
                    .map(k => `${k.replace(/^__/, '')}=${attrCombo[k]}`)
                    .join('|');
                return `u:${unit.key}::a:${attrPart}`;
            }

            // ------- REGENERATE GRID -------
            function regenerateGrid() {
                const oldMap = new Map();
                gridData.value.forEach(row => { oldMap.set(row.key, row); });

                const attrGroups = effectiveAttrGroups.value;
                const units = effectiveUnits.value;
                const attrCombos = cartesian(attrGroups);
                const baseRatio = (() => {
                    const bu = units.find(x => x.isBase);
                    return bu ? (parseFloat(bu.tyLe) || 1) : 1;
                })();

                const newRows = [];
                attrCombos.forEach(combo => {
                    units.forEach(u => {
                        const key = buildRowKey(combo, u);
                        const old = oldMap.get(key);

                        // Tên biến thể: chỉ chứa thuộc tính (KHÔNG gộp tên SP và đơn vị)
                        const attrPart = Object.keys(combo)
                            .filter(k => !k.startsWith('__'))
                            .map(k => combo[k])
                            .join(' - ');
                        const tenBienThe = attrPart || '';

                        newRows.push({
                            key: key,
                            // Metadata
                            attrLabels: Object.keys(combo)
                                .filter(k => !k.startsWith('__'))
                                .reduce((o, k) => { o[k] = combo[k]; return o; }, {}),
                            attrValueIds: combo.__ids || [],
                            unitKey: u.key,
                            unitName: u.name,
                            tyLe: u.tyLe,
                            isBase: u.isBase,
                            // Field người dùng nhập - GIỮ NGUYÊN từ row cũ nếu có
                            // conversionUnits: tất cả đơn vị quy đổi (ty_le > 1) từ unitConfig
                            // Payload sẽ dùng mảng này để gửi đúng units cho từng variant
                            conversionUnits: (unitConfig.conversionUnits || []),
                            existingId: old?.existingId ?? null,
                            tenBienThe: tenBienThe,
                            maHang: old?.maHang ?? (basicInfo.code.trim() ? `${basicInfo.code.trim()}-${u.name}` : ''),
                            maVach: old?.maVach ?? '',
                            giaVon: old?.giaVon ?? ((parseFloat(basicInfo.defaultCost) || 0) * ((parseFloat(u.tyLe) || 1) / baseRatio)),
                            giaBan: old?.giaBan ?? (u.price || parseFloat(basicInfo.defaultPrice) || 0),
                            dinhMucToiThieu: old?.dinhMucToiThieu ?? (parseInt(basicInfo.defaultMinStock) || 0),
                            // Quan trọng: giữ lại savedUnits + conversionUnits từ row cũ
                            // savedUnits: units gốc từ initFromProduct (dùng cho payload) hoặc conversionUnits hiện tại
                            // conversionUnits: units từ unitConfig (dùng cho UI)
                            savedUnits: old?.savedUnits ? [...old.savedUnits] : (old?.conversionUnits ? [...old.conversionUnits] : (unitConfig.conversionUnits || [])),
                            conversionUnits: old?.conversionUnits ? [...old.conversionUnits] : (unitConfig.conversionUnits || []),
                            // Ảnh biến thể: giữ ảnh cũ nếu regen, ảnh mới nếu user vừa upload
                            hinhAnh: old?.hinhAnh ?? '',
                            fileHinhAnh: old?.fileHinhAnh ?? null
                        });
                    });
                });

                gridData.value = newRows;
            }

            // ------- WATCH: lắng nghe thay đổi -------
            let watchTimer = null;
            function debouncedRegen() {
                if (!_initDone) return; // Guard: skip if initFromProduct hasn't run yet
                clearTimeout(watchTimer);
                watchTimer = setTimeout(regenerateGrid, 50);
            }
            watch(() => unitConfig.baseUnit, debouncedRegen);
            watch(() => unitConfig.basePrice, debouncedRegen);
            watch(() => unitConfig.conversionUnits.map(u => u.name + '|' + u.rate + '|' + u.price).join(';'), debouncedRegen);
            watch(() => attributesConfig.groups.map(g => g.name + '|' + g.values.join(',')).join(';'), debouncedRegen);
            watch(() => basicInfo.ten_san_pham + '|' + basicInfo.code, debouncedRegen);

            // ------- METHODS -------
            function addConversion() {
                unitConfig.conversionUnits.push({ id: uid(), don_vi_chuan_id: null, ten_don_vi: '', name: '', name_input: '', rate: 1, price: 0 });
            }
            function removeConversion(idx) {
                unitConfig.conversionUnits.splice(idx, 1);
            }

            // Khi user chọn đơn vị từ dropdown → tự điền name (VD: "Thùng 24") và rate (= qty)
            // Chỉ auto-fill khi user chưa gõ text trong input (name_input === '').
            watch(() => unitConfig.conversionUnits.map(u => u.don_vi_chuan_id), (newIds) => {
                unitConfig.conversionUnits.forEach((u, i) => {
                    if (u.don_vi_chuan_id && !u.name_input) {
                        const found = availableUnits.value.find(a => a.id === u.don_vi_chuan_id);
                        if (found) {
                            u.ten_don_vi = found.ten_don_vi;
                            u.rate = found.qty;
                        }
                    }
                });
            }, { deep: false });

            function onConversionRateInput(u, raw) {
                u.rate = Math.max(1, parseInt(raw) || 1);
            }

            // Đồng bộ ngược: khi user đổi giá bán ở bảng "Đơn vị tính"
            // -> cập nhật Grid (đơn vị tương ứng, không touch các dòng đã sửa)
            function applyUnitPriceToGrid(unitKey, newPrice) {
                const ratio = ratioToBase(unitKey);
                if (!ratio) return;
                gridData.value.forEach(row => {
                    if (row.unitKey !== unitKey) return;
                    if (row.touched?.giaBan) return; // user đã sửa -> không ghi đè
                    row.giaBan = parseFloat(newPrice) || 0;
                });
            }

            // Theo dõi biến đổi giá ở bảng Đơn vị tính để đẩy xuống Grid.
            // Dùng cờ _sync để chống loop: chỉ 1 chiều (Đơn vị -> Grid).
            const lastUnitPriceMap = new Map();
            watch(() => ({
                base: parseFloat(unitConfig.basePrice) || 0,
                conversions: unitConfig.conversionUnits.map(u => ({ key: 'cv_' + u.id, name: u.name, price: parseFloat(u.price) || 0, rate: parseInt(u.rate) || 1 }))
            }), (newVal) => {
                // Base
                const baseKey = 'base';
                const last = lastUnitPriceMap.get(baseKey);
                if (last === undefined || last !== newVal.base) {
                    if (last !== undefined) applyUnitPriceToGrid(baseKey, newVal.base);
                    lastUnitPriceMap.set(baseKey, newVal.base);
                }
                // Conversions
                newVal.conversions.forEach((u) => {
                    if (!u.name) return;
                    const last2 = lastUnitPriceMap.get(u.key);
                    if (last2 === undefined || last2 !== u.price) {
                        if (last2 !== undefined) applyUnitPriceToGrid(u.key, u.price);
                        lastUnitPriceMap.set(u.key, u.price);
                    }
                });
            }, { deep: true });

            function addAttrGroup() {
                attributesConfig.groups.push({
                    id: uid(), name: '', values: [],
                    valueInput: '', _dropdownOpen: false, _highlightedIdx: -1
                });
            }
            function removeAttrGroup(idx) {
                attributesConfig.groups.splice(idx, 1);
            }

            // Lấy danh sách gợi ý cho dropdown: values từ availableAttributes cha có cùng name
            function getDropdownValues(group) {
                const matched = availableAttributes.value.find(a => a.name === group.name);
                if (!matched) return [];
                return matched.values || [];
            }

            // Lọc dropdown: không show giá trị đã được chọn rồi (trong cùng nhóm VÀ các nhóm cùng tên khác)
            function getFilteredDropdown(group) {
                const all = getDropdownValues(group);
                const selectedLabels = new Set(group.values.map(v => v.label));

                // Lọc thêm các giá trị đã được chọn ở các nhóm CÙNG TÊN khác
                if (group.name.trim()) {
                    attributesConfig.groups.forEach(g => {
                        if (g !== group && g.name.trim() === group.name.trim()) {
                            g.values.forEach(v => {
                                selectedLabels.add(v.label);
                            });
                        }
                    });
                }

                return all.filter(v => !selectedLabels.has(v.label));
            }

            // Toggle dropdown
            function toggleDropdown(group) {
                group._dropdownOpen = !group._dropdownOpen;
                if (group._dropdownOpen) group._highlightedIdx = -1;
            }
            function closeDropdown(group) {
                group._dropdownOpen = false;
                group._highlightedIdx = -1;
            }

            // Chọn từ dropdown → thêm vào tags với id từ DB
            function selectFromDropdown(group, item) {
                if (!group.values.find(v => v.id === item.id)) {
                    group.values.push({ id: item.id, label: item.label, _isNew: false });
                }
                group.valueInput = '';
                closeDropdown(group);
            }

            // Gõ text mới + Enter/Tab/',' → thêm tag mới (chưa có trong DB → id=null)
            function addAttrValue(group) {
                const v = (group.valueInput || '').trim();
                if (!v) return;
                // Tránh trùng label đã có
                if (group.values.find(x => x.label === v)) {
                    group.valueInput = '';
                    return;
                }
                // id=null đánh dấu là item mới tạo bởi user (chưa có trong DB)
                group.values.push({ id: null, label: v, _isNew: true });
                group.valueInput = '';
                closeDropdown(group);
            }

            function removeAttrValue(group, vidx) {
                group.values.splice(vidx, 1);
            }

            // Keyboard navigation trên input: mở dropdown khi gõ, duyệt với arrow keys
            function onAttrValueKey(group, e) {
                const filtered = getFilteredDropdown(group);

                if (group._dropdownOpen && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    e.preventDefault();
                    if (e.key === 'ArrowDown') {
                        group._highlightedIdx = Math.min(group._highlightedIdx + 1, filtered.length - 1);
                    } else {
                        group._highlightedIdx = Math.max(group._highlightedIdx - 1, -1);
                    }
                    return;
                }
                if (group._dropdownOpen && e.key === 'Enter' && group._highlightedIdx >= 0) {
                    e.preventDefault();
                    selectFromDropdown(group, filtered[group._highlightedIdx]);
                    return;
                }
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    addAttrValue(group);
                }
                if (e.key === 'Escape') {
                    closeDropdown(group);
                }
            }

            // Click outside để đóng dropdown
            function setupDropdownOutsideClick(group, el) {
                if (!el._dropdownBinded) {
                    el._dropdownBinded = true;
                    document.addEventListener('click', (e) => {
                        if (!el.contains(e.target)) closeDropdown(group);
                    });
                }
            }

            function onImageSelect(e) {
                const f = e.target.files && e.target.files[0];
                if (!f) return;
                basicInfo.image = f;
                const reader = new FileReader();
                reader.onload = ev => { basicInfo.imagePreview = ev.target.result; };
                reader.readAsDataURL(f);
            }
            function clearImage() {
                basicInfo.image = null;
                basicInfo.imagePreview = '';
                const inp = document.getElementById('hinhAnhInputVue');
                if (inp) inp.value = '';
            }

            // Cascade: một dòng bị user sửa sẽ tự scale các dòng cùng thuộc tính
            // theo tỷ lệ (ty_le) giữa các đơn vị. Dòng chưa sửa giữ nguyên.
            function setUnitTouched(row, field) {
                row.touched = row.touched || {};
                row.touched[field] = true;
            }

            // Tính tỷ lệ đơn vị so với base. ty_le của base = 1.
            // Ví dụ: ty_le(lon)=1, ty_le(thùng)=24 => 1 thùng = 24 lon.
            function ratioToBase(unitKey) {
                const u = effectiveUnits.value.find(x => x.key === unitKey);
                return u ? (parseFloat(u.tyLe) || 1) : 1;
            }

            function onGridInput(row, field, e) {
                const v = e.target.value;
                if (field === 'giaVon' || field === 'giaBan' || field === 'soLuong' || field === 'dinhMucToiThieu') {
                    // Bỏ dấu chấm (do v-money format) rồi parseFloat
                    const raw = String(v).replace(/\./g, '').replace(/,/g, '.');
                    row[field] = parseFloat(raw) || 0;
                } else {
                    row[field] = v;
                }
                setUnitTouched(row, field);

                if (field === 'giaBan') {
                    cascadeByUnit(row, field, row[field]);
                }
            }

            // ------- VARIANT IMAGE (ảnh riêng cho từng biến thể) -------
            const _variantImageInputRefs = new Map();

            function setVariantImageInputRef(key, el) {
                if (el) _variantImageInputRefs.set(key, el);
                else _variantImageInputRefs.delete(key);
            }

            function onVariantImageChange(row, e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;
                row.fileHinhAnh = file;
                row.hinhAnh = '';
            }

            function clearVariantImage(row) {
                row.fileHinhAnh = null;
                row.hinhAnh = '';
                const inputEl = _variantImageInputRefs.get(row.key);
                if (inputEl) inputEl.value = '';
            }

            function getRowImagePreview(row) {
                if (row.fileHinhAnh) return URL.createObjectURL(row.fileHinhAnh);
                return '';
            }

            function getExistingImageUrl(path) {
                if (!path) return '';
                if (path.startsWith('http')) return path;
                const base = (typeof window !== 'undefined' && window.location) ? window.location.origin : '';
                return base + '/' + path.replace(/^\/+/, '');
            }

            function cascadeByUnit(sourceRow, field, newValue) {
                const sourceRatio = ratioToBase(sourceRow.unitKey);
                if (!sourceRatio) return;

                gridData.value.forEach(other => {
                    if (other === sourceRow) return;
                    // Cùng combo thuộc tính?
                    const sameAttrs =
                        JSON.stringify(Object.keys(other.attrLabels).sort()) ===
                        JSON.stringify(Object.keys(sourceRow.attrLabels).sort()) &&
                        Object.keys(sourceRow.attrLabels).every(k => other.attrLabels[k] === sourceRow.attrLabels[k]);
                    if (!sameAttrs) return;
                    // Row chưa touched mới bị cascade
                    if (other.touched?.[field]) return;

                    const otherRatio = ratioToBase(other.unitKey);
                    other[field] = Math.round((newValue * otherRatio) / sourceRatio);
                });
            }

            function fillDefaultsToGrid() {
                gridData.value.forEach(row => {
                    row.giaBan = parseFloat(basicInfo.defaultPrice) || 0;
                    row.dinhMucToiThieu = parseInt(basicInfo.defaultMinStock) || 0;
                    row.touched = { giaBan: true };
                });
            }

            // ------- VALIDATION -------
            function validate() {
                const errs = [];
                if (!basicInfo.ten_san_pham.trim()) errs.push('Vui lòng nhập Tên hàng.');
                const catId = parseInt(basicInfo.id_danh_muc);
                if (!catId || catId <= 0) errs.push('Vui lòng chọn Nhóm hàng.');
                if (!unitConfig.baseUnit.trim()) errs.push('Vui lòng nhập Tên đơn vị cơ bản.');
                if (gridData.value.length === 0) errs.push('Vui lòng khai báo ít nhất 1 đơn vị tính.');

                // ============================================================
                // YÊU CẦU 2: KIỂM TRA TRÙNG NHÓM THUỘC TÍNH
                // ============================================================
                if (duplicateAttrGroups.value.length > 0) {
                    errs.push('Có nhóm thuộc tính bị trùng tên. Vui lòng xóa bớt nhóm trùng lặp!');
                }

                // ============================================================
                // YÊU CẦU 2: KIỂM TRA TRÙNG LẶP BIẾN THỂ
                // ============================================================
                if (hasDuplicateVariants.value) {
                    errs.push('Có biến thể bị trùng lặp thuộc tính. Vui lòng kiểm tra lại!');
                }

                gridData.value.forEach((r, i) => {
                    const gb = parseFloat(r.giaBan);
                    if (!gb && gb !== 0) errs.push(`Dòng ${i + 1}: Giá bán không hợp lệ.`);
                });
                return errs;
            }

            // ------- PAYLOAD -------
            function buildPayload() {
                const hasAttr = effectiveAttrGroups.value.length > 0;
                const loai_bien_the = hasAttr ? 'thuoc_tinh' : 'don_vi';

                    // Thu thập thuộc tính MỚI (user gõ tay, chưa có trong DB)
                // Để backend tạo vào DB trước khi gán cho biến thể
                const newAttributes = [];
                effectiveAttrGroups.value.forEach(g => {
                    g.values.forEach(v => {
                        if (v._isNew && v.id === null) {
                            newAttributes.push({ groupName: g.name, label: v.label });
                        }
                    });
                });

                // Map group name -> thuoc_tinh_cha_id (lấy từ availableAttributes)
                const groupNameToParentId = {};
                (DATA.availableAttributes || []).forEach(a => {
                    groupNameToParentId[a.name] = a.id;
                });

                // attrLabels: map group name -> label value (dùng để resolve giá trị mới ở backend)
                const attrLabels = {};
                effectiveAttrGroups.value.forEach(g => {
                    attrLabels[g.name] = g.values.map(v => v.label);
                });

                const bienThe = gridData.value.map((row, i) => {
                    const idField = row.existingId ? { id: row.existingId } : {};
                    const isBase = row.isBase === true;

                    // Dùng savedUnits (reference gốc từ initFromProduct, không bị debouncedRegen overwrite)
                    // Lọc ra các đơn vị QUY ĐỔI (so_luong_san_pham_trong_don_vi > 1) thuộc dòng này
                    // Ưu tiên name (UI) > ten_don_vi (DB): nếu user gõ đơn vị mới → dùng name.
                    const unitsPayload = (row.savedUnits || [])
                        .filter(u => (parseInt(u.so_luong_san_pham_trong_don_vi) || 1) > 1)
                        .map(u => {
                            return {
                                id: u.id,
                                don_vi_chuan_id: u.don_vi_chuan_id || null,
                                ten_don_vi: (u.ten_don_vi || u.name || '').trim(),
                                so_luong_san_pham_trong_don_vi: parseInt(u.rate) || parseInt(u.so_luong_san_pham_trong_don_vi) || 1,
                                gia_von_quy_doi: 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                                gia_ban_quy_doi: parseFloat(u.gia_ban_quy_doi) || 0,
                                ma_hang: u.ma_hang || '',
                                ma_vach: u.ma_vach || ''
                            };
                        });

                    return Object.assign({}, idField, {
                        is_base: row.isBase ? 1 : 0,
                        ten_bien_the: row.tenBienThe || row.unitName || '',
                        la_don_vi: row.isBase && !hasAttr ? 1 : 0,
                        ten_don_vi: row.isBase ? unitConfig.baseUnit : row.unitName,
                        ty_le: row.tyLe || row.ty_le || 1,
                        ma_hang: row.maHang || row.ma_hang || '',
                        ma_vach: row.maVach || row.ma_vach || '',
                        gia_von: 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                        gia_ban: parseFloat(row.giaBan || row.gia_ban) || 0,
                        so_luong_ton: parseInt(row.soLuong || row.so_luong_ton) || 0,
                        dinh_muc_toi_thieu: parseInt(row.dinhMucToiThieu || row.dinh_muc_toi_thieu) || 0,
                        thuoc_tinh_ids: Array.isArray(row.attrValueIds) ? row.attrValueIds.join(',') : (row.attrValueIds || ''),
                        ten_don_vi_bien_the: row.unitName || '',
                        units: unitsPayload
                    });
                });

                const result = {
                    ten_san_pham: basicInfo.ten_san_pham.trim(),
                    id_danh_muc: parseInt(basicInfo.id_danh_muc) || null,
                    thuong_hieu: basicInfo.brand.trim(),
                    mo_ta: basicInfo.mo_ta.trim(),
                    trang_thai: basicInfo.trang_thai ? 1 : 0,
                    loai_bien_the: loai_bien_the,
                    bien_the: bienThe,
                    // Thuộc tính mới: backend tạo vào DB rồi resolve vào thuoc_tinh_ids
                    new_attributes: newAttributes.map(a => ({
                        group_name: a.groupName,
                        parent_id: groupNameToParentId[a.groupName] || null,
                        label: a.label
                    }))
                };
                console.log('[buildPayload] Payload:', result);
                return result;
            }

            function getCsrfToken() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta && meta.content) return meta.content;
                if (DATA && DATA.csrfToken) return DATA.csrfToken;
                return '';
            }

            async function handleSubmit() {
                submitHadError.value = false;
                const errs = validate();
                clearErrors();
                submitting.value = true;
                if (errs.length) {
                    generalError.value = 'Vui lòng kiểm tra lại các trường:\n• ' + errs.join('\n• ');
                    submitting.value = false;
                    return;
                }

                let payload;
                try {
                    payload = buildPayload();
                } catch (e) {
                    console.error('[handleSubmit] buildPayload failed:', e);
                    generalError.value = 'Lỗi xây dựng dữ liệu: ' + (e.message || String(e));
                    submitting.value = false;
                    return;
                }
                console.log("Payload gửi đi:", payload);

                const csrf = getCsrfToken();
                const form = document.getElementById('productForm');
                const actionUrl = form ? form.getAttribute('action') : '/admin/san-pham';

                // Nếu có file ảnh, dùng FormData (multipart) để upload file; nếu không, gửi FormData thường
                // LƯU Ý: KHÔNG set Content-Type header khi dùng FormData — browser tự set boundary
                let body;
                let headers = {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                };

                if (hasImage.value) {
                    const fd = new FormData();
                    // Scalar fields
                    ['ten_san_pham', 'id_danh_muc', 'thuong_hieu', 'mo_ta', 'trang_thai', 'loai_bien_the'].forEach(k => {
                        if (payload[k] !== undefined && payload[k] !== null) {
                            fd.append(k, payload[k]);
                        }
                    });
                    // bien_the: phải append dạng bien_the[i][key] để Laravel parse thành mảng
                    (payload.bien_the || []).forEach((bt, i) => {
                        Object.keys(bt).forEach(k => {
                            if (k === 'units') return;
                            if (bt[k] !== undefined && bt[k] !== null) {
                                fd.append(`bien_the[${i}][${k}]`, bt[k]);
                            }
                        });
                        (bt.units || []).forEach((u, j) => {
                            Object.keys(u).forEach(uk => {
                                if (u[uk] !== undefined && u[uk] !== null) {
                                    fd.append(`bien_the[${i}][units][${j}][${uk}]`, u[uk]);
                                }
                            });
                        });
                        // Append file ảnh riêng cho biến thể (nếu user đã chọn)
                        const row = gridData.value[i];
                        if (row && row.fileHinhAnh instanceof File) {
                            fd.append(`bien_the[${i}][hinh_anh]`, row.fileHinhAnh, row.fileHinhAnh.name);
                        }
                    });
                    fd.append('hinh_anh', basicInfo.image);
                    body = fd;
                } else {
                    // Dùng FormData thay vì JSON thuần — Laravel chỉ parse $request->all() với
                    // Content-Type form-urlencoded/multipart. JSON body sẽ cho $request->all() = []
                    const fd = new FormData();
                    if (DATA.editMode) fd.append('_method', 'PUT');
                    ['ten_san_pham', 'id_danh_muc', 'thuong_hieu', 'mo_ta', 'trang_thai', 'loai_bien_the'].forEach(k => {
                        if (payload[k] !== undefined && payload[k] !== null) {
                            fd.append(k, payload[k]);
                        }
                    });
                    (payload.bien_the || []).forEach((bt, i) => {
                        Object.keys(bt).forEach(k => {
                            if (k === 'units') return;
                            if (bt[k] !== undefined && bt[k] !== null) {
                                fd.append(`bien_the[${i}][${k}]`, bt[k]);
                            }
                        });
                        (bt.units || []).forEach((u, j) => {
                            Object.keys(u).forEach(uk => {
                                if (u[uk] !== undefined && u[uk] !== null) {
                                    fd.append(`bien_the[${i}][units][${j}][${uk}]`, u[uk]);
                                }
                            });
                        });
                        // Append file ảnh riêng cho biến thể (nếu user đã chọn)
                        const row = gridData.value[i];
                        if (row && row.fileHinhAnh instanceof File) {
                            fd.append(`bien_the[${i}][hinh_anh]`, row.fileHinhAnh, row.fileHinhAnh.name);
                        }
                    });
                    body = fd;
                }

                // DEBUG: dump all FormData entries
                console.log('[DEBUG handleSubmit] FormData entries:');
                for (const [key, value] of body.entries()) {
                    console.log('  ', key, '=', typeof value === 'object' ? value.name || value + '' : value);
                }
                const unitsPresent = Array.from(body.entries()).filter(([k]) => k.includes('units'));
                console.log('[DEBUG handleSubmit] units in FormData:', unitsPresent.length, unitsPresent.length === 0 ? '<-- NO UNITS! (will cause 422)' : '');
                console.log('[DEBUG handleSubmit] hasImage.value:', hasImage.value);
                console.log('[DEBUG handleSubmit] DATA.editMode:', DATA.editMode);
                console.log('[DEBUG handleSubmit] actionUrl:', actionUrl);

                const method = 'POST'; // Luôn POST; Laravel sẽ đọc _method=PUT từ FormData

                try {
                    const res = await fetch(actionUrl, {
                        method: method,
                        headers: headers,
                        body: body,
                        credentials: 'same-origin'
                    });

                    const ct = res.headers.get('content-type') || '';
                    const data = ct.includes('application/json') ? await res.json() : await res.text();

                    if (res.ok) {
                        const redirectUrl = (data && data.redirect) ? data.redirect : '/admin/san-pham';
                        window.location.href = redirectUrl;
                        return;
                    }

                    // 422: lỗi validation từ Laravel
                    if (res.status === 422 && data && data.errors) {
                        const localized = localizeErrors(data.errors);
                        errors.value = localized;
                        const summary = [];
                        Object.keys(localized).forEach(field => {
                            localized[field].forEach(m => summary.push('• ' + m));
                        });
                        generalError.value = 'Không thể lưu sản phẩm:\n' + summary.join('\n');
                        submitHadError.value = true;
                        try {
                            const modalBody = document.getElementById('editProductModalBody') || document.getElementById('addProductModalBody');
                            if (modalBody) modalBody.scrollTop = 0;
                        } catch (_) {}
                        return;
                    }

                    // Các lỗi khác: 500, 419, ...
                    const msg = (data && (data.message || data.error || data))
                        ? (typeof data === 'object' ? JSON.stringify(data) : data)
                        : ('HTTP ' + res.status);
                    generalError.value = msg;
                    submitHadError.value = true;
                    alert('Lỗi khi lưu sản phẩm: ' + msg);
                } catch (err) {
                    const msg = (err && err.message ? err.message : String(err));
                    generalError.value = 'Lỗi mạng: ' + msg;
                    submitHadError.value = true;
                    alert('Lỗi kết nối: ' + msg);
                } finally {
                    submitting.value = false;
                }
            }

            // Cập nhật DOM hiển thị lỗi + spinner + cảnh báo trùng lặp
            function syncErrorUi() {
                try {
                    const box = document.getElementById('formErrorBox');
                    const spinner = document.getElementById('btnLuuSpinner');
                    const icon = document.getElementById('btnLuuIcon');
                    const btn = document.getElementById('btnLuuSanPham');
                    const dupWarning = document.getElementById('duplicateVariantWarning');
                    const dupAttrGroupWarning = document.getElementById('duplicateAttrGroupWarning');

                    // ============================================================
                    // YÊU CẦU 2: HIỂN THỊ CẢNH BÁO TRÙNG NHÓM THUỘC TÍNH
                    // ============================================================
                    if (dupAttrGroupWarning) {
                        if (duplicateAttrGroupWarning.value) {
                            dupAttrGroupWarning.textContent = duplicateAttrGroupWarning.value;
                            dupAttrGroupWarning.classList.remove('d-none');
                        } else {
                            dupAttrGroupWarning.textContent = '';
                            dupAttrGroupWarning.classList.add('d-none');
                        }
                    }

                    // ============================================================
                    // YÊU CẦU 2: HIỂN THỊ CẢNH BÁO TRÙNG BIẾN THỂ
                    // ============================================================
                    if (dupWarning) {
                        if (duplicateWarningMessage.value) {
                            dupWarning.textContent = duplicateWarningMessage.value;
                            dupWarning.classList.remove('d-none');
                        } else {
                            dupWarning.textContent = '';
                            dupWarning.classList.add('d-none');
                        }
                    }

                    if (box) {
                        const msg = generalError.value || '';
                        if (msg) {
                            box.textContent = msg;
                            box.classList.remove('d-none');
                        } else {
                            box.textContent = '';
                            box.classList.add('d-none');
                        }
                    }
                    if (spinner && icon && btn) {
                        if (submitting.value) {
                            spinner.classList.remove('d-none');
                            icon.classList.add('d-none');
                            btn.disabled = true;
                            btn.classList.add('disabled');
                        } else {
                            spinner.classList.add('d-none');
                            icon.classList.remove('d-none');
                            // ============================================================
                            // YÊU CẦU 2: VÔ HIỆU HÓA NÚT LƯU KHI CÓ TRÙNG LẶP
                            // ============================================================
                            // Chỉ bật nút khi:
                            // - Form đã load xong
                            // - Không có lỗi submit trước đó
                            // - KHÔNG CÓ nhóm thuộc tính trùng lặp
                            // - KHÔNG CÓ biến thể trùng lặp
                            const hasAnyDuplicate = hasDuplicateVariants.value || duplicateAttrGroups.value.length > 0;
                            if (formLoaded.value && !submitHadError.value && !hasAnyDuplicate) {
                                btn.disabled = false;
                                btn.classList.remove('disabled');
                            } else {
                                btn.disabled = true;
                                btn.classList.add('disabled');
                            }
                        }
                    }
                } catch (e) {
                    console.error('syncErrorUi failed', e);
                }
            }

            // Mount: khởi tạo trạng thái ban đầu + expose cho click handler bên ngoài
            onMounted(() => {
                if (isEditMode && DATA.productData) {
                    initFromProduct(DATA.productData);
                } else {
                    _initDone = true;
                    regenerateGrid();
                }
                formLoaded.value = true;

                // Bat nut Lưu sau khi Vue da khoi tao xong
                const btn = document.getElementById('btnLuuSanPham');
                if (btn) { btn.disabled = false; btn.classList.remove('disabled'); }

                window.__vueProductExposed = {
                    handleSubmit: handleSubmit,
                    buildPayload: buildPayload,
                    getBasicInfo: () => basicInfo,
                    regenerateGrid: regenerateGrid,
                    syncErrorUi: syncErrorUi,
                    clearErrors: clearErrors,
                    getGeneralError: () => generalError.value,
                    getIsSubmitting: () => submitting.value
                };
                // Expose trực tiếp lên window để event listener bên ngoài gọi không cần qua __vueProductExposed
                window.handleSubmit = handleSubmit;
            });

            // Theo dõi thay đổi của generalError & submitting & duplicate checks để đồng bộ UI
            watch(generalError, () => syncErrorUi());
            watch(submitting, () => syncErrorUi());
            // Watch duplicate variants để tự động cập nhật UI khi grid thay đổi
            watch(hasDuplicateVariants, () => syncErrorUi());
            // Watch duplicate attr groups để tự động cập nhật UI
            watch(duplicateAttrGroups, () => syncErrorUi(), { deep: true });

            return {
                basicInfo, unitConfig, attributesConfig, sectionOpen,
                gridData, effectiveAttrGroups,
                errors, generalError, submitting, clearErrors,
                danhMucs: DATA.danhMucs,
                availableAttributes,
                availableUnits,
                allUnitOptions,
                baseUnitInput,
                addConversion, removeConversion, onConversionRateInput,
                addAttrGroup, removeAttrGroup, addAttrValue, removeAttrValue,
                onAttrValueKey, toggleDropdown, closeDropdown,
                getDropdownValues, getFilteredDropdown, selectFromDropdown,
                onImageSelect, clearImage, onGridInput, fillDefaultsToGrid,
                // Ảnh biến thể
                setVariantImageInputRef, onVariantImageChange, clearVariantImage,
                getRowImagePreview, getExistingImageUrl,
                handleSubmit, validate, buildPayload,
                // ============================================================
                // YÊU CẦU 2: EXPOSE COMPUTED PROPERTIES CHO UI
                // ============================================================
                hasDuplicateVariants,
                duplicateVariantIndices,
                duplicateWarningMessage,
                duplicateAttrGroups,
                duplicateAttrGroupWarning
            };
        },

        template: `
        <div class="bg-slate-50 p-3">
            <!-- BLOCK 1: Thông tin cơ bản + Giá mặc định -->
            <section class="bg-white rounded-lg shadow-sm mb-3 border border-slate-200">
                <button type="button"
                    class="w-full flex items-center justify-between px-4 py-3 text-left"
                    @click="sectionOpen.info = !sectionOpen.info">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-blue-100 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                        </span>
                        <h3 class="font-semibold text-slate-800 m-0">Thông tin cơ bản</h3>
                    </div>
                    <svg :class="['w-4 h-4 text-slate-500 transition-transform', sectionOpen.info ? 'rotate-180' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div v-show="sectionOpen.info" class="px-4 pb-4">
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-6">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Mã hàng <span class="text-red-500">*</span></label>
                            <input v-model="basicInfo.code" type="text" placeholder="VD: SP001"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div class="col-span-6">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Tên hàng <span class="text-red-500">*</span></label>
                            <input v-model="basicInfo.ten_san_pham" type="text" placeholder="VD: Bia Tiger" required
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div class="col-span-6">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Nhóm hàng <span class="text-red-500">*</span></label>
                            <select v-model="basicInfo.id_danh_muc" required
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="" disabled>-- Chọn nhóm hàng --</option>
                                <option v-for="d in danhMucs" :key="d.id" :value="String(d.id)">{{ d.ten }}</option>
                            </select>
                        </div>
                        <div class="col-span-6">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Thương hiệu</label>
                            <input v-model="basicInfo.brand" type="text" placeholder="VD: Tiger"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div class="col-span-12">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Hình ảnh</label>
                            <div class="flex items-start gap-4">
                                <div class="relative w-[140px] h-[140px] rounded-md border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden cursor-pointer hover:bg-slate-100"
                                    @click="$refs.imgInput.click()">
                                    <img v-if="basicInfo.imagePreview" :src="basicInfo.imagePreview" class="w-full h-full object-cover">
                                    <div v-else class="text-center text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        <p class="text-[11px] mt-1">Chọn ảnh</p>
                                    </div>
                                    <input ref="imgInput" id="hinhAnhInputVue" type="file" accept="image/*" class="hidden" @change="onImageSelect">
                                </div>
                                <div class="flex-1">
                                    <p class="text-[11px] text-slate-500">Hỗ trợ: JPEG, PNG, JPG, GIF, WEBP. Dung lượng tối đa: 5MB.</p>
                                    <button v-if="basicInfo.imagePreview" type="button" @click="clearImage"
                                        class="mt-2 px-3 py-1.5 text-xs rounded-md border border-red-300 text-red-600 hover:bg-red-50">Xóa ảnh</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-3 mt-3">
                        <div class="col-span-6">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Mô tả</label>
                            <textarea v-model="basicInfo.mo_ta" rows="2" placeholder="Mô tả sản phẩm..."
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                        </div>
                        <div class="col-span-6 flex flex-col justify-end">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Trạng thái</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input v-model="basicInfo.trang_thai" type="checkbox" class="sr-only peer">
                                <div class="w-10 h-5 bg-slate-200 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                <span class="ml-3 text-sm text-slate-600">{{ basicInfo.trang_thai ? 'Đang bán' : 'Ngừng bán' }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[11px] text-slate-500 m-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="inline w-3.5 h-3.5 mr-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                Giá trị mặc định sẽ tự động điền vào bảng "Hàng cùng loại" bên dưới.
                            </p>
                            <button v-if="gridData.length" type="button" @click="fillDefaultsToGrid"
                                class="text-[11px] text-blue-600 hover:underline">Áp dụng lại cho tất cả dòng</button>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Giá bán <span class="text-slate-400">(đv cơ bản)</span></label>
                                <input v-model.number="basicInfo.defaultPrice" v-money type="number" min="0" step="any"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none money-input">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Định mức tối thiểu <span class="text-slate-400">(đv cơ bản)</span></label>
                                <input v-model.number="basicInfo.defaultMinStock" type="number" min="0"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- BLOCK 2: Đơn vị tính & Thuộc tính -->
            <div class="grid grid-cols-2 gap-3">
                <section class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <button type="button"
                        class="w-full flex items-center justify-between px-4 py-3 text-left"
                        @click="sectionOpen.units = !sectionOpen.units">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h18"/></svg>
                            </span>
                            <h3 class="font-semibold text-slate-800 m-0">Đơn vị tính</h3>
                            <span v-if="unitConfig.conversionUnits.length" class="px-2 py-0.5 text-[11px] rounded-full bg-indigo-100 text-indigo-700">{{ unitConfig.conversionUnits.length }} quy đổi</span>
                        </div>
                        <svg :class="['w-4 h-4 text-slate-500 transition-transform', sectionOpen.units ? 'rotate-180' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div v-show="sectionOpen.units" class="px-4 pb-4">
                        <div class="grid grid-cols-12 gap-2 items-end mb-3">
                            <div class="col-span-6">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Đơn vị cơ bản <span class="text-red-500">*</span></label>
                                <div class="flex gap-1">
                                    <div class="relative flex-1">
                                        <select v-model="unitConfig.baseUnit"
                                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white appearance-none cursor-pointer">
                                            <option value="">— Chọn đơn vị —</option>
                                            <option v-for="u in allUnitOptions" :key="u.id" :value="u.ten_don_vi">{{ u.ten_don_vi }}</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                            <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                        </div>
                                    </div>
                                    <input v-model="baseUnitInput" type="text"
                                        placeholder="Gõ đơn vị mới..."
                                        class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                        style="min-width: 140px;">
                                </div>
                                <p v-if="unitConfig.baseUnit && !availableUnits.find(u => u.ten_don_vi === unitConfig.baseUnit)"
                                    class="mt-1 text-[11px] text-indigo-600 italic">
                                    Đơn vị "<b class="font-medium not-italic">{{ unitConfig.baseUnit }}</b>" sẽ được tạo mới khi lưu.
                                </p>
                            </div>
                            <div class="col-span-6">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Giá bán (đơn vị cơ bản)</label>
                                <input v-model.number="unitConfig.basePrice" v-money type="number" min="0" step="any"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none money-input">
                            </div>
                        </div>

                        <div v-if="unitConfig.conversionUnits.length" class="rounded-md border border-slate-200 overflow-hidden mb-3">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-xs text-slate-600">
                                    <tr>
                                        <th class="px-2 py-2 text-left font-medium">Tên đơn vị</th>
                                        <th class="px-2 py-2 text-left font-medium">Tỷ lệ</th>
                                        <th class="px-2 py-2 text-left font-medium">Giá bán</th>
                                        <th class="w-8"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(u, i) in unitConfig.conversionUnits" :key="u.id" class="border-t border-slate-100">
                                        <td class="p-2">
                                            <div class="flex gap-1">
                                                <select v-model="u.don_vi_chuan_id"
                                                    class="flex-1 rounded border border-slate-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                                    <option value="">— Chọn đơn vị —</option>
                                                    <option v-for="opt in availableUnits" :key="opt.id" :value="opt.id">{{ opt.ten_don_vi }} ({{ opt.qty }})</option>
                                                </select>
                                                <input v-model="u.name_input" type="text"
                                                    placeholder="Gõ đơn vị mới..."
                                                    class="flex-1 rounded border border-slate-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                                    style="min-width: 100px;"
                                                    @input="u.name_input = $event.target.value; u.ten_don_vi = $event.target.value">
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            <div class="flex items-center gap-1">
                                                <input :value="u.rate" @input="onConversionRateInput(u, $event.target.value)" type="number" min="1"
                                                    class="w-16 rounded border border-slate-300 px-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-indigo-500 outline-none">
                                                <span class="text-xs text-slate-500">{{ unitConfig.baseUnit || 'đv cơ bản' }}</span>
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            <input v-model.number="u.price" v-money type="number" min="0" step="any"
                                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-indigo-500 outline-none money-input">
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="removeConversion(i)" class="text-slate-400 hover:text-red-500" title="Xóa">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button type="button" @click="addConversion"
                            class="w-full px-3 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-dashed border-indigo-300 rounded-md flex items-center justify-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Thêm đơn vị quy đổi
                        </button>
                    </div>
                </section>

                <section class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <button type="button"
                        class="w-full flex items-center justify-between px-4 py-3 text-left"
                        @click="sectionOpen.attributes = !sectionOpen.attributes">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-amber-100 text-amber-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            </span>
                            <h3 class="font-semibold text-slate-800 m-0">Thuộc tính</h3>
                            <span v-if="attributesConfig.groups.length" class="px-2 py-0.5 text-[11px] rounded-full bg-amber-100 text-amber-700">
                                {{ attributesConfig.groups.length }} nhóm
                            </span>
                        </div>
                        <svg :class="['w-4 h-4 text-slate-500 transition-transform', sectionOpen.attributes ? 'rotate-180' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div v-show="sectionOpen.attributes" class="px-4 pb-4">
                        <div v-if="attributesConfig.groups.length === 0" class="text-center text-slate-400 py-6 border border-dashed border-slate-300 rounded-md mb-3">
                            <p class="text-xs m-0">Chưa có nhóm thuộc tính nào</p>
                            <p class="text-[11px] text-slate-400 mt-1">VD: Màu sắc, Size, Chất liệu...</p>
                        </div>

                        <!-- ====== NHÓM THUỘC TÍNH ====== -->
                        <div v-for="(g, gi) in attributesConfig.groups" :key="g.id" class="mb-3 p-3 rounded-md border border-slate-200 bg-slate-50/50">

                            <!-- Row 1: Chọn nhóm + Xóa -->
                            <div class="flex items-center gap-2 mb-2">
                                <!-- Select chọn nhóm từ availableAttributes -->
                                <select v-model="g.name"
                                    class="flex-1 rounded border border-slate-300 px-2 py-1.5 text-sm font-medium focus:ring-2 focus:ring-amber-500 outline-none bg-white">
                                    <option value="">— Chọn nhóm thuộc tính —</option>
                                    <option v-for="attr in availableAttributes" :key="attr.id" :value="attr.name">
                                        {{ attr.name }}
                                    </option>
                                </select>
                                <!-- Nút xóa nhóm -->
                                <button type="button" @click="removeAttrGroup(gi)" class="text-slate-400 hover:text-red-500 shrink-0" title="Xóa nhóm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Row 2: Chips gợi ý từ DB (chỉ hiển thị khi đã chọn nhóm) -->
                            <div v-if="g.name && getDropdownValues(g).length > 0" class="mb-2">
                                <p class="text-[10px] text-slate-400 mb-1 font-medium uppercase tracking-wide">Gợi ý</p>
                                <div class="flex flex-wrap gap-1">
                                    <button type="button"
                                        v-for="item in getDropdownValues(g)"
                                        :key="item.id"
                                        @click="selectFromDropdown(g, item)"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs border border-dashed border-slate-300 text-slate-600 hover:border-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors">
                                        <span class="text-slate-400 text-[10px]">+</span> {{ item.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Row 3: Tags đã chọn -->
                            <div v-if="g.values.length > 0" class="flex flex-wrap gap-1.5 mb-2 min-h-[28px]">
                                <span v-for="(v, vi) in g.values" :key="vi"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs"
                                    :class="v._isNew ? 'bg-orange-100 text-orange-800 border border-orange-300' : 'bg-amber-100 text-amber-800'">
                                    {{ v.label }}
                                    <button type="button" @click="removeAttrValue(g, vi)" class="hover:text-red-500 leading-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            </div>

                            <!-- Row 4: Input tạo giá trị mới -->
                            <div class="relative">
                                <input
                                    v-model="g.valueInput"
                                    type="text"
                                    placeholder="Gõ giá trị mới, nhấn Enter để tạo..."
                                    @keydown.enter.prevent="addAttrValue(g)"
                                    @keydown.comma.prevent="addAttrValue(g)"
                                    @focus="toggleDropdown(g)"
                                    @blur="setTimeout(() => closeDropdown(g), 200)"
                                    class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-amber-500 outline-none"
                                    :disabled="!g.name">

                                <!-- Dropdown gợi ý lọc theo text đã gõ -->
                                <div v-if="g._dropdownOpen && getFilteredDropdown(g).length > 0"
                                    class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg max-h-44 overflow-y-auto">
                                    <button type="button"
                                        v-for="(item, idx) in getFilteredDropdown(g)" :key="item.id"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-amber-50 transition-colors"
                                        :class="idx === g._highlightedIdx ? 'bg-amber-50' : ''"
                                        @click="selectFromDropdown(g, item)"
                                        @mouseenter="g._highlightedIdx = idx">
                                        {{ item.label }}
                                    </button>
                                </div>

                                <!-- Gợi ý tạo mới khi không khớp dropdown -->
                                <div v-if="g._dropdownOpen && g.valueInput.trim() && !g.values.find(v => v.label === g.valueInput.trim())"
                                    class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg">
                                    <div class="px-3 py-2 text-xs text-slate-500 italic border-t border-slate-100">
                                        Nhấn <b class="text-amber-700 not-italic font-medium">Enter</b> để tạo mới:
                                        <b class="text-orange-600 not-italic font-medium">"{{ g.valueInput.trim() }}"</b>
                                    </div>
                                </div>
                            </div>

                            <!-- Gợi ý: nhóm chưa chọn -->
                            <p v-if="!g.name" class="text-[10px] text-slate-400 mt-1.5 mb-0">
                                Chọn nhóm thuộc tính bên trên để hiển thị giá trị gợi ý
                            </p>
                        </div>

                        <!-- Nút Thêm nhóm -->
                        <button type="button" @click="addAttrGroup"
                            class="w-full px-3 py-2 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-dashed border-amber-300 rounded-md flex items-center justify-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Thêm nhóm thuộc tính
                        </button>
                    </div>
                </section>
            </div>

            <!-- BLOCK 3: Bảng Hàng cùng loại (Grid sinh tự động) -->
            <section class="bg-white rounded-lg shadow-sm border border-slate-200 mt-3">
                <button type="button"
                    class="w-full flex items-center justify-between px-4 py-3 text-left"
                    @click="sectionOpen.grid = !sectionOpen.grid">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 inline-flex items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v18H3z"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
                        </span>
                        <h3 class="font-semibold text-slate-800 m-0">Hàng cùng loại</h3>
                        <span class="px-2 py-0.5 text-[11px] rounded-full bg-emerald-100 text-emerald-700">
                            {{ gridData.length }} dòng
                        </span>
                    </div>
                    <svg :class="['w-4 h-4 text-slate-500 transition-transform', sectionOpen.grid ? 'rotate-180' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div v-show="sectionOpen.grid" class="px-4 pb-4">
                    <div v-if="gridData.length === 0" class="text-center text-slate-400 py-8 border border-dashed border-slate-300 rounded-md">
                        <p class="text-sm m-0">Vui lòng khai báo <b>đơn vị cơ bản</b> ở khối bên trên.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Bảng sẽ tự động sinh ra dựa trên đơn vị & thuộc tính.</p>
                    </div>
                    <div v-else class="overflow-x-auto rounded-md border border-slate-200">
                        <table class="w-full text-sm min-w-[900px]">
                            <thead class="bg-slate-50 text-xs text-slate-600">
                                <tr>
                                    <th class="px-2 py-2 text-left font-medium" v-for="g in effectiveAttrGroups" :key="g.id">
                                        {{ g.name }}
                                    </th>
                                    <th class="px-2 py-2 text-left font-medium">Ảnh</th>
                                    <th class="px-2 py-2 text-left font-medium">Đơn vị</th>
                                    <th class="px-2 py-2 text-left font-medium">Tỷ lệ</th>
                                    <th class="px-2 py-2 text-left font-medium">Mã hàng</th>
                                    <th class="px-2 py-2 text-left font-medium">Mã vạch</th>
                                    <th class="px-2 py-2 text-right font-medium">Giá bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in gridData" :key="row.key" class="border-t border-slate-100 hover:bg-slate-50/50">
                                    <td v-for="g in effectiveAttrGroups" :key="g.id" class="px-2 py-1.5 text-slate-700">
                                        {{ row.attrLabels[g.name] || '-' }}
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="relative w-12 h-12 rounded border border-slate-300 overflow-hidden bg-slate-50 flex items-center justify-center">
                                                <img v-if="row.fileHinhAnh"
                                                    :src="getRowImagePreview(row)"
                                                    alt="Ảnh biến thể"
                                                    class="w-full h-full object-cover">
                                                <img v-else-if="row.hinhAnh"
                                                    :src="getExistingImageUrl(row.hinhAnh)"
                                                    alt="Ảnh biến thể"
                                                    class="w-full h-full object-cover">
                                                <span v-else class="text-slate-400 text-[10px]">—</span>
                                                <input type="file" accept="image/*"
                                                    :ref="el => setVariantImageInputRef(row.key, el)"
                                                    @change="onVariantImageChange(row, $event)"
                                                    class="absolute inset-0 opacity-0 cursor-pointer"
                                                    title="Chọn ảnh cho biến thể">
                                            </div>
                                            <button v-if="row.fileHinhAnh || row.hinhAnh" type="button"
                                                @click="clearVariantImage(row)"
                                                class="text-[10px] text-red-600 hover:underline whitespace-nowrap">Xóa</button>
                                        </div>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <span class="px-2 py-0.5 rounded text-xs" :class="row.isBase ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700'">
                                            {{ row.unitName }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-1.5 text-slate-600 text-xs">x{{ row.tyLe }}</td>
                                    <td class="px-2 py-1.5">
                                        <input :value="row.maHang" @input="onGridInput(row, 'maHang', $event)" type="text" placeholder="Mã"
                                            class="w-full rounded border border-slate-300 px-2 py-1 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input :value="row.maVach" @input="onGridInput(row, 'maVach', $event)" type="text" placeholder="Mã vạch"
                                            class="w-full rounded border border-slate-300 px-2 py-1 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <span v-if="row.giaVon > 0" class="text-xs text-slate-500" title="Giá vốn tự động từ lô hàng">
                                            {{ Number(row.giaVon).toLocaleString() }}
                                        </span>
                                        <span v-else class="text-xs text-slate-400">—</span>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input :value="row.giaBan" @input="onGridInput(row, 'giaBan', $event)" v-money type="number" min="0" step="any"
                                            class="w-full rounded border border-slate-300 px-2 py-1 text-xs text-right focus:ring-2 focus:ring-emerald-500 outline-none money-input">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        `
    });
    app.directive('money', vMoneyDirective);

    // Mount & bind submit handler lên form cha
    let vueInstance = null;
    window.__vueAppInstance = null;

    function doMount() {
        const root = document.getElementById('createProductApp') || document.getElementById('editProductApp');
        if (!root) { console.warn('[Vue] Root element not found'); return; }
        if (vueInstance) {
            vueInstance.unmount();
            vueInstance = null;
        }
        vueInstance = app.mount(root);
        window.__vueAppInstance = vueInstance;
    }

    function mount() {
        doMount();

        // Gắn click cho nút Lưu ngay khi load (trước cả khi modal mở)
        const btn = document.getElementById('btnLuuSanPham');
        if (btn) {
            btn.removeEventListener('click', onBtnLuuClick);
            btn.addEventListener('click', onBtnLuuClick);
        }

        // Remount mỗi khi modal được mở (shown.bs.modal) để reset state
        // Lắng nghe cả addProductModal (trang tạo) và editProductModal (trang sửa)
        ['addProductModal', 'editProductModal'].forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.removeEventListener('shown.bs.modal', onModalShown);
                modal.addEventListener('shown.bs.modal', onModalShown);
                modal.removeEventListener('hidden.bs.modal', onModalHidden);
                modal.addEventListener('hidden.bs.modal', onModalHidden);
            }
        });
    }

    function onModalShown() {
        // Hard-reset ALL reactive state để ngăn state leak giữa các lần mở modal
        _initDone = false;
        gridData.value = [];
        lastUnitPriceMap.clear();
        if (watchTimer) clearTimeout(watchTimer);

        basicInfo.code = '';
        basicInfo.ten_san_pham = '';
        basicInfo.id_danh_muc = '';
        basicInfo.brand = '';
        basicInfo.mo_ta = '';
        basicInfo.trang_thai = true;
        basicInfo.defaultPrice = 0;
        basicInfo.defaultCost = 0;
        basicInfo.defaultMinStock = 0;
        basicInfo.image = null;
        basicInfo.imagePreview = '';

        baseUnitInput.value = '';
        unitConfig.baseUnit = '';
        unitConfig.basePrice = 0;
        unitConfig.conversionUnits = [];

        attributesConfig.groups = [];
        errors.value = {};
        generalError.value = '';
        submitHadError.value = false;

        // Remount Vue vào root để reset state
        doMount();
        // Gắn click cho nút Lưu sau khi remount
        const btn = document.getElementById('btnLuuSanPham');
        if (btn) {
            btn.removeEventListener('click', onBtnLuuClick);
            btn.addEventListener('click', onBtnLuuClick);
        }
    }

    function onModalHidden() {
        // Unmount để dọn DOM, reset hoàn toàn
        if (vueInstance) {
            vueInstance.unmount();
            vueInstance = null;
        }
    }

    function onBtnLuuClick(e) {
        if (e) e.preventDefault();
        console.log('[DEBUG onBtnLuuClick] window.handleSubmit:', typeof window.handleSubmit);
        console.log('[DEBUG onBtnLuuClick] __vueProductExposed:', window.__vueProductExposed);
        if (typeof window.handleSubmit === 'function') {
            window.handleSubmit();
            return;
        }
        if (window.__vueProductExposed && typeof window.__vueProductExposed.handleSubmit === 'function') {
            window.__vueProductExposed.handleSubmit();
            return;
        }
        if (vueInstance && typeof vueInstance.handleSubmit === 'function') {
            vueInstance.handleSubmit();
            return;
        }
        if (window.__vueAppInstance && typeof window.__vueAppInstance.handleSubmit === 'function') {
            window.__vueAppInstance.handleSubmit();
            return;
        }
        console.error('[Luu] Khong tim thay handleSubmit');
        console.debug('vueInstance:', vueInstance, 'window.handleSubmit:', typeof window.handleSubmit, '__vueProductExposed:', window.__vueProductExposed);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mount);
    } else {
        mount();
    }
})();
