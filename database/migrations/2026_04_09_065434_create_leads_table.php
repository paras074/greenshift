<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('annual_consumption')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('roof_site_type')->nullable();
            $table->string('decision_maker_name')->nullable();
            $table->string('mpan')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postcode', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('current_supplier')->nullable();
            $table->string('energy_type')->nullable();
            $table->foreignId('lead_status_id')->nullable()->constrained('lead_statuses')->nullOnDelete();
            $table->date('contract_end_date')->nullable();
            $table->foreignId('priority_status_id')->nullable()->constrained('priority_statuses')->nullOnDelete();
            $table->string('status')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};