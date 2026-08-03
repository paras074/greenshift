@extends('layouts.app')
@section('title', isset($lead) ? 'View Lead — ' . $lead->company_name : 'View Lead')

@section('content')

<div class="gs-dash-wrap view-page">

  {{-- ── TOP BAR ── --}}
  <div class="gs-page-topbar vl-topbar">
    <div class="gs-page-topbar-left">
      <div class="vl-profile">
        <div class="gs-user-avatar vl-avatar-lg">
          <img src="/images/site-logo.png">
        </div>
        <div class="vl-profile-info">
          <h2>{{ $lead->company_name ?? 'Company Name' }}</h2>
          <p>{{ $lead->phone ?? 'Phone' }}</p>
        </div>
      </div>
    </div>
    <div class="gs-page-topbar-actions">
      <a href="{{ route('leads.index') }}" class="gs-btn gs-btn--outline">
        <i class="bi bi-arrow-left"></i>
      </a>
      @can('edit leads')
        <a href="{{ route('leads.edit', $lead->id) }}" class="gs-btn gs-btn--primary">
          Edit Lead
        </a>
      @endcan
    </div>
  </div>

  {{-- ── MAIN GRID ── --}}
  <div class="gs-common-grid">

    {{-- ════════════════ LEFT COLUMN ════════════════ --}}
    <div class="gs-left-grid">

      {{-- LEAD INFORMATION --}}
      <div class="gs-panel">
        <div class="gs-panel-header">
          <h5 class="gs-panel-title">Lead Information</h5>
        </div>
        <div class="gs-panel-body">
          <div class="vl-info-grid">

            {{-- Company Details --}}
            <div class="vl-info-col">
              <div class="vl-section-title">{{ $lead->company_name ?? 'Company Name' }} <small>{{ $lead->email ?? '-' }}</small></div>


              <div class="vl-field-row">
                <span class="vl-field-label">
                  <i class="bi bi-telephone-fill vl-field-icon"></i>Mobile
                </span>
                <span class="vl-field-value">{{ $lead->phone ?? '-' }}</span>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">
                  <i class="bi bi-geo-alt-fill vl-field-icon"></i>Address
                </span>
                <span class="vl-field-value">{{ $lead->address ?? '-' }}</span>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">
                  <i class="bi bi-person"></i>Assigned
                </span>
                <span class="vl-field-value vl-assigned-link">
                    @if($assignedUsers && $assignedUsers->isNotEmpty())
                        {{ $assignedUsers->filter()->pluck('name')->implode(', ') }}
                    @else
                        Unassigned
                    @endif
                </span>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">
					      <i class="bi bi-thermometer-half vl-field-icon"></i>Temperature
                </span>
                <span class="vl-field-value">
                  {{$lead->priorityStatus->name  ?? '--'}}
                </span>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">
					      <i class="bi bi-lightning-charge-fill vl-field-icon"></i> {{ $lead->energy_type == 'gas' ? 'MPRN Number' : 'MPAN Number' }}
                </span>
                <span class="vl-field-value">
                  {{$lead->mpan  ?? '--'}}
                </span>
              </div>
            </div>

            {{-- Energy & Requirement --}}
            <div class="vl-info-col">
              <div class="vl-section-title">
                Energy &amp; Requirement
                <small>Required Load</small>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">Required Load</span>
                <span class="vl-field-value">
                  @if(!empty($lead->annual_consumption))
                      {{ $lead->annual_consumption }} kWh
                  @else
                      --
                  @endif
              </span>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">Budget Range</span>
                <span class="vl-field-value">
                    {{ !empty($lead->budget_range) ? '£' . $lead->budget_range : '--' }}
                </span>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">Site Type</span>
                <span class="vl-field-value">{{ $lead->roof_site_type ?? '--' }}</span>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">Roof Type</span>
                <span class="vl-field-value">{{ $lead->roof_site_type ?? '--' }}</span>
              </div>

              <div class="vl-field-row">
                <span class="vl-field-label">Expected Start Date</span>
                <span class="vl-field-value">
                  --
                </span>
              </div>
            </div>
          </div>{{-- end vl-info-grid --}}
          
           @if(!empty($lead->others['address']) && is_array($lead->others['address']))
                <div class="vl-field-row justify-content-start">
                    <span class="vl-field-label">
                      <i class="bi bi-geo-alt-fill vl-field-icon"></i>Site Address(s) -
                    </span>
                    @foreach($lead->others['address'] as $index => $extraAddress)
                        <span class="vl-field-value">{{ implode(', ', array_filter([$extraAddress['address'], $extraAddress['city'], $extraAddress['state'], $extraAddress['postcode']])) }}</span>
                    @endforeach
                </div>
            @endif

          @if(!empty($lead->description))
            <div class="vl-description">
                <strong>Description:</strong> 
                <div class="description-content">
                    {!! $lead->description !!}
                </div>
            </div>
          @endif
        </div>
      </div>{{-- end Lead Information --}}


      {{-- LOA & INVOICES --}}
      <div class="gs-panel">
        <div class="gs-panel-header">
          <h5 class="gs-panel-title">LOA &amp; Suppliers</h5>
            <div class="d-flex gap-1"><span>LOA Status :</span>
                @php
                  if (!empty($lead->others['signable_status'])) {
                      $statusText = ucfirst($lead->others['signable_status']);
                      $statusClass = $lead->others['signable_status'] === 'signed' ? 'active' : 'pending';
                  } 
                  elseif (!empty($lead->others['loa_mails_sent'])) {
                      $statusText = 'Sent';
                      $statusClass = 'pending';
                  } 
                  elseif (!empty($lead->others['loa_generated'])) {
                      $statusText = 'Generated';
                      $statusClass = 'pending';
                  }
                  else {
                      $statusText = '';
                      $statusClass = '';
                  }
                @endphp
            <span class="gs-status gs-status--{{ $statusClass }}">{{ $statusText }}</span>
            </div>
        </div>
        <div class="gs-panel-body" style="padding:0;">
            
            <div class="vl-table-scroll">
                @php
                    // Check if any quote has 'is_selected' == 1 inside its 'others' array
                    $hasExplicitSelection = $quotes->contains(function($quote) {
                        return isset($quote->others['is_selected']) && $quote->others['is_selected'] == 1;
                    });
                @endphp
            
                <table class="gs-table" id="loaTable">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Quote Amount</th>
                            <th>Phone</th>
                            <th>Warranty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotes as $quote)
                            @php
                                $assignedSupplierId = data_get($lead, 'others.supplier');
                                $hasLeadAssignment = !empty($assignedSupplierId);
                        
                                if ($hasLeadAssignment) {
                                    $isSelected = ($assignedSupplierId == $quote->id);
                                } else {
                                    $isQuoteFlagged = isset($quote->others['is_selected']) && $quote->others['is_selected'] == 1;
                                    $hasAnyFlaggedQuote = $quotes->contains(function($q) {
                                        return isset($q->others['is_selected']) && $q->others['is_selected'] == 1;
                                    });
                                    $isSelected = $isQuoteFlagged || (!$hasAnyFlaggedQuote && $loop->first);
                                }
                            @endphp
                            <tr class="rfq-row-item {{ $isSelected ? 'selected-row table-active' : '' }}" 
                                id="quote-row-{{ $quote->id }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="vendor-check rfq-select-trigger {{ $isSelected ? 'active' : '' }}" 
                                             style="cursor: pointer;"
                                             data-quote-id="{{ $quote->id }}"
                                             data-vendor-name="{{ $quote->supplier_name }}"
                                             data-price="{{ $quote->price ? '£' . number_format($quote->price, 0) : '-' }}">
                                            <i class="bi {{ $isSelected ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}" style="font-size: 1.1rem;"></i>
                                        </div>
                                        <span class="vendor-name fw-bold {{ $isSelected ? 'text-primary highlight' : '' }}">
                                            {{ $quote->supplier_name }}
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $quote->price ? '£ ' . number_format($quote->price, 0) : '-' }}</td>
                                <td>{{ $quote->phone ?? '-' }}</td>
                                <td>
                                    @if(!empty($quote->warranty))
                                        <span class="gs-status gs-status--success text-capitalize">{{ $quote->warranty }}</span>
                                    @else
                                        <span class="gs-status gs-status--pending">No Warranty</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @can('edit rfq')
                                            <a href="{{ route('rfq_quotes.view', $lead->id) }}" class="gs-btn gs-btn--sm gs-btn--outline text-decoration-none">
                                                Manage Quote
                                            </a>
                                        @endcan
                                        @can('delete rfq')    
                                            <form action="{{ route('rfq_quotes.destroy', $quote->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this quote?');" style="display: inline-block; margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn text-danger p-0 border-0 ms-1" style="background: none; font-size: 1.1rem; line-height: 1;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No quotes found for this lead.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
      </div>{{-- end LOA & Invoices --}}


      {{-- ACTIVITY LOG --}}
      <div class="gs-panel">
        <div class="gs-panel-header vl-activity-header">
          <h5 class="gs-panel-title">Activity Log (Tasks)</h5>
          <div class="vl-act-filters">
            <button class="gs-btn gs-btn--sm gs-btn--outline vl-act-btn--active" data-filter="all">
			  <i class="bi bi-funnel-fill"></i> All Activity
			</button>

			<button class="gs-btn gs-btn--sm gs-btn--outline" data-filter="email">
			  <i class="bi bi-envelope-fill"></i> Email
			</button>

			<button class="gs-btn gs-btn--sm gs-btn--outline" data-filter="call">
			  <i class="bi bi-telephone-fill"></i> Calls
			</button>
          </div>
        </div>
        <div class="gs-panel-body" style="padding:0;">

          <div class="vl-table-scroll">
            <table class="gs-table" id="activityTable">
              <thead>
                <tr>
                  <th style="width:40px;">
                    <input type="checkbox" class="gs-table-checkbox" id="checkAll">
                  </th>
                  <th>Task</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Assigned</th>
                  <th>Note</th>
                  <th>Type</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tasks as $task)
                <tr>
                  <td data-type="{{ $task->type ?? 'unknown' }}"><input type="checkbox" class="gs-table-checkbox row-check"></td>
                  <td>
                    <div class="vl-task-cell">
                      <div class="gs-user-avatar" style="width:32px;height:32px;font-size:13px;">
                        {{ strtoupper(substr($task->assignedBy->name ?? 'R', 0, 1)) }}
                      </div>
                      <span>{{ $task->title ?? 'Follow-up Call' }}</span>
                    </div>
                  </td>
                  <td class="vl-date-cell">
                    {{ isset($task->created_at) ? \Carbon\Carbon::parse($task->created_at)->format('d M Y') : '—' }}<br>
                    <span>{{ isset($task->created_at) ? \Carbon\Carbon::parse($task->created_at)->format('h:i A') : '' }}</span>
                  </td>
                  <td>
                    <span class="gs-status gs-status--{{ strtolower($task->status ?? 'pending') }}">
                      {{ $task->status ?? 'Pending' }}
                    </span>
                  </td>
                  <td>{{ $task->assignedBy->name ?? '—' }}</td>
                  @if($task->latestNote)  
                    <td>
                      {{ $task->latestNote ? Str::limit($task->latestNote->data, 50) : '—' }}
                    </td>
                  @else
                    <td>
                      —
                    </td>
                  @endif
                  <td>
                    <a class="gs-edit-btn" href="javascript:void(0);">
                        @switch($task->type)
                            @case('call')
                                <i class="bi bi-telephone"></i>
                                @break
                                
                            @case('email')
                                <i class="bi bi-envelope"></i>
                                @break
                                
                            @case('site-visit')
                                <i class="bi bi-geo-alt"></i>
                                @break
                                
                            @case('send-proposal')
                                <i class="bi bi-file-earmark-text"></i>
                                @break
                                
                            @case('follow-up')
                                <i class="bi bi-arrow-repeat"></i>
                                @break
                                
                            @case('meeting')
                                <i class="bi bi-people"></i>
                                @break
                                
                            @default
                                {{-- Fallback icon for generic tasks --}}
                                <i class="bi bi-journal-check"></i>
                        @endswitch
                    </a>
                </td>
                  <td><a href="{{ route('tasks.edit', $task->id) }}" class="gs-edit-btn"><i class="bi bi-pencil-fill"></i></a></td>
                </tr>
                @empty
                <tr class="no-data-row">
                  <td colspan="8" class="text-center text-muted small py-3">
                    No tasks found for this lead.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </div>{{-- end Activity Log --}}

    </div>{{-- end gs-left-grid --}}


    {{-- ════════════════ RIGHT COLUMN ════════════════ --}}
    <div class="gs-right-grid">

      {{-- STAGE --}}
      <div class="gs-panel">
        <div class="gs-panel-header">
          <h5 class="gs-panel-title">Stage</h5>
        </div>
        <div class="gs-panel-body">
          @php
          $stages = collect(get_all_lead_status())
            ->where('status', 'active') // optional if needed
            ->sortBy('sort_order')
            ->values();
          $currentStage = $lead->leadStatus->name ?? null;
          $currentIndex = $stages->pluck('name')->search($currentStage);

          if ($currentIndex === false) $currentIndex = 0;
          @endphp

          <div class="vl-stage">
            @foreach($stages as $i => $stage)
            <div class="vl-stage-item {{ $i <= $currentIndex ? 'vl-stage-item--done' : '' }}">
              <div class="vl-stage-circle {{ $i == $currentIndex ? 'vl-stage-circle--current' : ($i < $currentIndex ? 'vl-stage-circle--done' : '') }}">
                @if($i < $currentIndex)
                 <i class="bi bi-check-lg"></i>
                @else
                  {{ $i + 1 }}
                @endif
              </div>
              <span>{{ $stage['name'] }}</span>
            </div>
            @endforeach
          </div>
        </div>
      </div>{{-- end Stage --}}


      {{-- NEW TASK --}}
      <div class="gs-panel">
        <div class="gs-panel-header">
          <h5 class="gs-panel-title">New Task</h5>
        </div>
        <div class="gs-panel-body vl-side-panel-body">
          <div class="gs-field">
            <select class="gs-select" name="task_type" id="task_type">
              <option value="">Add Task</option>
              @foreach(taskTypes() as $key => $label)
                  <option value="{{ $key }}" {{ old('type', $task->type ?? '') == $key ? 'selected' : '' }}>
                      {{ $label }}
                  </option>
              @endforeach
            </select>
          </div>

          <a type="button" id="addTaskBtn" data-url="{{ route('tasks.create', ['lead_id' => $lead->id]) }}" class="gs-btn gs-btn--primary" style="width:100%;justify-content:center;"  href="{{ route('tasks.create', ['lead_id' => $lead->id]) }}">
            Add Task
          </a>
        </div>
      </div>{{-- end New Task --}}


      {{-- FOLLOW UP --}}
      <div class="gs-panel">
        <div class="gs-panel-header">
          <h5 class="gs-panel-title">
            Follow Up
          </h5>
        </div>
        <div class="gs-panel-body vl-side-panel-body">
          <div class="gs-field">
            <input type="date" class="gs-input" name="follow_up_date" value="">
          </div>

          <div class="vl-reminder-row">
            <span class="gs-label" style="text-transform:none;letter-spacing:0;">Reminder</span>
            <label class="vl-switch">
              <input type="checkbox" checked>
              <span class="vl-slider"></span>
            </label>
          </div>

          <button type="button" class="gs-btn gs-btn--teal" id="schedule-btn" onclick="ScheduleFollowUp('{{ $lead->id }}')" style="width:100%;justify-content:center;">
            <i class="bi bi-plus-lg"></i> Schedule Follow-Up
          </button>
          @if($hasScheduledTask)
              <div class="gs-alert gs-alert--warning" style="display:flex;">
                  <div style="display: flex; flex-direction: column;">
                      <div>
                          <i class="bi bi-exclamation-triangle-fill"></i> 
                          <strong>Follow Up Already Scheduled : </strong> 
                          {{ $scheduledTask->end_date->format('d M, Y') }}
                          @php
                              $names = $scheduledTask->assignedUsers()->pluck('name')->implode(', ');
                          @endphp
                          @if($names)
                              <span style="margin-left: 5px; opacity: 0.9;">
                                  [{{ $names }}]
                              </span>
                          @endif
                      </div>
                      @if($scheduledTask->reminder)
                          <small style="margin-top: 6px; opacity: 0.8;">
                              <i class="bi bi-bell-fill"></i> Reminder is active
                          </small>
                      @endif
                  </div>
              </div>
          @endif
        </div>
      </div>{{-- end Follow Up --}}


      {{-- TASKS / NOTES --}}
      <div class="gs-panel">
        <div class="gs-panel-header">
          <h5 class="gs-panel-title">Notes</h5>
        </div>
        <div class="gs-panel-body vl-side-panel-body">

          <textarea id="noteData"  class="gs-textarea" style="min-height:72px;" placeholder="Add internal note for your team..."></textarea>
            <button type="button" onclick="saveNote()" class="gs-btn gs-btn--primary" style="width:100%;justify-content:center;">
              Add Note
            </button>
          <div id="notesFeed" class="vl-task-notes mt-1">
              <p class="text-muted text-center small">Loading notes...</p>
          </div>
        </div>
      </div>{{-- end Tasks --}}
	  
	  
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
				<!--	<i class="bi bi-envelope-fill"></i> Mail-->
				<!--</button>-->
				<!--<button type="button" class="gs-contact-btn gs-contact-btn--whatsapp">-->
				<!--	<i class="bi bi-whatsapp"></i> WhatsApp-->
				<!--</button>-->
			</div>
		</div>
	</div>


  
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
                    <iframe data-phone="{{ $lead->phone }}" data-src="{{ $iframeUrl }}" title="Dialpad" id="dialpadFrame" allow="microphone; speaker-selection; autoplay; camera; display-capture; hid" sandbox="allow-popups allow-scripts allow-same-origin allow-forms" frameborder="0" style="width:100%;height:520px;"></iframe>
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

                        // Clean up the number value: Trim spaces and remove common non-numeric dividers (except +)
                        let phoneNumber = iframe.getAttribute('data-phone');
                        //phoneNumber = "8629061873";

                        // Drop execution immediately if string resolves as empty
                        if (!phoneNumber) {
                            console.log("Dialpad Aborted: Phone number value string input is currently empty.");
                            return;
                        }

                        console.log("Target phone localized and cleaned: " . phoneNumber);

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
                    });
                });
              </script>
						</div>
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
	
	
	
	
	
	
	
    </div>{{-- end gs-right-grid --}}

  </div>{{-- end gs-common-grid --}}

