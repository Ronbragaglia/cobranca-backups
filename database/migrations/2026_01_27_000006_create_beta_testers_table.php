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
        Schema::create('beta_testers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('company');
            $table->string('segment')->nullable();
            $table->enum('status', [
                'pending',
                'invited',
                'accepted',
                'active',
                'inactive'
            ])->default('pending');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->integer('discount_percentage')->default(50);
            $table->text('notes')->nullable();
            $table->integer('feedback_score')->nullable();
            $table->integer('referrals_count')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('email');
            $table->index('invited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beta_testers');
    }
};
