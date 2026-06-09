<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_hoa_don', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_hoa_don')->constrained('hoa_don')->cascadeOnDelete(); // FK → hoa_don.id, not null
            $table->foreignId('id_san_pham')->constrained('san_pham')->cascadeOnDelete(); // FK → san_pham.id, not null
            $table->integer('id_chi_tiet_phieu')->nullable(); // int, nullable, FK → chi_tiet_phieu.id
            $table->integer('so_luong'); // int, not null
            $table->decimal('gia_ban', 14, 2); // decimal(14,2), not null
            $table->decimal('thanh_tien', 16, 2); // decimal(16,2), not null
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_hoa_don');
    }
};
