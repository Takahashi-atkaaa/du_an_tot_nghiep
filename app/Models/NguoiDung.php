<?php

// Khai bao namespace cho model
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
// Su dung trait Authenticatable de ho tro xac thuc

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class NguoiDung extends Authenticatable
{
    use HasFactory, SoftDeletes;

    // Ten bang trong csdl
    protected $table = 'nguoi_dung';

    // Tat ca cac cot co the gan hang loat khi tao/sua
    protected $fillable = [
        'ho_ten',
        'email',
        'sdt',
        'mat_khau',
        'id_vai_tro',
        'trang_thai',
        'gioi_tinh',
        'cccd',
        'anh_dai_dien',
        'anh_cccd_mat_truoc',
        'anh_cccd_mat_sau',
    ];

    protected $hidden = [
        'mat_khau',
    ];

    // protected $casts = [
    //     'trang_thai' => 'boolean',
    // ];

    // Tu dong ma hoa mat khau moi khi gan
    // Su dung accessor de dam bao mat khau luon duoc bcrypt
    public function setMatKhauAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['mat_khau'] = Hash::needsRehash($value)
                ? Hash::make($value)
                : $value;
        }
    }

    // Tra ve mat khau (vi Laravel can mat_khau thay vi mat_khau)
    // Override thuoc tinh tu Model cha
    public function getAuthPassword()
    {
        // Tra ve gia tri cot mat_khau trong bang
        return $this->mat_khau;
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (blank($keyword)) {
            return $query;
        }

        $keyword = trim($keyword);

        return $query->where(function ($subQuery) use ($keyword) {
            $subQuery->where('ho_ten', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%")
                ->orWhere('sdt', 'like', "%{$keyword}%");
        });
    }

    public function quyenNguoiDungs()
    {
        return $this->hasMany(
            QuyenVaiTro::class,
            'id_nguoi_dung'
        );
    }

    public function vaiTro(): BelongsTo
    {
        return $this->belongsTo(VaiTro::class, 'id_vai_tro');
    }

    public function chiaCaLamViecs(): HasMany
    {
        return $this->hasMany(ChiaCaLamViec::class, 'id_nguoi_dung');
    }

    public function doiTras(): HasMany
    {
        return $this->hasMany(DoiTra::class, 'id_nguoi_dung');
    }

    public function hangLoisTieuHuy(): HasMany
    {
        return $this->hasMany(HangLoi::class, 'id_nguoi_dung_tieu_huy');
    }

    public function getHoTenKemVaiTroAttribute(): string
    {
        $hoTen = trim((string) ($this->getRawOriginal('ho_ten') ?: $this->ho_ten));
        $tenVaiTro = trim((string) optional($this->vaiTro)->ten_vai_tro);

        if ($tenVaiTro === '') {
            return $hoTen;
        }

        return sprintf('%s (%s)', $hoTen, Str::headline($tenVaiTro));
    }
}