</div>{{-- end gs-dash-wrap --}}

@push('scripts')
 <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function () {
  const leadId = {{ $lead->id }};
  // ── Check All checkbox ──
  const checkAll = document.getElementById('checkAll');
  if (checkAll) {
    checkAll.addEventListener('change', function () {
      document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });
  }

  // ── Activity filter buttons ──
  document.querySelectorAll('.vl-act-filters .gs-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.vl-act-filters .gs-btn').forEach(b => b.classList.remove('vl-act-btn--active'));
      this.classList.add('vl-act-btn--active');
    });
  });

  // 🔥 COMMON DATATABLE CONFIG (INDEX JAISA)
  function getDataTableConfig() {
    return {
      destroy: true,
      pageLength: 5,
      lengthMenu: [[5, 10, 25], ['5', '10', '25']],
      ordering: true,
      order: [[1, 'asc']],
      columnDefs: [
        { orderable: false, targets: [0, -1] } // first + last column
      ],
      scrollX: true,
      autoWidth: false,
      searching: true,
      language: {
        lengthMenu: 'Rows per page: _MENU_',
        info: 'Showing _START_–_END_ of _TOTAL_ entries',
        infoEmpty: 'No data found',
        zeroRecords: 'No matching data found',
        paginate: {
          previous: '<i class="bi bi-chevron-left"></i>',
          next: '<i class="bi bi-chevron-right"></i>'
        }
      }
    };
  }

  // ── DataTables Init (DONO TABLES) ──
  if (typeof $ !== 'undefined' && $.fn.DataTable) {
    if ($('#activityTable tbody tr.no-data-row').length === 0) {
      // LOA TABLE
      $('#loaTable').DataTable({
        ...getDataTableConfig(),
        columnDefs: [
          { orderable: false, targets: [4] } // Action column disable
        ]
      });

      // ACTIVITY TABLE
      $('#activityTable').DataTable({
        ...getDataTableConfig(),
        columnDefs: [
          { orderable: false, targets: [0, 6, 7] } // checkbox + icons
        ]
      });

      const activityTable = $('#activityTable').DataTable();
      $('.vl-act-filters .gs-btn').on('click', function() {
        
        const filterValue = $(this).data('filter');
        const searchTerm = (filterValue === 'all') ? '' : filterValue;
        $.fn.dataTable.ext.search.pop();

        if (filterValue !== 'all') {
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    const rowNode = activityTable.row(dataIndex).node();
                    const type = $(rowNode).find('td:first-child').data('type');                    
                    return type === filterValue;
                }
            );
        }
        activityTable.draw();
    });


      
    }
  }

    // 1. Function to Fetch All Notes
      async function fetchNotes() {
          try {
              const response = await fetch(`/note/all?lead_id=${leadId}`);
              const result = await response.json();

              if (result.success) {
                  renderNotes(result.data);
              }
          } catch (error) {
              console.error('Error fetching notes:', error);
          }
      } 

      // 2. Function to Render Notes HTML
      function renderNotes(notes) {
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
          if (!data) return alert("Please type a note.");

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

        window.CallNoteAdd = async function() {
          const data = document.getElementById('callNoteInput').value;
          if (!data) return alert("Please type a note.");

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
      };

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

                setBtnState(btn, 'Failed to send', 'error');

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

        btn.disabled = (state === 'loading');

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

      // Initial load
      fetchNotes();
});
$(document).ready(function() {
    $('#task_type').on('change', function() {
        const typeValue = $(this).val();
        const $btn = $('#addTaskBtn');
        const baseUrl = $btn.data('url');
        if (typeValue) {
            const newUrl = baseUrl + '&type=' + typeValue;
            $btn.attr('href', newUrl);
        } else {
            $btn.attr('href', baseUrl);
        }
    });
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
	</script>
@endpush

@endsection
