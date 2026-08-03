@extends('layouts.app')
@section('title', isset($lead) ? 'Edit Lead — ' . $lead->company_name : 'Add New Lead')
@section('content')

    @php
        $isEdit = isset($lead) && $lead->exists;
        $formAction = $isEdit ? route('leads.update', $lead) : route('leads.store');
        $get_all_lead_status = get_all_lead_status();
        $get_all_priority_status = get_all_priority_status();
        $salesManagers = GetAllUsersByRoleId(4);
        $salesexecutives = GetAllUsersByRoleId(5);
    @endphp

    <form method="POST" action="{{ $formAction }}" class="lead-form cs-form" id="leadForm" enctype="multipart/form-data">
        @csrf

        {{-- ── Top Bar ── --}}
        <div class="gs-page-topbar">
            <div class="gs-page-topbar-left">
                <div class="page-title-bar">
                    <h2>{{ $isEdit ? 'Edit Lead' : 'Add New Lead' }}</h2>
                    @if($isEdit && !empty($lead->status) && $lead->status == 'draft')
                        <span class="status-{{ $lead->status }}">{{ ucfirst($lead->status) }}</span>
                    @endif
                </div>
                <p>{{ $isEdit ? 'Update the lead information below' : 'Enter basic details to create a new lead' }}</p>
            </div>

            <div class="gs-page-topbar-actions">
                @canany(['create leads', 'edit leads'])
					<button type="button" class="gs-btn gs-btn--outline" onclick="#" id="backbtn">
						 <i class="bi bi-arrow-left"></i> Back
					</button>
                    <button type="submit" name="status" value="draft" class="gs-btn gs-btn--outline">
                        <i class="bi bi-floppy-fill"></i> Save Draft
                    </button>
                    <button type="submit" name="status" value="active" class="gs-btn gs-btn--primary">
                        <i class="bi bi-check-lg"></i> {{ $isEdit ? 'Edit Lead' : 'Save Lead' }}
                    </button>
                @endcanany
                @if($isEdit)
                    @can('delete leads')
                        <button type="button" class="gs-btn gs-btn--danger" onclick="DeleteLead('{{$lead->id}}')" id="btnDeleteLead">
                            <i class="bi bi-trash-fill"></i> Delete Lead
                        </button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- ── Progress Steps ── --}}
        <div class="gs-steps">
            <div class="gs-step gs-step--active"><span class="gs-step-dot"></span>New Lead Created</div>
            <i class="bi bi-arrow-right gs-step-arrow"></i>
            <div class="gs-step"><span class="gs-step-dot"></span>LOA & Bills</div>
            <i class="bi bi-arrow-right gs-step-arrow"></i>
            <div class="gs-step"><span class="gs-step-dot"></span>Awaiting Proof</div>
            <i class="bi bi-arrow-right gs-step-arrow"></i>
            <div class="gs-step"><span class="gs-step-dot"></span>RFQ</div>
        </div>

        {{-- ── Validation Errors ── --}}
        @if($errors->any())
            <div class="gs-alert gs-alert--danger all-erros">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Main Grid ── --}}
        <div class="gs-common-grid gs-create-lead">

            {{-- ════ LEFT COLUMN ════ --}}
            <div class="gs-left-grid">

                {{-- Lead Details --}}
                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Lead Details</h5>
                    </div>
                    <div class="gs-panel-body">
                        <div class="gs-form-grid">

                            {{-- Company Name --}}
                            <div class="gs-field gs-field--full">
                                <label class="gs-label" for="company_name">Company Name <span class="gs-required">*</span></label>
                                <input type="text" class="gs-input @error('company_name') is-invalid @enderror"
                                    name="company_name" id="company_name" required
                                    placeholder="Bertshi UK Limited"
                                    value="{{ old('company_name', $lead->company_name ?? '') }}" />
                                @error('company_name')<span class="gs-field-error">{{ $message }}</span>@enderror
                            </div>

                            {{-- Phone --}}
                            <div class="gs-field">
                                <label class="gs-label" for="phone">Phone Number <span class="gs-required">*</span></label>
                                <input type="tel" class="gs-input @error('phone') is-invalid @enderror"
                                    name="phone" id="phone" required
                                    placeholder="+44 7911 123456"
                                    value="{{ old('phone', $lead->phone ?? '') }}" />
                                @error('phone')<span class="gs-field-error">{{ $message }}</span>@enderror
                            </div>

                            {{-- Annual Consumption --}}
                            <div class="gs-field">
                                <label class="gs-label" for="annual_consumption">Annual Consumption (kWh)</label>
                                <input type="text" class="gs-input" name="annual_consumption" id="annual_consumption"
                                    placeholder="450384 kWh"
                                    value="{{ old('annual_consumption', $lead->annual_consumption ?? '') }}" />
                            </div>

                            {{-- Decision Maker Name --}}
                            <div class="gs-field">
                                <label class="gs-label" for="decision_maker_name">Decision Maker Name</label>
                                <input type="text" class="gs-input" name="decision_maker_name" id="decision_maker_name"
                                    placeholder="Ryan Mitchell"
                                    value="{{ old('decision_maker_name', $lead->decision_maker_name ?? '') }}" />
                            </div>

                            {{-- MPAN Number --}}
                            <div class="gs-field">
                                <label class="gs-label" for="mpan">MPAN Number</label>
                                <input type="text" class="gs-input" name="mpan" id="mpan"
                                    placeholder="1580000045044"
                                    value="{{ old('mpan', $lead->mpan ?? '') }}" />
                            </div>

                        </div>

                    </div>
                </div>
				
				{{-- ── Energy Requirement Sub-section ── --}}
				 <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Energy Requirement</h5>
                    </div>
                    <div class="gs-panel-body">
						
						
							<div class="gs-form-grid cl-energy-grid">

								{{-- Energy Requirement with unit --}} 
								<div class="gs-field">
									<label class="gs-label" for="energy_requirement">Energy Requirement</label>
									<div class="cl-input-unit-wrap">
										<input type="text" class="gs-input cl-input-unit"
											name="energy_requirement" id="energy_requirement"
											placeholder="450384"
											value="{{ old('energy_requirement', $lead->energy_requirement ?? '') }}" />
										<select class="cl-unit-select" name="energy_unit">
											<option value="kWh" @selected(old('energy_unit', $lead->energy_unit ?? 'kWh') == 'kWh')>kWh</option>
											<option value="MWh" @selected(old('energy_unit', $lead->energy_unit ?? '') == 'MWh')>MWh</option>
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

				
                {{-- Lead Description --}}
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

            </div>

            {{-- ════ RIGHT COLUMN ════ --}}
            <div class="gs-right-grid">

                {{-- ── Activity Logs Panel (NEW) ── --}}
                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Activity Logs</h5>
                    </div>
                    <div class="gs-panel-body cl-activity-body">

                        <p class="cl-activity-label">Activity Logs</p>

                        <ul class="cl-activity-list">
                            @forelse($lead->activityLogs ?? [] as $log)
                            <li class="cl-activity-item">
                                <span class="cl-activity-dot"></span>
                                <span class="cl-activity-text">{{ $log->description }}</span>
                                <span class="cl-activity-time">{{ $log->created_at->diffForHumans() }}</span>
                            </li>
                            @empty
                            {{-- Demo rows --}}
                            <li class="cl-activity-item">
                                <span class="cl-activity-dot"></span>
                                <span class="cl-activity-text">New OPP Created</span>
                                <span class="cl-activity-time">00 sec Ago</span>
                            </li>
                            <li class="cl-activity-item">
                                <span class="cl-activity-dot"></span>
                                <span class="cl-activity-text">Call Attempt to Bertshi UK Limited</span>
                                <span class="cl-activity-time">20 min Ago</span>
                            </li>
                            <li class="cl-activity-item">
                                <span class="cl-activity-dot"></span>
                                <span class="cl-activity-text">Lead Created Manually</span>
                                <span class="cl-activity-time">2 hr ago</span>
                            </li>
                            <li class="cl-activity-item">
                                <span class="cl-activity-dot"></span>
                                <span class="cl-activity-text">Lead Created Manually</span>
                                <span class="cl-activity-time">2 hr ago</span>
                            </li>
                            <li class="cl-activity-item">
                                <span class="cl-activity-dot"></span>
                                <span class="cl-activity-text">Lead Created Manually</span>
                                <span class="cl-activity-time">2 hr ago</span>
                            </li>
                            @endforelse
                        </ul>

                    </div>
                </div>

                {{-- Lead Source --}}
                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Lead Source</h5>
                    </div>
                    <div class="gs-panel-body">
                        <div class="gs-form-grid gs-form-grid--full" style="gap:14px;">

                            <div class="gs-field">
                                <label class="gs-label" for="current_supplier">Current Supplier</label>
                                <input type="text" class="gs-input" name="current_supplier" id="current_supplier"
                                    placeholder="Supplier Name"
                                    value="{{ old('current_supplier', $lead->current_supplier ?? '') }}" />
                            </div>

                            <div class="gs-field">
                                <label class="gs-label" for="lead_status_id">Lead Status</label>
                                <select class="gs-select" name="lead_status_id" id="lead_status_id">
                                    @foreach($get_all_lead_status as $value)
                                        <option value="{{ $value['id'] }}"
                                            {{ old('lead_status_id', $lead->lead_status_id ?? '') == $value['id'] ? 'selected' : '' }}>
                                            {{ $value['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="gs-field">
                                <label class="gs-label" for="contract_end_date">Contract End Date</label>
                                <input type="date" class="gs-input" name="contract_end_date" id="contract_end_date"
                                    value="{{ old('contract_end_date', isset($lead->contract_end_date) ? $lead->contract_end_date->format('Y-m-d') : '') }}" />
                            </div>

                            <div class="gs-field">
                                <label class="gs-label" for="priority_status_id">Temperature</label>
                                <select class="gs-select" name="priority_status_id" id="priority_status_id">
                                    @foreach($get_all_priority_status as $value)
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
			</div>
        </div>

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
                        @forelse($lead->attachments ?? [] as $file)
                        <div class="cl-file-item">
                            <div class="cl-file-icon cl-file-icon--{{ strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)) }}">
                                <i class="bi bi-file-earmark-fill"></i>
                            </div>
                            <div class="cl-file-info">
                                <span class="cl-file-name">{{ $file->file_name }}</span>
                                <span class="cl-file-size">{{ $file->file_size }}</span>
                            </div>
                            <div class="cl-file-actions">
                                <button type="button" class="cl-file-btn" title="Preview"><i class="bi bi-eye"></i></button>
                                <button type="button" class="cl-file-btn" title="Delete"><i class="bi bi-trash"></i></button>
                                <button type="button" class="cl-file-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                            </div>
                        </div>
                        @empty
                        {{-- Demo files --}}
                        <div class="cl-file-item">
                            <div class="cl-file-icon cl-file-icon--pdf"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                            <div class="cl-file-info">
                                <span class="cl-file-name">Old_Bill.pdf</span>
                                <span class="cl-file-size">(430 KB)</span>
                            </div>
                            <div class="cl-file-actions">
                                <button type="button" class="cl-file-btn"><i class="bi bi-eye"></i></button>
                                <button type="button" class="cl-file-btn"><i class="bi bi-trash"></i></button>
                                <button type="button" class="cl-file-btn"><i class="bi bi-pencil"></i></button>
                            </div>
                        </div>
                        <div class="cl-file-item">
                            <div class="cl-file-icon cl-file-icon--jpg"><i class="bi bi-file-earmark-image-fill"></i></div>
                            <div class="cl-file-info">
                                <span class="cl-file-name">Site_Image.jpg</span>
                                <span class="cl-file-size">(1.2 MB)</span>
                            </div>
                            <div class="cl-file-actions">
                                <button type="button" class="cl-file-btn"><i class="bi bi-eye"></i></button>
                                <button type="button" class="cl-file-btn"><i class="bi bi-trash"></i></button>
                                <button type="button" class="cl-file-btn"><i class="bi bi-pencil"></i></button>
                            </div>
                        </div>
                        <div class="cl-file-item">
                            <div class="cl-file-icon cl-file-icon--pdf"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                            <div class="cl-file-info">
                                <span class="cl-file-name">Quotation.pdf</span>
                                <span class="cl-file-size">(430 KB)</span>
                            </div>
                            <div class="cl-file-actions">
                                <button type="button" class="cl-file-btn"><i class="bi bi-eye"></i></button>
                                <button type="button" class="cl-file-btn"><i class="bi bi-trash"></i></button>
                                <button type="button" class="cl-file-btn"><i class="bi bi-pencil"></i></button>
                            </div>
                        </div>
                        @endforelse
                    </div>

                </div>
            </div>

            {{-- ── Notes Tab ── --}}
            <div class="cl-tab-pane" id="tab-notes">
                <div class="cl-notes-body">
                    <div class="gs-field">
                        <label class="gs-label" for="lead_notes">Add Note</label>
                        <textarea class="gs-textarea" name="lead_notes" id="lead_notes" rows="4"
                            placeholder="Write an internal note...">{{ old('lead_notes', $lead->notes ?? '') }}</textarea>
                    </div>
                    <div class="cl-notes-footer">
                        <button type="button" class="gs-btn gs-btn--primary gs-btn--sm">
                            <i class="bi bi-plus-lg"></i> Add Note
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Contact Timeline Tab ── --}}
            <div class="cl-tab-pane" id="tab-timeline">
                <div class="cl-timeline-body">
                    @forelse($lead->contactTimeline ?? [] as $event)
                    <div class="cl-timeline-item">
                        <div class="cl-timeline-icon cl-timeline-icon--{{ $event->type ?? 'default' }}">
                            <i class="bi bi-{{ $event->icon ?? 'circle-fill' }}"></i>
                        </div>
                        <div class="cl-timeline-content">
                            <p class="cl-timeline-title">{{ $event->title }}</p>
                            <p class="cl-timeline-desc">{{ $event->description }}</p>
                        </div>
                        <span class="cl-timeline-time">{{ $event->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                    {{-- Demo timeline --}}
                    <div class="cl-timeline-item">
                        <div class="cl-timeline-icon cl-timeline-icon--call">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div class="cl-timeline-content">
                            <p class="cl-timeline-title">Call Attempt</p>
                            <p class="cl-timeline-desc">Called Bertshi UK Limited — no answer</p>
                        </div>
                        <span class="cl-timeline-time">20 min ago</span>
                    </div>
                    <div class="cl-timeline-item">
                        <div class="cl-timeline-icon cl-timeline-icon--email">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div class="cl-timeline-content">
                            <p class="cl-timeline-title">Email Sent</p>
                            <p class="cl-timeline-desc">Sent proposal email to UK Limited@gmail.com</p>
                        </div>
                        <span class="cl-timeline-time">2 hr ago</span>
                    </div>
                    <div class="cl-timeline-item">
                        <div class="cl-timeline-icon cl-timeline-icon--created">
                            <i class="bi bi-plus-circle-fill"></i>
                        </div>
                        <div class="cl-timeline-content">
                            <p class="cl-timeline-title">Lead Created</p>
                            <p class="cl-timeline-desc">Lead created manually by admin</p>
                        </div>
                        <span class="cl-timeline-time">2 hr ago</span>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>{{-- end cl-tabs-panel --}}

    </form>

    {{-- ── Delete Confirmation Form ── --}}
    @if($isEdit)
        @can('delete leads')
            <form method="POST" action="{{ route('leads.delete', $lead) }}" id="DeleteLeadForm-{{$lead->id}}" style="display:none;">
                @csrf
                <input type="hidden" name="_action" value="delete">
            </form>
        @endcan
    @endif

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    // ── CKEditor ──
    if (document.querySelector('#description')) {
        ClassicEditor.create(document.querySelector('#description'), {
            toolbar: ['heading','|','bold','italic','|','bulletedList','numberedList','|','link','blockQuote','|','undo','redo'],
            placeholder: 'Describe the lead requirements, goals, notes…'
        }).catch(error => console.error(error));
    }

    // ── Phone formatter ──
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '');
            if (x.startsWith('44')) x = x.substring(2);
            if (x.startsWith('0'))  x = x.substring(1);
            let formatted = '+44 ';
            if (x.length > 0) formatted += x.substring(0, 4);
            if (x.length >= 5) formatted += ' ' + x.substring(4, 7);
            if (x.length >= 8) formatted += ' ' + x.substring(7, 10);
            e.target.value = formatted.trim();
        });
    }

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
    if (zone && input) {
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('cl-upload-zone--over');
        });
        zone.addEventListener('dragleave', function () {
            zone.classList.remove('cl-upload-zone--over');
        });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('cl-upload-zone--over');
            // handle files via DataTransfer
        });
    }

});
</script>
@endpush
