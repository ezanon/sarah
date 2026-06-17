<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipamento_responsavel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipamento_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['equipamento_id', 'user_id']); // evita duplicatas
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipamento_responsavel');
    }
};