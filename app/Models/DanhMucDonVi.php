<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DanhMucDonVi extends Model
{
    use SoftDeletes;

    protected $table = 'danh_muc_don_vi';

    protected $fillable = [
        'ten_don_vi',
        'so_luong_san_pham_trong_don_vi',
        'trang_thai',
    ];

    protected $casts = [
        'so_luong_san_pham_trong_don_vi' => 'integer',
        'trang_thai' => 'boolean',
    ];

    public function donViQuyDois()
    {
        return $this->hasMany(DonViQuyDoi::class, 'don_vi_chuan_id');
    }

    // Accessor: "Thùng 24"
    public function getTenHienThiAttribute(): string
    {
        return $this->ten_don_vi . ' ' . $this->so_luong_san_pham_trong_don_vi;
    }
}
