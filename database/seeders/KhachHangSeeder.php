<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhachHangSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('khach_hang')->insert([
            [
                'ten_khach_hang' => 'Nguyễn Văn A',
                'so_dien_thoai' => '0911111111',
                'email' => 'nguyenvana@example.com',
                'dia_chi' => 'Hà Nội',
                'cong_no' => 0,
                'phi_chu' => null,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_khach_hang' => 'Trần Thị B',
                'so_dien_thoai' => '0922222222',
                'email' => 'tranthib@example.com',
                'dia_chi' => 'TP.HCM',
                'cong_no' => 0,
                'phi_chu' => null,
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten_khach_hang' => 'Lê Văn C',
                'so_dien_thoai' => '0933333333',
                'email' => null,
                'dia_chi' => 'Đà Nẵng',
                'cong_no' => 500000,
                'phi_chu' => 'Nợ tiền hàng',
                'trang_thai' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
