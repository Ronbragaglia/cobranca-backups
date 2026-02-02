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
            $table->date('data_vencimento')->nullable()->after('status');
            $table->string('telefone')->nullable()->after('data_vencimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->dropColumn(['data_vencimento', 'telefone']);
        });
    }
};