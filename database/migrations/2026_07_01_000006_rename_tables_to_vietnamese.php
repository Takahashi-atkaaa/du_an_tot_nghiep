<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_variants') && !Schema::hasTable('bien_the_san_pham')) {
            DB::statement('RENAME TABLE `product_variants` TO `bien_the_san_pham`');
        }
        if (Schema::hasTable('product_units') && !Schema::hasTable('don_vi_quy_doi')) {
            DB::statement('RENAME TABLE `product_units` TO `don_vi_quy_doi`');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bien_the_san_pham') && !Schema::hasTable('product_variants')) {
            DB::statement('RENAME TABLE `bien_the_san_pham` TO `product_variants`');
        }
        if (Schema::hasTable('don_vi_quy_doi') && !Schema::hasTable('product_units')) {
            DB::statement('RENAME TABLE `don_vi_quy_doi` TO `product_units`');
        }
    }
};
