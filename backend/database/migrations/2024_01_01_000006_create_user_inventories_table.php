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
        Schema::create('user_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('case_opening_id')->nullable()->constrained('case_openings')->onDelete('set null');
            
            // Item Instance Data
            $table->decimal('float_value', 10, 8)->nullable();
            $table->integer('pattern_index')->nullable();
            $table->string('inspect_link')->nullable();
            
            // Status
            $table->enum('status', ['active', 'withdrawn', 'sold', 'traded'])->default('active');
            $table->timestamp('acquired_at');
            $table->timestamp('withdrawn_at')->nullable();
            
            // Withdrawal Data
            $table->string('trade_offer_id')->nullable();
            $table->string('steam_asset_id')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['item_id']);
            $table->index(['case_opening_id']);
            $table->index(['status']);
            $table->index(['acquired_at']);
            $table->index(['trade_offer_id']);
            
            // Composite indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'acquired_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_inventories');
    }
};