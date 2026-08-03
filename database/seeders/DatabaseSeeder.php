<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // Nhom phan quyen & nhan su
            VaiTroSeeder::class,
            QuyenSeeder::class,
            PhanQuyenSeeder::class,
            NhanSuSeeder::class,
            DiemDanhSeeder::class,

            // Nhom danh muc
            KhachHangSeeder::class,
            NhaCungCapSeeder::class,
            DanhMucDonViSeeder::class,
            ThuocTinhSeeder::class,

            // Nhom san pham & kho
            SanPhamSeeder::class,
            KhoHangSeeder::class,
            DonViQuyDoiSeeder::class,

            // Nhom khuyen mai
            KhuyenMaiSeeder::class,
            KhuyenMaiSanPhamSeeder::class,

            // Nhom ban hang
            BanHangSeedBoSungSeeder::class,
            ThanhToanHoaDonSeeder::class,
            GiaoDichSeeder::class,
            LichSuTichDiemSeeder::class,

            // Nhom kiem kho
            PhieuKiemKhoSeeder::class,

            // Nhom luong
            BangLuongSeedBoSungSeeder::class,

            // Nhom giao ca
            GiaoCaSeeder::class,
        ]);
    }
}
