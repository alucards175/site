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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            
            // Steam Integration
            $table->string('steamid')->unique();
            $table->string('steam_profile_url')->nullable();
            $table->string('steam_trade_url')->nullable();
            
            // Balance and Statistics
            $table->decimal('balance', 10, 2)->default(0);
            $table->integer('level')->default(1);
            $table->integer('experience')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->decimal('total_won', 12, 2)->default(0);
            
            // Referral System
            $table->unsignedBigInteger('referrer_id')->nullable();
            $table->string('referral_code', 8)->unique();
            
            // Moderation
            $table->boolean('is_banned')->default(false);
            $table->text('ban_reason')->nullable();
            
            // Activity Tracking
            $table->timestamp('last_activity')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('timezone')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes
            $table->index(['steamid']);
            $table->index(['referral_code']);
            $table->index(['referrer_id']);
            $table->index(['level']);
            $table->index(['is_banned']);
            $table->index(['last_activity']);
            
            // Foreign keys
            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};