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
    Schema::create('quyen_vai_tro', function (Blueprint $table) {
        $table->id();

        $table->foreignId('id_vai_tro')
            ->constrained('vai_tro')
            ->cascadeOnDelete();

        $table->foreignId('id_quyen')
            ->constrained('quyen')
            ->cascadeOnDelete();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quyen_vai_tro');
    }
};
