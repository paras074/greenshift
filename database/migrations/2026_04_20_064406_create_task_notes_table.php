<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_notes', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('task_id')->nullable()->constrained('tasks')->onDelete('set null');
                  
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->longText('data');
            
            $table->unsignedBigInteger('mentioned_id')->nullable()->index();
            
            $table->text('others')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_notes');
    }
};