<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ChiTietLoHang;
use App\Models\BienTheSanPham;

/**
 * Đồng bộ bien_the_san_pham.so_luong_ton = SUM(chi_tiet_lo_hang.so_luong_ton).
 *
 * Dùng để chạy dọn dẹp một lần sau khi refactor Phase 4.2
 * (xóa các manual increment/decrement trùng Observer) hoặc
 * bất kỳ khi nào dữ liệu bị lệch.
 */
class InventoryRecalculate extends Command
{
    protected $signature = 'inventory:recalculate
                            {--variant=* : Chỉ đồng bộ các variant_id cụ thể (có thể truyền nhiều lần)}
                            {--dry-run : Chỉ hiển thị sự khác biệt, không ghi DB}';

    protected $description = 'Đồng bộ bien_the_san_pham.so_luong_ton = SUM(chi_tiet_lo_hang.so_luong_ton)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $variantIds = $this->option('variant');

        if ($dryRun) {
            return $this->runDry($variantIds);
        }

        return $this->runUpdate($variantIds);
    }

    private function runDry(array $variantIds): int
    {
        $query = ChiTietLoHang::query()
            ->selectRaw('variant_id, SUM(so_luong_ton) AS tong_ton_thuc_te')
            ->whereNotNull('variant_id')
            ->groupBy('variant_id');

        if (!empty($variantIds)) {
            $query->whereIn('variant_id', $variantIds);
        }

        $sumByVariant = $query->pluck('tong_ton_thuc_te', 'variant_id')->toArray();

        $variantQuery = BienTheSanPham::query()->select('id', 'so_luong_ton');
        if (!empty($variantIds)) {
            $variantQuery->whereIn('id', $variantIds);
        }

        $diffCount = 0;
        $rows = [];
        foreach ($variantQuery->get() as $variant) {
            $thucTe = (int) ($sumByVariant[$variant->id] ?? 0);
            $hienTai = (int) $variant->so_luong_ton;
            if ($thucTe !== $hienTai) {
                $diffCount++;
                $rows[] = [
                    $variant->id,
                    $hienTai,
                    $thucTe,
                    $thucTe - $hienTai,
                ];
            }
        }

        if (empty($rows)) {
            $this->info('Không có biến thể nào lệch giữa tổng tồn và SUM chi_tiet_lo_hang.');
            return self::SUCCESS;
        }

        $this->warn(sprintf('Có %d biến thể lệch giữa tổng tồn và SUM:', $diffCount));
        $this->table(
            ['variant_id', 'so_luong_ton (DB)', 'SUM(chi_tiet_lo_hang)', 'Delta'],
            $rows
        );

        $this->line('Chạy lại không có --dry-run để ghi đè.');
        return self::SUCCESS;
    }

    private function runUpdate(array $variantIds): int
    {
        $count = 0;
        DB::transaction(function () use ($variantIds, &$count) {
            $query = ChiTietLoHang::query()
                ->selectRaw('variant_id, SUM(so_luong_ton) AS tong_ton')
                ->whereNotNull('variant_id')
                ->groupBy('variant_id');

            if (!empty($variantIds)) {
                $query->whereIn('variant_id', $variantIds);
            }

            foreach ($query->get() as $row) {
                BienTheSanPham::where('id', $row->variant_id)
                    ->update(['so_luong_ton' => (int) $row->tong_ton]);
                $count++;
            }
        });

        $this->info("Đã đồng bộ {$count} biến thể.");
        return self::SUCCESS;
    }
}