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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Informações da ação
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            
            // Detalhes da requisição
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('method')->nullable();
            $table->string('url')->nullable();
            
            // Dados alterados
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Metadados
            $table->json('metadata')->nullable();
            
            // Resultado
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['tenant_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
