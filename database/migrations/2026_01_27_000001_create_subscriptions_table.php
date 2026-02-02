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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            
            // Stripe
            $table->string('stripe_subscription_id')->unique()->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_status')->nullable();
            
            // Status da assinatura
            $table->enum('status', ['active', 'past_due', 'canceled', 'incomplete', 'incomplete_expired', 'trialing', 'unpaid'])->default('incomplete');
            
            // Datas
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            
            // Uso atual
            $table->integer('current_messages_month')->default(0);
            $table->integer('current_whatsapp_instances')->default(0);
            $table->timestamp('usage_reset_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('stripe_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
