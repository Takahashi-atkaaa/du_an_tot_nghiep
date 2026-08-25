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
    const { createApp, reactive, ref, watch, computed, onMounted, nextTick } = Vue;
    const DATA = window.__CREATE_PRODUCT_DATA__ || { danhMucs: [], csrfToken: '' };
    const uid = () => 'id_' + Math.random().toString(36).slice(2, 10);
    const asArray = value => Array.isArray(value) ? value : [];

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
            let isInitializing = true; // Chặn watch tự sinh lại grid khi khôi phục dữ liệu Edit

            const hasImage = computed(() => basicInfo.image instanceof File);

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
                // Signature = chuỗi (attrValueIds đã sort) + '__' + (ten_don_vi hoặc ty_le)
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

// SAFETY: Nếu unitConfig.baseUnit rỗng (có thể do toEditVueData phân loại nhầm base),
                    // fallback về biến thể gốc đầu tiên có ten_don_vi.
                    // Đảm bảo buildPayload không gửi chuỗi rỗng "" làm ten_don_vi.
                    const allVariantsForBase = asArray(prod.variants).length > 0
                        ? asArray(prod.variants)
                        : asArray(prod.bien_the);
                    const baseVariant = allVariantsForBase.find(v => !v.la_don_vi && v.ten_don_vi)
                        || allVariantsForBase.find(v => v.ten_don_vi)
                        || null;

                    // Ưu tiên unitConfig.baseUnit từ API; fallback về biến thể gốc đầu tiên.
                    const apiBaseUnit = prod.unitConfig?.baseUnit ?? '';
                    if (apiBaseUnit) {
                        unitConfig.baseUnit = apiBaseUnit;
                    } else if (baseVariant && baseVariant.ten_don_vi) {
                        unitConfig.baseUnit = baseVariant.ten_don_vi;
                    } else {
                        unitConfig.baseUnit = '';
                    }

                    unitConfig.basePrice = prod.unitConfig?.basePrice
                        ?? (baseVariant?.gia_ban ? parseFloat(baseVariant.gia_ban) : 0)
                        ?? (prod.basicInfo?.defaultPrice ?? 0);
                    // Loại bỏ conversion trùng tên với đơn vị cơ bản để tránh grid sinh ra 2 dòng cùng tên.
                    const safeBase = (prod.unitConfig?.baseUnit ?? '').trim().toLowerCase();
                    // Gom các đơn vị quy đổi: ưu tiên prod.unitConfig.conversionUnits;
                    // fallback: gom từ TẤT CẢ variants (kết hợp don_vi_quy_doi + units) để đảm bảo
                    // đơn vị quy đổi hiện có trong DB luôn xuất hiện lại trong dropdown khi Edit,
                    // kể cả khi variant đầu tiên là variant "Thùng" (không có DonViQuyDoi gắn vào).
                    const allVariants = asArray(prod.variants).length > 0
                        ? asArray(prod.variants)
                        : asArray(prod.bien_the);
                    const fallbackConversion = [];
                    const seenNames = new Set();
                    allVariants.forEach(v => {
                        const sources = [
                            ...asArray(v.don_vi_quy_doi),
                            ...asArray(v.units)
                        ];
                        sources.forEach(u => {
                            const key = (u.ten_don_vi || u.name || '').trim().toLowerCase();
                            if (!key || seenNames.has(key)) return;
                            seenNames.add(key);
                            fallbackConversion.push(u);
                        });
                    });
                    const conversionSource = asArray(prod.unitConfig?.conversionUnits).length > 0
                        ? asArray(prod.unitConfig?.conversionUnits)
                        : fallbackConversion;
                    unitConfig.conversionUnits = asArray(conversionSource)
                        .filter(u => safeBase === '' || String(u.ten_don_vi || u.name || '').trim().toLowerCase() !== safeBase)
                        .map(u => ({
                        id: u.id || uid(),
                        name: u.ten_don_vi || u.name || '',
                        ten_don_vi: u.ten_don_vi || u.name || '',
                        name_input: '',
                        rate: u.ty_le_quy_doi ?? u.so_luong_san_pham_trong_don_vi ?? u.rate ?? 1,
                        price: u.gia_ban_quy_doi ?? u.price ?? 0,
                        // Giữ lại các trường DB để dùng trong payload
                        _dbId: u.id || null,
                        _giaVonQuyDoi: u.gia_von_quy_doi ?? 0,
                        _giaBanQuyDoi: u.gia_ban_quy_doi ?? 0,
                        _maHang: u.ma_hang ?? u.maHang ?? '',
                        _maVach: u.ma_vach ?? u.maVach ?? '',
                        // Gán don_vi_chuan_id dựa trên matching với availableUnits
                        don_vi_chuan_id: (() => {
                            const found = availableUnits.value.find(a =>
                                a.ten_don_vi === (u.ten_don_vi || u.name || '') &&
                                a.qty === (u.so_luong_san_pham_trong_don_vi || u.ty_le_quy_doi || u.rate || 1)
                            );
                            return found ? found.id : null;
                        })()
                    }));

                    const attrGroupByValueId = new Map();
                    asArray(availableAttributes.value).forEach(group => {
                        asArray(group.values).forEach(value => {
                            attrGroupByValueId.set(String(value.id), {
                                id: group.id,
                                name: group.name
                            });
                        });
                    });

                    const rawAttributeGroups = prod.attributesConfig?.groups || [];
                    const productVariants = asArray(prod.variants).length > 0
                        ? asArray(prod.variants)
                        : asArray(prod.bien_the);
                    const restoredGroups = new Map();
                    asArray(rawAttributeGroups).forEach(group => {
                        asArray(group.values).forEach(rawValue => {
                            const value = typeof rawValue === 'object'
                                ? rawValue
                                : { id: null, label: rawValue };
                            const availableGroup = attrGroupByValueId.get(String(value.id));
                            const groupName = availableGroup?.name || group.name || 'Thuộc tính';
                            const groupId = availableGroup?.id || group.id || uid();
                            const key = String(groupId);

                            if (!restoredGroups.has(key)) {
                                restoredGroups.set(key, {
                                    id: groupId,
                                    name: groupName,
                                    values: [],
                                    valueInput: '',
                                    _dropdownOpen: false,
                                    _highlightedIdx: -1
                                });
                            }

                            const restoredGroup = restoredGroups.get(key);
                            if (!restoredGroup.values.some(v => String(v.id) === String(value.id))) {
                                restoredGroup.values.push({
                                    id: value.id ?? null,
                                    label: value.label ?? value.ten_thuoc_tinh ?? '',
                                    _isNew: !!value._isNew
                                });
                            }
                        });
                    });

                    // Nếu API không gửi groups, dựng lại nhóm từ toàn bộ ID thuộc tính của variants.
                    if (restoredGroups.size === 0) {
                        const variantAttrIds = new Set();
                        productVariants.forEach(variant => {
                            const ids = Array.isArray(variant.thuoc_tinh_ids)
                                ? variant.thuoc_tinh_ids
                                : String(variant.thuoc_tinh_ids || '').split(',').filter(Boolean);
                            ids.forEach(id => variantAttrIds.add(String(id)));
                        });
                        asArray(availableAttributes.value).forEach(group => {
                            const values = asArray(group.values)
                                .filter(value => variantAttrIds.has(String(value.id)))
                                .map(value => ({ id: value.id, label: value.label, _isNew: false }));
                            if (values.length > 0) {
                                restoredGroups.set(String(group.id), {
                                    id: group.id,
                                    name: group.name,
                                    values,
                                    valueInput: '',
                                    _dropdownOpen: false,
                                    _highlightedIdx: -1
                                });
                            }
                        });
                    }
                    attributesConfig.groups = [...restoredGroups.values()];

                    const attrLabelMap = {};
                    attributesConfig.groups.forEach(g => {
                        g.values.forEach(v => {
                            attrLabelMap[String(v.id)] = { groupName: g.name, label: v.label };
                        });
                    });

                    // Grid: khôi phục toàn bộ dòng gốc và dòng quy đổi từ API.
                    const mappedRows = [];
                    gridData.value = [];
                    productVariants.forEach(bt => {
                        const attrValueIds = Array.isArray(bt.thuoc_tinh_ids)
                            ? bt.thuoc_tinh_ids
                            : String(bt.thuoc_tinh_ids || '').split(',').map(x => x.trim()).filter(Boolean);
                        const attrLabels = {};
                        attrValueIds.forEach(id => {
                            const found = attrLabelMap[String(id)];
                            if (found) attrLabels[found.groupName] = found.label;
                        });
                        const conversionSource = asArray(bt.don_vi_quy_doi).length > 0
                            ? asArray(bt.don_vi_quy_doi)
                            : asArray(bt.units);
                        const conversionUnits = conversionSource.map(u => ({
                            id: u.id || uid(),
                            name: u.ten_don_vi || u.name || '',
                            rate: u.so_luong_san_pham_trong_don_vi ?? u.ty_le_quy_doi ?? u.rate ?? 1,
                            price: u.gia_ban_quy_doi ?? u.price ?? u.gia_ban ?? 0,
                            _dbId: u.id || null,
                            _giaVonQuyDoi: u.gia_von_quy_doi ?? u.gia_von ?? 0,
                            _giaBanQuyDoi: u.gia_ban_quy_doi ?? u.gia_ban ?? 0,
                            gia_von_quy_doi: u.gia_von_quy_doi ?? u.gia_von ?? 0,
                            gia_ban_quy_doi: u.gia_ban_quy_doi ?? u.gia_ban ?? 0,
                            ma_hang: u.ma_hang ?? u.maHang ?? '',
                            ma_vach: u.ma_vach ?? u.maVach ?? '',
                            _maHang: u.ma_hang ?? u.maHang ?? '',
                            _maVach: u.ma_vach ?? u.maVach ?? '',
                            don_vi_chuan_id: u.don_vi_chuan_id || null
                        }));

                        const baseRow = {
                            existingId: bt.id ?? null,
                            _dbId: bt.id ?? null,
                            attrLabels: { ...attrLabels },
                            attrValueIds: [...attrValueIds],
                            unitKey: 'base',
                            unitName: bt.ten_don_vi || prod.unitConfig?.baseUnit || '',
                            tyLe: 1,
                            isBase: true,
                            tenBienThe: bt.ten_bien_the ?? '',
                            maHang: bt.ma_hang ?? '',
                            maVach: bt.ma_vach ?? '',
                            giaVon: parseFloat(bt.gia_von) || 0,
                            giaBan: parseFloat(bt.gia_ban) || 0,
                            dinhMucToiThieu: bt.dinh_muc_toi_thieu ?? 0,
                            soLuong: bt.so_luong_ton ?? 0,
                            touched: {},
                            conversionUnits,
                            savedUnits: conversionSource.map(u => ({ ...u })),
                            // Ảnh biến thể: support cả snake_case (API) và camelCase
                            hinhAnh: bt.hinh_anh || bt.hinhAnh || '',
                            fileHinhAnh: null
                        };
                        mappedRows.push(baseRow);

                        conversionSource.forEach((u, idx) => {
                            const convUnitName = u.ten_don_vi || u.name || '';
                            if (!convUnitName) return;
                            mappedRows.push({
                                existingId: u.id ?? null,
                                _dbId: u.id ?? null,
                                attrLabels: { ...attrLabels },
                                attrValueIds: [...attrValueIds],
                                unitKey: 'cv_' + (u.id || idx),
                                unitName: convUnitName,
                                tyLe: parseFloat(u.so_luong_san_pham_trong_don_vi ?? u.ty_le_quy_doi ?? u.rate) || 1,
                                isBase: false,
                                tenBienThe: bt.ten_bien_the ?? '',
                                maHang: u.ma_hang ?? u.maHang ?? '',
                                maVach: u.ma_vach ?? u.maVach ?? '',
                                giaVon: parseFloat(u.gia_von_quy_doi ?? u.gia_von) || parseFloat(bt.gia_von) || 0,
                                giaBan: parseFloat(u.gia_ban_quy_doi ?? u.gia_ban) || parseFloat(bt.gia_ban) || 0,
                                dinhMucToiThieu: 0,
                                soLuong: 0,
                                touched: {},
                                conversionUnits: [],
                                savedUnits: [],
                                // Ảnh biến thể: ưu tiên ảnh riêng của DonViQuyDoi, fallback về variant cha
                                hinhAnh: u.hinh_anh || u.hinhAnh || bt.hinh_anh || bt.hinhAnh || '',
                                fileHinhAnh: null
                            });
                        });
                    });

                    // Gán key cho mỗi row
                    mappedRows.forEach(row => {
                        row.key = buildRowKey(row.attrLabels, { key: row.unitKey, name: row.unitName });
                    });

                    gridData.value = mappedRows;

                    // Populate _variantIdMap for regenerateGrid to preserve IDs
                    _variantIdMap = new Map();
                    gridData.value.forEach(row => {
                        const id = row.existingId || row._dbId;
                        if (id) _variantIdMap.set(id, row);
                    });

                    _initDone = true;
                    // Không regenerate tại đây: mappedRows đã chứa đầy đủ base + conversion từ API.
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
            let _variantIdMap = new Map(); // Map of variantId -> row for preserving IDs across regen

            // ------- COMPUTED: đơn vị thực tế -------
            const effectiveUnits = computed(() => {
                const list = [];
                const baseName = unitConfig.baseUnit.trim();
                if (baseName) {
                    list.push({ key: 'base', name: baseName, tyLe: 1,
                        price: parseFloat(unitConfig.basePrice) || parseFloat(basicInfo.defaultPrice) || 0, isBase: true });
                }
                unitConfig.conversionUnits.forEach((u, i) => {
                    if (!u.name.trim()) return;
                    // Bỏ qua conversion trùng tên đơn vị cơ bản để grid không nhân đôi.
                    if (baseName && u.name.trim().toLowerCase() === baseName.toLowerCase()) return;
                    list.push({ key: 'cv_' + u.id, name: u.name.trim(), tyLe: parseFloat(u.rate) || 1,
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
                // Lưu bản chụp (snapshot) của gridData hiện tại để dò tìm sau.
                // Vì ngay sau dòng `gridData.value = newRows` ở cuối hàm, _variantIdMap
                // cũng đã được rebuild → oldGrid.find bằng tenBienThe + unitName là cách
                // duy nhất đảm bảo khôi phục ảnh khi key format giữa initFromProduct và
                // regenerateGrid không trùng nhau.
                const oldGrid = gridData.value.slice();

                // Build lookup map by attrValueIds + unitName to preserve existingId/_dbId
                const lookupMap = new Map();
                if (typeof _variantIdMap !== 'undefined') {
                    _variantIdMap.forEach((row, id) => {
                        const attrIds = [...(row.attrValueIds || [])].sort().join(',');
                        const unitName = row.unitName || '';
                        const lookupKey = `${attrIds}__${unitName}`;
                        lookupMap.set(lookupKey, row);
                    });
                }

                const attrGroups = effectiveAttrGroups.value;
                const units = effectiveUnits.value;
                const baseNameLC = unitConfig.baseUnit.trim().toLowerCase();
                const attrCombos = cartesian(attrGroups);
                const baseRatio = (() => {
                    const bu = units.find(x => x.isBase);
                    return bu ? (parseFloat(bu.tyLe) || 1) : 1;
                })();

                const newRows = [];

                attrCombos.forEach(combo => {
                    units.forEach(u => {
                        const attrIds = [...(combo.__ids || [])].sort().join(',');
                        let lookupKey = `${attrIds}__${u.name}`;
                        let old = lookupMap.get(lookupKey);

                        // Fallback 1: thử theo unitKey nếu lookupKey ban đầu không khớp
                        if (!old && u.key) {
                            lookupKey = `${attrIds}__${u.key}`;
                            old = lookupMap.get(lookupKey);
                        }

                        // BỎ fallback theo index - nếu lookupKey không khớp thì existingId = null
                        // Lý do: nếu số dòng mới > số dòng cũ (do cartesian nhân lên), lấy id
                        // theo index gây nhân đôi/nhầm variant. Khi regen phải dựa trên attrCombos+unit
                        // thật sự, không phải vị trí trong mảng.

                        // Tên biến thể: chỉ chứa thuộc tính (KHÔNG gộp tên SP và đơn vị)
                        const attrPart = Object.keys(combo)
                            .filter(k => !k.startsWith('__')).map(k => combo[k]).join(' - ');
                        const tenBienThe = attrPart || '';

                        // BỌC THÉP TÌM KIẾM: nếu lookupMap (attrIds + unitName) miss
                        // (thường do key format giữa initFromProduct và regen khác nhau),
                        // tìm dự phòng trực tiếp trong oldGrid bằng ten_bien_the + unitName.
                        // Đây là cách duy nhất chống mất ảnh khi regen lần đầu sau khi load Edit.
                        if (!old && tenBienThe) {
                            old = oldGrid.find(r => {
                                const oldTen = r.tenBienThe || r.ten_bien_the || '';
                                const oldUnit = r.unitName || r.ten_don_vi || '';
                                return String(oldTen).trim() === String(tenBienThe).trim()
                                    && String(oldUnit).trim() === String(u.name || '').trim();
                            });
                        }

// Nếu dòng cũ trong lookup có unitName trùng đơn vị cơ bản hiện tại nhưng
                    // isBase = false (do lưu trước có đơn vị cơ bản khác), ép về isBase = true
                    // để tránh sinh 2 dòng cùng tên với cờ trái nhau.
                    const shouldBeBase = (u.isBase)
                        || (baseNameLC && String(old?.unitName || '').trim().toLowerCase() === baseNameLC);

                    newRows.push({
                        key: buildRowKey(combo, u),
                        attrLabels: Object.keys(combo).filter(k => !k.startsWith('__'))
                            .reduce((o, k) => { o[k] = combo[k]; return o; }, {}),
                        attrValueIds: combo.__ids || [],
                        unitKey: u.key,
                        unitName: u.name,
                        tyLe: u.tyLe,
                        isBase: shouldBeBase,
                            existingId: old?.existingId ?? old?._dbId ?? null,
                            _dbId: old?._dbId ?? null,
                            tenBienThe: tenBienThe,
                            maHang: old?.maHang ?? (basicInfo.code.trim() ? `${basicInfo.code.trim()}-${u.name}` : ''),
                            maVach: old?.maVach ?? '',
                            giaVon: old?.giaVon ?? ((parseFloat(basicInfo.defaultCost) || 0) * ((parseFloat(u.tyLe) || 1) / baseRatio)),
                            giaBan: old?.giaBan ?? (u.price || parseFloat(basicInfo.defaultPrice) || 0),
                            dinhMucToiThieu: old?.dinhMucToiThieu ?? (parseInt(basicInfo.defaultMinStock) || 0),
                            conversionUnits: old?.conversionUnits ? [...old.conversionUnits] : [],
                            savedUnits: old?.savedUnits ? [...old.savedUnits] : [],
                            // Ảnh biến thể: lấy từ old (đã lookup bằng key hoặc tenBienThe+unitName)
                            hinhAnh: old?.hinhAnh || old?.hinh_anh || '',
                            fileHinhAnh: old?.fileHinhAnh ?? null
                        });
                    });
                });

                // Update _variantIdMap for next regen cycle
                _variantIdMap = new Map();
                newRows.forEach(row => {
                    const id = row.existingId || row._dbId;
                    if (id) _variantIdMap.set(id, row);
                });

                gridData.value = newRows;
            }

            // ------- WATCH: tái sinh grid khi cấu hình thay đổi -------
            let watchTimer = null;
            function debouncedRegen() {
                if (isInitializing || !_initDone) return;
                clearTimeout(watchTimer);
                watchTimer = setTimeout(regenerateGrid, 50);
            }
            watch(() => unitConfig.baseUnit, (newBase, oldBase) => {
                handleBaseUnitChange(newBase, oldBase);
                debouncedRegen();
            });
            watch(() => unitConfig.basePrice, debouncedRegen);
            watch(() => unitConfig.conversionUnits, debouncedRegen, { deep: true });
            watch(() => attributesConfig.groups, debouncedRegen, { deep: true });

            // ------- BASE UNIT SWAP -------
            // Khi user đổi Đơn vị cơ bản sang một tên đang có trong conversionUnits,
            // tự động đảo: đẩy base cũ vào conversionUnits với rate = 1/old_rate.
            function handleBaseUnitChange(newBase, oldBase) {
                if (isInitializing || !_initDone) return;
                const newName = String(newBase || '').trim();
                const oldName = String(oldBase || '').trim();
                if (!newName || newName === oldName) return;

                // Tìm conversion đang có tên = newName
                const idx = unitConfig.conversionUnits.findIndex(u =>
                    String(u.name || '').trim().toLowerCase() === newName.toLowerCase()
                );
                if (idx === -1) return; // Không có trong conversion → đổi base bình thường, không cần xử lý

                const cv = unitConfig.conversionUnits[idx];
                const oldRate = parseFloat(cv.rate) || 1;
                if (oldRate <= 0 || oldRate === 1) {
                    // Rate = 1 hoặc không hợp lệ → không đảo, chỉ xóa khỏi conversion
                    unitConfig.conversionUnits.splice(idx, 1);
                    return;
                }

                const confirmMsg =
                    `“${newName}” đang là đơn vị quy đổi với tỷ lệ ${oldRate} (1 ${newName} = ${oldRate} ${oldName || 'đv cơ bản'}).\n\n` +
                    `Nếu đổi “${newName}” thành đơn vị cơ bản, “${oldName || 'đv cơ bản cũ'}” sẽ trở thành đơn vị quy đổi với tỷ lệ mới = 1/${oldRate} ≈ ${(1 / oldRate).toFixed(4)}.\n\n` +
                    `Bạn có chắc muốn đổi không?`;
                if (!window.confirm(confirmMsg)) {
                    // User cancel → khôi phục baseUnit cũ
                    unitConfig.baseUnit = oldBase;
                    return;
                }

                // Đảo: thêm oldName vào conversionUnits với rate = 1/oldRate
                const newRate = 1 / oldRate;
                unitConfig.conversionUnits.splice(idx, 1); // Xóa entry đang chọn
                if (oldName) {
                    unitConfig.conversionUnits.push({
                        id: uid(),
                        name: oldName,
                        ten_don_vi: oldName,
                        rate: newRate,
                        price: 0,
                        _dbId: null,
                        _giaVonQuyDoi: 0,
                        _giaBanQuyDoi: 0,
                        _maHang: '',
                        _maVach: '',
                        ma_hang: '',
                        ma_vach: '',
                        don_vi_chuan_id: null
                    });
                }
            }

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
                // Khi upload file mới, xóa ảnh cũ để backend biết đây là thay thế
                row.hinhAnh = '';
            }

            function clearVariantImage(row) {
                row.fileHinhAnh = null;
                row.hinhAnh = '';
                const inputEl = _variantImageInputRefs.get(row.key);
                if (inputEl) inputEl.value = '';
            }

            function getRowImagePreview(row) {
                if (row.fileHinhAnh) {
                    return URL.createObjectURL(row.fileHinhAnh);
                }
                return '';
            }

            function getExistingImageUrl(path) {
                if (!path) return '';
                if (path.startsWith('http')) return path;
                // Path DB dạng "uploads/san-pham/xxx.jpg" -> browser tự resolve theo origin hiện tại
                // (đảm bảo hoạt động ở cả subpath / laragon public/admin/... và domain thuần)
                const cleanPath = path.replace(/^\/+/, '');
                return '/' + cleanPath;
            }

            // ------- ADD/REMOVE CONVERSION UNIT -------
            function addConversion() {
                unitConfig.conversionUnits.push({ id: uid(), name: '', ten_don_vi: '', name_input: '', rate: 1, price: 0,
                    _dbId: null, _giaVonQuyDoi: 0, _giaBanQuyDoi: 0, _maHang: '', _maVach: '',
                    don_vi_chuan_id: null });
            }

            function removeConversion(index) {
                unitConfig.conversionUnits.splice(index, 1);
            }

            // Khi user chọn đơn vị từ dropdown → tự điền name + rate
            watch(() => unitConfig.conversionUnits.map(u => u.don_vi_chuan_id), (newIds) => {
                if (isInitializing) return;
                unitConfig.conversionUnits.forEach((u) => {
                    if (u.don_vi_chuan_id) {
                        const found = availableUnits.value.find(a => a.id === u.don_vi_chuan_id);
                        if (found) {
                            u.ten_don_vi = found.ten_don_vi;
                            u.rate = found.qty;
                        }
                    }
                });
            }, { deep: false });

            function onConversionRateInput(unit, val) {
                const n = parseFloat(val);
                // Cho phép rate >= 0 (số thập phân). Nếu < 1, giữ nguyên giá trị user nhập
                // để tránh mất dữ liệu khi đảo đơn vị cơ bản (rate = 1/24 ≈ 0.0417).
                unit.rate = isNaN(n) || n <= 0 ? 1 : n;
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
                // Khai báo biến kiểm tra xem sản phẩm có thuộc tính hay không
                const hasAttr = effectiveAttrGroups.value && effectiveAttrGroups.value.length > 0;

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
                    // Debug: log gridData state
                    console.log(`[buildPayload] row[${i}]: existingId=${row.existingId} _dbId=${row._dbId} unitName=${row.unitName}`);

                    // CRITICAL: Always include existing ID (use _dbId as fallback)
                    const existingId = row.existingId || row._dbId;

                    // Dùng conversionUnits (reactive, chứa cả đơn vị mới thêm bởi user)
                    // Lọc bỏ rate = 0 (không hợp lệ); giữ rate < 1 để hỗ trợ đảo đơn vị cơ bản
                    // (ví dụ: 1 Cái = 1/24 Thùng ≈ 0.0417)
                    const unitsPayload = (row.conversionUnits || [])
                        .filter(u => (parseFloat(u.rate) || 0) > 0)
                        .map(u => {
                            return {
                                id: u._dbId || null, // DB ID (null cho đơn vị mới)
                                don_vi_chuan_id: u.don_vi_chuan_id || null,
                                ten_don_vi: u.ten_don_vi || u.name || '',
                                so_luong_san_pham_trong_don_vi: parseFloat(u.rate) || 1,
                                gia_von_quy_doi: 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                                gia_ban_quy_doi: parseFloat(u.gia_ban_quy_doi) || 0,
                                ma_hang: u.ma_hang || '',
                                ma_vach: u.ma_vach || ''
                            };
                        });

                    console.log(`[buildPayload] row[${i}] unitsPayload.length=${unitsPayload.length}`);

                    // Xác định loại biến thể: dựa vào row.isBase thay vì hasAttr
                    // Sửa ten_don_vi: ưu tiên row.unitName (giá trị user thấy trên grid)
                    // trước khi fall back về unitConfig.baseUnit, tránh gửi rỗng "" khi
                    // unitConfig.baseUnit chưa được gán kịp lúc load sản phẩm phức tạp.
                    return {
                        id: existingId ?? null,
                        is_base: row.isBase ? 1 : 0,
                        ten_bien_the: row.tenBienThe || row.unitName,
                        la_don_vi: row.isBase && !hasAttr ? 1 : 0,
                        ten_don_vi: row.isBase
                            ? (row.unitName || unitConfig.baseUnit || row.ten_don_vi || '')
                            : (row.unitName || row.ten_don_vi || ''),
                        ty_le: row.tyLe || 1,
                        ma_hang: row.maHang,
                        ma_vach: row.maVach,
                        gia_von: 0, // Luôn = 0; giá vốn chỉ được sinh tự động từ lần nhập hàng đầu tiên
                        gia_ban: parseFloat(row.giaBan) || 0,
                        so_luong_ton: parseInt(row.soLuong) || 0,
                        dinh_muc_toi_thieu: parseInt(row.dinhMucToiThieu) || 0,
                        thuoc_tinh_ids: Array.isArray(row.attrValueIds)
                            ? row.attrValueIds.join(',') : (row.attrValueIds || ''),
                        ten_don_vi_bien_the: row.unitName || '',
                        units: unitsPayload
                    };
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

            // ====== Sync variant IDs to hidden form inputs (fallback safety) ======
            function syncVariantIdsToForm() {
                const form = document.getElementById('productForm');
                if (!form) return;

                // Remove old hidden inputs
                form.querySelectorAll('.variant-id-input').forEach(el => el.remove());

                // Add hidden inputs for ALL existing variants
                gridData.value.forEach((row, i) => {
                    const id = row.existingId || row._dbId;
                    if (id) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `bien_the[${i}][id]`;
                        input.value = id;
                        input.className = 'variant-id-input';
                        form.appendChild(input);
                    }
                });
            }

            // ====== handleSubmit: gửi request PUT ======
            async function handleSubmit() {
                submitHadError.value = false;
                clearErrors();
                submitting.value = true;

                // CRITICAL: Ensure variant IDs are in form before building payload
                syncVariantIdsToForm();

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
                        // Append file ảnh riêng cho biến thể (nếu user đã chọn)
                        const row = gridData.value[i];
                        if (row && row.fileHinhAnh instanceof File) {
                            fd.append(`bien_the[${i}][hinh_anh]`, row.fileHinhAnh, row.fileHinhAnh.name);
                        }
                    });
                    // Ảnh chính của sản phẩm (nếu user vừa upload)
                    fd.append('hinh_anh', basicInfo.image);
                    body = fd;
                } else {
                    // Vẫn phải gửi FormData để multipart parse được → mới đọc được file bien_the
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
                        // Append file ảnh riêng cho biến thể (nếu user đã chọn)
                        const row = gridData.value[i];
                        if (row && row.fileHinhAnh instanceof File) {
                            fd.append(`bien_the[${i}][hinh_anh]`, row.fileHinhAnh, row.fileHinhAnh.name);
                        }
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

                    // Các lỗi khác (500, 400, etc.)
                    const msg = (data && (data.message || data.error || data))
                        ? (typeof data === 'object' ? JSON.stringify(data) : data)
                        : ('HTTP ' + res.status);
                    generalError.value = 'Lỗi từ server: ' + msg;
                    submitHadError.value = true;
                } catch (err) {
                    // BẮT BUỘC tắt spinner khi có lỗi
                    submitting.value = false;
                    const msg = (err && err.message ? err.message : String(err));
                    generalError.value = 'Lỗi kết nối: ' + msg;
                    submitHadError.value = true;
                } finally {
                    // Đảm bảo spinner luôn được tắt sau khi xử lý xong
                    submitting.value = false;
                }
            }

            // ====== syncErrorUi: cập nhật DOM hiển thị lỗi + cảnh báo trùng lặp ======
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
                            // ============================================================
                            // YÊU CẦU 2: VÔ HIỆU HÓA NÚT LƯU KHI CÓ TRÙNG LẶP
                            // ============================================================
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

            // ====== MOUNT: khởi tạo ======
            onMounted(() => {
                if (isEditMode && DATA.productData) {
                    initFromProduct(DATA.productData);
                    _initDone = true;
                }
                // Chờ Vue hoàn tất các watcher phát sinh từ việc gán state API rồi mới mở khóa.
                nextTick(() => {
                    isInitializing = false;
                });
                formLoaded.value = true;

                // Bổ sung đơn vị dự phòng cho cả base unit VÀ các conversion unit hiện có,
                // để dropdown hiển thị đúng thay vì tự nhảy sang option khác.
                const ensureUnitInList = (u) => {
                    if (!u) return;
                    const n = String(u.ten_don_vi || u.name || '').trim();
                    if (!n) return;
                    if (!availableUnits.value.find(x => x.ten_don_vi === n)) {
                        availableUnits.value.push({
                            id: 'temp_custom_' + n,
                            ten_don_vi: n,
                            qty: parseInt(u.so_luong_san_pham_trong_don_vi || u.qty || 1) || 1
                        });
                    }
                };
                ensureUnitInList({ ten_don_vi: unitConfig.baseUnit });
                unitConfig.conversionUnits.forEach(ensureUnitInList);

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
                addConversion, removeConversion, onConversionRateInput,
                addAttrGroup, removeAttrGroup, addAttrValue, removeAttrValue,
                onAttrValueKey, toggleDropdown, closeDropdown,
                getDropdownValues, getFilteredDropdown, selectFromDropdown,
                onImageSelect, clearImage, onGridInput,
                // Ảnh biến thể
                setVariantImageInputRef, onVariantImageChange, clearVariantImage,
                getRowImagePreview, getExistingImageUrl,
                handleSubmit, buildPayload,
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
                                        <option v-for="u in allUnitOptions" :key="u.id" :value="u.ten_don_vi">{{ u.ten_don_vi }}</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                        <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                    </div>
                                </div>
                                <p v-if="unitConfig.baseUnit && !availableUnits.find(u => u.ten_don_vi === unitConfig.baseUnit)"
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
                                                <input :value="u.rate" @input="onConversionRateInput(u, $event.target.value)" type="number" min="0" step="any"
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
