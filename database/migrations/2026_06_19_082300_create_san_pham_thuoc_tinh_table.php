<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('san_pham_thuoc_tinh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')
                ->constrained('san_pham')
                ->cascadeOnDelete();
            $table->foreignId('id_thuoc_tinh')
                ->constrained('thuoc_tinh_san_pham')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['id_san_pham', 'id_thuoc_tinh']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('san_pham_thuoc_tinh');
    }
};
