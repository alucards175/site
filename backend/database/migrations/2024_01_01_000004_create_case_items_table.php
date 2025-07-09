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
        Schema::create('case_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            
            // Drop Configuration
            $table->decimal('drop_chance', 8, 6); // Percentage (0.000001 to 99.999999)
            $table->decimal('min_float', 10, 8)->default(0.00000000);
            $table->decimal('max_float', 10, 8)->default(1.00000000);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['case_id']);
            $table->index(['item_id']);
            $table->index(['drop_chance']);
            
            // Unique constraint to prevent duplicate case-item pairs
            $table->unique(['case_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_items');
    }
};