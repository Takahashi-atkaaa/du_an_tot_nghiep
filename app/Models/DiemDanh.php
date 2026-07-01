<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemDanh extends Model
{
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

    protected $casts = [
        'gio_vao' => 'datetime',
        'gio_tan_ca' => 'datetime',
        'so_gio_di_lam_muon' => 'integer',
        'so_gio_lam_them' => 'float',
    ];

    public function chiaCaLamViec(): BelongsTo
    {
        return $this->belongsTo(ChiaCaLamViec::class, 'id_chia_ca_lam_viec');
    }
}
