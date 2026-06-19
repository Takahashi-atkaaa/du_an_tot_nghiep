<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->foreignId('san_pham_cha_id')
                ->nullable()
                ->after('trang_thai')
                ->constrained('san_pham')
                ->nullOnDelete();

            $table->boolean('la_san_pham_cha')
                ->default(false)
                ->after('san_pham_cha_id');

            $table->index('san_pham_cha_id');
        });
    }

    public function down(): void
    {
        Schema::table('san_pham', function (Blueprint $table) {
            $table->dropForeign(['san_pham_cha_id']);
            $table->dropIndex(['san_pham_cha_id']);
            $table->dropColumn(['san_pham_cha_id', 'la_san_pham_cha']);
        });
    }
};
