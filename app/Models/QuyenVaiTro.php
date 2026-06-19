<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuyenVaiTro extends Model
{
    protected $table = 'quyen_vai_tro';

    protected $fillable = [
        'id_vai_tro',
        'id_quyen',
    ];

    /**
     * Thuộc về một vai trò
     */
    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'id_vai_tro');
    }

    /**
     * Thuộc về một quyền
     */
    public function quyen()
    {
        return $this->belongsTo(Quyen::class, 'id_quyen');
    }
}