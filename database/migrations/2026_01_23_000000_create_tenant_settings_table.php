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
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Configurações de lembretes de WhatsApp
            $table->json('whatsapp_reminder_days_before')->nullable()->comment('Dias antes do vencimento para enviar lembretes (ex: [3, 1])');
            $table->boolean('whatsapp_reminder_on_due_date')->default(true)->comment('Enviar lembrete no dia do vencimento');
            $table->json('whatsapp_reminder_days_after')->nullable()->comment('Dias após o vencimento para enviar lembretes (ex: [1, 3, 7])');
            $table->boolean('whatsapp_enabled')->default(true)->comment('Lembretes de WhatsApp habilitados');
            
            // Configurações de cobrança
            $table->string('default_currency', 3)->default('BRL')->comment('Moeda padrão');
            $table->integer('default_due_days')->default(7)->comment('Dias padrão de vencimento');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
