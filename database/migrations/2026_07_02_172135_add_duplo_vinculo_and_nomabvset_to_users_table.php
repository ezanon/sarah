<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('duplo_vinculo')->nullable()->after('nivel_cnpq');
            $table->string('nomabvset')->nullable()->after('duplo_vinculo'); // Código do departamento
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['duplo_vinculo', 'nomabvset']);
        });
    }
};