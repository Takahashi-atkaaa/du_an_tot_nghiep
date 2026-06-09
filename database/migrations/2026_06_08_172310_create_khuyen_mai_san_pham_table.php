<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khuyen_mai_san_pham', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_khuyen_mai')->constrained('khuyen_mai')->cascadeOnDelete(); // FK → khuyen_mai.id
            $table->foreignId('id_san_pham')->constrained('san_pham')->cascadeOnDelete(); // FK → san_pham.id
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khuyen_mai_san_pham');
    }
};
