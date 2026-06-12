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
	];

	protected $casts = [
		'trang_thai' => 'boolean',
	];
}

