<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratorio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // criador
            $table->string('nome'); // nome do equipamento
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->integer('ano_aquisicao')->nullable();
            $table->integer('ano_incorporacao')->nullable();
            $table->string('financiamento')->nullable();
            $table->string('cod_processo_convenio')->nullable();
            $table->string('patrimonio')->nullable()->unique(); // número de patrimônio
            $table->decimal('valor', 12, 2)->nullable();
            $table->string('cod_processo_incorporacao')->nullable();
            $table->string('foto')->nullable(); // caminho da foto
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipamentos');
    }
};