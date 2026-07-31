<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thanh_toan_hoa_don', function (Blueprint $table) {
    $table->id();

    $table->foreignId('id_hoa_don')
        ->constrained('hoa_don')
        ->cascadeOnDelete();

    $table->string('phuong_thuc', 30);

    $table->decimal('so_tien', 15, 2);

    $table->string('trang_thai', 30)
        ->default('da_thanh_toan');

    $table->string('ma_giao_dich', 100)
        ->nullable();

    $table->text('ghi_chu')
        ->nullable();

    $table->timestamp('thanh_toan_luc')
        ->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thanh_toan_hoa_dons');
    }
    
};
