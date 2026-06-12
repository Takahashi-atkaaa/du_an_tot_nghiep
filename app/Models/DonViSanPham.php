<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class DonViSanPham extends BaseModel
{
	use SoftDeletes;

	protected $table = 'don_vi_san_pham';

	protected $fillable = [
		'ten_don_vi',
		'so_luong_san_pham_trong_don_vi',
		'trang_thai',
	];

	protected $casts = [
		'trang_thai' => 'boolean',
	];
}
