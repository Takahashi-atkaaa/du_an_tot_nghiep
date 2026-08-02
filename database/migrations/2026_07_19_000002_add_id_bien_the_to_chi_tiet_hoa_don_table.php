<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chi_tiet_hoa_don', function (Blueprint $table) {
            // Lưu id biến thể cụ thể đã bán để có thể trừ đúng tồn kho khi IPN VNPay
            // xác nhận thanh toán (không phải chỉ id_san_pham cha).
            $table->unsignedBigInteger('id_bien_the')
                ->nullable()
                ->after('id_san_pham');

            $table->index('id_bien_the');
        });
    }

    public function down(): void
    {
        Schema::table('chi_tiet_hoa_don', function (Blueprint $table) {
            $table->dropIndex(['id_bien_the']);
            $table->dropColumn('id_bien_the');
        });
    }
};
