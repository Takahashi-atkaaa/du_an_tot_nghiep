<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BangLuong extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bang_luong';

    protected $fillable = [
        'ten_bang_luong',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'tong_luong_tat_ca_nhan_vien',
        'so_nhan_vien',
        'trang_thai',
    ];

    protected $casts = [
        'tong_luong_tat_ca_nhan_vien' => 'decimal:2',
        'so_nhan_vien' => 'integer',
        'ngay_bat_dau' => 'date',
        'ngay_ket_thuc' => 'date',
    ];

    public function phieuLuongs(): HasMany
    {
        return $this->hasMany(PhieuLuong::class, 'id_bang_luong');
    }
}
