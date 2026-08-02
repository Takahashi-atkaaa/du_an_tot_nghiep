<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE giao_dich MODIFY phuong_thuc ENUM('tien_mat','vnpay','payos')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE giao_dich MODIFY phuong_thuc ENUM('tien_mat','vnpay')");
    }
};
