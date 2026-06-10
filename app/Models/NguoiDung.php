<?php

// Khai bao namespace cho model
namespace App\Models;

// Su dung trait Authenticatable de ho tro xac thuc
use Illuminate\Foundation\Auth\User as Authenticatable;

class NguoiDung extends Authenticatable
{
    // Ten bang trong csdl
    protected $table = 'nguoi_dung';

    // Tat ca cac cot co the gan hang loat khi tao/sua
    protected $fillable = [
        'ho_ten',     // Ho va ten nguoi dung
        'email',      // Dia chi email (dung de dang nhap)
        'sdt',        // So dien thoai
        'mat_khau',   // Mat khau da duoc ma hoa
        'vai_tro',    // Vai tro: Admin, nhan_vien, truong_ca
        'trang_thai', // Trang thai hoat dong: true = active, false = bi khoa
    ];



    // Tu dong ma hoa mat khau moi khi gan
    // Su dung accessor de dam bao mat khau luon duoc bcrypt
    public function setMatKhauAttribute($value)
    {
        // Neu mat khau chua duoc ma hoa thi ma hoa no
        // Kiem tra do dai > 60 vi bcrypt tao ra chuoi 60 ky tu
        if (strlen($value) !== 60) {
            $this->attributes['mat_khau'] = bcrypt($value);
        } else {
            $this->attributes['mat_khau'] = $value;
        }
    }

    // Tra ve mat khau (vi Laravel can mat_khau thay vi mat_khau)
    // Override thuoc tinh tu Model cha
    public function getAuthPassword()
    {
        // Tra ve gia tri cot mat_khau trong bang
        return $this->mat_khau;
    }
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
