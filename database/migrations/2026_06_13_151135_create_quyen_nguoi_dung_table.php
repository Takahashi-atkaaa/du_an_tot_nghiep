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
        Schema::create('quyen_nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_nguoi_dung');
            $table->unsignedBigInteger('id_quyen');
            $table->timestamps();

            // Khóa ngoại
            $table->foreign('id_nguoi_dung')->references('id')->on('nguoi_dung')->onDelete('cascade');
            $table->foreign('id_quyen')->references('id')->on('quyen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quyen_nguoi_dung');
    }
};