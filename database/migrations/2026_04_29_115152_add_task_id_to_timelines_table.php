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
        Schema::table('timelines', function (Blueprint $table) {
            // We use 'after' to place it specifically after lead_id
            $table->foreignId('task_id')
                ->after('lead_id') 
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timelines', function (Blueprint $table) {
            // Drop the foreign key first, then the column
            $table->dropForeign(['task_id']);
            $table->dropColumn('task_id');
        });
    }
};