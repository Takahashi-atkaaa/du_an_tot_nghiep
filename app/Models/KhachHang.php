<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KhachHang extends Model
{
	use HasFactory, SoftDeletes;

	protected $table = 'khach_hang';

	protected $fillable = [
		'ten_khach_hang',
		'so_dien_thoai',
		'email',
		'dia_chi',
		'cong_no',
		'phi_chu',
		'trang_thai',
		'diem_tich_luy',
		'tong_chi_tieu',
	];

	protected $casts = [
		'trang_thai' => 'boolean',
		'cong_no' => 'decimal:2',
		'tong_chi_tieu' => 'decimal:2',
		'diem_tich_luy' => 'integer',
	];

	public function scopeSearch($query, ?string $keyword)
	{
		if (blank($keyword)) {
			return $query;
		}

		$keyword = trim($keyword);

		return $query->where(function ($subQuery) use ($keyword) {
			$subQuery->where('ten_khach_hang', 'like', '%' . $keyword . '%')
				->orWhere('so_dien_thoai', 'like', '%' . $keyword . '%')
				->orWhere('email', 'like', '%' . $keyword . '%');
		});
	}

	public function hoaDons()
	{
		return $this->hasMany(HoaDon::class, 'id_khach_hang');
	}

	public function lichSuTichDiems()
	{
		return $this->hasMany(LichSuTichDiem::class, 'id_khach_hang');
	}
}
