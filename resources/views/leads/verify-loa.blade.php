@extends('layouts.app')
@section('title', isset($lead) ? 'Verify LOA — ' . $lead->company_name : 'Verify LOA')

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
                <h5 class="gs-panel-title">LOA Information</h5>
            </div>
            <div class="gs-panel-body">
                <div class="gs-form-grid">
                    <div class="gs-field gs-field--full">
                        <label class="gs-label" for="company_name">Company Name </label>
                        <div type="text" class="gs-input ">{{ $lead->company_name ?? 'Company Name' }}</div>
                    </div>

                    <div class="gs-field">
                        <label class="gs-label">Phone Number</label>
                        <div type="text" class="gs-input ">{{ $lead->phone ?? '' }}</div>
                    </div>

                    <div class="gs-field">
                        <label class="gs-label">Email Address <span class="gs-required">*</span></label>
                        <div type="text" class="gs-input ">{{ $lead->email ?? '' }}</div>
                    </div>

                    {{-- Annual Consumption --}}
                    <div class="gs-field">
                        <label class="gs-label">Annual Consumption (kWh)</label>
                        <div type="text" class="gs-input ">{{ $lead->annual_consumption ?? '' }}</div>
                    </div>

                    <div class="gs-field">
                        <label class="gs-label">Decision Maker Name</label>
                        <div type="text" class="gs-input ">{{ $lead->decision_maker_name ?? '' }}</div>
                    </div>

                    <div class="gs-field">
                        <label class="gs-label">MPAN/MPRN Number</label>
                        <div class="cl-input-unit-wrap">
                            <select class="cl-unit-select" name="mpan_unit" style="min-width: 65px;">
                                @if($lead->energy_type == 'electricity')
                                    <option value="mpan" selected>MPAN</option>
                                @elseif($lead->energy_type == 'gas')
                                    <option value="mprn" selected>MPRN</option>
                                @endif   
                            </select>
                            <div type="text" class="gs-input ">{{ $lead->mpan ?? '' }}</div>
                        </div>
                    </div>

                            {{-- AQ --}}
                    <div class="gs-field">
                        <label class="gs-label" for="aq">AQ</label>
                        <div type="text" class="gs-input ">{{ $lead->aq ?? '' }}</div>
                    </div>

                    <div class="gs-field">
                        <label class="gs-label" for="reg_number">Registration Number</label>
                        <div type="text" class="gs-input ">{{ $lead->reg_number ?? '' }}</div>
                    </div>
                    
                    <div class="gs-field">
                        <label class="gs-label">Decision Maker Name</label>
                        <div type="text" class="gs-input ">{{ $lead->decision_maker_name ?? '' }}</div>
                    </div>
                    
                    <div class="gs-field">
                        <label class="gs-label">Decision Maker designation</label>
                        <div type="text" class="gs-input ">{{ $lead->others['decision_maker_designation'] ?? '' }}</div>
                    </div>

                    <div class="gs-field gs-field--full">
                        <label class="gs-label" for="company_name">Company Address </label>
                        <div type="text" class="gs-input ">{{ implode(', ', array_filter([$lead->address, $lead->city, $lead->state, $lead->postcode])) }}</div>
                    </div>
                    @if(!empty($lead->others['address']) && is_array($lead->others['address']))
                        <div class="gs-field gs-field--full">
                        <label class="gs-label" for="company_name">Site Address </label>
                        @foreach($lead->others['address'] as $index => $extraAddress)
                            <div type="text" class="gs-input ">{{ implode(', ', array_filter([$extraAddress['address'], $extraAddress['city'], $extraAddress['state'], $extraAddress['postcode']])) }}</div>
                        @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>


        <div class="gs-panel">
            <div class="gs-panel-header">
                <h5 class="gs-panel-title">Signed LOA Attachment</h5>
            </div>
            <div class="gs-panel-body">
               @foreach($lead->attachments as $attachment)
                @php 
                    $others = is_array($attachment->others) ? $attachment->others : json_decode($attachment->others, true); 
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
                @if(isset($others['is_signed_loa']) && $others['is_signed_loa'] == 1)
                    <div class="cl-file-item p-0 no-hover-bg border-0">
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
                        </div>
                    </div>
                @endif
            @endforeach
            
            @php
                $leadOthers = is_array($lead->others) ? $lead->others : json_decode($lead->others, true);
            @endphp
    
            @if(isset($leadOthers['loa_verified']) && $leadOthers['loa_verified'] == 1)
                <div class="mt-4 pt-3 border-top border-2 border-success" style="border-top-color: #3d9082 !important;">
                    <div class="d-flex align-items-center gap-2 text-success fw-semibold mb-1" style="color: #2e7d32;">
                        <i class="bi bi-check-circle-fill"></i> LOA Already Verified
                    </div>
                    <div class="text-muted small ps-4">
                        <strong>Verified At:</strong> {{ \Carbon\Carbon::parse($leadOthers['loa_verified_at'])->format('d M Y, h:i A') }}
                    </div>
                    @if(!empty($leadOthers['loa_admin_message']))
                        <div class="mt-2 p-2 rounded bg-light border text-secondary text-wrap" style="font-style: italic; font-size: 13px;">
                            "{{ $leadOthers['loa_admin_message'] }}"
                        </div>
                    @endif
                </div>
            @else
                <div class="gs-field gs-field--full mt-4">
                    <form action="{{ route('leads.verify-lead-loa') }}" method="POST" class="w-100">
                        @csrf
                        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                        
                        <!--<div class="mb-3">-->
                        <!--    <textarea name="loa_notes" class="gs-textarea" rows="3" placeholder="Add verification notes or remarks here..."></textarea>-->
                        <!--</div>-->
                        
                        <button type="submit" class="gs-btn gs-btn--primary d-flex justify-content-center align-items-center gap-2 w-50">
                            Verify LOA <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>
            @endif
            
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
