<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bien_the_san_pham', function (Blueprint $table) {
            $table->boolean('la_don_vi')->nullable()->after('trang_thai');
            $table->string('ten_don_vi', 100)->nullable()->after('la_don_vi');
        });
    }

    public function down(): void
    {
        Schema::table('bien_the_san_pham', function (Blueprint $table) {
            $table->dropColumn(['la_don_vi', 'ten_don_vi']);
        });
    }
};
