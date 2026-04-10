<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_queues', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('agreed_price', 10, 2);
            $table->date('delivery_date');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_queues');
    }
};
