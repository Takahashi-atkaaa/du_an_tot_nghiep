<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('don_vi_quy_doi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('bien_the_san_pham')->cascadeOnDelete();
            $table->string('ten_don_vi')->comment('VD: "Thùng", "Hộp 10 cái"');
            $table->integer('ty_le_quy_doi')->default(1)->comment('24 lon/thùng, 10 cái/hộp');
            $table->string('ma_hang')->nullable();
            $table->string('ma_vach')->nullable();
            $table->decimal('gia_von_quy_doi', 14, 2)->default(0);
            $table->decimal('gia_ban_quy_doi', 14, 2)->default(0);
            $table->decimal('gia_ban_si', 14, 2)->nullable();
            $table->string('hinh_anh')->nullable();
            $table->boolean('la_don_vi_mac_dinh')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_vi_quy_doi');
    }
};
