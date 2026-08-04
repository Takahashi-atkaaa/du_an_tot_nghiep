<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhuyenMaiSanPhamSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $khuyenMais = DB::table('khuyen_mai')->pluck('id')->toArray();
        $sanPhams = DB::table('san_pham')->whereNull('deleted_at')->pluck('id')->toArray();

        if (empty($khuyenMais) || empty($sanPhams)) {
            $this->command->warn('[KhuyenMaiSanPhamSeeder] Khong co khuyen mai hoac san pham. Bo qua.');
            return;
        }

        $pivots = [];

        foreach ($khuyenMais as $kmId) {
            $pickCount = rand(8, 15);
            $keys = array_rand($sanPhams, min($pickCount, count($sanPhams)));
            if (!is_array($keys)) {
                $keys = [$keys];
            }

            foreach ($keys as $k) {
                $pivots[] = [
                    'id_khuyen_mai' => $kmId,
                    'id_san_pham' => $sanPhams[$k],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $unique = [];
        foreach ($pivots as $p) {
            $key = $p['id_khuyen_mai'].'-'.$p['id_san_pham'];
            $unique[$key] = $p;
        }
        $pivots = array_values($unique);

        DB::table('khuyen_mai_san_pham')->insert($pivots);

        $this->command->info('[KhuyenMaiSanPhamSeeder] Da tao '.count($pivots).' pivot khuyen_mai_san_pham.');
    }
}