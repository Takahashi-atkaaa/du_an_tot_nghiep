<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaiTro extends Model
{
  protected $table = 'vai_tro';

    protected $fillable = [
        'id',
        'ten_vai_tro',
    ];

    /**
     * Một vai trò có nhiều người dùng
     */
    public function nguoiDungs()
    {
        return $this->hasMany(NguoiDung::class, 'id_vai_tro');
    }

    /**
     * Một vai trò có nhiều quyền
     */
    public function quyens()
    {
        return $this->belongsToMany(
            Quyen::class,
            'quyen_vai_tro',
            'id_vai_tro',
            'id_quyen'
        );
    }

    public function hasPermission($maQuyen)
    {
        return $this->quyens()
            ->where('ma_quyen', $maQuyen)
            ->exists();
    }
}
