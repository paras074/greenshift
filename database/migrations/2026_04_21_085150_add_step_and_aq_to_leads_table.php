<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Foreign Key for lead_steps table
            $table->foreignId('lead_step_id')
                  ->after('energy_type') // Places it logically in the table
                  ->nullable()
                  ->constrained('lead_steps')
                  ->nullOnDelete();

            // New AQ column (varchar)
            $table->string('aq')->nullable()->after('lead_step_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop foreign key first, then columns
            $table->dropForeign(['lead_step_id']);
            $table->dropColumn(['lead_step_id', 'aq']);
        });
    }
};