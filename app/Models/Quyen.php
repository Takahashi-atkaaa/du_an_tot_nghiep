<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quyen extends BaseModel
{
    protected $table = 'quyen';

    protected $fillable = [
        'id',
        'ma_quyen',
        'ten_quyen',
    ];

    public function vaiTros(): BelongsToMany
    {
        return $this->belongsToMany(
            VaiTro::class,
            'quyen_vai_tro',
            'id_quyen',
            'id_vai_tro'
        );
    }
}
