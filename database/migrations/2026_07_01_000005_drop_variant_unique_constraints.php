<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop unique constraints trên ma_hang và ma_vach của product_variants
        $this->dropUniqueConstraint('product_variants', 'product_variants_ma_vach_unique');
        $this->dropUniqueConstraint('product_variants', 'product_variants_ma_hang_unique');

        // Drop unique constraints trên ma_vach của product_units
        $this->dropUniqueConstraint('product_units', 'product_units_ma_vach_unique');
        $this->dropUniqueConstraint('product_units', 'product_units_ma_hang_unique');
    }

    public function down(): void
    {
        // Cannot restore unique constraints without recreating them
    }

    private function dropUniqueConstraint(string $table, string $constraintName): void
    {
        try {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$constraintName}`");
        } catch (\Throwable $e) {
            // Constraint might not exist, ignore
        }
    }
};
