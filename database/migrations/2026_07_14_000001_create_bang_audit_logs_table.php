<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bang_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->string('hanh_dong', 100);
            $table->string('bang_bi_tac_dong', 100)->nullable();
            $table->unsignedBigInteger('id_ban_ghi')->nullable();
            $table->string('mo_ta', 500);
            $table->json('du_lieu_cu')->nullable();
            $table->json('du_lieu_moi')->nullable();
            $table->enum('muc_do', ['info', 'warning', 'danger'])->default('info');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['hanh_dong', 'created_at']);
            $table->index(['muc_do', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bang_audit_logs');
    }
};