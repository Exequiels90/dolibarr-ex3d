<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('additional_supplies', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->decimal('unit_cost', 8, 2);
            $table->timestamps();
            
            $table->unique('item_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_supplies');
    }
};
