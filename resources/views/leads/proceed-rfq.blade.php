@extends('layouts.app')
@section('title', isset($lead) ? 'Proceed To RFQ — ' . $lead->company_name : 'Proceed To RFQ')

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

  <div class="gs-common-grid">
    <div class="gs-left-grid">
        <div class="gs-panel">
            <div class="gs-panel-header">
                <h5 class="gs-panel-title">Details</h5>
            </div>
            <div class="gs-panel-body">
                
                 <div class="vl-info-grid">

                    {{-- Company Details --}}
                    <div class="vl-info-col">
                        <div class="vl-section-title">{{ $lead->company_name ?? 'Company Name' }} <small>{{ $lead->email ?? '-' }}</small></div>
        
                        <div class="vl-field-row">
                            <span class="vl-field-label"><i class="bi bi-lightning-charge-fill vl-field-icon"></i> Required Load</span>
                                <span class="vl-field-value">
                                  @if(!empty($lead->annual_consumption))
                                    {{ $lead->annual_consumption }} kWh
                                  @else
                                      --
                                  @endif
                            </span>
                        </div>
                        
                        
                        
                      <div class="vl-field-row">
                        <span class="vl-field-label">
                           <i class="bi bi-thermometer-half vl-field-icon"></i> Temperature
                        </span>
                        <span class="vl-field-value"> {{$lead->priorityStatus->name  ?? '--'}}</span>
                      </div>
                    </div>
        
                    {{-- Energy & Requirement --}}
                    <div class="vl-info-col">
                      <div class="vl-section-title"> <span class="opacity-0">
                        Energy &amp; Requirement
                        <small>Required Load</small></span>
                      </div>
        
                        <div class="vl-field-row">
                            <span class="vl-field-label"><i class="bi bi-coin"></i> Budget Range</span>
                            <span class="vl-field-value">
                                {{ !empty($lead->budget_range) ? '£' . $lead->budget_range : '--' }}
                            </span>
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
                    </div>
                  </div>
            </div>
        </div>
        
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


    </div>
    
    <div class="gs-right-grid">
        <div class="gs-panel gs-discussion-panel">
            <div class="gs-panel-header">
                <h5 class="gs-panel-title">Team Discussion</h5>
            </div>
    
            <div class="panel-body">
                <div id="notesFeed" class="vl-task-notes mt-1 p-3">
                    <p class="text-muted text-center small p-3">Loading notes...</p>   
                </div>
            </div>
        </div>
        
        <div class="gs-panel">
            <div class="gs-panel-header">
                <h5 class="gs-panel-title">Approval Note</h5>
            </div>
    
            <div class="gs-panel-body">
                
                <div class="approval-item active">Budget validated</div>
                <div class="approval-item active">Site Address details correct</div>
                <div class="approval-item {{ ($lead->others['loa_verified'] ?? null) == 1 ? 'active' : '' }}">
                    LOA verified
                </div>
                <hr>
                <div class="gs-field gs-field--full mt-4">
                    <form action="{{ route('leads.proceed-to-rfq') }}" method="POST" class="w-100">
                        @csrf
                        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                        
                        <div class="mb-3">
                            <textarea name="rfq_admin_note" class="gs-textarea" rows="3" placeholder="Everything Looks Fine proceed to RFQ..."></textarea>
                        </div>
                        
                        <button type="submit" name="action" value="approve" class="gs-btn gs-btn--primary d-flex justify-content-center align-items-center gap-2 w-100">
                            Approve & Proceed <i class="bi bi-arrow-right"></i>
                        </button>
                        
                        <button type="submit" name="action" value="lost" class="gs-btn gs-btn--danger d-flex justify-content-center align-items-center gap-2 w-100 mt-2">
                            Mark Lost <i class="bi bi-x-circle"></i>
                        </button>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const leadId = {{ $lead->id }};
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
                feed.innerHTML = '<p class="text-muted text-center small p-3">Nothing here</p>';
                return;
            }
        
            notes.forEach((note, index) => {
                const date = new Date(note.created_at).toLocaleDateString('en-GB', {
                    day: '2-digit', month: 'short', year: 'numeric'
                });
        
                const phoneIcon = (note.others && note.others === 'call') 
                    ? '<i class="bi bi-telephone ms-1"></i>' 
                    : '';
                const initial = note.user.name.charAt(0).toUpperCase();
                
                const borderClass = index === 0 ? '' : 'border-top pt-3';
                
                feed.innerHTML += `
                    <div class="d-flex mb-0 ${borderClass}">
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
});
</script>
@endpush

@endsection
