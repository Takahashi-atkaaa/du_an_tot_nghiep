<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NguoiDung extends Model
{
	use HasFactory, SoftDeletes;

	protected $table = 'nguoi_dung';

	protected $fillable = [
		'ho_ten',
		'email',
		'sdt',
		'mat_khau',
		'vai_tro',
		'trang_thai',
	];

	protected $hidden = [
		'mat_khau',
	];

	protected $casts = [
		'trang_thai' => 'boolean',
	];

	public function scopeSearch($query, ?string $keyword)
	{
		if (blank($keyword)) {
			return $query;
		}

		$keyword = trim($keyword);

		return $query->where(function ($subQuery) use ($keyword) {
			$subQuery->where('ho_ten', 'like', '%' . $keyword . '%')
				->orWhere('email', 'like', '%' . $keyword . '%')
				->orWhere('sdt', 'like', '%' . $keyword . '%')
				->orWhere('vai_tro', 'like', '%' . $keyword . '%');
		});
	}
}
