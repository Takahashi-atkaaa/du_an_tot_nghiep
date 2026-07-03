<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupVariantIdColumns extends Command
{
    protected $signature = 'products:cleanup-variant-id {--force : Bo xac nhan}';

    protected $description = 'Xoa cot id_san_pham cu khoi chi_tiet_lo_hang va chi_tiet_phieu sau khi migrate';

    public function handle(): int
    {
        // Chi tiết phiếu đã xử lý ở lần trước - kiểm tra trạng thái
        $ctPhieuHas = collect(DB::select("SHOW COLUMNS FROM chi_tiet_phieu WHERE Field = 'id_san_pham'"))->isNotEmpty();
        if (!$ctPhieuHas) {
            $this->info('[chi_tiet_phieu] Cot id_san_pham da duoc xoa.');
        }

        // Chi tiết lô hàng
        $ctLoHangHas = collect(DB::select("SHOW COLUMNS FROM chi_tiet_lo_hang WHERE Field = 'id_san_pham'"))->isNotEmpty();

        if (!$ctLoHangHas) {
            $this->info('[chi_tiet_lo_hang] Cot id_san_pham da duoc xoa.');
            if (!$ctPhieuHas) {
                $this->info('Cleanup hoan tat. Khong co gi de lam.');
                return 0;
            }
        }

        $dangThieu = DB::table('chi_tiet_lo_hang')->whereNull('variant_id')->count();
        if ($dangThieu > 0) {
            $this->error("Con {$dangThieu} chi_tiet_lo_hang chua co variant_id. Vui long xu ly truoc.");
            return 1;
        }

        if (!$this->option('force') && !$this->confirm('Se xoa cot id_san_pham khoi chi_tiet_lo_hang va chi_tiet_phieu. Tiep tuc?')) {
            $this->info('Huy bo.');
            return 0;
        }

        // Xử lý chi_tiet_lo_hang
        if ($ctLoHangHas) {
            $this->cleanupChiTietLoHang();
        }

        $this->info('Cleanup hoan tat.');
        return 0;
    }

    private function cleanupChiTietLoHang(): void
    {
        // B1: Drop unique constraint bằng DROP INDEX (không qua ALTER TABLE DROP COLUMN trigger)
        // Unique key: (id_lo_hang, id_san_pham, han_su_dung) -> tên "chi_tiet_lo_unique"
        try {
            DB::statement("ALTER TABLE `chi_tiet_lo_hang` DROP INDEX `chi_tiet_lo_unique`");
            $this->info('  [chi_tiet_lo_hang] Da xoa unique key chi_tiet_lo_unique.');
        } catch (\Throwable $e) {
            $this->warn('  [chi_tiet_lo_hang] Khong the xoa chi_tiet_lo_unique: ' . $e->getMessage());
            $this->line('  [chi_tiet_lo_hang] Thu xoa truc tiep bang DROP INDEX...');
            try {
                DB::statement('DROP INDEX `chi_tiet_lo_unique` ON `chi_tiet_lo_hang`');
                $this->info('  [chi_tiet_lo_hang] Da xoa unique key (method 2).');
            } catch (\Throwable $e2) {
                $this->error('  [chi_tiet_lo_hang] Loi xoa unique key: ' . $e2->getMessage());
                return;
            }
        }

        // B2: Xóa index cũ trên id_san_pham
        $this->safeDropIndex('chi_tiet_lo_hang_id_san_pham_han_su_dung_index', 'chi_tiet_lo_hang');

        // B3: Xóa cột id_san_pham
        try {
            DB::statement('ALTER TABLE `chi_tiet_lo_hang` DROP COLUMN `id_san_pham`');
            $this->info('  [chi_tiet_lo_hang] Da xoa cot id_san_pham.');
        } catch (\Throwable $e) {
            $this->error('  [chi_tiet_lo_hang] Loi xoa cot: ' . $e->getMessage());
        }
    }

    private function safeDropIndex(string $name, string $table): void
    {
        try {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
            $this->line("  - Da xoa index: {$name}");
        } catch (\Throwable) {}
    }
}
