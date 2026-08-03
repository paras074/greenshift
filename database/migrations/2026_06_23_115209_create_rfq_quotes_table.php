<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('lead_id')->nullable(); // Nullable as requested
            $table->string('supplier_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('delivery_timeline')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('warranty')->nullable();
            $table->text('description')->nullable();
            $table->json('others')->nullable(); // Handled as JSON for the array data
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_quotes');
    }
};