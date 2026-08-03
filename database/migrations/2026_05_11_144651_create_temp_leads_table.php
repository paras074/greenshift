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
        Schema::create('temp_leads', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('google_place_id', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('annual_consumption')->nullable();
            $table->text('total_annual_consumption')->nullable();
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
            $table->integer('lead_status_id')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->integer('priority_status_id')->nullable();
            $table->string('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('lead_gathering_from', 50)->nullable();
            $table->longText('others')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_leads');
    }
};
