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
        Schema::create('cobranca_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cobranca_id')->constrained('cobrancas')->onDelete('cascade');
            $table->string('tipo'); // email, whatsapp
            $table->string('status'); // enviado, falhou, simulado
            $table->timestamp('data');
            $table->text('erro')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobranca_envios');
    }
};