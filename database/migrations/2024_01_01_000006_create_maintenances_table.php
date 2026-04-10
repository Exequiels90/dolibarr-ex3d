<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('machine_name');
            $table->decimal('total_print_hours', 8, 2)->default(0);
            $table->decimal('last_maintenance_hours', 8, 2)->default(0);
            $table->integer('maintenance_interval_hours')->default(100);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique('machine_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
