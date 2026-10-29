<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('relatorios_diretoria', function (Blueprint $table) {
            $table->id();
            $table->string('departamento');      // Sigla do departamento (ex: GSA, GFM)
            $table->integer('ano');              // Ano do relatório
            $table->string('caminho_arquivo');   // Caminho relativo em public/relatorios/diretoria/
            $table->timestamp('gerado_em');      // Data/hora de geração
            $table->unsignedBigInteger('user_id'); // Quem gerou
            $table->timestamps();

            $table->unique(['departamento', 'ano']); // Um relatório por depto/ano
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('relatorios_diretoria');
    }
};