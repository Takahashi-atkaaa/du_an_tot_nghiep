<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoa_don_khuyen_mai', function (Blueprint $table) {

            $table->id();

            // Hóa đơn sử dụng khuyến mãi
            $table->unsignedBigInteger('id_hoa_don');

            // Khuyến mãi đã được áp dụng
            $table->unsignedBigInteger('id_khuyen_mai');

            // Số tiền thực tế đã giảm bởi KM này
            $table->decimal('tien_giam', 15, 2)->default(0);

            // san_pham hoặc hoa_don
            $table->string('loai_ap_dung', 30);

            $table->timestamps();

            $table->foreign('id_hoa_don')
                ->references('id')
                ->on('hoa_don')
                ->cascadeOnDelete();

            $table->foreign('id_khuyen_mai')
                ->references('id')
                ->on('khuyen_mai')
                ->cascadeOnDelete();

            /*
             * Một khuyến mãi chỉ ghi 1 lần
             * trong cùng một hóa đơn.
             */
            $table->unique(
                ['id_hoa_don', 'id_khuyen_mai'],
                'uq_hoa_don_khuyen_mai'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoa_don_khuyen_mai');
    }
};