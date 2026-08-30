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
            KiemKhoQuyenSeeder::class,

            // Nhom danh muc (DanhMucDonViSeeder, ThuocTinhSeeder da xoa - tao du lieu thu cong)
            KhachHangSeeder::class,
            NhaCungCapSeeder::class,

            // Nhom san pham & kho (SanPhamSeeder da xoa - tao du lieu thu cong)
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

            // Nhom giao ca
            GiaoCaSeeder::class,
        ]);
    }
}
