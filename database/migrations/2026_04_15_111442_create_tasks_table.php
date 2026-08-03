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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('type'); 
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->text('assign_to')->nullable();
            $table->foreignId('assign_by')->constrained('users')->onDelete('cascade');
            $table->date('end_date')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('pending');
            $table->longText('others')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
