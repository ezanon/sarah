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
        Schema::create('links_academicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plataforma', 50); // lattes, orcid, google_scholar, etc.
            $table->string('identificador', 255); // código que o usuário digita
            $table->timestamps();

            $table->unique(['user_id', 'plataforma']); // 1 link por plataforma por usuário
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links_academicos');
    }
};
