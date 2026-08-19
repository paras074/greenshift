@extends('layouts.app')
@section('title', isset($lead) ? 'Edit Lead — ' . $lead->company_name : 'Add New Lead')
@section('content')

    {{-- Determine form action & method --}}
    @php
        $isEdit = isset($lead) && $lead->exists;
        $formAction = $isEdit ? route('leads.update', $lead) : route('leads.store');

        $get_all_lead_status = get_all_lead_status();
        $get_all_priority_status = get_all_priority_status();
        $salesManagers = GetAllUsersByRoleId(4);
        $salesexecutives = GetAllUsersByRoleId(5);
    @endphp

    <style>
        .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid #fff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            display: inline-block;
            animation: spin 0.8s linear infinite;
            margin-right: 6px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
</style>
    
    <form method="POST" action="{{ $formAction }}" class="lead-form cs-form" id="leadForm">
        @csrf

        {{-- ── Top Bar ─────────────────────────────────────────────── --}}
        <div class="gs-page-topbar">
            <div class="gs-page-topbar-left">
				<div class="page-title-bar">
					<h2>{{ $isEdit ? 'Edit Lead' : 'Add New Lead' }}</h2>
					@if($isEdit && !empty($lead->status) && $lead->status == 'draft')
						<span class="status-{{ $lead->status }}">
							{{ ucfirst($lead->status) }}
						</span>
					@endif
				</div>
                <p>{{ $isEdit ? 'Update the lead information below' : 'Enter basic details to create a new lead' }}</p>
            </div>

            <div class="gs-page-topbar-actions">
                @canany(['create leads', 'edit leads'])
                    @can('fetch-leads leads')
                        <a href="{{ route('api.index') }}" type="button" class="gs-btn gs-btn--outline" id="btnHousingApi">
                            <i class="bi bi-house-fill"></i> Fetch API
                        </a>
                    @endcan
                    {{-- Save as Draft --}}
                    <button type="submit" name="status" value="draft" class="gs-btn gs-btn--outline">
                        <i class="bi bi-floppy-fill"></i> Save Draft
                    </button>

                    {{-- Save / Update --}}
                    <button type="submit" name="status" value="active" class="gs-btn gs-btn--primary">
                        <i class="bi bi-check-lg"></i> {{ $isEdit ? 'Update Lead' : 'Save Lead' }}
                    </button>
                @endcanany

                @if($isEdit)
                    @can('delete leads')
                        {{-- Delete button triggers hidden form --}}
                        <button type="button" class="gs-btn gs-btn--danger" onclick="DeleteLead('{{$lead->id}}')" id="btnDeleteLead">
                            <i class="bi bi-trash-fill"></i> Delete Lead
                        </button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- ── Progress Steps ─────────────────────────────────────── --}}
        
        @php
            $stages = get_all_lead_status();
            $firstStageId = !empty($stages) ? $stages[0]['id'] : null;
            $currentStatusId = $firstStageId;

            if($isEdit) {
                $currentStatusId = old('lead_status_id', $lead->lead_status_id ?? $firstStageId);
            }

            $currentStepIndex = 0;
            foreach($stages as $index => $stage) {
                if($stage['id'] == $currentStatusId) {
                    $currentStepIndex = $index;
                    break;
                }
            }
        @endphp

        <div class="gs-steps">
            @foreach($stages as $index => $stage)
                @php 
                    $isActive = ($index <= $currentStepIndex) ? 'gs-step--active' : ''; 
                @endphp

                <div class="gs-step {{ $isActive }}">
                    <span class="gs-step-dot"></span>
                    {{ $stage['name'] }}
                </div>

                @if(!$loop->last)
                    <i class="bi bi-arrow-right gs-step-arrow {{ $isActive }}"></i>
                @endif
            @endforeach
        </div>


        {{-- ── Validation Errors ───────────────────────────────────── --}}
        @if($errors->any())
            <div class="gs-alert gs-alert--danger all-erros">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Main Grid ───────────────────────────────────────────── --}}
        <div class="gs-common-grid gs-create-lead">

            {{-- ════ LEFT COLUMN ════ --}}
            <div class="gs-left-grid">

                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Lead Details</h5>
                        @canany(['create loa', 'approve loa', 'create rfq', 'view rfq'])
                            @if($isEdit)
                                @php
                                    $selectedStatus = collect($get_all_lead_status)
                                        ->firstWhere('id', $lead->lead_status_id);

                                    $isReceivedStatus = isset($selectedStatus['name']) &&
                                        str_contains(strtolower($selectedStatus['name']), 'bills received');
                                        
                                    $isLoaReceived = isset($selectedStatus['name']) &&
                                        str_contains(strtolower($selectedStatus['name']), 'loa received');
                                        
                                    $isrfq = isset($selectedStatus['name']) &&
                                        str_contains(strtolower($selectedStatus['name']), 'rfq');

                                    $hasLoaGenerated = !empty($lead->others['loa_generated']);
                                @endphp

                                @if($isReceivedStatus)
                                    @if(($lead->others['signable_status'] ?? null) === 'signed')
                                        @can('approve loa')
                                            <a type="button" class="gs-btn gs-btn--primary" href="{{ route('leads.verify-loa', $lead->id) }}">
                                                <i class="bi bi-file-earmark-check"></i> Verify LOA
                                            </a>
                                        @endcan
                                    @else
                                        @can('create loa')
                                            <button type="button" class="gs-btn gs-btn--primary" onclick="{{ !empty($lead->others['loa_generated']) ? 'updateLOA' : 'generateLOA' }}('{{ $lead->id }}')" id="btnGenerateLOA">
                                                <i class="bi bi-plus-lg"></i> {{ $hasLoaGenerated ? 'Update LOA' : 'Generate LOA' }}
                                            </button>
                                        @endcan
                                    @endif
                                @endif
                                
                                @if($isLoaReceived)
                                    <a type="button" class="gs-btn gs-btn--primary" href="{{ route('leads.to-rfq', $lead->id) }}">
                                        <i class="bi bi-plus-lg"></i> Proceed To RFQ
                                    </a>
                                @endif
                                
                                @if($isrfq)
                                    <div class="d-flex gap-3">
                                        @can('create rfq')
                                            <a type="button" class="gs-btn gs-btn--primary" href="{{ route('leads.add_rfq', $lead->id) }}">
                                                <i class="bi bi-plus-lg"></i> Add Quote
                                            </a>
                                        @endcan
                                        
                                        @can('view rfq')
                                            <a type="button" class="gs-btn gs-btn--primary" href="{{ route('rfq_quotes.view', $lead->id) }}">
                                                <i class="bi bi-plus-lg"></i> View Quotes
                                            </a>
                                        @endcan
                                    </div>
                                @endif
                            @endif
                        @endcanany
                    </div>
                    <div class="gs-panel-body">
                        <div class="gs-form-grid">

                            {{-- Company Name (full width) --}}
                            <div class="gs-field gs-field--full">
                                <label class="gs-label" for="company_name">Company Name <span class="gs-required">*</span></label>
                                <input type="text" class="gs-input @error('company_name') is-invalid @enderror" name="company_name" id="company_name" required placeholder="British UK Limited" value="{{ old('company_name', $lead->company_name ?? '') }}" />
                                @error('company_name')
                                    <span class="gs-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="gs-field">
                                <label class="gs-label" for="phone">Phone Number <span class="gs-required">*</span></label>
                                <input type="tel" class="gs-input @error('phone') is-invalid @enderror" name="phone" id="phone" required placeholder="+44 7911 123456" value="{{ old('phone', $lead->phone ?? '') }}" />
                                @error('phone')
                                    <span class="gs-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="gs-field">
                                <label class="gs-label" for="email">Email Address <span class="gs-required">*</span></label>
                                <input type="email" class="gs-input @error('email') is-invalid @enderror" name="email" id="email" required placeholder="john.doe@example.com" value="{{ old('email', $lead->email ?? '') }}" />
                                @error('email')
                                    <span class="gs-field-error">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Annual Consumption --}}
                            <div class="gs-field">
                                <label class="gs-label" for="annual_consumption">Annual Consumption (kWh)</label>
                                <input type="text" class="gs-input" name="annual_consumption" id="annual_consumption" placeholder="450384" value="{{ old('annual_consumption', $lead->annual_consumption ?? '') }}" />
                            </div>

                            {{-- MPAN Number --}}
                            <div class="gs-field">
                                <label class="gs-label" for="mpan">MPAN/MPRN Number</label>
                                <div class="cl-input-unit-wrap">
                                <select class="cl-unit-select" name="mpan_unit" style="min-width: 65px;">
                                    <option value="mpan"
                                        {{ $isEdit && $lead->energy_type == 'electricity' ? 'selected' : '' }}>
                                        MPAN
                                    </option>
                                    <option value="mprn"
                                        {{ $isEdit && $lead->energy_type == 'gas' ? 'selected' : '' }}>
                                        MPRN
                                    </option>
                                </select>
                                <input type="text" class="gs-input cl-input-unit" name="mpan" id="mpan" placeholder="1580000045044" value="{{ old('mpan', $lead->mpan ?? '') }}" />
                                </div>
                            </div>
                            {{-- AQ --}}
                            <div class="gs-field">
                                <label class="gs-label" for="aq">AQ</label>
                                <input type="text" class="gs-input" name="aq" id="aq" placeholder="AQ Value" value="{{ old('aq', $lead->aq ?? '') }}" />
                            </div>
                            {{-- Registration Number --}}
                            <div class="gs-field">
                                <label class="gs-label" for="reg_number">Registration Number</label>
                                <input type="text" class="gs-input" name="reg_number" id="reg_number" placeholder="Registration Number" value="{{ old('reg_number', $lead->reg_number ?? '') }}" />
                            </div>

                            {{-- Decision Maker Name --}}
                            <div class="gs-field">
                                <label class="gs-label" for="decision_maker_name">Decision Maker Name</label>
                                <input type="text" class="gs-input" name="decision_maker_name" id="decision_maker_name" placeholder="Ryan Mitchell" value="{{ old('decision_maker_name', $lead->decision_maker_name ?? '') }}" />
                            </div>
                            
                            {{-- Decision Maker Designation --}}
                            <div class="gs-field">
                                <label class="gs-label" for="decision_maker_designation">Decision Maker designation</label>
                                <input type="text" class="gs-input" name="decision_maker_designation" id="decision_maker_designation" placeholder="Manager" value="{{ old('decision_maker_name', $lead->others['decision_maker_designation'] ?? '') }}" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Address Details</h5>
                        @php
                            $initialCount = (!empty($lead->others['address']) && is_array($lead->others['address'])) ? count($lead->others['address']) : 0;
                        @endphp
                        <a href="javascript:void(0);" type="button" class="gs-btn gs-btn--outline" id="" onclick="AddAddress(this)" data-count="{{ $initialCount }}">
                            <i class="bi bi-plus"></i> Add Site address
                        </a>
                    </div>
                    <div class="gs-panel-body" id="address-body">
                        <div class="gs-form-grid">
                            <div class="gs-field">
                                <label class="gs-label" for="address">Address</label>
                                <input type="text" class="gs-input" name="address" id="address" placeholder="Street Address" value="{{ old('address', $lead->address ?? '') }}" />
                            </div>

                            {{-- City --}}
                            <div class="gs-field">
                                <label class="gs-label" for="city">City</label>
                                <input type="text" class="gs-input" name="city" id="city" placeholder="South Bank" value="{{ old('city', $lead->city ?? '') }}" />
                            </div>

                            {{-- State --}}
                            <div class="gs-field">
                                <label class="gs-label" for="state">State</label>
                                <input type="text" class="gs-input" name="state" id="state" placeholder="Middlesbrough" value="{{ old('state', $lead->state ?? '') }}" />
                            </div>

                            {{-- Post Code --}}
                            <div class="gs-field">
                                <label class="gs-label" for="postcode">Post Code</label>
                                <input type="text" class="gs-input" name="postcode" id="postcode" placeholder="TS6 6TZ" value="{{ old('postcode', $lead->postcode ?? '') }}" />
                            </div>
                        </div>
                        
                        {{-- Dynamic Additional Addresses --}}
                        @if(!empty($lead->others['address']) && is_array($lead->others['address']))
                            @foreach($lead->others['address'] as $index => $extraAddress)
                                @php 
                                    $suffix = '_' . $loop->iteration; 
                                @endphp
                        
                                <div class="gs-form-grid gs-additional-address-block" style="margin-top: 25px;">
                                    
                                    {{-- Extra Address Line --}}
                                    <div class="gs-field">
                                        <label class="gs-label" for="address{{ $suffix }}">Address {{ $loop->iteration }}</label>
                                        <input type="text" 
                                               class="gs-input" 
                                               name="address{{ $suffix }}" 
                                               id="address{{ $suffix }}" 
                                               placeholder="Street Address" 
                                               value="{{ old('address' . $suffix, $extraAddress['address'] ?? '') }}" />
                                    </div>
                        
                                    {{-- Extra City --}}
                                    <div class="gs-field">
                                        <label class="gs-label" for="city{{ $suffix }}">City {{ $loop->iteration }}</label>
                                        <input type="text" 
                                               class="gs-input" 
                                               name="city{{ $suffix }}" 
                                               id="city{{ $suffix }}" 
                                               placeholder="City Name" 
                                               value="{{ old('city' . $suffix, $extraAddress['city'] ?? '') }}" />
                                    </div>
                        
                                    {{-- Extra State --}}
                                    <div class="gs-field">
                                        <label class="gs-label" for="state{{ $suffix }}">State {{ $loop->iteration }}</label>
                                        <input type="text" 
                                               class="gs-input" 
                                               name="state{{ $suffix }}" 
                                               id="state{{ $suffix }}" 
                                               placeholder="State Name" 
                                               value="{{ old('state' . $suffix, $extraAddress['state'] ?? '') }}" />
                                    </div>
                        
                                    {{-- Extra Post Code --}}
                                    <div class="gs-field">
                                        <label class="gs-label" for="postcode{{ $suffix }}">Post Code {{ $loop->iteration }}</label>
                                        <input type="text" 
                                               class="gs-input" 
                                               name="postcode{{ $suffix }}" 
                                               id="postcode{{ $suffix }}" 
                                               placeholder="Postcode" 
                                               value="{{ old('postcode' . $suffix, $extraAddress['postcode'] ?? '') }}" />
                                    </div>
                        
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                @if($isEdit)
                   {{-- ── Energy Requirement Sub-section ── --}}
				 <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Energy Requirement</h5>
                    </div>
                    <div class="gs-panel-body">
						
						
							<div class="gs-form-grid cl-energy-grid">

								{{-- Energy Requirement with unit --}} 
								<div class="gs-field">
									<label class="gs-label" for="total_annual_consumption">Total Energy Requirement</label>
									<div class="cl-input-unit-wrap">
										<input type="text" class="gs-input cl-input-unit"
											name="total_annual_consumption" id="total_annual_consumption"
											placeholder="450384"
											value="{{ old('total_annual_consumption', $lead->total_annual_consumption ?? '') }}" />
										<select class="cl-unit-select" name="energy_unit">
											<option value="kWh" @selected(old('energy_unit', $lead->energy_unit ?? 'kWh') == 'kWh')>kWh</option>
											{{-- <option value="MWh" @selected(old('energy_unit', $lead->energy_unit ?? '') == 'MWh')>MWh</option> --}}
										</select>
									</div>
								</div>

								{{-- Budget Range --}}
								<div class="gs-field">
									<label class="gs-label" for="budget_range">Budget Range</label>
									<div class="cl-input-prefix-wrap">
										<span class="cl-input-prefix">£</span>
										<input type="text" class="gs-input cl-input-prefix-field"
											name="budget_range" id="budget_range"
											placeholder="250000"
											value="{{ old('budget_range', $lead->budget_range ?? '') }}" />
									</div>
								</div>

								{{-- Roof Type / Site Type --}}
								<div class="gs-field">
									<label class="gs-label" for="roof_site_type">Roof Type / Site Type</label>
									<input type="text" class="gs-input"
										name="roof_site_type" id="roof_site_type"
										placeholder="Industrial"
										value="{{ old('roof_site_type', $lead->roof_site_type ?? '') }}" />
								</div>

							</div>
					
					</div>
				</div>
                @endif

                {{-- Lead Description Panel --}}
                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Lead Description</h5>
                    </div>
                    <div class="gs-panel-body">
                        <div class="gs-field">
                            <textarea class="gs-textarea" id="description" name="description" rows="5">{{ old('description', $lead->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                @if($isEdit)
                    {{-- ── Activity Logs Panel (NEW) ── --}}
                    @php
                        $timeline = getLeadTimeline($lead->id);
                    @endphp
                    <div class="gs-panel">
                        <div class="gs-panel-header">
                            <h5 class="gs-panel-title">Activity Logs</h5>
                        </div>
                        <div class="gs-panel-body cl-activity-body">

                            <ul class="cl-activity-list">
                                @forelse($timeline as $item)
                                <li class="cl-activity-item">
                                    <span class="cl-activity-dot"></span>
                                    <span class="cl-activity-text">{!! $item['text'] !!}</span>
                                    <span class="cl-activity-time">{{ $item['date'] }}</span>
                                </li>
                                @empty
                                <div class="timeline-empty">
                                    <span>No Activity Log found.</span>
                                </div>
                                @endforelse
                            </ul>

                        </div>
                    </div>
                @endif
            </div>

            {{-- ════ RIGHT COLUMN ════ --}}
            <div class="gs-right-grid">

                {{-- Lead Source Panel --}}
                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Lead Source</h5>
                    </div>
                    <div class="gs-panel-body">
                        <div class="gs-form-grid gs-form-grid--full" style="gap:14px;">

                            {{-- Current Supplier --}}
                            <div class="gs-field">
                                <label class="gs-label" for="current_supplier">Current Supplier</label>
                                <input type="text" class="gs-input" name="current_supplier" id="current_supplier" placeholder="Supplier Name" value="{{ old('current_supplier', $lead->current_supplier ?? '') }}" />
                            </div>

                            {{-- Energy Type --}}
                            <div class="gs-field">
                                <label class="gs-label" for="energy_type">Energy Type</label>
                                <select class="gs-select" name="energy_type" id="energy_type">
                                    <option value="electricity" @selected(old('energy_type', $lead->energy_type ?? 'electricity') == 'electricity')>Electricity</option>
                                    <option value="gas" @selected(old('energy_type', $lead->energy_type ?? 'electricity') == 'gas')>Gas</option>
                                </select>
                            </div>

                            {{-- Lead Status --}}
                            <div class="gs-field">
                                <label class="gs-label" for="lead_status_id">Lead Status</label>
                                <select class="gs-select" name="lead_status_id" id="lead_status_id">
                                    @foreach($get_all_lead_status as $key => $value)
                                        <option value="{{ $value['id'] }}"
                                            {{ old('lead_status_id', $lead->lead_status_id ?? '') == $value['id'] ? 'selected' : '' }}>
                                            {{ $value['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Contract End Date --}}
                            <div class="gs-field">
                                <label class="gs-label" for="contract_end_date">Contract End Date</label>
                                <input type="date" class="gs-input" name="contract_end_date" id="contract_end_date" value="{{ old('contract_end_date', isset($lead->contract_end_date) ? $lead->contract_end_date->format('Y-m-d') : '') }}" />
                            </div>

                            {{-- Temperature --}}
                            <div class="gs-field">
                                <label class="gs-label" for="priority_status_id">Temperature</label>
                                <select class="gs-select" name="priority_status_id" id="priority_status_id">
                                    @foreach($get_all_priority_status as $key => $value)
                                        <option value="{{ $value['id'] }}"
                                            {{ old('priority_status_id', $lead->priority_status_id ?? '') == $value['id'] ? 'selected' : '' }}>
                                            {{ $value['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                @can('assign leads')
                    <div class="gs-panel">
                        <div class="gs-panel-header">
                            <h5 class="gs-panel-title">Assign Team</h5>
                        </div>
                        <div class="gs-panel-body">
                            <div class="gs-form-grid gs-form-grid--full" style="gap:14px;">
                                {{-- Sales Manager --}}
                                <div class="gs-field">
                                    <label class="gs-label" for="assigned_to">Assign Collaborator</label>
                                    <div class="custom-multiselect">
                                        <div class="multiselect-search-container">
                                            <input type="search" class="search-bar" id="sale_managers_search" placeholder="Search Sales Manager...">
                                        </div>

                                        <div class="multiselect-list sales-manager">
                                            @foreach($salesManagers as $manager)
                                                <div class="item-group">
                                                    <label class="checkbox-container">
                                                        <input type="checkbox" class="gs-input" name="assigned_manager[]" value="{{ $manager->id }}" @checked(in_array($manager->id, $assignedUserIds ?? []))>  <span class="checkmark"></span> {{ $manager->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                            <div class="no-results" style="display: none; padding: 10px; color: #888; text-align: center;">
                                                No users found.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sales Executives --}}
                                <div class="gs-field">
                                    <label class="gs-label" for="assigned_to">Assign Member</label>
                                    <div class="custom-multiselect">
                                        <div class="multiselect-search-container">
                                            <input type="search" class="search-bar" id="sale_members_search" placeholder="Search Sales Members...">
                                        </div>

                                        <div class="multiselect-list sales-member">
                                            @foreach($salesexecutives as $executive)
                                                <div class="item-group">
                                                    <label class="checkbox-container">
                                                        <input type="checkbox" class="gs-input" name="assigned_executive[]" value="{{ $executive->id }}" @checked(in_array($executive->id, $assignedUserIds ?? []))> <span class="checkmark"></span>{{ $executive->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                            <div class="no-results" style="display: none; padding: 10px; color: #888; text-align: center;">
                                                No users found.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                {{-- Requirement Gathering Panel --}}
                @if($isEdit)
                    <div class="gs-panel">
                        <div class="gs-panel-header">
                            <h5 class="gs-panel-title">Requirement Gathering</h5>
                        </div>
                        <div class="gs-panel-body">
                            <div class="gs-contact-btns">
                                <button  type="button"  class="gs-contact-btn gs-contact-btn--call" data-bs-toggle="offcanvas" data-bs-target="#callOffcanvas">
                                    <i class="bi bi-telephone-fill"></i> Call
                                </button>
                                <!--<button type="button" class="gs-contact-btn gs-contact-btn--mail">-->
                                <!--    <i class="bi bi-envelope-fill"></i> Mail-->
                                <!--</button>-->
                                <!--<button type="button" class="gs-contact-btn gs-contact-btn--whatsapp">-->
                                <!--    <i class="bi bi-whatsapp"></i> WhatsApp-->
                                <!--</button>-->
                            </div>
                        </div>
                    </div>
                @endif    
            </div>
        </div> 
        @if($isEdit)
		<div class="offcanvas wide-modal offcanvas-end" tabindex="-1" id="callOffcanvas">
		    <div class="offcanvas-header gs-notif-header">
				<div class="gs-notif-header-left">
					<div class="gs-notif-header-icon"><i class="bi bi-buildings"></i></div>
					<h5 class="offcanvas-title gs-notif-title " id="detailOffcanvasLabel">Call Log</h5>
				</div>
				<button type="button" class="gs-notif-close" data-bs-dismiss="offcanvas" aria-label="Close">
					<i class="bi bi-x-lg"></i>
				</button>
			</div>
			<div class="offcanvas-body text-center">
				<div class="call-log">
					<div class="call-log-duration">
                        <div class="call-his cld">
                            <p><i class="bi bi-telephone-fill"></i> Total Calls: <span>{{ $leadMetrics['total_calls'] }}</span></p>
                        </div>

                        <div class="total-dur cld">
                            <p><i class="bi bi-clock-fill"></i> Total Duration: <span>{{ $leadMetrics['total_duration'] }}</span></p>
                        </div>

                        <div class="call-ans cld">
                            <p><i class="bi bi-telephone-inbound-fill"></i> Answered : <span>{{ $leadMetrics['recordings_available'] }}</span></p>
                        </div>

                        <div class="missed-ans cld">
                            <p><i class="bi bi-telephone-x-fill"></i> Missed/declined : <span>{{ $leadMetrics['recordings_missing'] }}</span></p>
                        </div>
                    </div>
					<div class="call-two-col">
						<div class="call-col-left">
                            <div class="oc-col-body">
                                <div class="call-dialer-box" style="min-height: 520px;">
                                @php
                                    // Pulling dialpad_api_key out of your framework settings table
                                    $get_dialpad_id = get_setting_data(['dialpad_api_key']);
                                    $dialpad_id = $get_dialpad_id['dialpad_api_key'] ?? '';
                                    
                                    $customPayload = json_encode([
                                        'lead_id' => $lead->id ?? null,
                                        'user_id' => auth()->id() ?? null
                                    ]);
                                    
                                    $queryParams = [
                                        'custom_data' => $customPayload
                                    ];
                                    
                                    $iframeUrl = "https://dialpad.com/apps/" . $dialpad_id . "?" . http_build_query($queryParams);
                                @endphp
                                
                                <iframe data-src="{{ $iframeUrl }}" title="Dialpad" id="dialpadFrame" allow="microphone; speaker-selection; autoplay; camera; display-capture; hid" sandbox="allow-popups allow-scripts allow-same-origin allow-forms" frameborder="0" style="width:100%;height:520px;"></iframe>
                                </div>
                            </div>
                        </div>

<script>				
document.addEventListener("DOMContentLoaded", function() {
    
    
    // Replace 'yourOffcanvasId' with the actual ID element string of your HTML offcanvas wrapper component
    const offcanvasElement = document.getElementById('callOffcanvas'); 
    const iframe = document.getElementById('dialpadFrame');
    const customCallData = {!! json_encode($customPayload) !!};
    
    if (!offcanvasElement) {
        console.error("Offcanvas element wrapper target not found. Double-check your element ID.");
        return;
    }
    
    var issecond = false;

    // 1. Intercept the standard Bootstrap activation wrapper event hook
    offcanvasElement.addEventListener('shown.bs.offcanvas', function () {
        console.log("Offcanvas completely visible. Parsing phone context...");

        // Grab the current value directly from your form input element node
        const phoneInput = document.getElementById('phone');
        if (!phoneInput) return;

        // Clean up the number value: Trim spaces and remove common non-numeric dividers (except +)
        let phoneNumber = phoneInput.value.trim().replace(/[\s\-\(\)]/g, '');
        //phoneNumber = "8629061873";

        // Drop execution immediately if string resolves as empty
        if (!phoneNumber) {
            console.log("Dialpad Aborted: Phone number value string input is currently empty.");
            return;
        }
    
        let CphoneNumber = phoneNumber.replace(/\D/g, '');
        
        fetch("{{ route('dialpad.check-blocked-number') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                "Accept": "application/json"
            },
            body: JSON.stringify({
                phone_number: CphoneNumber
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Request failed");
            }
            return response.json();
        })
        .then(result => {
            if (result.blocked) {
                window.miniAlert("This phone number is blocked.");
                return;
            }
        
            // Lazily load Dialpad iframe source context now so it initializes on demand
            if (!iframe.src || iframe.src === 'about:blank') {
                iframe.src = iframe.getAttribute('data-src');
            }
    
            // Clean out any old active message hooks to prevent multi-bubble calling stacks
            window.removeEventListener('message', handleDialpadMessages);
            
            // Bind the postMessage listening engine cleanly onto the window context wrapper
            window.addEventListener('message', handleDialpadMessages);
    
            function handleDialpadMessages(event) {
                if (event.origin !== 'https://dialpad.com') return;
    
                const data = event.data;
                console.log("Received from Dialpad inside offcanvas:", data);
    
                // Catch the authentication payload sequence Dialpad initializes 
                if (data.api === 'opencti_dialpad' && data.method === 'user_authentication') {
                    if (data.payload && data.payload.user_authenticated === true) {
                        if(!issecond){
                            issecond = true;
                            return;
                        }
                        console.log("Dialpad authentication successful. Proceeding with call initiation...");
                        // Activate current system CTI workspace view context
                        iframe.contentWindow.postMessage({
                            'api': 'opencti_dialpad',
                            'version': '1.0',
                            'method': 'enable_current_tab'
                        }, 'https://dialpad.com');
    
                        // Fire raw outbound call transaction alongside customized tracking tracking arrays
                        console.log("Placing outbound call connection out to: " + phoneNumber);
                        
                        iframe.contentWindow.postMessage({
                            'api': 'opencti_dialpad',
                            'version': '1.0',
                            'method': 'initiate_call',
                            'payload': {
                                'phone_number': phoneNumber,
                                'enable_current_tab': true,
                                'custom_data': customCallData
                            }
                        }, 'https://dialpad.com');
    
                        // Unbind listener loop context to prevent duplicate injection cycles during continuous operations
                        //window.removeEventListener('message', handleDialpadMessages);
                    }
                }
            }
        })
        .catch(error => {
            console.error(error);
            window.miniAlert("Unable to verify phone number.");
        });
        
    });
});
</script>
						<div class="call-col-right">
							<div class="oc-right-wrapper">
								<label class="gs-label">Call Notes</label>
								<textarea class="call-notes-area" id="callNoteInput" placeholder="Talking With company :&#10;Discussing About Energy Requirements And Site Details." onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
								<div class="d-flex gap-8 mb-3 mt-2">
									<button type="button" class="gs-contact-btn gs-contact-btn--whatsapp" style="flex:1;justify-content:center;" id="openPortalBtn"> <i class="bi bi-box-arrow-in-right"></i> Customer Portal Link </button>
									<button class="gs-btn gs-btn--teal" style="flex:1;justify-content:center;" onclick="CallNoteAdd()" type="button"><i class="bi bi-plus-lg"></i> Save Note</button>
								</div>
								<div id="notes-container" class="notes-cnt">
								</div>
							</div>
							<div class="gs-activity-card">
                                <x-activity-timeline title="Recent Activity" :lead_id="$lead->id" :limit=null/>
							</div>
						</div> 
					</div>
				</div>
			</div>
		</div>
		<div class="offcanvas offcanvas-center cplink-popup" tabindex="-1" id="CustomerPortalLink">
			<div class="cplink-popup-wrapper">
                @if(!empty($lead->others['loa_mails_sent']) && $lead->others['loa_mails_sent'] > 0)
                    <div class="gs-alert gs-alert--success w-100 mt-0">
                        <i class="bi bi-check-circle-fill"></i>
                        LOA emails sent: {{ $lead->others['loa_mails_sent'] }}
                    </div>
                @endif
                <div class="offcanvas-header gs-notif-header">
					<div class="gs-notif-header-left">
						<div class="gs-notif-header-icon">
							<i class="bi bi-bell-fill"></i>
						</div>
						<div class="s-notif-header-icon">
							<h5 class="mb-2">Complete Your LOA Verification</h5>
							<p><b>Hi <span class="name">{{ $lead->decision_maker_name ?? $lead->company_name ?? '' }}</span>!</b> Please complete your Letter of Authority (LOA) verification by uploading your electricity bill and signing the form to proceed with your quotation.</p>
						</div>
					</div>
					<button type="button" class="gs-notif-close" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-x-lg"></i></button>
				</div>

                @php
                    $data = [
                        'lead_id' => $lead->id ?? ''
                    ];

                    $encoded = urlencode(base64_encode(json_encode($data)));

                    $url = route('loa.verify', ['data' => $encoded]);
                @endphp

				<div class="offcanvas-body text-center">
					<form id="verificationForm">
                        @csrf
						<div class="inputgroup">
							<input type="email" class="gs-input" name="receiver_email" placeholder="Enter recipient Email" value="{{ $lead->email ?? '' }}" />
                            <input type="hidden" name="edited_lead_id" placeholder="Enter recipient Email" value="{{ $lead->id ?? '' }}" />
						    <div class="url-copy-wrapper">
                                <input type="text" class="gs-input" value="{{ $url }}" id="copyUrlField" name="verification_url" readonly>

                                <button type="button" class="gs-btn gs-btn--teal copy-btn" onclick="copyUrl()">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                        </div>
						<div class="form-footer">
							<div class="forml-footer">
								<div class="forml-icon">
									<i class="bi bi-lock-fill"></i>
								</div>
								<div class="forml-content">
									<h5>Secure portal to upload bills & e-sign LOA</h5>
									<p>Your information is encrypted and secure.</p>
								</div>
							</div>
							<div class="formr-footer">
								<button type="button" class="gs-btn gs-btn--primary" id="verifybutton" onclick="sendVerificationEmail()">Send Now</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
        @endif  
        @if($isEdit)
            {{-- ══════════════════════════════════════════════════════════
                BOTTOM — Attachments / Notes / Contact Timeline (NEW)
            ═══════════════════════════════════════════════════════════ --}}
            <div class="gs-panel cl-tabs-panel">
                <div class="gs-panel-header cl-tabs-header">
                    <div class="cl-tabs">
                        <button type="button" class="cl-tab cl-tab--active" data-tab="attachments">
                            <i class="bi bi-paperclip"></i> Attachments
                        </button>
                        <button type="button" class="cl-tab" data-tab="notes">
                            <i class="bi bi-sticky-fill"></i> Notes
                        </button>
                        <button type="button" class="cl-tab" data-tab="timeline">
                            <i class="bi bi-clock-history"></i> Contact Timeline
                        </button>
                    </div>
                </div>

                {{-- ── Attachments Tab ── --}}
                <div class="cl-tab-pane cl-tab-pane--active" id="tab-attachments">
                    <div class="cl-attach-layout">

                        {{-- Upload Zone --}}
                        <div class="cl-upload-zone" id="uploadZone">
                            <input type="file" name="attachments[]" id="attachmentInput" multiple
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" hidden>
                            <div class="cl-upload-icon">
                                <i class="bi bi-cloud-arrow-up-fill"></i>
                            </div>
                            <p class="cl-upload-text">
                                Drag &amp; Drop files or
                                <button type="button" class="cl-upload-browse" onclick="document.getElementById('attachmentInput').click()">Browse</button>
                            </p>
                            <p class="cl-upload-hint">Upload Bills / Site Images (Optional)</p>
                        </div>

                        {{-- File List --}}
                        <div class="cl-file-list">

                            <div class="cl-file-item-header" id="uploaded-items">
                                
                            </div>
                            @foreach($lead->attachments as $attachment)

                                @php
                                    $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));

                                    $iconClass = 'bi-file-earmark';
                                    $typeClass = '';

                                    if ($ext === 'pdf') {
                                        $iconClass = 'bi-file-earmark-pdf-fill';
                                        $typeClass = 'cl-file-icon--pdf';
                                    } elseif (in_array($ext, ['jpg','jpeg','png'])) {
                                        $iconClass = 'bi-file-earmark-image-fill';
                                        $typeClass = 'cl-file-icon--jpg';
                                    } elseif (in_array($ext, ['doc','docx'])) {
                                        $iconClass = 'bi-file-earmark-word-fill';
                                        $typeClass = 'cl-file-icon--doc';
                                    }
                                @endphp

                                <div class="cl-file-item">
                                    <div class="cl-file-icon {{ $typeClass }}">
                                        <i class="bi {{ $iconClass }}"></i>
                                    </div>

                                    <div class="cl-file-info">
                                        <span class="cl-file-name">{{ $attachment->file_name }}</span>
                                        <span class="cl-file-size">({{ $attachment->readable_size }})</span>
                                    </div>

                                    <div class="cl-file-actions">
                                        {{-- VIEW --}}
                                        <button type="button" 
                                                class="cl-file-btn"
                                                onclick="window.open('{{ asset('storage/'.$attachment->file_path) }}', '_blank')">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        {{-- DELETE --}}
                                        <button type="button" 
                                                class="cl-file-btn"
                                                onclick="deleteAttachment({{ $attachment->id }}, this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>

                            @endforeach
                        </div>

                    </div>
                </div>

                {{-- ── Notes Tab ── --}}
                <div class="cl-tab-pane" id="tab-notes">
                    <div class="cl-notes-body">
                        <div class="gs-field">
                            <label class="gs-label" for="lead_notes">Add Note</label>
                            <textarea class="gs-textarea" id="noteData" rows="4"
                                 placeholder="Add internal note for your team..."></textarea>
                        </div>
                        <div class="cl-notes-footer">
                            <button type="button" class="gs-btn gs-btn--primary gs-btn--sm" onclick="saveNote()">
                                <i class="bi bi-plus-lg"></i> Add Note
                            </button>
                        </div>
                        <div id="notesFeed" class="vl-task-notes mt-1">
                            @if($isEdit)
                                <p class="text-muted text-center small">Loading notes...</p>
                            @endif    
                        </div>

                    </div>
                </div>

                {{-- ── Contact Timeline Tab ── --}}
                <div class="cl-tab-pane" id="tab-timeline">
                    <div class="cl-timeline-body"> 



                        @forelse($callLogs as $call)
                            <div class="cl-timeline-item mb-3">
                                @if(($call->direction ?? 'outbound') === 'inbound')
                                    <div class="gs-user-avatar me-0" title="Inbound Call">
                                        <i class="bi bi-telephone-inbound-fill"></i>
                                    </div>
                                @else
                                    <div class="gs-user-avatar me-0" title="Outbound Call">
                                        <i class="bi bi-telephone-outbound-fill"></i>
                                    </div>
                                @endif
                                
                                <div class="cl-timeline-content w-100 ps-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="cl-timeline-title mb-0 fw-bold text-capitalize">
                                            Call activity
                                        </p>
                                        <span class="cl-timeline-time text-muted small">
                                            {{ $call->created_at ? $call->created_at->diffForHumans() : '' }}
                                        </span>
                                    </div>

                                    <p class="cl-timeline-desc text-muted mb-1 small">
                                        <strong>Agent:</strong> {{ $call->user->name ?? $call->agent_name ?? 'System' }}
                                        @if($call->contact_number)
                                            | <strong>Contact number:</strong> {{ $call->contact_number }}
                                        @endif
                                        @if($call->duration)
                                            | <strong>Duration:</strong> {{ gmdate("i:s", $call->duration / 1000) }} mins
                                        @endif
                                    </p>

                                    @if($call->local_recording_path)
                                        <div class="mt-2 audio-player-wrapper">
                                            <audio controls preload="none" class="w-100" style="max-width: 400px; height: 32px;">
                                                <source src="/{{ $call->local_recording_path }}" type="audio/mpeg">
                                                Your browser does not support the audio element.
                                            </audio>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            {{-- This message shows ONLY if $communicationNotes is empty --}}
                            <div class="no-logs-message text-center p-4">
                                <div class="mb-2">
                                    <i class="bi bi-clock-history fa-2x text-muted"></i>
                                </div>
                                <p class="text-muted">No any Contact Timeline logs found for this lead.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>{{-- end cl-tabs-panel --}}
        @endif
    </form>

    {{-- ── Delete Confirmation Form (separate, hidden) ─────────────── --}}
    @if($isEdit)
        @can('delete leads')
            <form method="POST" action="{{ route('leads.delete', $lead) }}" id="DeleteLeadForm-{{$lead->id}}" style="display:none;">
                @csrf
                <input type="hidden" name="_action" value="delete">
            </form>
        @endcan

        <script>
            const uploadUrl = "{{ route('leads.attach', $lead->id) }}";
            const csrfToken = "{{ csrf_token() }}";
            const leadId = {{ $lead->id }};
        </script>

    @endif



@endsection


@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // ── CKEditor on description ──────────────────────────────
            if (document.querySelector('#description')) {
                ClassicEditor
                    .create(document.querySelector('#description'), {
                        toolbar: [
                            'heading', '|',
                            'bold', 'italic', '|',
                            'bulletedList', 'numberedList', '|',
                            'link', 'blockQuote', '|',
                            'undo', 'redo'
                        ],
                        placeholder: 'Describe the lead requirements, goals, notes…'
                    })
                    .catch(error => console.error(error));
            }

            // ── Delete confirmation ──────────────────────────────────
            
            document.getElementById('phone').addEventListener('input', function(e) {
                let x = e.target.value.replace(/\D/g, '');
                // remove leading 44 if user types it
                if (x.startsWith('44')) {
                    x = x.substring(2);
                }
                // remove leading 0
                if (x.startsWith('0')) {
                    x = x.substring(1);
                }
                let formatted = '+44 ';
                if (x.length > 0) formatted += x.substring(0,4);
                if (x.length >= 5) formatted += ' ' + x.substring(4,7);
                if (x.length >= 8) formatted += ' ' + x.substring(7,10);
                e.target.value = formatted.trim();
            });

            // ── Tab switching ──
            document.querySelectorAll('.cl-tab').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const target = this.dataset.tab;

                    document.querySelectorAll('.cl-tab').forEach(b => b.classList.remove('cl-tab--active'));
                    document.querySelectorAll('.cl-tab-pane').forEach(p => p.classList.remove('cl-tab-pane--active'));

                    this.classList.add('cl-tab--active');
                    const pane = document.getElementById('tab-' + target);
                    if (pane) pane.classList.add('cl-tab-pane--active');
                });
            });

            

            // ── Drag & Drop upload zone ──
            const zone = document.getElementById('uploadZone');
            const input = document.getElementById('attachmentInput');
            const container = document.getElementById('uploaded-items');

            window.toggleActiveClass = function() {
                if (container.children.length > 0) {
                    container.classList.add('active');
                } else {
                    container.classList.remove('active');
                }
            };


            if (zone && input && container) {
                
                zone.addEventListener('click', (e) => {
                    if (e.target !== input) {
                        input.click();
                    }
                });

                input.addEventListener('click', (e) => {
                    e.stopPropagation();
                });

                // Drag enter
                zone.addEventListener('dragenter', (e) => {
                    e.preventDefault();
                    zone.classList.add('cl-upload-zone--over');
                });

                // Drag over
                zone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                });

                // Drag leave
                zone.addEventListener('dragleave', () => {
                    zone.classList.remove('cl-upload-zone--over');
                });

                // Drop
                zone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    zone.classList.remove('cl-upload-zone--over');

                    const files = e.dataTransfer.files;

                    if (files.length) {
                        console.log('Dropped files:', files);

                        const dataTransfer = new DataTransfer();
                        for (let file of files) {
                            dataTransfer.items.add(file);
                        }
                        input.files = dataTransfer.files;
                    }
                });

                
                input.addEventListener('change', () => {
                    container.innerHTML = '';

                    if (input.files.length) {
                        Array.from(input.files).forEach(file => {

                            let ext = file.name.split('.').pop().toLowerCase();

                            let iconClass = 'bi-file-earmark';
                            let typeClass = '';

                            if (ext === 'pdf') {
                                iconClass = 'bi-file-earmark-pdf-fill';
                                typeClass = 'cl-file-icon--pdf';
                            } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
                                iconClass = 'bi-file-earmark-image-fill';
                                typeClass = 'cl-file-icon--jpg';
                            } else if (['doc', 'docx'].includes(ext)) {
                                iconClass = 'bi-file-earmark-word-fill';
                                typeClass = 'cl-file-icon--doc';
                            }

                            const size = (file.size / 1024 / 1024).toFixed(2) + ' MB';

                            const div = document.createElement('div');
                            div.classList.add('cl-file-item');

                            div.innerHTML = `
                                <div class="cl-file-icon ${typeClass}">
                                    <i class="bi ${iconClass}"></i>
                                </div>
                                <div class="cl-file-info">
                                    <span class="cl-file-name">${file.name}</span>
                                    <span class="cl-file-size">(${size})</span>
                                </div>
                                <div class="cl-file-actions">
                                    <button type="button" class="gs-filter-btn-apply upload-btn">Upload</button>
                                    <button type="button" class="cl-file-btn remove-file">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;

                            // REMOVE
                            div.querySelector('.remove-file').addEventListener('click', () => {
                                div.remove();
                                toggleActiveClass();
                            });

                            // UPLOAD
                            const uploadBtn = div.querySelector('.upload-btn');

                            uploadBtn.addEventListener('click', async () => {

                                const originalText = uploadBtn.innerHTML;
                                uploadBtn.disabled = true;
                                uploadBtn.innerHTML = 'Uploading...'; // loader text

                                const formData = new FormData();
                                formData.append('file', file);
                                formData.append('_token', csrfToken);

                                try {
                                    const response = await fetch(uploadUrl, {
                                        method: 'POST',
                                        body: formData
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        uploadBtn.innerHTML = 'Uploaded ✅';

                                        // optional: replace name/size from backend
                                        div.querySelector('.cl-file-name').textContent = data.attachment.name;
                                        div.querySelector('.cl-file-size').textContent = `(${data.attachment.size})`;

                                    } else {
                                        throw new Error(data.message);
                                    }

                                } catch (error) {
                                    uploadBtn.innerHTML = 'Failed ❌';
                                    uploadBtn.disabled = false;
                                    console.error(error);
                                }
                            });

                            container.appendChild(div);
                        });
                    }

                    toggleActiveClass();
                });
            }

            window.deleteAttachment = function(id, btn) {
                window.miniConfirm('Are you sure you want to delete this file?', 'Delete', function () { _deleteAttachment(id, btn); });
            }
            async function _deleteAttachment(id, btn) {

                const item = btn.closest('.cl-file-item');

                // loader UI
                btn.disabled = true;
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

                try {
                    const response = await fetch(`/attachments/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        // remove from UI
                        item.remove();

                        // update active class
                        toggleActiveClass();

                    } else {
                        throw new Error(data.message);
                    }

                } catch (error) {
                    console.error(error);
                    window.miniAlert('Delete failed');

                    // restore button
                    btn.disabled = false;
                    btn.innerHTML = original;
                }
            }

            if (typeof leadId !== 'undefined' && leadId) {
                // 1. Function to Fetch All Notes
                window.fetchNotes = async function() {
                    try {
                        const response = await fetch(`/note/all?lead_id=${leadId}`);
                        const result = await response.json();

                        if (result.success) {
                            window.renderNotes(result.data);
                        }
                    } catch (error) {
                        console.error('Error fetching notes:', error);
                    }
                } 

                // 2. Function to Render Notes HTML
                window.renderNotes = function(notes) {
                    const feed = document.getElementById('notesFeed');
                    feed.innerHTML = ''; // Clear current feed

                    if (notes.length === 0) {
                        feed.innerHTML = '';
                        return;
                    }

                    notes.forEach(note => {
                        const date = new Date(note.created_at).toLocaleDateString('en-GB', {
                            day: '2-digit', month: 'short', year: 'numeric'
                        });

                        const phoneIcon = (note.others && note.others === 'call') 
                            ? '<i class="bi bi-telephone ms-1"></i>' 
                            : '';
                        const initial = note.user.name.charAt(0).toUpperCase();
                        
                        feed.innerHTML += `
                            <div class="d-flex mb-0 border-top pt-3">
                                <div class="gs-user-avatar me-3" width="40" height="40">${initial}</div>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold small">${note.user.name} ${phoneIcon}</span>
                                        <span class="text-muted small" style="font-size: 0.75rem;">${date}</span>
                                    </div>
                                    <p class="text-secondary small mb-0 mt-1">${note.data}</p>
                                </div>
                            </div>
                        `;
                    });
                }

                // 3. Function to Save a New Note
                window.saveNote = async function() {
                    const data = document.getElementById('noteData').value;
                    if (!data) return window.miniAlert("Please type a note.");

                    try {
                        const response = await fetch("{{ route('notes.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                lead_id: leadId,
                                data: data
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            document.getElementById('noteData').value = ''; // Clear textarea
                            fetchNotes(); // Refresh the list
                        }
                    } catch (error) {
                        console.error('Error saving note:', error);
                    }
                }

                
                // 3. Function to Save a New Note
                window.CallNoteAdd = async function() {
                    const data = document.getElementById('callNoteInput').value;
                    if (!data) return window.miniAlert("Please type a note.");

                    try {
                        const response = await fetch("{{ route('notes.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                lead_id: leadId,
                                data: data
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            document.getElementById('callNoteInput').value = ''; // Clear textarea
                            fetchNotes();
                        }
                    } catch (error) {
                        console.error('Error saving note:', error);
                    }
                }

                // Initial load
                fetchNotes();
            }

            window.sendVerificationEmail = async function () {

                let btn = document.getElementById('verifybutton');

                let receiver_email = document.querySelector('[name="receiver_email"]').value.trim();
                let verification_url = document.querySelector('[name="verification_url"]').value.trim();
                let edited_lead_id = document.querySelector('[name="edited_lead_id"]').value.trim();

                if (!receiver_email) {
                    setBtnState(btn, 'Recipient email is required', 'error');
                    return;
                }

                if (!verification_url) {
                    setBtnState(btn, 'Missing URL', 'error');
                    return;
                }

                try {

                    // LOADING STATE
                    setBtnState(btn, 'Sending...', 'loading');

                    const response = await fetch("{{ route('send.verification.email') }}", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            receiver_email,
                            verification_url,
                            edited_lead_id
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {

                        if (data.error_type === 'LOA_MISSING') {
                            setBtnState(btn, 'Generate LOA First', 'error');
                        } else {
                            setBtnState(btn, 'Failed to send', 'error');
                        }

                        setTimeout(() => {
                            setBtnState(btn, 'Send Now', 'reset');
                        }, 2000);

                        return;
                    }

                    // SUCCESS
                    setBtnState(btn, 'Sent ✓', 'success');

                    setTimeout(() => {
                        setBtnState(btn, 'Send Now', 'reset');
                    }, 2000);

                } catch (error) {

                    console.error(error);

                    setBtnState(btn, 'Failed', 'error');

                    setTimeout(() => {
                        setBtnState(btn, 'Send Now', 'reset');
                    }, 2000);
                }
            };

            window.setBtnState = function(btn, text, state) {
                const isLoading = state === 'loading';

                btn.disabled = isLoading;

                if (state === 'loading') {
                    btn.innerHTML = `<span class="spinner"></span> ${text}`;
                    return;
                }

                btn.innerHTML = text;
            }

            window.copyUrl = function() {
                let copyText = document.getElementById("copyUrlField");
                navigator.clipboard.writeText(copyText.value);
                let btn = document.querySelector('.copy-btn');
                btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                }, 2000);
            };

        });
    </script>

	<script>
        document.getElementById('openPortalBtn').addEventListener('click', function (e) {
            e.stopPropagation();
            var portalOffcanvas = new bootstrap.Offcanvas(document.getElementById('CustomerPortalLink'), {
                backdrop: false,
                scroll: true
            });
            portalOffcanvas.show();
        });

        document.getElementById('CustomerPortalLink').addEventListener('shown.bs.offcanvas', function () {
            document.addEventListener('focusin', stopParentFocusTrap, true);
        });

        document.getElementById('CustomerPortalLink').addEventListener('hidden.bs.offcanvas', function () {
            document.removeEventListener('focusin', stopParentFocusTrap, true);
        });

        function stopParentFocusTrap(e) {
            const portal = document.getElementById('CustomerPortalLink');
            if (portal.contains(e.target)) {
                e.stopImmediatePropagation(); // parent ka focusin rok do
            }
        }
        @if($isEdit)
            window.generateLOA = function() {
                let btn = document.getElementById('btnGenerateLOA');
                setBtnState(btn, 'Generating...', 'loading');

                fetch("{{ route('loa.generate-pdf-lead') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        lead_id: "{{ $lead->id }}"
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        setBtnState(btn, '✓ Generated', 'success');
                    } else {
                        throw new Error(data.message || 'Generation failed');
                    }
                })
                .catch(error => {
                    console.error(error);
                    setBtnState(btn, 'Failed to generate', 'error');
                    setTimeout(() => {
                        setBtnState(btn, 'Generate LOA', 'reset');
                    }, 2000);
                });
            };

            window.updateLOA = function() {
                window.miniConfirm(
                    'Previous LOA will be removed from the website and replaced with the newly generated LOA. Do you want to continue?',
                    'Continue',
                    _updateLOA
                );
            };
            function _updateLOA() {
                let btn = document.getElementById('btnGenerateLOA');
                setBtnState(btn, 'Updating...', 'loading');

                fetch("{{ route('loa.generate-pdf-lead') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        lead_id: "{{ $lead->id }}"
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        setBtnState(btn, '✓ Updated', 'success');
                    } else {
                        throw new Error(data.message || 'Update failed');
                    }
                })
                .catch(error => {
                    console.error(error);
                    setBtnState(btn, 'Failed to update', 'error');
                    setTimeout(() => {
                        setBtnState(btn, 'Update LOA', 'reset');
                    }, 2000);
                });
            };
        @endif
        
        window.AddAddress = function(buttonElement) {
            let currentCount = parseInt(buttonElement.getAttribute('data-count')) || 0;
            let nextCount = currentCount + 1;
            let suffix = '_' + nextCount;
        
            const container = document.getElementById('address-body');
            if (!container) return;
        	
            const newAddressBlock = document.createElement('div');
            newAddressBlock.className = 'gs-form-grid gs-additional-address-block';
            newAddressBlock.style.marginTop = '25px';
            
            newAddressBlock.innerHTML = `
                <div class="gs-field">
                    <label class="gs-label" for="address${suffix}">Address ${nextCount}</label>
                    <input type="text" class="gs-input" name="address${suffix}" id="address${suffix}" placeholder="Street Address" value="" />
                </div>
        
                <div class="gs-field">
                    <label class="gs-label" for="city${suffix}">City ${nextCount}</label>
                    <input type="text" class="gs-input" name="city${suffix}" id="city${suffix}" placeholder="City Name" value="" />
                </div>
        
                <div class="gs-field">
                    <label class="gs-label" for="state${suffix}">State ${nextCount}</label>
                    <input type="text" class="gs-input" name="state${suffix}" id="state${suffix}" placeholder="State Name" value="" />
                </div>
        
                <div class="gs-field">
                    <label class="gs-label" for="postcode${suffix}">Post Code ${nextCount}</label>
                    <input type="text" class="gs-input" name="postcode${suffix}" id="postcode${suffix}" placeholder="Postcode" value="" />
                </div>
            `;
            container.appendChild(newAddressBlock);
            buttonElement.setAttribute('data-count', nextCount);
        };


	</script>
@endpush