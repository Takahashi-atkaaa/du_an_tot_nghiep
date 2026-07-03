<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiemDanh extends Model
{
    //
    protected $table = 'diem_danh';

    protected $fillable = [
        'ten_danh_muc',
        'trang_thai',
        'id_chia_ca_lam_viec',
        'mau_sac',
        'icon'
    ];

    public function chiaCaLamViec()
    {
        return $this->belongsTo(
            ChiaCaLamViec::class,
            'id_chia_ca_lam_viec'
        );
    }

}
