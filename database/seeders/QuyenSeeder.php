<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuyenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('quyen')->insert([

            // Danh mục
            [
                'ma_quyen' => 'xem_danh_muc',
                'ten_quyen' => 'Xem danh mục'
            ],
            [
                'ma_quyen' => 'them_danh_muc',
                'ten_quyen' => 'Thêm danh mục'
            ],
            [
                'ma_quyen' => 'sua_danh_muc',
                'ten_quyen' => 'Sửa danh mục'
            ],
            [
                'ma_quyen' => 'xoa_danh_muc',
                'ten_quyen' => 'Xóa danh mục'
            ],

            // Sản phẩm
            [
                'ma_quyen' => 'xem_san_pham',
                'ten_quyen' => 'Xem sản phẩm'
            ],
            [
                'ma_quyen' => 'them_san_pham',
                'ten_quyen' => 'Thêm sản phẩm'
            ],
            [
                'ma_quyen' => 'sua_san_pham',
                'ten_quyen' => 'Sửa sản phẩm'
            ],
            [
                'ma_quyen' => 'xoa_san_pham',
                'ten_quyen' => 'Xóa sản phẩm'
            ],

            // Nhà cung cấp
            [
                'ma_quyen' => 'xem_nha_cung_cap',
                'ten_quyen' => 'Xem nhà cung cấp'
            ],
            [
                'ma_quyen' => 'them_nha_cung_cap',
                'ten_quyen' => 'Thêm nhà cung cấp'
            ],
            [
                'ma_quyen' => 'sua_nha_cung_cap',
                'ten_quyen' => 'Sửa nhà cung cấp'
            ],
            [
                'ma_quyen' => 'xoa_nha_cung_cap',
                'ten_quyen' => 'Xóa nhà cung cấp'
            ],

            // Ca làm việc
            [
                'ma_quyen' => 'xem_ca_lam_viec',
                'ten_quyen' => 'Xem ca làm việc'
            ],
            [
                'ma_quyen' => 'them_ca_lam_viec',
                'ten_quyen' => 'Thêm ca làm việc'
            ],
            [
                'ma_quyen' => 'sua_ca_lam_viec',
                'ten_quyen' => 'Sửa ca làm việc'
            ],
            [
                'ma_quyen' => 'xoa_ca_lam_viec',
                'ten_quyen' => 'Xóa ca làm việc'
            ],

            // Người dùng
            [
                'ma_quyen' => 'xem_nguoi_dung',
                'ten_quyen' => 'Xem người dùng'
            ],
            [
                'ma_quyen' => 'them_nguoi_dung',
                'ten_quyen' => 'Thêm người dùng'
            ],
            [
                'ma_quyen' => 'sua_nguoi_dung',
                'ten_quyen' => 'Sửa người dùng'
            ],
            [
                'ma_quyen' => 'xoa_nguoi_dung',
                'ten_quyen' => 'Xóa người dùng'
            ],

            // Phân quyền
            [
                'ma_quyen' => 'phan_quyen',
                'ten_quyen' => 'Phân quyền người dùng'
            ],

        ]);
    }
}
