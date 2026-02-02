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
        Schema::table('tenants', function (Blueprint $table) {
            // Campos para Evolution API
            $table->string('evolution_api_key')->nullable();
            $table->string('evolution_api_url')->nullable();
            $table->json('evolution_instances')->nullable()->comment('Instâncias do Evolution API');
            
            // Configurações de QR
            $table->string('qr_code_image')->nullable();
            $table->boolean('custom_qr_enabled')->default(false);
            
            // Limites de uso
            $table->integer('max_whatsapp_instances')->default(1);
            $table->integer('max_messages_per_month')->default(500);
            $table->integer('current_messages_month')->default(0);
            $table->timestamp('usage_reset_at')->nullable();
            
            // Status
            $table->boolean('active')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'evolution_api_key',
                'evolution_api_url',
                'evolution_instances',
                'qr_code_image',
                'custom_qr_enabled',
                'max_whatsapp_instances',
                'max_messages_per_month',
                'current_messages_month',
                'usage_reset_at',
                'active',
                'trial_ends_at',
            ]);
        });
    }
};
