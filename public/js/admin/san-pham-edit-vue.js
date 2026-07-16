/* san-pham-edit-vue.js — Chỉnh sửa sản phẩm (Edit Mode)
 * Tách riêng từ san-pham-create-vue.js
 * - isEditMode cố định = true
 * - Sửa buildPayload: dùng conversionUnits thay vì savedUnits
 * - Sửa handleSubmit: loại bỏ fdEntries undefined
 * - Loại bỏ validate() client-side (chỉ dùng backend)
 * - Loại bỏ loai_bien_the khỏi payload
 * - Creatable Combobox cho thuộc tính: chọn từ DB hoặc gõ tạo mới
 */
(function () {
    const { createApp, reactive, ref, watch, computed, onMounted } = Vue;
    const DATA = window.__CREATE_PRODUCT_DATA__ || { danhMucs: [], csrfToken: '' };
    const uid = () => 'id_' + Math.random().toString(36).slice(2, 10);

    // ============ APP ============
    const app = createApp({
        setup() {
            // ------- STATE -------
            const isEditMode = true; // Cố định Edit Mode

            const basicInfo = reactive({
                code: '',
                ten_san_pham: '',
                id_danh_muc: '',
                brand: '',
                mo_ta: '',
                trang_thai: true,
                defaultPrice: 0,
                defaultCost: 0,
                defaultMinStock: 0,
                image: null,
                imagePreview: ''
            });

            const unitConfig = reactive({
                baseUnit: '',
                basePrice: 0,
                conversionUnits: []
            });

            // availableAttributes: thuộc tính cha từ DB để gợi ý trong dropdown
            const availableAttributes = ref(DATA.availableAttributes || []);

            // availableUnits: tất cả đơn vị tính từ DB để gợi ý trong dropdown
            const availableUnits = ref(DATA.availableUnits || []);

            // allUnitOptions: availableUnits + giá trị hiện tại của baseUnit nếu không có trong list
            // Đảm bảo selected value luôn là option hợp lệ dù không có trong DB
            const allUnitOptions = computed(() => {
                const base = availableUnits.value;
                const current = unitConfig.baseUnit.trim();
                if (current && !base.find(u => u.name === current)) {
                    return [...base, { id: '__custom__', name: current }];
                }
                return base;
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

            // ------- STATE: lỗi validation -------
            const errors = ref({});
            const generalError = ref('');
            const submitting = ref(false);
            const submitHadError = ref(false);
            const formLoaded = ref(false);

            const hasImage = computed(() => basicInfo.image instanceof File);

            function clearErrors() {
                errors.value = {};
                generalError.value = '';
            }

            // ====== initFromProduct: khởi tạo từ dữ liệu DB ======
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
                    unitConfig.basePrice = prod.unitConfig?.basePrice ?? (prod.basicInfo?.defaultPrice ?? 0);
                    unitConfig.conversionUnits = (prod.unitConfig?.conversionUnits || []).map(u => ({
                        id: u.id || uid(),
                        name: u.name || '',
                        rate: u.ty_le_quy_doi ?? u.rate ?? 1,
                        price: u.gia_ban_quy_doi ?? u.price ?? 0,
                        // Giữ lại các trường DB để dùng trong payload
                        _dbId: u.id || null,
                        _giaVonQuyDoi: u.gia_von_quy_doi ?? 0,
                        _giaBanQuyDoi: u.gia_ban_quy_doi ?? 0,
                        _maHang: u.ma_hang ?? '',
                        _maVach: u.ma_vach ?? ''
                    }));

                    attributesConfig.groups = (prod.attributesConfig?.groups || []).map(g => ({
                        id: g.id || uid(),
                        name: g.name || '',
                        values: (g.values || []).map(v => ({
                            id: v.id ?? (g.id + '_' + (v.label || v)),
                            label: v.label ?? v,
                            _isNew: !!v._isNew
                        })),
                        valueInput: '',
                        _dropdownOpen: false,
                        _highlightedIdx: -1
                    }));

                    const attrLabelMap = {};
                    attributesConfig.groups.forEach(g => {
                        g.values.forEach(v => {
                            attrLabelMap[v.id] = { groupName: g.name, label: v.label };
                        });
                    });

                    // Grid: mỗi row = 1 variant từ DB
                    gridData.value = (prod.bien_the || []).map(bt => {
                        const attrValueIds = Array.isArray(bt.thuoc_tinh_ids)
                            ? bt.thuoc_tinh_ids
                            : (bt.thuoc_tinh_ids ? String(bt.thuoc_tinh_ids).split(',').map(x => x.trim()).filter(Boolean) : []);
                        const attrLabels = {};
                        attrValueIds.forEach(id => {
                            const found = attrLabelMap[id];
                            if (found) attrLabels[found.groupName] = found.label;
                        });

                        const rowUnitName = bt.ten_bien_the || prod.unitConfig?.baseUnit || '';
                        const unitKey = rowUnitName === prod.unitConfig?.baseUnit
                            ? 'base'
                            : ('cv_' + (bt.units && bt.units.length > 0 ? (bt.units[0].id || uid()) : uid()));

                        // conversionUnits: units người dùng đang chỉnh sửa (reactive, dùng cho payload)
                        // beingEdited: mark units as "being edited" vs "unchanged"
                        const rowConversionUnits = (bt.units || []).map(u => ({
                            id: u.id,
                            ten_don_vi: u.ten_don_vi,
                            ty_le_quy_doi: u.ty_le_quy_doi,
                            gia_von_quy_doi: u.gia_von_quy_doi,
                            gia_ban_quy_doi: u.gia_ban_quy_doi,
                            ma_hang: u.ma_hang,
                            ma_vach: u.ma_vach,
                            // Flag: true = tồn tại trong DB, false = mới thêm
                            _fromDb: true
                        }));

                        return {
                            key: buildRowKey(attrLabels, { key: unitKey, name: rowUnitName }),
                            existingId: bt.id ?? null,
                            attrLabels: attrLabels,
                            attrValueIds: attrValueIds,
                            unitKey: unitKey,
                            unitName: rowUnitName,
                            tyLe: 1,
                            isBase: rowUnitName === prod.unitConfig?.baseUnit,
                            tenBienThe: bt.ten_bien_the ?? '',
                            maHang: bt.ma_hang ?? '',
                            maVach: bt.ma_vach ?? '',
                            giaVon: parseFloat(bt.gia_von) || 0,
                            giaBan: parseFloat(bt.gia_ban) || 0,
                            dinhMucToiThieu: bt.dinh_muc_toi_thieu ?? 0,
                            soLuong: bt.so_luong_ton ?? 0,
                            touched: {},
                            // conversionUnits: dùng cho payload (reactive, user edits)
                            conversionUnits: rowConversionUnits,
                            // savedUnits: bản sao gốc từ DB (fallback safety)
                            savedUnits: rowConversionUnits.map(u => ({ ...u }))
                        };
                    });
                    _initDone = true;
                    regenerateGrid(); // Tái sinh grid để hiển thị đúng số dòng variant
                } catch (e) {
                    console.error('initFromProduct failed', e);
                }
            }

            // ------- DICTIONARY: dịch thông báo Laravel sang tiếng Việt -------
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
                return m;
            }

            function localizeErrors(errsObj) {
                const out = {};
                Object.keys(errsObj || {}).forEach(field => {
                    out[field] = (errsObj[field] || []).map(translateError);
                });
                return out;
            }

            // ------- STATE: grid -------
            const gridData = ref([]);
            let _initDone = false;

            // ------- COMPUTED: đơn vị thực tế -------
            const effectiveUnits = computed(() => {
                const list = [];
                if (unitConfig.baseUnit.trim()) {
                    list.push({ key: 'base', name: unitConfig.baseUnit.trim(), tyLe: 1,
                        price: parseFloat(unitConfig.basePrice) || parseFloat(basicInfo.defaultPrice) || 0, isBase: true });
                }
                unitConfig.conversionUnits.forEach((u, i) => {
                    if (!u.name.trim()) return;
                    list.push({ key: 'cv_' + u.id, name: u.name.trim(), tyLe: parseInt(u.rate) || 1,
                        price: parseFloat(u.price) || 0, isBase: false });
                });
                return list;
            });

            // ------- COMPUTED: groups thuộc tính hợp lệ -------
            const effectiveAttrGroups = computed(() => {
                return attributesConfig.groups
                    .filter(g => g.name.trim() && g.values.length > 0)
                    .map(g => ({
                        id: g.id, name: g.name.trim(),
                        values: g.values.map(v => {
                            if (typeof v === 'string') return { id: g.id + '_' + v, label: v, _isNew: true };
                            return { id: v.id, label: v.label, _isNew: !!v._isNew };
                        })
                    }));
            });

            // ------- CARTESIAN PRODUCT -------
            function cartesian(groups) {
                if (groups.length === 0) return [{}];
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

            // ------- BUILD KEY -------
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

                        const attrPart = Object.keys(combo)
                            .filter(k => k !== '__ids').map(k => combo[k]).join(' - ');
                        const tenBienThe = attrPart
                            ? `${basicInfo.ten_san_pham.trim() || 'Sản phẩm'} (${attrPart}${attrPart ? ' - ' : ''}${u.name})`
                            : `${basicInfo.ten_san_pham.trim() || 'Sản phẩm'} (${u.name})`;

                        newRows.push({
                            key: key,
                            attrLabels: Object.keys(combo).filter(k => k !== '__ids')
                                .reduce((o, k) => { o[k] = combo[k]; return o; }, {}),
                            attrValueIds: combo.__ids || [],
                            unitKey: u.key,
                            unitName: u.name,
                            tyLe: u.tyLe,
                            isBase: u.isBase,
                            existingId: old?.existingId ?? null,
                            tenBienThe: tenBienThe,
                            maHang: old?.maHang ?? (basicInfo.code.trim() ? `${basicInfo.code.trim()}-${u.name}` : ''),
                            maVach: old?.maVach ?? '',
                            giaVon: old?.giaVon ?? ((parseFloat(basicInfo.defaultCost) || 0) * ((parseFloat(u.tyLe) || 1) / baseRatio)),
                            giaBan: old?.giaBan ?? (u.price || parseFloat(basicInfo.defaultPrice) || 0),
                            dinhMucToiThieu: old?.dinhMucToiThieu ?? (parseInt(basicInfo.defaultMinStock) || 0),
                            // Giữ lại units từ row cũ (quan trọng: preserve per-row units data)
                            conversionUnits: old?.conversionUnits
                                ? [...old.conversionUnits]
                                : (unitConfig.conversionUnits || []).map(u2 => ({
                                    id: u2.id,
                                    ten_don_vi: u2.name,
                                    ty_le_quy_doi: u2.rate,
                                    gia_von_quy_doi: u2._giaVonQuyDoi ?? 0,
                                    gia_ban_quy_doi: u2._giaBanQuyDoi ?? 0,
                                    ma_hang: u2._maHang ?? '',
                                    ma_vach: u2._maVach ?? ''
                                  })),
                            savedUnits: old?.savedUnits ? [...old.savedUnits] : []
                        });
                    });
                });

                gridData.value = newRows;
            }

            // ------- WATCH: tái sinh grid khi cấu hình thay đổi -------
            let watchTimer = null;
            function debouncedRegen() {
                if (!_initDone) return;
                clearTimeout(watchTimer);
                watchTimer = setTimeout(regenerateGrid, 50);
            }
            watch(() => unitConfig.baseUnit, debouncedRegen);
            watch(() => unitConfig.basePrice, debouncedRegen);
            watch(() => unitConfig.conversionUnits, debouncedRegen, { deep: true });
            watch(() => attributesConfig.groups, debouncedRegen, { deep: true });

            // ------- IMAGE -------
            function onImageSelect(e) {
                const file = e.target.files[0];
                if (!file) return;
                basicInfo.image = file;
                basicInfo.imagePreview = URL.createObjectURL(file);
            }

            function clearImage() {
                basicInfo.image = null;
                basicInfo.imagePreview = '';
            }

            // ------- GRID INPUT -------
            function onGridInput(row, field, e) {
                const val = e.target.value;
                row[field] = val;
                row.touched[field] = true;
            }

            // ------- ADD/REMOVE CONVERSION UNIT -------
            function addConversion() {
                unitConfig.conversionUnits.push({ id: uid(), name: '', rate: 1, price: 0,
                    _dbId: null, _giaVonQuyDoi: 0, _giaBanQuyDoi: 0, _maHang: '', _maVach: '' });
            }

            function removeConversion(index) {
                unitConfig.conversionUnits.splice(index, 1);
            }

            function onConversionRateInput(unit, val) {
                const n = parseInt(val);
                unit.rate = isNaN(n) || n < 1 ? 1 : n;
            }

            // ------- ADD/REMOVE ATTRIBUTE GROUP -------
            function addAttrGroup() {
                attributesConfig.groups.push({
                    id: uid(), name: '', values: [],
                    valueInput: '', _dropdownOpen: false, _highlightedIdx: -1
                });
            }

            function removeAttrGroup(index) {
                attributesConfig.groups.splice(index, 1);
            }

            // Lấy danh sách gợi ý cho dropdown: values từ availableAttributes cha có cùng name
            function getDropdownValues(group) {
                const matched = availableAttributes.value.find(a => a.name === group.name);
                if (!matched) return [];
                return matched.values || [];
            }

            // Lọc dropdown: không show giá trị đã được chọn rồi
            function getFilteredDropdown(group) {
                const all = getDropdownValues(group);
                const selectedLabels = new Set(group.values.map(v => v.label));
                return all.filter(v => !selectedLabels.has(v.label));
            }

            function toggleDropdown(group) {
                group._dropdownOpen = !group._dropdownOpen;
                if (group._dropdownOpen) group._highlightedIdx = -1;
            }
            function closeDropdown(group) {
                group._dropdownOpen = false;
                group._highlightedIdx = -1;
            }

            function selectFromDropdown(group, item) {
                if (!group.values.find(v => v.id === item.id)) {
                    group.values.push({ id: item.id, label: item.label, _isNew: false });
                }
                group.valueInput = '';
                closeDropdown(group);
            }

            function addAttrValue(g) {
                const v = (g.valueInput || '').trim();
                if (!v) return;
                if (group.values.find(x => x.label === v)) {
                    g.valueInput = '';
                    return;
                }
                // id=null đánh dấu là item mới tạo bởi user (chưa có trong DB)
                g.values.push({ id: null, label: v, _isNew: true });
                g.valueInput = '';
                closeDropdown(g);
            }

            function removeAttrValue(g, vi) {
                g.values.splice(vi, 1);
            }

            function onAttrValueKey(g, e) {
                const filtered = getFilteredDropdown(g);

                if (g._dropdownOpen && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    e.preventDefault();
                    if (e.key === 'ArrowDown') {
                        g._highlightedIdx = Math.min(g._highlightedIdx + 1, filtered.length - 1);
                    } else {
                        g._highlightedIdx = Math.max(g._highlightedIdx - 1, -1);
                    }
                    return;
                }
                if (g._dropdownOpen && e.key === 'Enter' && g._highlightedIdx >= 0) {
                    e.preventDefault();
                    selectFromDropdown(g, filtered[g._highlightedIdx]);
                    return;
                }
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    addAttrValue(g);
                }
                if (e.key === 'Escape') {
                    closeDropdown(g);
                }
            }

            // ====== buildPayload: tạo payload cho request ======
            // FIX: dùng conversionUnits (reactive, user đang sửa) thay vì savedUnits
            function buildPayload() {
                // Thu thập thuộc tính MỚI (user gõ tay, chưa có trong DB)
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

                const bienThe = gridData.value.map((row, i) => {
                    const idField = row.existingId ? { id: row.existingId } : {};

                    // Dùng conversionUnits vì đây là dữ liệu reactive, phản ánh chỉnh sửa của user
                    // Lọc: chỉ gửi đơn vị có ty_le > 1 (đơn vị quy đổi)
                    console.log(`[buildPayload] row[${i}] key=${row.key} unitName=${row.unitName} conversionUnits=`, JSON.parse(JSON.stringify(row.conversionUnits || [])));
                    const unitsPayload = (row.conversionUnits || [])
                        .filter(u => (parseInt(u.ty_le_quy_doi) || 1) > 1)
                        .map(u => ({
                            id: u.id || null,
                            ten_don_vi: u.ten_don_vi || u.name || '',
                            ty_le_quy_doi: parseInt(u.ty_le_quy_doi) || 1,
                            gia_von_quy_doi: parseFloat(u.gia_von_quy_doi) || 0,
                            gia_ban_quy_doi: parseFloat(u.gia_ban_quy_doi) || 0,
                            ma_hang: u.ma_hang || '',
                            ma_vach: u.ma_vach || ''
                        }));

                    console.log(`[buildPayload] row[${i}] unitName=${row.unitName} unitsPayload.length=${unitsPayload.length}`);

                    return Object.assign({}, idField, {
                        ten_bien_the: row.unitName,
                        ma_hang: row.maHang,
                        ma_vach: row.maVach,
                        gia_von: parseFloat(row.giaVon) || 0,
                        gia_ban: parseFloat(row.giaBan) || 0,
                        so_luong_ton: parseInt(row.soLuong) || 0,
                        dinh_muc_toi_thieu: parseInt(row.dinhMucToiThieu) || 0,
                        thuoc_tinh_ids: Array.isArray(row.attrValueIds)
                            ? row.attrValueIds.join(',') : (row.attrValueIds || ''),
                        units: unitsPayload
                    });
                });

                return {
                    ten_san_pham: basicInfo.ten_san_pham.trim(),
                    id_danh_muc: parseInt(basicInfo.id_danh_muc) || null,
                    thuong_hieu: basicInfo.brand.trim(),
                    mo_ta: basicInfo.mo_ta.trim(),
                    trang_thai: basicInfo.trang_thai ? 1 : 0,
                    bien_the: bienThe,
                    new_attributes: newAttributes.map(a => ({
                        group_name: a.groupName,
                        parent_id: groupNameToParentId[a.groupName] || null,
                        label: a.label
                    }))
                };
            }

            function getCsrfToken() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta && meta.content) return meta.content;
                if (DATA && DATA.csrfToken) return DATA.csrfToken;
                return '';
            }

            // ====== handleSubmit: gửi request PUT ======
            async function handleSubmit() {
                submitHadError.value = false;
                clearErrors();
                submitting.value = true;

                const payload = buildPayload();
                console.log('[handleSubmit] Payload:', JSON.parse(JSON.stringify(payload)));

                const csrf = getCsrfToken();
                const form = document.getElementById('productForm');
                const actionUrl = form ? form.getAttribute('action') : '/admin/san-pham';

                let body;
                const headers = {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                };

                if (hasImage.value) {
                    const fd = new FormData();
                    fd.append('_method', 'PUT');
                    ['ten_san_pham', 'id_danh_muc', 'thuong_hieu', 'mo_ta', 'trang_thai'].forEach(k => {
                        if (payload[k] !== undefined && payload[k] !== null) fd.append(k, payload[k]);
                    });
                    (payload.bien_the || []).forEach((bt, i) => {
                        Object.keys(bt).forEach(k => {
                            if (k === 'units') return;
                            if (bt[k] !== undefined && bt[k] !== null) fd.append(`bien_the[${i}][${k}]`, bt[k]);
                        });
                        (bt.units || []).forEach((u, j) => {
                            Object.keys(u).forEach(uk => {
                                if (u[uk] !== undefined && u[uk] !== null) fd.append(`bien_the[${i}][units][${j}][${uk}]`, u[uk]);
                            });
                        });
                    });
                    fd.append('hinh_anh', basicInfo.image);
                    body = fd;
                } else {
                    const fd = new FormData();
                    fd.append('_method', 'PUT');
                    ['ten_san_pham', 'id_danh_muc', 'thuong_hieu', 'mo_ta', 'trang_thai'].forEach(k => {
                        if (payload[k] !== undefined && payload[k] !== null) fd.append(k, payload[k]);
                    });
                    (payload.bien_the || []).forEach((bt, i) => {
                        Object.keys(bt).forEach(k => {
                            if (k === 'units') return;
                            if (bt[k] !== undefined && bt[k] !== null) fd.append(`bien_the[${i}][${k}]`, bt[k]);
                        });
                        (bt.units || []).forEach((u, j) => {
                            Object.keys(u).forEach(uk => {
                                if (u[uk] !== undefined && u[uk] !== null) fd.append(`bien_the[${i}][units][${j}][${uk}]`, u[uk]);
                            });
                        });
                    });
                    body = fd;
                }

                // DEBUG: log FormData entries
                console.log('[handleSubmit] units count in payload:',
                    payload.bien_the.reduce((sum, bt) => sum + (bt.units || []).length, 0));

                const method = 'POST'; // Laravel đọc _method=PUT từ FormData

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

                    // 422: lỗi validation Laravel
                    if (res.status === 422 && data && data.errors) {
                        errors.value = localizeErrors(data.errors);
                        const summary = [];
                        Object.keys(errors.value).forEach(field => {
                            errors.value[field].forEach(m => summary.push('• ' + m));
                        });
                        generalError.value = 'Không thể lưu sản phẩm:\n' + summary.join('\n');
                        submitHadError.value = true;
                        try {
                            const modalBody = document.getElementById('editProductModalBody');
                            if (modalBody) modalBody.scrollTop = 0;
                        } catch (_) {}
                        return;
                    }

                    // Các lỗi khác
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

            // ====== syncErrorUi: cập nhật DOM hiển thị lỗi ======
            function syncErrorUi() {
                try {
                    const box = document.getElementById('formErrorBox');
                    const spinner = document.getElementById('btnLuuSpinner');
                    const icon = document.getElementById('btnLuuIcon');
                    const btn = document.getElementById('btnLuuSanPham');

                    if (box) {
                        box.textContent = generalError.value || '';
                        box.classList.toggle('d-none', !generalError.value);
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
                            if (formLoaded.value && !submitHadError.value) {
                                btn.disabled = false;
                                btn.classList.remove('disabled');
                            }
                        }
                    }
                } catch (e) {
                    console.error('syncErrorUi failed', e);
                }
            }

            // ====== MOUNT: khởi tạo ======
            onMounted(() => {
                if (isEditMode && DATA.productData) {
                    initFromProduct(DATA.productData);
                    _initDone = true;
                    regenerateGrid();
                }
                formLoaded.value = true;

                const btn = document.getElementById('btnLuuSanPham');
                if (btn) { btn.disabled = false; btn.classList.remove('disabled'); }

                window.__vueProductExposed = {
                    handleSubmit, buildPayload,
                    getBasicInfo: () => basicInfo,
                    regenerateGrid, syncErrorUi, clearErrors,
                    getGeneralError: () => generalError.value,
                    getIsSubmitting: () => submitting.value
                };
                window.handleSubmit = handleSubmit;
            });

            watch(generalError, () => syncErrorUi());
            watch(submitting, () => syncErrorUi());

            return {
                basicInfo, unitConfig, attributesConfig, sectionOpen,
                gridData, effectiveAttrGroups,
                errors, generalError, submitting, clearErrors,
                danhMucs: DATA.danhMucs,
                availableAttributes,
                availableUnits,
                allUnitOptions,
                addConversion, removeConversion, onConversionRateInput,
                addAttrGroup, removeAttrGroup, addAttrValue, removeAttrValue,
                onAttrValueKey, toggleDropdown, closeDropdown,
                getDropdownValues, getFilteredDropdown, selectFromDropdown,
                onImageSelect, clearImage, onGridInput,
                handleSubmit, buildPayload
            };
        },

        template: `
        <div class="bg-slate-50 p-3">
            <!-- BLOCK 1: Thông tin cơ bản -->
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
                            <label class="block text-xs font-medium text-slate-600 mb-1">Mã hàng</label>
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
                                <div class="relative">
                                    <select v-model="unitConfig.baseUnit"
                                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white appearance-none cursor-pointer">
                                        <option value="">— Chọn đơn vị —</option>
                                        <option v-for="u in allUnitOptions" :key="u.id" :value="u.name">{{ u.name }}</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                        <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                    </div>
                                </div>
                                <p v-if="unitConfig.baseUnit && !availableUnits.find(u => u.name === unitConfig.baseUnit)"
                                    class="mt-1 text-[11px] text-indigo-600 italic">
                                    Đơn vị "<b class="font-medium not-italic">{{ unitConfig.baseUnit }}</b>" sẽ được tạo mới khi lưu.
                                </p>
                            </div>
                            <div class="col-span-6">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Giá bán (đơn vị cơ bản)</label>
                                <input v-model.number="unitConfig.basePrice" type="number" min="0" step="any"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
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
                                            <input v-model="u.name" type="text" placeholder="VD: Thùng"
                                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                        </td>
                                        <td class="p-2">
                                            <div class="flex items-center gap-1">
                                                <input :value="u.rate" @input="onConversionRateInput(u, $event.target.value)" type="number" min="1"
                                                    class="w-16 rounded border border-slate-300 px-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-indigo-500 outline-none">
                                                <span class="text-xs text-slate-500">{{ unitConfig.baseUnit || 'đv cơ bản' }}</span>
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            <input v-model.number="u.price" type="number" min="0" step="any"
                                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm text-right focus:ring-2 focus:ring-indigo-500 outline-none">
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

            <!-- BLOCK 3: Bảng Hàng cùng loại (Grid) -->
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
                                    <th class="px-2 py-2 text-left font-medium">Đơn vị</th>
                                    <th class="px-2 py-2 text-left font-medium">Tỷ lệ</th>
                                    <th class="px-2 py-2 text-left font-medium">Mã hàng</th>
                                    <th class="px-2 py-2 text-left font-medium">Mã vạch</th>
                                    <th class="px-2 py-2 text-right font-medium">Giá vốn</th>
                                    <th class="px-2 py-2 text-right font-medium">Giá bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in gridData" :key="row.key" class="border-t border-slate-100 hover:bg-slate-50/50">
                                    <td v-for="g in effectiveAttrGroups" :key="g.id" class="px-2 py-1.5 text-slate-700">
                                        {{ row.attrLabels[g.name] || '-' }}
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
                                        <input :value="row.giaVon" @input="onGridInput(row, 'giaVon', $event)" type="number" min="0" step="any"
                                            class="w-full rounded border border-slate-300 px-2 py-1 text-xs text-right focus:ring-2 focus:ring-emerald-500 outline-none">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input :value="row.giaBan" @input="onGridInput(row, 'giaBan', $event)" type="number" min="0" step="any"
                                            class="w-full rounded border border-slate-300 px-2 py-1 text-xs text-right focus:ring-2 focus:ring-emerald-500 outline-none">
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

    // Mount & bind submit handler
    function mount() {
        const root = document.getElementById('editProductApp');
        if (!root) { console.warn('[Vue] Root element #editProductApp not found'); return; }
        const instance = app.mount(root);
        window.__vueAppInstance = instance;

        const btn = document.getElementById('btnLuuSanPham');
        if (btn) {
            btn.removeEventListener('click', onBtnLuuClick);
            btn.addEventListener('click', onBtnLuuClick);
        }
    }

    function onBtnLuuClick(e) {
        if (e) e.preventDefault();
        if (typeof window.handleSubmit === 'function') {
            window.handleSubmit();
            return;
        }
        if (window.__vueProductExposed && typeof window.__vueProductExposed.handleSubmit === 'function') {
            window.__vueProductExposed.handleSubmit();
            return;
        }
        if (window.__vueAppInstance && typeof window.__vueAppInstance.handleSubmit === 'function') {
            window.__vueAppInstance.handleSubmit();
            return;
        }
        console.error('[Luu] Khong tim thay handleSubmit');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mount);
    } else {
        mount();
    }
})();
