<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('giao_ca', function (Blueprint $table) {
            $table->decimal('doanh_thu_chuyen_khoan', 15, 2)
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('giao_ca', function (Blueprint $table) {
            $table->dropColumn('doanh_thu_chuyen_khoan');
        });
    }
};
