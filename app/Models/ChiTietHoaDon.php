<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class ChiTietHoaDon extends Model
    {
        use HasFactory;

        protected $table = 'chi_tiet_hoa_don';

        protected $fillable = [
            'id_hoa_don',
            'id_san_pham',
            'id_chi_tiet_phieu',
            'so_luong',
            'gia_ban',
            'thanh_tien',
        ];

        public function chiTietPhieu(){
            return $this->belongsTo(ChiTietPhieu::class, 'id_chi_tiet_phieu');
        }

        public function hoaDon(){
            return $this->belongsTo(HoaDon::class, 'id_hoa_don');
        }

        public function sanPham(){
            return $this->belongsTo(SanPham::class, 'id_san_pham');
        }


    }