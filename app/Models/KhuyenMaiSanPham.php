<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KhuyenMaiSanPham extends Model
{
	use HasFactory, SoftDeletes;

	protected $table = 'lich_su_tich_diem';

	protected $fillable = [
		'id_khuyen_mai',
		'id_san_pham'
	];
}
