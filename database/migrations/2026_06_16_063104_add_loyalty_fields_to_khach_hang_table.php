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
    Schema::table('khach_hang', function (Blueprint $table) {

        $table->integer('diem_tich_luy')
              ->default(0);

        $table->decimal('tong_chi_tieu', 14, 2)
              ->default(0);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            //
        });
    }
};
