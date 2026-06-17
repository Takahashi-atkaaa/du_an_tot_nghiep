<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LichSuTichDiem extends Model
{
	use HasFactory, SoftDeletes;

	protected $table = 'lich_su_tich_diem';

	protected $fillable = [
		'id_khach_hang',
		'id_hoa_don',
		'loai_bien_dong',
		'so_diem',
		'ly_do',
	];

	protected $casts = [
		'so_diem' => 'integer',
	];

	public function khachHang()
	{
		return $this->belongsTo(KhachHang::class, 'id_khach_hang');
	}

	public function hoaDon()
	{
		return $this->belongsTo(HoaDon::class, 'id_hoa_don');
	}
}
