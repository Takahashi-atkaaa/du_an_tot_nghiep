<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HangLoi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hang_loi';

    protected $fillable = [
        'id_doi_tra',
        'id_chi_tiet_doi_tra',
        'id_bien_the',
        'so_luong',
        'trang_thai',
        'ly_do',
        'ngay_tieu_huy',
        'id_nguoi_dung_tieu_huy',
    ];

    protected $casts = [
        'so_luong' => 'integer',
        'ngay_tieu_huy' => 'datetime',
    ];

    public function doiTra()
    {
        return $this->belongsTo(DoiTra::class, 'id_doi_tra');
    }

    public function chiTietDoiTra()
    {
        return $this->belongsTo(ChiTietDoiTra::class, 'id_chi_tiet_doi_tra');
    }

    public function bienTheSanPham()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the');
    }

    public function nguoiDungTieuHuy()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung_tieu_huy');
    }

    public function getNguoiXuLyDoiTraHienThiAttribute(): string
    {
        $nguoiDung = $this->doiTra?->nguoiDung;

        if (!$nguoiDung) {
            return 'N/A';
        }

        $hoTen = trim((string) ($nguoiDung->getRawOriginal('ho_ten') ?: $nguoiDung->ho_ten));
        $tenVaiTro = trim((string) optional($nguoiDung->vaiTro)->ten_vai_tro);

        if ($tenVaiTro === '') {
            return $hoTen !== '' ? $hoTen : 'N/A';
        }

        return sprintf('%s (%s)', $hoTen, Str::headline($tenVaiTro));
    }

    public function getNguoiBanHienThiAttribute(): string
    {
        return $this->nguoi_xu_ly_doi_tra_hien_thi;
    }

    public function getTenSanPhamBienTheHienThiAttribute(): string
    {
        $bienThe = $this->bienTheSanPham;
        $tenSanPham = trim((string) optional($bienThe?->product)->ten_san_pham);

        if (!$bienThe) {
            return $tenSanPham !== '' ? $tenSanPham : 'Sản phẩm';
        }

        $phanBienThe = [];

        foreach (($bienThe->thuoc_tinh_labels ?? []) as $label) {
            $label = trim((string) $label);

            if ($label !== '' && !in_array($label, $phanBienThe, true)) {
                $phanBienThe[] = $label;
            }
        }

        foreach ([$bienThe->ten_bien_the, $bienThe->ten_don_vi] as $giaTri) {
            $giaTri = trim((string) $giaTri);

            if ($giaTri !== '' && !in_array($giaTri, $phanBienThe, true)) {
                $phanBienThe[] = $giaTri;
            }
        }

        if ($tenSanPham === '') {
            return !empty($phanBienThe) ? implode(' - ', $phanBienThe) : 'Sản phẩm';
        }

        if (empty($phanBienThe)) {
            return $tenSanPham;
        }

        return $tenSanPham . ' - ' . implode(' - ', $phanBienThe);
    }
}
