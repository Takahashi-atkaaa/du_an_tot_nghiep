<?php

namespace Database\Seeders;

use App\Models\Quyen;
use Illuminate\Database\Seeder;

class QuyenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quyens = [

        [
            'ma_quyen' => 'quan_ly_danh_muc',
            'ten_quyen' => 'Quản lý danh mục'
        ],
        [
            'ma_quyen' => 'quan_ly_san_pham',
            'ten_quyen' => 'Quản lý sản phẩm'
        ],
        [
            'ma_quyen' => 'quan_ly_nha_cung_cap',
            'ten_quyen' => 'Quản lý nhà cung cấp'
        ],
        [
            'ma_quyen' => 'quan_ly_khach_hang',
            'ten_quyen' => 'Quản lý khách hàng'
        ],
        [
            'ma_quyen' => 'quan_ly_khuyen_mai',
            'ten_quyen' => 'Quản lý khuyến mãi'
        ],
        [
            'ma_quyen' => 'quan_ly_hoa_don',
            'ten_quyen' => 'Quản lý hóa đơn'
        ],
        [
            'ma_quyen' => 'quan_ly_kho_hang',
            'ten_quyen' => 'Quản lý kho hàng'
        ],
        [
            'ma_quyen' => 'quan_ly_ca_lam',
            'ten_quyen' => 'Quản lý ca làm'
        ],
        [
            'ma_quyen' => 'quan_ly_nhan_su',
            'ten_quyen' => 'Quản lý nhân sự'
        ],
        [
            'ma_quyen' => 'ban_hang',
            'ten_quyen' => 'Bán hàng'
        ],
        [
            'ma_quyen' => 'phan_quyen',
            'ten_quyen' => 'Phân quyền'
        ],

];

        foreach ($quyens as $quyen) {
            Quyen::firstOrCreate(
                ['ma_quyen' => $quyen['ma_quyen']],
                ['ten_quyen' => $quyen['ten_quyen']]
            );
        }
    }
}
