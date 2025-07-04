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
    Schema::create('requisition_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained();
        $table->unsignedInteger('quantity')->default(1);
        $table->decimal('price', 8, 2); // Price at time of request
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
    }
};
