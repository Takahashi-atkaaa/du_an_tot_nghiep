<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khuyen_mai_san_pham', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khuyen_mai_id')->constrained('khuyen_mai')->cascadeOnDelete();
            $table->foreignId('san_pham_id')->constrained('san_pham')->cascadeOnDelete();
            $table->unique(['khuyen_mai_id', 'san_pham_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khuyen_mai_san_pham');
    }
};
