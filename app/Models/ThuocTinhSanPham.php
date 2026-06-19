<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ThuocTinhSanPham extends BaseModel
{
    use SoftDeletes;

    protected $table = 'thuoc_tinh_san_pham';

    protected $fillable = [
        'ten_thuoc_tinh',
        'trang_thai',
        'thuoc_tinh_cha_id',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    public function thuocTinhCha()
    {
        return $this->belongsTo(ThuocTinhSanPham::class, 'thuoc_tinh_cha_id');
    }

    public function thuocTinhCons()
    {
        return $this->hasMany(ThuocTinhSanPham::class, 'thuoc_tinh_cha_id');
    }

    public function sanPhams()
    {
        return $this->belongsToMany(SanPham::class, 'san_pham_thuoc_tinh', 'id_thuoc_tinh', 'id_san_pham');
    }
}
