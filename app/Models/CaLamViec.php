<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaLamViec extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ca_lam_viec';

    protected $fillable = [
        'ten_ca',
        'gio_bat_dau',
        'gio_ket_thuc',
        'so_nhan_vien_toi_thieu',
        'so_nhan_vien_toi_da',
        'so_phut_di_lam_tre_toi_da',
    ];

    protected $casts = [
        'so_nhan_vien_toi_thieu' => 'integer',
        'so_nhan_vien_toi_da' => 'integer',
        'so_phut_di_lam_tre_toi_da' => 'integer',
    ];

    public function chiaCaLamViecs(): HasMany
    {
        return $this->hasMany(ChiaCaLamViec::class, 'id_ca_lam_viec');
    }

    public function hoaDon(){
        return $this->HasMany(HoaDon::class, 'id_ca_lam_viec', 'id');
    }
}
