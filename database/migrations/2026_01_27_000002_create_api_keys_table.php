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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('name');
            $table->string('key')->unique();
            $table->string('prefix', 10)->index();
            
            // Permissões
            $table->json('abilities')->nullable();
            
            // Limites de uso
            $table->integer('rate_limit_per_minute')->default(60);
            $table->integer('rate_limit_per_hour')->default(1000);
            
            // Estatísticas
            $table->integer('total_requests')->default(0);
            $table->timestamp('last_used_at')->nullable();
            
            // Status
            $table->boolean('active')->default(true);
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'active']);
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
