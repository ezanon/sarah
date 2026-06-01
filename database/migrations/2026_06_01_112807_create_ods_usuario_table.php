<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ods_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('ods_id'); // 1 a 17
            $table->timestamps();
            $table->unique(['user_id', 'ods_id']); // evita duplicação
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ods_usuario');
    }
};