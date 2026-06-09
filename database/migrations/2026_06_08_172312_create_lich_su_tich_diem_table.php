<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_su_tich_diem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khach_hang_id')->constrained('khach_hang')->cascadeOnDelete();
            $table->foreignId('hoa_don_id')->nullable()->constrained('hoa_don')->nullOnDelete();
            $table->enum('loai_giao_dich', ['cong', 'tru']);
            $table->integer('so_diem');
            $table->integer('so_diem_con_lai')->nullable();
            $table->text('mo_ta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_su_tich_diem');
    }
};
