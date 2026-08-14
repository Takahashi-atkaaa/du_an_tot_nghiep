<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hang_loi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_doi_tra')
                ->constrained('doi_tra')
                ->cascadeOnDelete();

            $table->foreignId('id_chi_tiet_doi_tra')
                ->constrained('chi_tiet_doi_tra')
                ->cascadeOnDelete();

            $table->foreignId('id_bien_the')
                ->constrained('bien_the_san_pham')
                ->restrictOnDelete();

            $table->integer('so_luong');

            $table->enum('trang_thai', ['cho_tieu_huy', 'da_tieu_huy'])
                ->default('cho_tieu_huy');

            $table->text('ly_do')->nullable();

            $table->dateTime('ngay_tieu_huy')->nullable();

            $table->foreignId('id_nguoi_dung_tieu_huy')
                ->nullable()
                ->constrained('nguoi_dung')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('id_doi_tra');
            $table->index('id_chi_tiet_doi_tra');
            $table->index('id_bien_the');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hang_loi');
    }
};
