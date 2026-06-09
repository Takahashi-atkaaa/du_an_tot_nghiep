<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SanPham extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'san_pham';

    protected $fillable = [
        'id_danh_muc',
        'ten_san_pham',
        'ma_vach',
        'hinh_anh',
        'thuong_hieu',
        'gia_ban',
        'so_luong_ton_kho',
        'mo_ta',
        'id_thuoc_tinh',
        'id_don_vi',
        'dinh_muc_toi_thieu',
        'trang_thai',
    ];

    protected $casts = [
        'gia_ban' => 'decimal:2',
        'trang_thai' => 'boolean',
    ];


}