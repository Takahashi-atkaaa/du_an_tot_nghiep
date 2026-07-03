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

        // units: gather distinct units across variants
        $units = [];
        foreach ($variants as $v) {
            foreach ($v->units as $u) {
                $key = trim($u->ten_don_vi);
                if ($key === '') continue;
                if (!isset($units[$key])) {
                    $units[$key] = [
                        'id' => $u->id,
                        'name' => $u->ten_don_vi,
                        'ty_le_quy_doi' => $u->ty_le_quy_doi,
                        'gia_von_quy_doi' => $u->gia_von_quy_doi,
                        'gia_ban_quy_doi' => $u->gia_ban_quy_doi,
                        'ma_vach' => $u->ma_vach,
                        'la_don_vi_mac_dinh' => $u->la_don_vi_mac_dinh,
                    ];
                }
            }
        }

        // attributes groups: collect thuocTinhs from variants
        $groups = [];
        foreach ($variants as $v) {
            $thuocs = $v->thuocTinhs();
            foreach ($thuocs as $t) {
                $parentName = $t->thuoc_tinh_cha_id ? ($t->parent?->ten_thuoc_tinh ?? 'Thuộc tính') : ($t->ten_thuoc_tinh ?: 'Thuộc tính');
                $gid = $t->thuoc_tinh_cha_id ? 'g_' . $t->thuoc_tinh_cha_id : 'g_root';
                if (!isset($groups[$gid])) {
                    $groups[$gid] = ['id' => $gid, 'name' => $parentName, 'values' => []];
                }
                $groups[$gid]['values'][$t->id] = ['id' => $t->id, 'label' => $t->ten_thuoc_tinh];
            }
        }

        // map variants to simple array
        $variantsArr = [];
        foreach ($variants as $v) {
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
                'units' => $v->units->map(fn($u) => ['id' => $u->id, 'ten_don_vi' => $u->ten_don_vi, 'ty_le_quy_doi' => $u->ty_le_quy_doi, 'gia_von_quy_doi' => $u->gia_von_quy_doi, 'gia_ban_quy_doi' => $u->gia_ban_quy_doi, 'ma_vach' => $u->ma_vach, 'la_don_vi_mac_dinh' => $u->la_don_vi_mac_dinh])->all(),
            ];
        }

        $unitList = array_values($units);
        $baseUnit = '';
        $basePrice = $basic['defaultPrice'];
        $conversionUnits = [];

        if (!empty($unitList)) {
            $baseUnitItem = null;
            foreach ($unitList as $u) {
                if (!empty($u['la_don_vi_mac_dinh'])) {
                    $baseUnitItem = $u;
                    break;
                }
            }
            if (!$baseUnitItem) {
                $baseUnitItem = $unitList[0];
            }
            if ($baseUnitItem) {
                $baseUnit = $baseUnitItem['name'];
                $basePrice = $baseUnitItem['gia_ban_quy_doi'] ?? $basic['defaultPrice'];
                foreach ($unitList as $u) {
                    if (trim($u['name']) !== trim($baseUnit)) {
                        $conversionUnits[] = $u;
                    }
                }
            }
        }

        return [
            'basicInfo' => $basic,
            'unitConfig' => ['baseUnit' => $baseUnit, 'basePrice' => $basePrice, 'conversionUnits' => $conversionUnits],
            'attributesConfig' => ['groups' => array_values($groups)],
            'bien_the' => $variantsArr,
        ];
    }
}
