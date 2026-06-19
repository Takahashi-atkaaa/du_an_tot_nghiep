<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\DanhMucSanPham;
use App\Models\DonViSanPham;
use App\Models\ThuocTinhSanPham;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SanPham extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'san_pham';

    protected $fillable = [
        'id_danh_muc',
        'ten_san_pham',
        'ma_hang',
        'ma_vach',
        'hinh_anh',
        'thuong_hieu',
        'gia_von',
        'gia_ban',
        'so_luong_ton_kho',
        'mo_ta',
        'id_don_vi',
        'dinh_muc_toi_thieu',
        'trang_thai',
    ];

    protected $casts = [
        'gia_von' => 'decimal:2',
        'gia_ban' => 'decimal:2',
        'trang_thai' => 'boolean',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucSanPham::class, 'id_danh_muc');
    }

    public function donVi()
    {
        return $this->belongsTo(DonViSanPham::class, 'id_don_vi');
    }

    public function thuocTinhs()
    {
        return $this->belongsToMany(ThuocTinhSanPham::class, 'san_pham_thuoc_tinh', 'id_san_pham', 'id_thuoc_tinh');
    }

    public function sanPhamCha()
    {
        return $this->belongsTo(SanPham::class, 'san_pham_cha_id');
    }

    public function bienThe()
    {
        return $this->hasMany(SanPham::class, 'san_pham_cha_id');
    }

    public function scopeSanPhamCha($query)
    {
        return $query->where('san_pham_cha_id', null);
    }

    public function getSoBienTheAttribute()
    {
        return $this->bienThe()->count();
    }

    public function getGiaBanHienThiAttribute()
    {
        if ($this->san_pham_cha_id) {
            return $this->gia_ban;
        }

        $bienThe = $this->bienThe;
        if ($bienThe->isNotEmpty()) {
            $min = $bienThe->min('gia_ban');
            $max = $bienThe->max('gia_ban');
            if ($min == $max) {
                return $min;
            }
            return "{$min} - {$max}";
        }

        return $this->gia_ban;
    }

    public function getTonKhoTongAttribute()
    {
        if ($this->san_pham_cha_id) {
            return $this->so_luong_ton_kho;
        }
        return $this->so_luong_ton_kho + $this->bienThe->sum('so_luong_ton_kho');
    }
}
