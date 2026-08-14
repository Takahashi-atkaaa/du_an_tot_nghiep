<?php

namespace App\Models;

use App\Models\ThuocTinhSanPham;
use Illuminate\Database\Eloquent\SoftDeletes;

class BienTheSanPham extends BaseModel
{
    use SoftDeletes;

    protected $table = 'bien_the_san_pham';

    protected $fillable = [
        'product_id',
        'ten_bien_the',
        'ma_hang',
        'ma_vach',
        'gia_von',
        'gia_ban',
        'so_luong_ton',
        'dinh_muc_toi_thieu',
        'hinh_anh',
        'thuoc_tinh_ids',
        'trang_thai',
        'la_don_vi',
        'ten_don_vi',
    ];

    protected $casts = [
        'thuoc_tinh_ids' => 'array',
        'gia_von' => 'decimal:2',
        'gia_ban' => 'decimal:2',
        'so_luong_ton' => 'integer',
        'dinh_muc_toi_thieu' => 'integer',
        'trang_thai' => 'boolean',
        'la_don_vi' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function units()
    {
        return $this->hasMany(DonViQuyDoi::class, 'variant_id');
    }

    public function productUnits()
    {
        return $this->hasMany(DonViQuyDoi::class, 'product_id');
    }

    public function chiTietLoHang()
    {
        return $this->hasMany(ChiTietLoHang::class, 'variant_id');
    }

    public function chiTietLoHangTon()
    {
        return $this->hasMany(ChiTietLoHang::class, 'variant_id')
            ->where('so_luong_ton', '>', 0);
    }

    public function chiTietDoiTras()
    {
        return $this->hasMany(ChiTietDoiTra::class, 'id_bien_the');
    }

    public function chiTietDoiTraThayThe()
    {
        return $this->hasMany(ChiTietDoiTra::class, 'id_bien_the_thay_the');
    }

    public function hangLois()
    {
        return $this->hasMany(HangLoi::class, 'id_bien_the');
    }

    // ============================================================
    // ACCESSOR donVi: trả về thông tin đơn vị CƠ BẢN của variant
    // Chỉ dùng cho Blade: $bienThe->donVi->ten_don_vi
    // TUYỆT ĐỐI KHÔNG trả về đơn vị quy đổi ở đây!
    // ============================================================
    public function getDonViAttribute()
    {
        return (object)[
            'ten_don_vi' => $this->ten_don_vi,
            'ma_vach' => $this->ma_vach,
        ];
    }

    public function getThuocTinhsAttribute()
    {
        if (empty($this->thuoc_tinh_ids)) {
            return collect();
        }
        return ThuocTinhSanPham::whereIn('id', $this->thuoc_tinh_ids)->get();
    }

    public function getTenHienThiAttribute()
    {
        if ($this->la_don_vi && $this->ten_don_vi) {
            return ($this->product->ten_san_pham ?? '') . ' - ' . $this->ten_don_vi;
        }
        if ($this->ten_bien_the) {
            return ($this->product->ten_san_pham ?? '') . ' - ' . $this->ten_bien_the;
        }
        return $this->product->ten_san_pham ?? '';
    }

    public function getTenHienThiDonViAttribute(): ?string
    {
        if ($this->la_don_vi) {
            return $this->ten_don_vi;
        }
        return $this->ten_bien_the;
    }

    public function getTenBienTheHienThiAttribute(): ?string
    {
        if ($this->la_don_vi) {
            return null;
        }
        return $this->ten_bien_the;
    }

    public function getThuocTinhLabelsAttribute(): array
    {
        if (empty($this->thuoc_tinh_ids)) {
            return [];
        }
        $labels = [];
        $attrs = ThuocTinhSanPham::whereIn('id', $this->thuoc_tinh_ids)->get()->keyBy('id');
        foreach ($this->thuoc_tinh_ids as $id) {
            if (isset($attrs[$id])) {
                $attr = $attrs[$id];
                if ($attr->thuoc_tinh_cha_id && isset($attrs[$attr->thuoc_tinh_cha_id])) {
                    $labels[] = $attrs[$attr->thuoc_tinh_cha_id]->ten_thuoc_tinh;
                }
                $labels[] = $attr->ten_thuoc_tinh;
            }
        }
        return $labels;
    }

    public static function placeholderImageUrl(): string
    {
        return asset('images/product-placeholder.svg');
    }

    public static function hasImageFile(?string $path): bool
    {
        $normalized = self::normalizeImagePath($path);

        if ($normalized === null) {
            return false;
        }

        if (preg_match('/^https?:\/\//i', $normalized)) {
            return true;
        }

        return file_exists(public_path($normalized));
    }

    public static function resolveImageUrl(?string $path): string
    {
        $normalized = self::normalizeImagePath($path);

        if ($normalized === null) {
            return self::placeholderImageUrl();
        }

        if (preg_match('/^https?:\/\//i', $normalized)) {
            return $normalized;
        }

        if (file_exists(public_path($normalized))) {
            return asset($normalized);
        }

        return self::placeholderImageUrl();
    }

    private static function normalizeImagePath(?string $path): ?string
    {
        $value = trim((string) $path);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $value = preg_replace('#^/+?#', '', $value);

        if (str_starts_with($value, 'public/')) {
            $value = substr($value, 7);
        }

        return $value !== '' ? $value : null;
    }
}
