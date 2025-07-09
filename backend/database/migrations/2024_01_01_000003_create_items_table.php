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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('market_price', 10, 2)->nullable();
            
            // Item Classification
            $table->enum('rarity', [
                'consumer', 'industrial', 'mil_spec', 'restricted', 
                'classified', 'covert', 'contraband', 'special'
            ])->default('consumer');
            $table->string('category')->default('weapon');
            $table->enum('exterior', [
                'factory_new', 'minimal_wear', 'field_tested', 
                'well_worn', 'battle_scarred'
            ])->nullable();
            
            // Float and Wear
            $table->decimal('float_min', 10, 8)->default(0.00000000);
            $table->decimal('float_max', 10, 8)->default(1.00000000);
            
            // Steam Integration
            $table->string('steam_market_name')->nullable();
            $table->string('steam_asset_id')->nullable();
            $table->text('inspect_link')->nullable();
            
            // Properties
            $table->boolean('is_active')->default(true);
            $table->boolean('is_tradeable')->default(true);
            $table->boolean('is_marketable')->default(true);
            
            // Additional Data
            $table->string('collection')->nullable();
            $table->decimal('wear_rating', 3, 2)->nullable();
            $table->integer('pattern_index')->nullable();
            $table->string('rarity_color', 7)->nullable(); // Hex color code
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['rarity']);
            $table->index(['category']);
            $table->index(['exterior']);
            $table->index(['price']);
            $table->index(['market_price']);
            $table->index(['is_active']);
            $table->index(['is_tradeable']);
            $table->index(['is_marketable']);
            $table->index(['steam_asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};