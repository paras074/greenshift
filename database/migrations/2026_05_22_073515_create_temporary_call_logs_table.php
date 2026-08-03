<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Double check the original table exists before trying to clone
        if (Schema::hasTable('call_logs')) {
            
            // For MySQL/MariaDB: The absolute cleanest way to replicate an exact schema clone (including indexes)
            DB::statement('CREATE TABLE temporary_call_logs LIKE call_logs');
            
        } else {
            throw new \Exception("Migration aborted: The source table 'call_logs' could not be found to replicate.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_call_logs');
    }
};