<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letter of Authority - Green Shift Energy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000000;
            background: #ffffff;
            padding: 30px 40px;
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 120px;
        }

        .logo-text {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-text .brand-name {
            font-size: 22pt;
            font-weight: bold;
            color: #2d6a2d;
            letter-spacing: 1px;
        }

        .logo-text .brand-sub {
            font-size: 7pt;
            letter-spacing: 4px;
            color: #555;
            display: block;
        }

        /* Title */
        h1.doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table td {
            border: 1px solid #000;
            padding: 7px 10px;
            vertical-align: top;
            font-size: 10pt;
        }

        table td.label {
            font-weight: bold;
            width: 38%;
            background-color: #ffffff;
        }

        table td.value {
            width: 62%;
        }

        .section-header td {
            font-weight: bold;
            font-size: 10.5pt;
            background-color: #ffffff;
            border: 1px solid #000;
            padding: 8px 10px;
        }

        /* Intro text */
        .intro-text {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .sub-text {
            margin-bottom: 12px;
            font-size: 10pt;
        }

        /* Bullet list */
        ul.auth-list {
            margin: 8px 0 8px 20px;
            font-size: 10pt;
        }

        ul.auth-list li {
            margin-bottom: 5px;
        }

        /* Body paragraphs */
        .body-para {
            font-size: 10pt;
            margin-bottom: 0px;
            line-height: 1.5;
        }

        .body-para-bold {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 6px;
        }

        /* Signature section */
        .signature-section {
            margin-top: 0px;
            font-size: 10pt;
            line-height: 2.2;
        }

        .sig-line {
            display: inline-block;
            width: 55%;
            border-bottom: 1px solid #000;
            margin-right: 5px;
        }

        .sig-row {
            margin-bottom: 8px;
        }

        /* Logo SVG inline fallback */
        .logo-svg {
            text-align: center;
            margin-bottom: 18px;
        }
        .d-flex {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
        <td align="center" style="border:none; text-align: center;">
            <span class="logo">
                @php 
                    $imageUrl = asset('images/site-logo.png'); 
                    $imageData = file_get_contents($imageUrl); 
                    $base64 = base64_encode($imageData); 
                @endphp
                <img src="data:image/png;base64,{{ $base64 }}" alt="Greenshift Energy Consulting Logo" style="width: 150px; display: inline-block;">
            </span>
        </td>
    </tr>
</table>

    <!-- TITLE -->
    <h1 class="doc-title">LETTER OF AUTHORITY</h1>

    <!-- CUSTOMER DETAILS TABLE -->
    <table>
        <tr class="section-header">
            <td colspan="2"><strong>CUSTOMER DETAILS</strong></td>
        </tr>
        <tr>
            <td class="label">Name of Customer:</td>
            <td class="value">
                <span>{{ $lead->company_name }}</span>
                <span style="float:right;font-style:italic;">
                    ('I'/'we'/'us')
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">Registered no:</td>
            <td class="value">{{ $lead->reg_number }}</td>
        </tr>
        <tr>
            <td class="label">Business address:</td>
            <td class="value" style="height:40px;">{{ implode(', ', array_filter([$lead->address, $lead->city, $lead->state, $lead->postcode])) }}</td>
        </tr>
        <tr>
            <td class="label">Postcode:</td>
            <td class="value">{{ $lead->postcode }}</td>
        </tr>
        <tr>
            <td class="label">Telephone no:</td>
            <td class="value">{{ $lead->phone }}</td>
        </tr>
        <tr>
            <td class="label">Authorised representative:</td>
            <td class="value">{{ $lead->decision_maker_name }}</td>
        </tr>
        <tr>
            <td class="label">Email Address:</td>
            <td class="value">{{ $lead->email }}</td>
        </tr>
        <tr>
            <td class="label">@if($lead->energy_type == 'electricity') MPAN: @elseif($lead->energy_type == 'gas') MPR: @endif</td>
            <td class="value">{{ $lead->mpan }}</td>
        </tr>
        <tr>
            <td class="label">Site address(es):</td>
            <td class="value" style="line-height: 1.6; padding-top: 10px; padding-bottom: 10px;">
                @if(!empty($lead->others['address']) && is_array($lead->others['address']))
                @php $isFirstOutput = true; @endphp
                
                @foreach($lead->others['address'] as $extraAddress)
                    @php
                        // Filter out empty inputs to prevent consecutive or hanging commas
                        $extraFields = array_filter([
                            trim($extraAddress['address'] ?? ''),
                            trim($extraAddress['city'] ?? ''),
                            trim($extraAddress['state'] ?? ''),
                            trim($extraAddress['postcode'] ?? '')
                        ]);
                    @endphp
            
                    {{-- Only output if the variation contains actual text --}}
                    @if(!empty($extraFields))
                        {{-- Avoid rendering an initial leading <br /> if this is the very first line printed --}}
                        @if(!$isFirstOutput)
                            <br />
                        @endif
                        
                        {{ implode(', ', $extraFields) }}
                        
                        @php $isFirstOutput = false; @endphp
                    @endif
                @endforeach
            @endif
            </td>
        </tr>
    </table>

    <!-- INTRO -->
    <p class="intro-text">To whom it may concern:</p>
    <p class="sub-text">I/we have appointed:</p>

    <!-- BROKER DETAILS TABLE -->
    <table>
        <tr class="section-header">
            <td colspan="2"><strong>BROKER DETAILS</strong></td>
        </tr>
        <tr>
            <td class="label">Name:</td>
            <td class="value"><strong>GREEN SHIFT ENERGY LIMITED</strong>&nbsp;&nbsp;&nbsp;<em>('Broker')</em></td>
        </tr>
        <tr>
            <td class="label">Registered no:</td>
            <td class="value">15908207</td>
        </tr>
        <tr>
            <td class="label">Business address:</td>
            <td class="value">128 City Road, London, United Kingdom</td>
        </tr>
        <tr>
            <td class="label">Postcode:</td>
            <td class="value">EC1V 2NX</td>
        </tr>
        <tr>
            <td class="label">Telephone no:</td>
            <td class="value">0330 818 7978</td>
        </tr>
    </table>

    <!-- PAGE 2 CONTENT -->
    <p class="body-para">to act on our behalf in relation to the supply of electricity, gas and/or water.</p>

    <p class="body-para">We hereby authorise the Broker and its appointed representatives to carry out the activities listed below on our behalf. We understand that the TPI may use the services of the following agents to assist in carrying out these activities:</p>

    <ul class="auth-list">
        <li>OnlineDirect Limited, 300 Pavilion Drive, Brackmills, Northants, NN4 7YE (Company no: 03599738)</li>
        <li>UD Software Solutions Group Ltd t/a Powwr, Parkway House | Palatine Rd | Manchester M22 4DB, (Company No: 06904669)</li>
    </ul>

    <p class="body-para">Access industry held data including consumptions, contract end dates, metering information, issue termination notices should the need arise and opt out of future contract renewals on our behalf.</p>

    <p class="body-para">Contact our current supplier to resolve any issues arising, therefore they can request all billing information and authorise any adjustments, refunds, or billing amendments.</p>

    <p class="body-para">Raise and deal with complaints on our behalf to a satisfactory resolution (The supplier will notify the customer if a complaint is raised on the account and confirm when this has been resolved)</p>

    <p class="body-para">By signing the authority letter you understand the services provided by Green Shift Energy Limited are paid for via a fee directly if agreed or via an uplift on the unit rate and if the fee is via an uplift this will be collected by the supplier via the customer's supply bill and paid to us.</p>

    <p class="body-para-bold">This Letter of Authority does not give the Broker authority to sign a contract on our behalf. We agree and accept that:</p>

    <ul class="auth-list">
        <li>the Broker is acting as an introducer for energy suppliers and is not acting as our agent.</li>
        <li>the Broker can process any contract agreed/signed by us on our behalf.</li>
        <li>a credit check may be carried out against us (and/or our directors/partners/owners).</li>
    </ul>

    <p class="body-para">We confirm that we have authorised the Broker to use our customer data for the purposes of delivering services to us and to share that data with selected relevant third parties in order to do so.</p>

    <p class="body-para">This letter of authority is effective from the date of signature and remains valid for a period of 12 months from such date.</p>

    <p class="body-para">This letter of authority supersedes all previous letters of authority.</p>

    <p class="body-para">In signing this letter of authority we agree that the terms and conditions of Green Shift Energy Limited apply to the services to be provided and that a copy of those terms and conditions is available at <strong><a href="https://greenshiftenergy.co.uk/terms-and-conditions/" 
   style="color: #5693b1;" 
   target="_blank" 
   rel="noopener noreferrer">
   https://greenshiftenergy.co.uk/terms-and-conditions/
</a></strong></p>

    <!-- SIGNATURE SECTION -->
    <div class="signature-section">
        <table style="border:none; width:100%; margin-bottom:0;margin: 0;">
            <tr>
                <td style="border:none; padding:0 0 6px 0; width:40%;">
                    <table cellpadding="0" cellspacing="0" border="0" style="width:85%;margin: 0;border-collapse:collapse; border:0;">
                        <tr>
                            <td style="width: 61px;white-space:nowrap; padding:0px; vertical-align:bottom; font-family:Arial,sans-serif; font-size:14px; border:0;">Signed:</td>
                            <td style="height:100px; vertical-align:middle; text-align:left; color:#ffffff; font-size:13px; line-height:3em;font-family:Arial,sans-serif; border:0; border-bottom:1px solid #000; padding-right:20px;">
                                {signature:signer1:Sign+Here}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border:none; padding:0 0 6px 0; width:50%; vertical-align:bottom;">
                    PRINT NAME:&nbsp;<span style="display:inline-block; width:55%; border-bottom:1px solid #000; vertical-align:bottom; padding-bottom:2px; text-indent:5px;">{{ $lead->decision_maker_name }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border:none; padding:4px 0; font-size:9.5pt; font-style:italic;">
                    For and on behalf of the Customer
                </td>
            </tr>
            <tr>
                <td style="border:none; padding:6px 0; width:50%;white-space: nowrap;">
                    Position:&nbsp;<span style="display:inline-block; width:75%; border-bottom:1px solid #000;vertical-align: bottom; padding-bottom: 2px; text-indent: 5px;">
                        @if(isset($lead) && filled(data_get($lead, 'others.decision_maker_designation')))
                            {{ data_get($lead, 'others.decision_maker_designation') }}
                        @endif
                    </span>
                </td>
                <td style="border:none; padding:6px 0; width:50%;">
                    Email address:&nbsp;<span style="display:inline-block; width:60%; border-bottom:1px solid #000; vertical-align: bottom; padding-bottom: 2px; text-indent: 5px;">{{ $lead->email }}</span>
                </td>
            </tr>
            <tr>
                <td style="border:none; padding:6px 0; width:50%; white-space: nowrap;">
                    Dated:&nbsp;<span style="display: inline-block; width: 75%; border-bottom: 1px solid #000; vertical-align: bottom; padding-bottom: 2px; text-indent: 5px;"><?php echo date('d F Y'); ?></span>
                </td>
                <td style="border:none; padding:6px 0;">&nbsp;</td>
            </tr>
        </table>
    </div>

</body>
</html>
