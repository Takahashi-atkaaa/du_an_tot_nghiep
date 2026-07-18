<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThanhToanHoaDon extends Model
{
    protected $table = 'thanh_toan_hoa_don';

    protected $fillable = [
        'id_hoa_don',
        'phuong_thuc',
        'so_tien',
        'trang_thai',
        'ma_giao_dich',
        'ghi_chu',
        'thanh_toan_luc',
    ];

    protected $casts = [
        'so_tien' => 'decimal:2',
        'thanh_toan_luc' => 'datetime',
    ];

    public function hoaDon()
    {
        return $this->belongsTo(HoaDon::class, 'id_hoa_don');
    }
}