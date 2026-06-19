<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\DanhMucSanPham;
use App\Models\DonViSanPham;
use App\Models\ThuocTinhSanPham;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SanPham extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'san_pham';

    protected $fillable = [
        'id_danh_muc',
        'ten_san_pham',
        'ma_hang',
        'ma_vach',
        'hinh_anh',
        'thuong_hieu',
        'gia_von',
        'gia_ban',
        'so_luong_ton_kho',
        'mo_ta',
        'id_don_vi',
        'dinh_muc_toi_thieu',
        'trang_thai',
    ];

    protected $casts = [
        'gia_von' => 'decimal:2',
        'gia_ban' => 'decimal:2',
        'trang_thai' => 'boolean',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMucSanPham::class, 'id_danh_muc');
    }

    public function donVi()
    {
        return $this->belongsTo(DonViSanPham::class, 'id_don_vi');
    }

    public function thuocTinhs()
    {
        return $this->belongsToMany(ThuocTinhSanPham::class, 'san_pham_thuoc_tinh', 'id_san_pham', 'id_thuoc_tinh');
    }
}
