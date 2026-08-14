<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doi_tra', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_nguoi_dung')
                ->constrained('nguoi_dung')
                ->cascadeOnDelete();

            $table->foreignId('id_hoa_don')
                ->constrained('hoa_don')
                ->cascadeOnDelete();

            $table->enum('Loai', ['doi_tra', 'tra_hang'])
                ->default('doi_tra');

            $table->dateTime('ngay');

            $table->boolean('tru_diem_cua_khach')
                ->default(false);

            $table->text('ly_do')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('id_nguoi_dung');
            $table->index('id_hoa_don');
        });

        Schema::create('chi_tiet_doi_tra', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_doi_tra')
                ->constrained('doi_tra')
                ->cascadeOnDelete();

            $table->foreignId('id_bien_the')
                ->constrained('bien_the_san_pham')
                ->restrictOnDelete();

            $table->foreignId('id_bien_the_thay_the')
                ->nullable()
                ->constrained('bien_the_san_pham')
                ->nullOnDelete();

            $table->integer('so_luong');

            $table->decimal('gia_ban', 14, 2);

            $table->decimal('thanh_tien', 16, 2);

            $table->timestamps();
            $table->softDeletes();

            $table->index('id_doi_tra');
            $table->index('id_bien_the');
            $table->index('id_bien_the_thay_the');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_doi_tra');
        Schema::dropIfExists('doi_tra');
    }
};
