<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NhaCungCap extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nha_cung_cap';

    protected $fillable = [
        'ten_nha_cung_cap',
        'email',
        'so_dien_thoai',
        'nguoi_dai_dien',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}