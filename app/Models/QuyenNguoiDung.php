<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuyenNguoiDung extends Model
{

    protected $table = 'quyen_nguoi_dung';

    protected $fillable = [
        'id',
        'id_nguoi_dung',
        'id_quyen',
    ];

    public function quyen()
    {
        return $this->belongsTo(Quyen::class, 'id_quyen');
    }

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}   
