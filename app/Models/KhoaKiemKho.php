<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhoaKiemKho extends Model
{
    protected $table = 'khoa_kiem_kho';

    protected $fillable = [
        'id_phieu_kiem_kho',
        'variant_id',
        'ngay_khoa',
        'ngay_mo',
        'ly_do',
    ];

    protected $casts = [
        'ngay_khoa' => 'datetime',
        'ngay_mo' => 'datetime',
    ];

    public function phieuKiemKho()
    {
        return $this->belongsTo(PhieuKiemKho::class, 'id_phieu_kiem_kho');
    }

    public function variant()
    {
        return $this->belongsTo(BienTheSanPham::class, 'variant_id');
    }

    /**
     * Scope: các record đang khoá (chưa mở)
     */
    public function scopeDangKhoa($query)
    {
        return $query->whereNull('ngay_mo');
    }
}
