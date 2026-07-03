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

            const attributesConfig = reactive({
                groups: [] // [{ id, name, values: [{id, label}] }]
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
            const submitting = ref(false); // đang gửi request để disable nút Lưu

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
                    unitConfig.basePrice = prod.unitConfig?.basePrice ?? (prod.basicInfo?.defaultPrice ?? 0);
                    unitConfig.conversionUnits = (prod.unitConfig?.conversionUnits || []).map(u => ({
                        id: u.id || uid(),
                        name: u.name || '',
                        rate: u.ty_le_quy_doi ?? u.rate ?? 1,
                        price: u.gia_ban_quy_doi ?? u.price ?? 0
                    }));

                    attributesConfig.groups = (prod.attributesConfig?.groups || []).map(g => ({
                        id: g.id || uid(),
                        name: g.name || '',
                        values: (g.values || []).map(v => ({
                            id: v.id ?? (g.id + '_' + (v.label || v)),
                            label: v.label ?? v
                        })),
                        valueInput: ''
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

                        const rowUnitName = (bt.units && bt.units.length > 0)
                            ? bt.units[0].ten_don_vi || ''
                            : (prod.unitConfig?.baseUnit || '');

                        const unitKey = rowUnitName === unitConfig.baseUnit
                            ? 'base'
                            : ('cv_' + (bt.units && bt.units.length > 0 ? (bt.units[0].id || uid()) : uid()));

                        return {
                            key: buildRowKey(attrLabels, { key: unitKey, name: rowUnitName }),
                            existingId: bt.id ?? null,
                            attrLabels: attrLabels,
                            attrValueIds: attrValueIds,
                            unitKey: unitKey,
                            unitName: rowUnitName,
                            tyLe: bt.units && bt.units.length > 0 ? (bt.units[0].ty_le_quy_doi ?? bt.ty_le ?? 1) : 1,
                            isBase: bt.units && bt.units.length > 0 ? !!bt.units[0].la_don_vi_mac_dinh : true,
                            tenBienThe: bt.ten_bien_the ?? '',
                            maHang: bt.ma_hang ?? '',
                            maVach: bt.ma_vach ?? '',
                            giaVon: parseFloat(bt.gia_von) || 0,
                            giaBan: parseFloat(bt.gia_ban) || 0,
                            dinhMucToiThieu: bt.dinh_muc_toi_thieu ?? 0,
                            soLuong: bt.so_luong_ton ?? 0,
                            touched: {},
                            savedUnits: (bt.units || []).map(u => ({
                                id: u.id,
                                ten_don_vi: u.ten_don_vi,
                                ty_le_quy_doi: u.ty_le_quy_doi,
                                gia_von_quy_doi: u.gia_von_quy_doi,
                                gia_ban_quy_doi: u.gia_ban_quy_doi,
                                ma_hang: u.ma_hang,
                                ma_vach: u.ma_vach
                            }))
                        };
                    });
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
                            // Đảm bảo mỗi value có id ổn định
                            if (typeof v === 'string') {
                                return { id: g.id + '_' + v, label: v };
                            }
                            return v;
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
                            result.push({ ...prefix, [g.name]: v.label, __ids: (prefix.__ids || []).concat([v.id]) });
                        });
                    });
                    return result;
                }, [{}]);
            }

            // ------- BUILD KEY: định danh bền vững cho mỗi row -------
            function buildRowKey(attrCombo, unit) {
                const attrPart = Object.keys(attrCombo)
                    .filter(k => k !== '__ids')
                    .sort()
                    .map(k => `${k}=${attrCombo[k]}`)
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

                        // Tên biến thể: ghép tên sp + thuộc tính (nếu có) + đơn vị
                        const attrPart = Object.keys(combo)
                            .filter(k => k !== '__ids')
                            .map(k => combo[k])
                            .join(' - ');
                        const tenBienThe = attrPart
                            ? `${basicInfo.ten_san_pham.trim() || 'Sản phẩm'} (${attrPart}${attrPart ? ' - ' : ''}${u.name})`
                            : `${basicInfo.ten_san_pham.trim() || 'Sản phẩm'} (${u.name})`;

                        newRows.push({
                            key: key,
                            // Metadata
                            attrLabels: Object.keys(combo)
                                .filter(k => k !== '__ids')
                                .reduce((o, k) => { o[k] = combo[k]; return o; }, {}),
                            attrValueIds: combo.__ids || [],
                            unitKey: u.key,
                            unitName: u.name,
                            tyLe: u.tyLe,
                            isBase: u.isBase,
                            // Field người dùng nhập - GIỮ NGUYÊN từ row cũ nếu có
                            tenBienThe: tenBienThe,
                            maHang: old?.maHang ?? (basicInfo.code.trim() ? `${basicInfo.code.trim()}-${u.name}` : ''),
                            maVach: old?.maVach ?? '',
                            giaVon: old?.giaVon ?? ((parseFloat(basicInfo.defaultCost) || 0) * ((parseFloat(u.tyLe) || 1) / baseRatio)),
                            giaBan: old?.giaBan ?? (u.price || parseFloat(basicInfo.defaultPrice) || 0),
                            dinhMucToiThieu: old?.dinhMucToiThieu ?? (parseInt(basicInfo.defaultMinStock) || 0)
                        });
                    });
                });

                gridData.value = newRows;
            }

            // ------- WATCH: lắng nghe thay đổi -------
            let watchTimer = null;
            function debouncedRegen() {
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
                unitConfig.conversionUnits.push({ id: uid(), name: '', rate: 1, price: 0 });
            }
            function removeConversion(idx) {
                unitConfig.conversionUnits.splice(idx, 1);
            }
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
                    if (row.touched && row.touched.giaBan) return; // user đã sửa -> không ghi đè
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
                attributesConfig.groups.push({ id: uid(), name: '', values: [], valueInput: '' });
            }
            function removeAttrGroup(idx) {
                attributesConfig.groups.splice(idx, 1);
            }
            function addAttrValue(group) {
                const v = (group.valueInput || '').trim();
                if (!v) return;
                if (!group.values.includes(v)) group.values.push(v);
                group.valueInput = '';
            }
            function removeAttrValue(group, vidx) {
                group.values.splice(vidx, 1);
            }
            function onAttrValueKey(group, e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    addAttrValue(group);
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

// Cascade: khi user nhập giaVon/giaBan ở BẤT KỲ dòng nào,
            // các dòng cùng attrCombo nhưng khác đơn vị sẽ tự fill theo tỷ lệ.
            // Quy tắc: row đã touched sẽ KHÔNG bị ghi đè (giữ giá trị user đã nhập).
            function setUnitTouched(row, field) {
                row.touched = row.touched || {};
                row.touched[field] = true;
            }

            // Tính tỷ lệ đơn vị so với base.
            // ty_le(lon)=1, ty_le(thùng)=24 => 1 thùng = 24 lon.
            function ratioToBase(unitKey) {
                const u = effectiveUnits.value.find(x => x.key === unitKey);
                return u ? (parseFloat(u.tyLe) || 1) : 1;
            }

            function onGridInput(row, field, e) {
                const v = e.target.value;
                if (field === 'giaVon' || field === 'giaBan' || field === 'soLuong' || field === 'dinhMucToiThieu') {
                    row[field] = parseFloat(v) || 0;
                } else {
                    row[field] = v;
                }
                setUnitTouched(row, field);

                if (field === 'giaBan' || field === 'giaVon') {
                    cascadeByUnit(row, field, row[field]);
                }
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
                    if (other.touched && other.touched[field]) return;

                    const otherRatio = ratioToBase(other.unitKey);
                    other[field] = Math.round((newValue * otherRatio) / sourceRatio);
                });
            }

            function fillDefaultsToGrid() {
                gridData.value.forEach(row => {
                    row.giaVon = parseFloat(basicInfo.defaultCost) || 0;
                    row.giaBan = parseFloat(basicInfo.defaultPrice) || 0;
                    row.dinhMucToiThieu = parseInt(basicInfo.defaultMinStock) || 0;
                    row.touched = { giaVon: true, giaBan: true };
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

                const bienThe = gridData.value.map((row, i) => {
                    const idField = row.existingId ? { id: row.existingId } : {};
                    const unitsPayload = effectiveUnits.value.map(u => {
                        const isThisUnit = u.key === row.unitKey;
                        const savedUnit = (row.savedUnits || []).find(su => String(su.ten_don_vi) === String(u.name)) || {};
                        return {
                            id: savedUnit.id,
                            ten_don_vi: u.name,
                            ty_le_quy_doi: u.tyLe,
                            gia_von_quy_doi: isThisUnit ? (parseFloat(row.giaVon) || 0) : (parseFloat(savedUnit.gia_von_quy_doi) || 0),
                            gia_ban_quy_doi: isThisUnit ? (parseFloat(row.giaBan) || 0) : (parseFloat(savedUnit.gia_ban_quy_doi) || parseFloat(u.price) || 0),
                            ma_hang: isThisUnit ? (row.maHang || '') : (savedUnit.ma_hang || ''),
                            ma_vach: isThisUnit ? (row.maVach || '') : (savedUnit.ma_vach || '')
                        };
                    });

                    return Object.assign({}, idField, {
                        ten_bien_the: row.tenBienThe,
                        ma_hang: row.maHang,
                        ma_vach: row.maVach,
                        gia_von: parseFloat(row.giaVon) || 0,
                        gia_ban: parseFloat(row.giaBan) || 0,
                        so_luong_ton: parseInt(row.soLuong) || 0,
                        dinh_muc_toi_thieu: parseInt(row.dinhMucToiThieu) || 0,
                        thuoc_tinh_ids: Array.isArray(row.attrValueIds) ? row.attrValueIds.join(',') : (row.attrValueIds || ''),
                        units: unitsPayload
                    });
                });

                return {
                    ten_san_pham: basicInfo.ten_san_pham.trim(),
                    id_danh_muc: parseInt(basicInfo.id_danh_muc) || null,
                    thuong_hieu: basicInfo.brand.trim(),
                    mo_ta: basicInfo.mo_ta.trim(),
                    trang_thai: basicInfo.trang_thai ? 1 : 0,
                    loai_bien_the: loai_bien_the,
                    bien_the: bienThe
                };
            }

            // ------- SUBMIT (AJAX JSON) -------
            function getCsrfToken() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta && meta.content) return meta.content;
                if (DATA && DATA.csrfToken) return DATA.csrfToken;
                return '';
            }

            function handleSubmit() {
                const errs = validate();
                clearErrors();
                submitting.value = true;
                if (errs.length) {
                    generalError.value = 'Vui lòng kiểm tra lại các trường:\n• ' + errs.join('\n• ');
                    submitting.value = false;
                    return;
                }

                const payload = buildPayload();
                const csrf = getCsrfToken();
                const form = document.getElementById('productForm');
                const actionUrl = form ? form.getAttribute('action') : '/admin/san-pham';

                // Nếu có file ảnh, dùng FormData (multipart) để upload file; nếu không, gửi JSON thuần
                const hasImage = !!basicInfo.image;
                let body;
                let headers = {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                };

                if (hasImage) {
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
                    });
                    fd.append('hinh_anh', basicInfo.image);
                    body = fd;
                } else {
                    headers['Content-Type'] = 'application/json';
                    body = JSON.stringify(payload);
                }

                const method = DATA.editMode ? 'PUT' : 'POST';
                if (hasImage && DATA.editMode) {
                    body.append('_method', 'PUT');
                }

                fetch(actionUrl, {
                    method: method,
                    headers: headers,
                    body: body,
                    credentials: 'same-origin'
                })
                .then(async res => {
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
                        // Gom tất cả lỗi thành 1 thông báo chung cho tiện
                        const summary = [];
                        Object.keys(localized).forEach(field => {
                            localized[field].forEach(m => summary.push('• ' + m));
                        });
                        generalError.value = 'Không thể lưu sản phẩm:\n' + summary.join('\n');
                        // Cuộn modal về đầu để user thấy lỗi
                        try {
                            const body = document.getElementById('addProductModalBody');
                            if (body) body.scrollTop = 0;
                        } catch (_) {}
                        return;
                    }
                    // Lỗi khác (500, 403, ...)
                    generalError.value = (data && data.message ? data.message : ('HTTP ' + res.status));
                })
                .catch(err => {
                    generalError.value = 'Lỗi mạng: ' + (err && err.message ? err.message : String(err));
                })
                .finally(() => {
                    submitting.value = false;
                });
            }

            // Cập nhật DOM hiển thị lỗi + spinner (vì button Lưu và alert box nằm ngoài Vue template)
            function syncErrorUi() {
                try {
                    const box = document.getElementById('formErrorBox');
                    const spinner = document.getElementById('btnLuuSpinner');
                    const icon = document.getElementById('btnLuuIcon');
                    const btn = document.getElementById('btnLuuSanPham');

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
                            btn.disabled = false;
                            btn.classList.remove('disabled');
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
                    regenerateGrid();
                }

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

            // Theo dõi thay đổi của generalError & submitting để đồng bộ UI
            watch(generalError, () => syncErrorUi());
            watch(submitting, () => syncErrorUi());

            return {
                basicInfo, unitConfig, attributesConfig, sectionOpen,
                gridData, effectiveAttrGroups,
                errors, generalError, submitting, clearErrors,
                danhMucs: DATA.danhMucs,
                addConversion, removeConversion, onConversionRateInput,
                addAttrGroup, removeAttrGroup, addAttrValue, removeAttrValue, onAttrValueKey,
                onImageSelect, clearImage, onGridInput, fillDefaultsToGrid,
                handleSubmit, validate, buildPayload
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
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Giá vốn <span class="text-slate-400">(đv cơ bản)</span></label>
                                <input v-model.number="basicInfo.defaultCost" type="number" min="0" step="any"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Giá bán <span class="text-slate-400">(đv cơ bản)</span></label>
                                <input v-model.number="basicInfo.defaultPrice" type="number" min="0" step="any"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
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
                                <label class="block text-xs font-medium text-slate-600 mb-1">Tên đơn vị cơ bản <span class="text-red-500">*</span></label>
                                <input v-model="unitConfig.baseUnit" type="text" placeholder="VD: Lon"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
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

                        <div v-for="(g, gi) in attributesConfig.groups" :key="g.id" class="mb-3 p-3 rounded-md border border-slate-200 bg-slate-50/50">
                            <div class="flex items-center justify-between mb-2">
                                <input v-model="g.name" type="text" placeholder="Tên thuộc tính (VD: Màu sắc)"
                                    class="flex-1 rounded border border-slate-300 px-2 py-1.5 text-sm font-medium focus:ring-2 focus:ring-amber-500 outline-none">
                                <button type="button" @click="removeAttrGroup(gi)" class="ml-2 text-slate-400 hover:text-red-500" title="Xóa nhóm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mb-2 min-h-[28px]">
                                <span v-for="(v, vi) in g.values" :key="v + vi"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-xs">
                                    {{ v }}
                                    <button type="button" @click="removeAttrValue(g, vi)" class="hover:text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            </div>
                            <input v-model="g.valueInput" type="text" placeholder="Nhập giá trị, Enter để thêm (VD: Đỏ)"
                                @keydown="onAttrValueKey(g, $event)"
                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

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

    // Mount & bind submit handler lên form cha
    let vueInstance = null;

    function doMount() {
        const root = document.getElementById('createProductApp') || document.getElementById('editProductApp');
        if (!root) return;
        if (vueInstance) {
            vueInstance.unmount();
            vueInstance = null;
        }
        vueInstance = app.mount(root);
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
        // 1. window.handleSubmit (expose trong onMounted ngay sau khi định nghĩa)
        if (typeof window.handleSubmit === 'function') {
            window.handleSubmit();
            return;
        }
        // 2. window.__vueProductExposed.handleSubmit
        if (window.__vueProductExposed && typeof window.__vueProductExposed.handleSubmit === 'function') {
            window.__vueProductExposed.handleSubmit();
            return;
        }
        // 3. vueInstance (Vue 3 expose trực tiếp trên instance)
        if (vueInstance && typeof vueInstance.handleSubmit === 'function') {
            vueInstance.handleSubmit();
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
