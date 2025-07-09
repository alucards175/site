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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('category')->default('general');
            
            // Visibility and Features
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            
            // Requirements and Limits
            $table->integer('min_level')->default(1);
            $table->integer('max_daily_openings')->default(0); // 0 = unlimited
            
            // Ordering and Statistics
            $table->integer('sort_order')->default(0);
            $table->integer('total_openings')->default(0);
            
            // Provably Fair and RTP
            $table->decimal('house_edge', 5, 4)->default(0.05); // 5%
            $table->decimal('rtp_percentage', 5, 2)->default(95.00); // Return to Player
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['is_active']);
            $table->index(['is_featured']);
            $table->index(['category']);
            $table->index(['price']);
            $table->index(['min_level']);
            $table->index(['sort_order']);
            $table->index(['total_openings']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};