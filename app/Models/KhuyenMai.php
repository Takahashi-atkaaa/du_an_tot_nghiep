<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KhuyenMai extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'khuyen_mai';

    protected $fillable = [
        'ten_chuong_trinh',
        'loai_giam_gia',
        'gia_tri_giam',
        'giam_toi_da',
        'so_luong_sp_toi_thieu',
        'don_hang_toi_thieu',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'trang_thai',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_bat_dau' => 'datetime',
        'ngay_ket_thuc' => 'datetime',
        'gia_tri_giam' => 'decimal:2',
        'giam_toi_da' => 'decimal:2',
        'don_hang_toi_thieu' => 'decimal:2',
        'trang_thai' => 'boolean',
    ];
}
