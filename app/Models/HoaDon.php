<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HoaDon extends Model
{
	use HasFactory, SoftDeletes;

	protected $table = 'hoa_don';

	protected $fillable = [
		'id_nguoi_dung',
		'id_khach_hang',
		'id_ca_lam_viec',
		'id_khuyen_mai',
		'tong_tien_hang',
		'tien_giam_gia',
		'khach_can_tra',
		'tien_khach_dua',
		'tien_thua',
		'phuong_thuc_thanh_toan',
		'trang_thai',
		'diem_su_dung',
		'diem_thu_duoc',
	];

	protected $casts = [
		'tong_tien_hang' => 'decimal:2',
		'tien_giam_gia' => 'decimal:2',
		'khach_can_tra' => 'decimal:2',
		'tien_khach_dua' => 'decimal:2',
		'tien_thua' => 'decimal:2',
		'diem_su_dung' => 'integer',
		'diem_thu_duoc' => 'integer',
	];

	public function khachHang()
	{
		return $this->belongsTo(KhachHang::class, 'id_khach_hang');
	}

	public function ca_lam_viec(){
		return $this->belongsTo(CaLamViec::class, 'id_ca_lam_viec');
	}

	public function chiTietHoaDon(){
		return $this->HasMany(ChiTietHoaDon::class, 'id_hoa_don');
	}
	public function nguoiDung(){
		return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
	}

	public function caLamViec(){
		return $this->belongsTo(CaLamViec::class, 'id_ca_lam_viec');
	}
}
