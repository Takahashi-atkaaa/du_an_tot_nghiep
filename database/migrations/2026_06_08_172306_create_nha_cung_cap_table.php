<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nha_cung_cap', function (Blueprint $table) {
            $table->id(); // int unsigned, khóa chính tự động tăng (AI)
            $table->string('ten_nha_cung_cap'); // varchar(255), not null
            $table->string('email')->nullable(); // varchar(255), nullable
            $table->string('so_dien_thoai'); // varchar(255), not null
            $table->string('nguoi_dai_dien')->nullable(); // varchar(255), nullable
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at, hỗ trợ xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nha_cung_cap');
    }
};
