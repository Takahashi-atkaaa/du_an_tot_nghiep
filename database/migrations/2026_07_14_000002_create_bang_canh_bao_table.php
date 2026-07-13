<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bang_canh_bao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_audit_log')->nullable()->constrained('bang_audit_logs')->nullOnDelete();
            $table->foreignId('id_nguoi_dung_thuc_hien')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->string('tieu_de', 200);
            $table->text('noi_dung');
            $table->enum('muc_do', ['info', 'warning', 'danger'])->default('warning');
            $table->string('hanh_dong', 100)->nullable();
            $table->string('url_lien_ket', 500)->nullable();
            $table->boolean('da_doc')->default(false);
            $table->foreignId('id_nguoi_dung_da_doc')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->timestamp('thoi_gian_doc')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['da_doc', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bang_canh_bao');
    }
};