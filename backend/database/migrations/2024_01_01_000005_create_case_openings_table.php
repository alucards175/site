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
        Schema::create('case_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            
            // Financial Data
            $table->decimal('case_price', 8, 2);
            $table->decimal('item_value', 10, 2);
            $table->decimal('profit', 10, 2); // item_value - case_price
            
            // Provably Fair Data
            $table->string('server_seed', 128);
            $table->string('client_seed', 128);
            $table->integer('nonce');
            $table->string('hash', 64); // SHA-256 hash
            
            // Item Details
            $table->decimal('float_value', 10, 8)->nullable();
            
            // Special Flags
            $table->boolean('is_jackpot')->default(false);
            
            // Tracking Data
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['case_id']);
            $table->index(['item_id']);
            $table->index(['created_at']);
            $table->index(['profit']);
            $table->index(['is_jackpot']);
            $table->index(['session_id']);
            
            // Composite indexes for analytics
            $table->index(['user_id', 'created_at']);
            $table->index(['case_id', 'created_at']);
            $table->index(['item_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_openings');
    }
};