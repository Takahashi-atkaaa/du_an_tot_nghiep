<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('phieu_luong');
        Schema::dropIfExists('bang_luong');
        Schema::dropIfExists('thiet_lap_luong');
        Schema::dropIfExists('diem_danh');
    }

    public function down(): void
    {
    }
};