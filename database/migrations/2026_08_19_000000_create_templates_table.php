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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('loa');   // loa | email
            $table->string('subject')->nullable();     // used for email templates
            $table->longText('content')->nullable();   // the editable HTML body
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Seed the default LOA template with the current letter body so
        // generation keeps working exactly as before, but now editable.
        $defaultLoaContent = <<<'HTML'
<p>to act on our behalf in relation to the supply of electricity, gas and/or water.</p>
<p>We hereby authorise the Broker and its appointed representatives to carry out the activities listed below on our behalf. We understand that the TPI may use the services of the following agents to assist in carrying out these activities:</p>
<ul>
<li>OnlineDirect Limited, 300 Pavilion Drive, Brackmills, Northants, NN4 7YE (Company no: 03599738)</li>
<li>UD Software Solutions Group Ltd t/a Powwr, Parkway House | Palatine Rd | Manchester M22 4DB, (Company No: 06904669)</li>
</ul>
<p>Access industry held data including consumptions, contract end dates, metering information, issue termination notices should the need arise and opt out of future contract renewals on our behalf.</p>
<p>Contact our current supplier to resolve any issues arising, therefore they can request all billing information and authorise any adjustments, refunds, or billing amendments.</p>
<p>Raise and deal with complaints on our behalf to a satisfactory resolution (The supplier will notify the customer if a complaint is raised on the account and confirm when this has been resolved)</p>
<p>By signing the authority letter you understand the services provided by Green Shift Energy Limited are paid for via a fee directly if agreed or via an uplift on the unit rate and if the fee is via an uplift this will be collected by the supplier via the customer's supply bill and paid to us.</p>
<p><strong>This Letter of Authority does not give the Broker authority to sign a contract on our behalf. We agree and accept that:</strong></p>
<ul>
<li>the Broker is acting as an introducer for energy suppliers and is not acting as our agent.</li>
<li>the Broker can process any contract agreed/signed by us on our behalf.</li>
<li>a credit check may be carried out against us (and/or our directors/partners/owners).</li>
</ul>
<p>We confirm that we have authorised the Broker to use our customer data for the purposes of delivering services to us and to share that data with selected relevant third parties in order to do so.</p>
<p>This letter of authority is effective from the date of signature and remains valid for a period of 12 months from such date.</p>
<p>This letter of authority supersedes all previous letters of authority.</p>
<p>In signing this letter of authority we agree that the terms and conditions of Green Shift Energy Limited apply to the services to be provided and that a copy of those terms and conditions is available at <strong><a href="https://greenshiftenergy.co.uk/terms-and-conditions/">https://greenshiftenergy.co.uk/terms-and-conditions/</a></strong></p>
HTML;

        DB::table('templates')->insert([
            'name'       => 'Default LOA',
            'type'       => 'loa',
            'subject'    => null,
            'content'    => $defaultLoaContent,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
