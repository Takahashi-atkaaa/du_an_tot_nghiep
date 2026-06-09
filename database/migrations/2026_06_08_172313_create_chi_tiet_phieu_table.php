<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_phieu', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->foreignId('id_phieu')->constrained('phieu')->cascadeOnDelete(); // FK → phieu.id, not null
            $table->foreignId('id_san_pham')->constrained('san_pham')->cascadeOnDelete(); // FK → san_pham.id, not null
            $table->integer('so_luong'); // int, not null
            $table->decimal('gia_nhap', 14, 2); // decimal(14,2), not null
            $table->string('ma_lo')->nullable(); // varchar(255), nullable
            $table->date('han_su_dung')->nullable(); // date, nullable
            $table->integer('so_luong_con_lai')->nullable(); // int, nullable
            $table->text('ghi_chu')->nullable(); // text, nullable
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_phieu');
    }
};
