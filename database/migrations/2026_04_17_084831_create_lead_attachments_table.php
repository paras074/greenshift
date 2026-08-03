<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_attachments', function (Blueprint $table) {
            $table->id();
            // Relationship to Lead
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            // Relationship to User (Uploader)
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            
            // File Details
            $table->string('file_path');      // Storage path
            $table->string('file_name');      // Original name
            $table->string('file_type');      // MIME type
            $table->unsignedBigInteger('file_size'); // Size in bytes
            
            // Text Details
            $table->text('description')->nullable();
            $table->text('others')->nullable(); // String based "others"
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_attachments');
    }
};