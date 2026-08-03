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
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key (AI)
            
            // Unique Dialpad Call Session ID to prevent duplicate entry rows across webhook hits
            $table->string('dialpad_call_id')->unique(); 
            
            // Context & Relationships
            $table->unsignedBigInteger('lead_id')->nullable()->index(); // Nullable Integer only
            $table->unsignedBigInteger('user_id')->nullable()->index(); // ID of the Laravel user/agent placing the call
            
            // Core Identity Parameters
            $table->string('contact_number')->nullable(); // Customer's phone number
            $table->string('direction')->nullable();      // inbound / outbound
            $table->string('state')->nullable();          // connected / hangup / admin_recording
            
            // Agent Context Fields
            $table->string('agent_name')->nullable();
            $table->string('agent_email')->nullable();

            // Explicit Call Timestamps (parsed into readable datetimes)
            $table->dateTime('call_started_at')->nullable();
            $table->dateTime('call_ended_at')->nullable();
            
            // Pure call duration in milliseconds straight from Dialpad
            $table->bigInteger('duration')->default(0); 
            
            // Local absolute path to your downloaded MP3 file
            $table->text('local_recording_path')->nullable();
            
            // Catch-all array tracking named exactly "others"
            $table->json('others')->nullable(); 
            
            // Laravel default timestamp columns (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};