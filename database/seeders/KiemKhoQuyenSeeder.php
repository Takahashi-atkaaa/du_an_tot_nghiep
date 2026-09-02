<?php

namespace Database\Seeders;

use App\Models\Quyen;
use Illuminate\Database\Seeder;

class KiemKhoQuyenSeeder extends Seeder
{
    /**
     * Insert 6 quyền mới cho module kiểm kho
     */
    public function run(): void
    {
        $labels = config('permissions.labels', []);
        $quyens = collect(config('permissions.groups.kho_hang', []))
            ->filter(fn (string $code): bool => str_starts_with($code, 'kiem_kho_'))
            ->map(fn (string $code): array => [
                'ma_quyen' => $code,
                'ten_quyen' => $labels[$code] ?? $code,
            ])->all();

        foreach ($quyens as $quyen) {
            Quyen::firstOrCreate(
                ['ma_quyen' => $quyen['ma_quyen']],
                ['ten_quyen' => $quyen['ten_quyen']]
            );
        }

        // Gan cho admin (id_vai_tro = 1) - tat ca quyen
        $adminRole = \App\Models\VaiTro::find(1);
        if ($adminRole) {
            $quyenIds = Quyen::whereIn('ma_quyen', array_column($quyens, 'ma_quyen'))->pluck('id')->all();
            $adminRole->quyens()->syncWithoutDetaching($quyenIds);
        }
        // Các vai trò khác (trưởng ca, NV kho, NV bán hàng) sẽ được phân quyền sau qua UI Phân quyền.
    }
}
