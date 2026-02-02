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
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->string('notificacao_whatsapp_status')->nullable()->default(null);
            $table->timestamp('ultimo_envio_whatsapp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->dropColumn(['notificacao_whatsapp_status', 'ultimo_envio_whatsapp']);
        });
    }
};