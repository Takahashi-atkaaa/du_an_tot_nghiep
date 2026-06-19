<?php

namespace App\Models;
use App\Models\QuyenNguoiDung;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quyen extends BaseModel
{

    protected $table = 'quyen';

    protected $fillable = [
        'id',
        'ma_quyen',
        'ten_quyen',
    ];


    public function quyenNguoiDung()
    {
        return $this->hasMany(\App\Models\QuyenVaiTro::class, 'id_quyen');
    }
}

