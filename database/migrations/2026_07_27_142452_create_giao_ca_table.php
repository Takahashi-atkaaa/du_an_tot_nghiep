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
        Schema::create('giao_ca', function (Blueprint $table) {
            $table->id();

            // Trưởng ca bàn giao
            $table->foreignId('id_truong_ca_ban_giao')
                ->constrained('nguoi_dung')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Trưởng ca nhận ca
            $table->foreignId('id_truong_ca_nhan_ca')
                ->constrained('nguoi_dung')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Ca làm việc
            $table->foreignId('id_ca_lam_viec')
                ->constrained('ca_lam_viec')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Tiền
            $table->decimal('tien_mat_dau_ca', 15, 2)->default(0);
            $table->decimal('tien_mat_cuoi_ca', 15, 2)->default(0);
            $table->decimal('chenh_lech', 15, 2)->default(0);

            // Snapshot thời gian ca
            $table->dateTime('thoi_gian_bat_dau_ca');
            $table->dateTime('thoi_gian_ket_thuc_ca');

            // Trạng thái
            $table->tinyInteger('trang_thai')->default(0)
                ->comment('0: Chờ xác nhận, 1: Đã xác nhận');

            // Ghi chú
            $table->text('ghi_chu')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giao_ca');
    }
};
