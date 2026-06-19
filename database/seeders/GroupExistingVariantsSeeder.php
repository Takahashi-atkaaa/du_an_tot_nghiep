<?php

namespace Database\Seeders;

use App\Models\SanPham;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupExistingVariantsSeeder extends Seeder
{
    /**
     * Nhóm các biến thể đang tách riêng thành 1 nhóm cha-con.
     * Logic: Gom các sản phẩm có cùng tên gốc (bỏ phần trong ngoặc).
     */
    public function run(): void
    {
        // Lấy tất cả sản phẩm chưa có cha, nhóm theo tên gốc
        $products = SanPham::whereNull('san_pham_cha_id')
            ->where('la_san_pham_cha', false)
            ->get();

        // Gom nhóm theo tên gốc (loại bỏ phần trong ngoặc đơn)
        $groups = [];
        foreach ($products as $p) {
            $baseName = preg_replace('/\s*\(.*\)\s*$/', '', trim($p->ten_san_pham));
            $key = mb_strtolower($baseName);
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'base_name' => $baseName,
                    'items' => [],
                ];
            }
            $groups[$key]['items'][] = $p;
        }

        foreach ($groups as $group) {
            $items = $group['items'];

            // Cần ít nhất 2 sản phẩm cùng tên mới nhóm lại
            if (count($items) < 2) {
                continue;
            }

            $template = $items[0]; // Dùng sản phẩm đầu tiên làm template

            // Tạo sản phẩm CHA (ma_vach, ma_hang phải unique nên generate mới)
            $parent = SanPham::create([
                'id_danh_muc'        => $template->id_danh_muc,
                'ten_san_pham'       => $group['base_name'],
                'ma_hang'            => $this->generateUniqueMaHang(),
                'ma_vach'            => $this->generateUniqueMaVach(),
                'hinh_anh'           => $template->hinh_anh,
                'thuong_hieu'        => $template->thuong_hieu,
                'gia_ban'            => 0,
                'so_luong_ton_kho'  => 0,
                'mo_ta'              => $template->mo_ta,
                'id_don_vi'          => $template->id_don_vi,
                'dinh_muc_toi_thieu' => $template->dinh_muc_toi_thieu,
                'trang_thai'         => $template->trang_thai,
                'la_san_pham_cha'    => true,
            ]);

            // Gán cha cho các biến thể
            foreach ($items as $item) {
                $item->update(['san_pham_cha_id' => $parent->id]);

                // Gán lại thuộc tính từ bảng trung gian (nếu có)
                $attrs = DB::table('san_pham_thuoc_tinh')
                    ->where('id_san_pham', $item->id)
                    ->pluck('id_thuoc_tinh');

                if ($attrs->isNotEmpty()) {
                    DB::table('san_pham_thuoc_tinh')
                        ->where('id_san_pham', $item->id)
                        ->delete();

                    DB::table('san_pham_thuoc_tinh')
                        ->insert($attrs->map(fn($id) => [
                            'id_san_pham' => $item->id,
                            'id_thuoc_tinh' => $id,
                        ])->toArray());
                }
            }

            $this->command->info("Đã nhóm: '{$group['base_name']}' — {$parent->id} (cha) + " . count($items) . " biến thể");
        }

        $this->command->info('Xong! Đã nhóm các biến thể thành cha-con.');
    }

    private function generateUniqueMaVach(): string
    {
        do {
            $maVach = (string) random_int(1000000000000, 9999999999999);
        } while (\App\Models\SanPham::where('ma_vach', $maVach)->exists());
        return $maVach;
    }

    private function generateUniqueMaHang(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $maHang = 'MH' . substr(str_shuffle(str_repeat($chars, 6)), 0, 6);
        } while (\App\Models\SanPham::where('ma_hang', $maHang)->exists());
        return $maHang;
    }
}
