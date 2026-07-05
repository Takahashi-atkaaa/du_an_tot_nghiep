<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DanhMucSanPham extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'danh_muc_san_pham';

    protected $fillable = [
        'ten_danh_muc',
        'trang_thai',
        'mau_sac',
        'icon'
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    /**
     * Một danh mục có nhiều sản phẩm
     */

    public function sanPhams()
{
    return $this->hasMany(SanPham::class, 'id_danh_muc');
}
}   