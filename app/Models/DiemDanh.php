<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiemDanh extends Model
{
    //
    protected $table = 'diem_danh';

    protected $fillable = [
        'id_chia_ca_lam_viec',
        'gio_vao',
        'so_gio_di_lam_muon',
        'gio_tan_ca',
        'so_gio_lam_them',
        'trang_thai_vao_lam',
        'trang_thai_tan_ca',
    ];

    public function chiaCaLamViec()
    {
        return $this->belongsTo(
            ChiaCaLamViec::class,
            'id_chia_ca_lam_viec'
        );
    }

}
