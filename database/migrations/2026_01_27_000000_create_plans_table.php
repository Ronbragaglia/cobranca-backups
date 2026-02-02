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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('BRL');
            $table->integer('interval')->default(1)->comment('Intervalo em meses');
            $table->string('stripe_price_id')->nullable();
            
            // Limites do plano
            $table->integer('max_whatsapp_instances')->default(1);
            $table->integer('max_messages_per_month')->default(500);
            $table->boolean('unlimited_messages')->default(false);
            $table->boolean('api_access')->default(true);
            $table->boolean('custom_qr')->default(false);
            $table->boolean('analytics')->default(false);
            $table->boolean('priority_support')->default(false);
            
            // Features JSON
            $table->json('features')->nullable();
            
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
