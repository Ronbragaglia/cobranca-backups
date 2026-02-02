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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('name');
            $table->text('description')->nullable();
            
            // Tipo de template
            $table->enum('type', ['cobranca', 'lembrete', 'agradecimento', 'custom'])->default('custom');
            
            // Conteúdo do template
            $table->text('content');
            $table->json('variables')->nullable()->comment('Variáveis disponíveis no template');
            
            // Configurações
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            
            // Estatísticas
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
