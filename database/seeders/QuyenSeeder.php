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
        $labels = config('permissions.labels', []);
        $codes = collect(config('permissions.groups', []))
            ->flatten()
            ->unique()
            ->values();

        $quyens = $codes->map(fn (string $code): array => [
            'ma_quyen' => $code,
            'ten_quyen' => $labels[$code] ?? $code,
        ])->all();

        foreach ($quyens as $quyen) {
            Quyen::firstOrCreate(
                ['ma_quyen' => $quyen['ma_quyen']],
                ['ten_quyen' => $quyen['ten_quyen']]
            );
        }
    }
}
