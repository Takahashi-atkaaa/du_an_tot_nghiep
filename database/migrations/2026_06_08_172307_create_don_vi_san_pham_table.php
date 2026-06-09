<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('don_vi_san_pham', function (Blueprint $table) {
            $table->id();
            $table->string('ten_don_vi');
            $table->string('ky_hieu')->unique();
            $table->decimal('ty_le_quy_doi', 12, 3)->default(1);
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_vi_san_pham');
    }
};
