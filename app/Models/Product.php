<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'san_pham';

    protected $fillable = [
        'id_danh_muc',
        'ten_san_pham',
        'thuong_hieu',
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucSanPham::class, 'id_danh_muc');
    }

    public function variants()
    {
        return $this->hasMany(BienTheSanPham::class, 'product_id');
    }

    // ============================================================
    // ACCESSOR donVi: trả về object có ten_don_vi của biến thể đầu tiên
    // Để tương thích với code Blade: $sanPham->donVi->ten_don_vi
    // ============================================================
    public function getDonViAttribute()
    {
        // Lấy variant đầu tiên hoặc variant có la_don_vi=true
        $variant = $this->variants()->where('la_don_vi', 1)->first()
            ?? $this->variants()->first();

        if (!$variant) {
            return (object)['ten_don_vi' => null];
        }

        return (object)[
            'ten_don_vi' => $variant->ten_don_vi,
            'ma_vach' => $variant->ma_vach,
            'gia_von' => $variant->gia_von,
            'gia_ban' => $variant->gia_ban,
        ];
    }

    /**
     * Accessor: variant đại diện cho đơn vị cơ bản.
     * Dùng cho view danh sách sản phẩm để:
     *   - Hiển thị tên đơn vị gốc ở dòng chính
     *   - Lọc các đơn vị quy đổi thuộc về dòng chính
     */
    public function getFirstMasterVariantAttribute(): ?BienTheSanPham
    {
        $variants = $this->variants()->get();

        // Ưu tiên variant có la_don_vi = 1
        foreach ($variants as $v) {
            if (($v->la_don_vi ?? 0) == 1) {
                return $v;
            }
        }

        // Nếu không có, chọn variant có ten_don_vi không trùng với đơn vị quy đổi
        $conversionNames = [];
        foreach ($variants as $v) {
            foreach ($v->units as $u) {
                $name = trim((string) $u->ten_don_vi);
                if ($name !== '') {
                    $conversionNames[strtolower($name)] = true;
                }
            }
        }
        foreach ($variants as $v) {
            $tenDonVi = trim((string) ($v->ten_don_vi ?? ''));
            if ($tenDonVi !== '' && !isset($conversionNames[strtolower($tenDonVi)])) {
                return $v;
            }
        }

        // Fallback: variant đầu tiên
        return $variants->first();
    }

    // Flattened rows for index table: each row = 1 variant unit (gốc hoặc quy đổi)
    public function getFlattenedRowsAttribute(): \Illuminate\Support\Collection
    {
        $rows = collect();

        // Load tất cả variants trước (đã eager load units ở controller index)
        $variants = $this->variants()->get();

        // Tập tên các đơn vị quy đổi (DonViQuyDoi) để phân biệt với đơn vị cơ bản.
        $conversionNames = [];
        foreach ($variants as $v) {
            foreach ($v->units as $u) {
                $name = trim((string) $u->ten_don_vi);
                if ($name !== '') {
                    $conversionNames[strtolower($name)] = true;
                }
            }
        }

        // Xác định variant đại diện cho đơn vị cơ bản (để render dòng gốc + dropdown quy đổi).
        // Tiêu chí ưu tiên:
        //   1) la_don_vi = 1
        //   2) ten_don_vi không trùng với bất kỳ DonViQuyDoi nào (đảm bảo là base thật)
        //   3) Fallback: variant đầu tiên
        $firstVariant = null;
        foreach ($variants as $v) {
            if (($v->la_don_vi ?? 0) == 1) {
                $firstVariant = $v;
                break;
            }
        }
        if (!$firstVariant) {
            foreach ($variants as $v) {
                $tenDonVi = trim((string) ($v->ten_don_vi ?? ''));
                if ($tenDonVi !== '' && !isset($conversionNames[strtolower($tenDonVi)])) {
                    $firstVariant = $v;
                    break;
                }
            }
        }
        $firstVariant = $firstVariant ?? $variants->first();

        // Collect all thuoc_tinh_ids từ tất cả variants để query một lần (tránh N+1)
        $allAttrIds = [];
        foreach ($variants as $v) {
            if (!empty($v->thuoc_tinh_ids)) {
                $allAttrIds = array_merge($allAttrIds, $v->thuoc_tinh_ids);
            }
        }
        $allAttrIds = array_unique($allAttrIds);
        $allAttrs = !empty($allAttrIds)
            ? ThuocTinhSanPham::whereIn('id', $allAttrIds)->get()->keyBy('id')
            : collect();

        foreach ($variants as $variant) {
            // Xác định loại biến thể: đơn vị hay thuộc tính
            $laDonVi = $variant->la_don_vi ?? false;
            // ten_don_vi của variant CHA: lấy trực tiếp từ variant
            // (variant la_don_vi=true: tên đơn vị của variant gốc
            //  variant thuộc tính: tên đơn vị cơ bản của variant đó, vd "Hộp")
            $tenDonViVariant = $variant->ten_don_vi ?? '';

            // Build ten_bien_the_display từ thuoc_tinh_ids (chỉ khi KHÔNG phải biến thể đơn vị)
            $ten_bien_the_display = '';
            if (!$laDonVi) {
                $attrs = [];
                if (!empty($variant->thuoc_tinh_ids)) {
                    foreach ($variant->thuoc_tinh_ids as $id) {
                        if (isset($allAttrs[$id])) {
                            $attrs[] = $allAttrs[$id]->ten_thuoc_tinh;
                        }
                    }
                }
                $ten_bien_the_display = implode(' - ', $attrs);
            }

            // Dòng gốc: đơn vị cơ bản từ bien_the_san_pham
            $rows->push((object)[
                'loai_dong' => 'goc',
                'product' => $this,
                'variant' => $variant,
                'unit' => null,
                'ten_don_vi' => $tenDonViVariant,
                'ten_bien_the_display' => $ten_bien_the_display,
                'la_don_vi' => $laDonVi,
                'ty_le' => 1,
                'gia_ban' => $variant->gia_ban ?? 0,
                'gia_von' => $variant->gia_von ?? 0,
                'so_luong_ton' => $variant->so_luong_ton ?? 0,
                'trang_thai' => $variant->trang_thai,
                'ma_hang' => $variant->ma_hang ?? '',
                'ma_vach' => $variant->ma_vach ?? '',
                'hinh_anh' => $variant->hinh_anh,
            ]);

            // Các dòng quy đổi: luôn hiển thị nếu variant có units
            foreach ($variant->units as $unit) {
                $tyLe = (int)($unit->so_luong_san_pham_trong_don_vi ?: 1);
                $rows->push((object)[
                    'loai_dong' => 'quy_doi',
                    'product' => $this,
                    'variant' => $variant,
                    'unit' => $unit,
                    'ten_don_vi' => $unit->ten_don_vi ?? '',
                    'ten_bien_the_display' => $ten_bien_the_display,
                    'la_don_vi' => false,
                    'ty_le' => $tyLe,
                    'gia_ban' => $unit->gia_ban_quy_doi ?? 0,
                    'gia_von' => $unit->gia_von_quy_doi ?? 0,
                    'so_luong_ton' => $tyLe > 0 ? (int)floor(($variant->so_luong_ton ?? 0) / $tyLe) : 0,
                    'trang_thai' => $variant->trang_thai,
                    'ma_hang' => $unit->ma_hang ?? '',
                    'ma_vach' => $unit->ma_vach ?? '',
                    'hinh_anh' => $unit->hinh_anh ?? $variant->hinh_anh,
                ]);
            }
        }

        return $rows;
    }

    // Return structured data for Vue edit initializer
    public function toEditVueData(): array
    {
        $variants = $this->variants()->with('units')->get();

        // Ưu tiên variant có la_don_vi = 1 làm đại diện cho đơn vị cơ bản.
        // Nếu không có, dùng variant đầu tiên (giữ tương thích ngược).
        $first = $variants->firstWhere('la_don_vi', 1) ?? $variants->first();

        $basic = [
            'code' => $first->ma_hang ?? '',
            'ten_san_pham' => $this->ten_san_pham,
            'id_danh_muc' => $this->id_danh_muc,
            'brand' => $this->thuong_hieu,
            'mo_ta' => $this->mo_ta ?? '',
            'trang_thai' => $this->trang_thai ?? true,
            'defaultPrice' => $first->gia_ban ?? 0,
            'defaultCost' => $first->gia_von ?? 0,
            'defaultMinStock' => $first->dinh_muc_toi_thieu ?? 0,
            'imagePreview' => $first->hinh_anh ? asset($first->hinh_anh) : '',
        ];

        // Tập tên các đơn vị quy đổi (DonViQuyDoi) để phân biệt với đơn vị cơ bản.
        // Nếu bien_the_san_pham.ten_don_vi trùng tên với một đơn vị quy đổi → KHÔNG phải đơn vị cơ bản
        // (do dữ liệu cũ bị ghi đè nhầm), bỏ qua khi gom base.
        $conversionNames = [];
        foreach ($variants as $v) {
            foreach ($v->units as $u) {
                $name = trim((string) $u->ten_don_vi);
                if ($name !== '') {
                    $conversionNames[strtolower($name)] = true;
                }
            }
        }

        // Collect all units: base from bien_the_san_pham.ten_don_vi + conversion from don_vi_quy_doi
        // FIX: Ưu tiên ten_don_vi (đơn vị cơ bản) làm base, không dùng ten_bien_the
        $units = [];
        $baseUnitName = '';

        foreach ($variants as $v) {
            $baseName = trim((string) ($v->ten_don_vi ?? ''));
            // Chỉ coi là base khi tên này CHƯA tồn tại trong các đơn vị quy đổi
            if ($baseName !== ''
                && !isset($conversionNames[strtolower($baseName)])
                && !isset($units[$baseName])
            ) {
                $units[$baseName] = [
                    'name' => $baseName,
                    'so_luong_san_pham_trong_don_vi' => 1,
                    'gia_ban_quy_doi' => $v->gia_ban,
                    'gia_von_quy_doi' => $v->gia_von,
                    'ma_vach' => $v->ma_vach,
                    'variant_id' => $v->id,
                    'is_base' => true,
                ];
                if ($baseUnitName === '') {
                    $baseUnitName = $baseName;
                }
            }
            // Các đơn vị quy đổi
            foreach ($v->units as $u) {
                $key = trim($u->ten_don_vi);
                if ($key === '') continue;
                if (!isset($units[$key])) {
                    $units[$key] = [
                        'name' => $key,
                        'so_luong_san_pham_trong_don_vi' => $u->so_luong_san_pham_trong_don_vi,
                        'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                        'gia_von_quy_doi' => $u->gia_von_quy_doi,
                        'ma_hang' => $u->ma_hang,
                        'ma_vach' => $u->ma_vach,
                        'variant_id' => $v->id,
                        'don_vi_chuan_id' => $u->don_vi_chuan_id,
                        'is_base' => false,
                    ];
                }
            }
        }

        // attributes groups: collect thuocTinhs from variants
        $groups = [];
        foreach ($variants as $v) {
            $thuocs = $v->thuocTinhs;
            foreach ($thuocs as $t) {
                $parentName = $t->thuoc_tinh_cha_id ? ($t->parent?->ten_thuoc_tinh ?? 'Thuộc tính') : ($t->ten_thuoc_tinh ?: 'Thuộc tính');
                $gid = $t->thuoc_tinh_cha_id ? 'g_' . $t->thuoc_tinh_cha_id : 'g_root';
                if (!isset($groups[$gid])) {
                    $groups[$gid] = ['id' => $gid, 'name' => $parentName, 'values' => []];
                }
                $groups[$gid]['values'][$t->id] = ['id' => $t->id, 'label' => $t->ten_thuoc_tinh];
            }
        }

        // map variants to simple array — each variant carries its base unit in ten_bien_the
        $variantsArr = [];
        foreach ($variants as $v) {
            $variantUnits = $v->units->map(fn($u) => [
                'id' => $u->id,
                'ten_don_vi' => $u->ten_don_vi,
                'so_luong_san_pham_trong_don_vi' => $u->so_luong_san_pham_trong_don_vi,
                'gia_von_quy_doi' => $u->gia_von_quy_doi,
                'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                'ma_hang' => $u->ma_hang,
                'ma_vach' => $u->ma_vach,
                'la_don_vi_mac_dinh' => $u->la_don_vi_mac_dinh,
                'don_vi_chuan_id' => $u->don_vi_chuan_id,
            ])->all();
            $variantsArr[] = [
                'id' => $v->id,
                'ten_bien_the' => $v->ten_bien_the,
                'la_don_vi' => $v->la_don_vi ?? false,
                'ten_don_vi' => $v->ten_don_vi ?? '',
                'ma_hang' => $v->ma_hang,
                'ma_vach' => $v->ma_vach,
                'gia_von' => $v->gia_von,
                'gia_ban' => $v->gia_ban,
                'so_luong_ton' => $v->so_luong_ton,
                'dinh_muc_toi_thieu' => $v->dinh_muc_toi_thieu,
                'thuoc_tinh_ids' => $v->thuoc_tinh_ids ?? [],
                'units' => $variantUnits,
            ];
        }

        $unitList = array_values($units);
        $baseUnit = $baseUnitName ?: '';
        $basePrice = $basic['defaultPrice'];
        $conversionUnits = [];

        if (!empty($unitList)) {
            // Tìm item có is_base = true làm base
            foreach ($unitList as $u) {
                if (!empty($u['is_base'])) {
                    $baseUnit = $u['name'];
                    $basePrice = (float)($u['gia_ban_quy_doi'] ?? $basic['defaultPrice']);
                    break;
                }
            }

            // Các đơn vị còn lại = conversion units
            foreach ($unitList as $u) {
                if (trim($u['name']) !== trim($baseUnit)) {
                    $conversionUnits[] = $u;
                }
            }
        } else {
            $baseUnit = 'Cái';
            $basePrice = $basic['defaultPrice'];
        }

        return [
            'basicInfo' => $basic,
            'unitConfig' => ['baseUnit' => $baseUnit, 'basePrice' => $basePrice, 'conversionUnits' => $conversionUnits],
            'attributesConfig' => ['groups' => array_values($groups)],
            'bien_the' => $variantsArr,
        ];
    }

    /**
     * Debug helper: trả về thông tin đơn vị của sản phẩm để debug.
     */
    public function debugUnits(): array
    {
        $variants = $this->variants()->with('units')->get();
        $rows = [];
        foreach ($variants as $v) {
            $rows[] = [
                'variant_id' => $v->id,
                'ten_bien_the' => $v->ten_bien_the,
                'ten_don_vi' => $v->ten_don_vi,
                'la_don_vi' => $v->la_don_vi,
                'thuoc_tinh_ids' => $v->thuoc_tinh_ids,
                'units' => $v->units->map(fn($u) => [
                    'id' => $u->id,
                    'ten_don_vi' => $u->ten_don_vi,
                    'so_luong_san_pham_trong_don_vi' => $u->so_luong_san_pham_trong_don_vi,
                ])->all(),
            ];
        }
        return $rows;
    }
}
