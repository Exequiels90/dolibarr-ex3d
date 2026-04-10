<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('filament_id')->constrained()->onDelete('cascade');
            $table->decimal('total_grams', 8, 2);
            $table->decimal('printing_time_hours', 5, 2);
            $table->decimal('post_processing_cost', 8, 2)->default(0);
            $table->decimal('safety_margin_percentage', 5, 2)->default(10);
            $table->timestamps();
            
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
