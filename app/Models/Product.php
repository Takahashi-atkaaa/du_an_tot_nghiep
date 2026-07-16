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

    // Flattened rows for index table: each row = 1 variant unit (gốc hoặc quy đổi)
    public function getFlattenedRowsAttribute(): \Illuminate\Support\Collection
    {
        $rows = collect();

        foreach ($this->variants as $variant) {
            // Dòng gốc: đơn vị cơ bản lưu trong ten_bien_the của bien_the_san_pham
            $rows->push((object)[
                'loai_dong' => 'goc',
                'product' => $this,
                'variant' => $variant,
                'unit' => null,
                'ten_don_vi' => $variant->ten_bien_the ?? '',
                'ty_le' => 1,
                'gia_ban' => $variant->gia_ban ?? 0,
                'gia_von' => $variant->gia_von ?? 0,
                'so_luong_ton' => $variant->so_luong_ton ?? 0,
                'trang_thai' => $variant->trang_thai,
                'ma_hang' => $variant->ma_hang ?? '',
                'ma_vach' => $variant->ma_vach ?? '',
                'hinh_anh' => $variant->hinh_anh,
            ]);

            // Các dòng quy đổi
            foreach ($variant->units as $unit) {
                $tyLe = (int)($unit->ty_le_quy_doi ?: 1);
                $rows->push((object)[
                    'loai_dong' => 'quy_doi',
                    'product' => $this,
                    'variant' => $variant,
                    'unit' => $unit,
                    'ten_don_vi' => $unit->ten_don_vi ?? '',
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
        $first = $variants->first();

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

        // Collect all units: base from bien_the_san_pham.ten_bien_the + conversion from don_vi_quy_doi
        $units = [];
        foreach ($variants as $v) {
            $baseName = trim($v->ten_bien_the ?? '');
            if ($baseName !== '' && !isset($units[$baseName])) {
                $units[$baseName] = [
                    'name' => $baseName,
                    'ty_le_quy_doi' => 1,
                    'gia_ban_quy_doi' => $v->gia_ban,
                    'gia_von_quy_doi' => $v->gia_von,
                    'ma_vach' => $v->ma_vach,
                    'variant_id' => $v->id,
                ];
            }
            foreach ($v->units as $u) {
                $key = trim($u->ten_don_vi);
                if ($key === '') continue;
                if (!isset($units[$key])) {
                    $units[$key] = [
                        'name' => $key,
                        'ty_le_quy_doi' => $u->ty_le_quy_doi,
                        'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                        'gia_von_quy_doi' => $u->gia_von_quy_doi,
                        'ma_vach' => $u->ma_vach,
                        'variant_id' => $v->id,
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
                'ty_le_quy_doi' => $u->ty_le_quy_doi,
                'gia_von_quy_doi' => $u->gia_von_quy_doi,
                'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                'ma_vach' => $u->ma_vach,
                'la_don_vi_mac_dinh' => $u->la_don_vi_mac_dinh,
            ])->all();
            $variantsArr[] = [
                'id' => $v->id,
                'ten_bien_the' => $v->ten_bien_the,
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
        $baseUnit = '';
        $basePrice = $basic['defaultPrice'];
        $conversionUnits = [];

        if (!empty($unitList)) {
            // Lay don vi dau tien lam base (thuong la don vi co ty_le = 1)
            $baseUnitItem = $unitList[0];
            $baseUnit = $baseUnitItem['name'];
            $basePrice = (float)($baseUnitItem['gia_ban_quy_doi'] ?? $basic['defaultPrice']);

            // Cac don vi con lai = conversion units
            for ($i = 1; $i < count($unitList); $i++) {
                $u = $unitList[$i];
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
}
