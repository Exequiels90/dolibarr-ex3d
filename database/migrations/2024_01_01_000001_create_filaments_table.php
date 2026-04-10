<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filaments', function (Blueprint $table) {
            $table->id();
            $table->string('brand_type');
            $table->string('color');
            $table->decimal('cost_per_kg', 8, 2);
            $table->integer('spool_weight_g');
            $table->timestamps();
            
            $table->index(['brand_type', 'color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filaments');
    }
};
