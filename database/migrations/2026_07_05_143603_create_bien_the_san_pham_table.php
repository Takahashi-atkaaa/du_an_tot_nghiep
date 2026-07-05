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
        Schema::create('bien_the_san_pham', function (Blueprint $table) {
            $table->id();

            // Tên biến thể (Lon, Chai, Thùng...)
            $table->string('ten_bien_the');

            // Trạng thái
            $table->boolean('trang_thai')->default(true);

            // Khóa ngoại đến bảng sản phẩm
            $table->foreignId('id_san_pham')
                ->constrained('san_pham')
                ->cascadeOnDelete();

            // Hệ số quy đổi
            $table->integer('he_so_quy_doi')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bien_the_san_pham');
    }
};