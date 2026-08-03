@extends('layouts.app')
@section('title', isset($lead) ? 'Create RFQ For — ' . $lead->company_name : 'Create RFQ')

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
      @can('view rfq')
        <a type="button" class="gs-btn gs-btn--primary" href="{{ route('rfq_quotes.view', $lead->id) }}">
            <i class="bi bi-plus-lg"></i> View Quotes
        </a>
     @endcan
        @can('edit leads')
        <a href="{{ route('leads.edit', $lead->id) }}" class="gs-btn gs-btn--primary">
          Edit Lead
        </a>
      @endcan
    </div>
  </div>

  <div class="gs-three-grid">
    <div class="gs-first-grid">
        <div class="gs-panel">
            <div class="gs-panel-header">
                <h5 class="gs-panel-title">Details</h5>
            </div>
            <div class="gs-panel-body" style="padding:12px;">
                <div class="vl-info-col">
                    <div class="vl-field-row">
                        <span class="vl-field-label"><i class="bi bi-lightning-charge-fill vl-field-icon"></i> Required Load</span>
                        <span class="vl-field-value">
                            @if(!empty($lead->total_annual_consumption))
                                {{ $lead->total_annual_consumption }} kWh
                            @elseif(!empty($lead->annual_consumption))
                                {{ $lead->annual_consumption }} kWh
                            @else
                                --
                            @endif
                        </span>
                    </div>
                    
                    <div class="vl-field-row">
                        <span class="vl-field-label"><i class="bi bi-coin"></i> Budget Range</span>
                        <span class="vl-field-value">
                            {{ !empty($lead->budget_range) ? '£ ' . $lead->budget_range : '--' }}
                        </span>
                    </div>
                    
                    <div class="vl-field-row">
                        <span class="vl-field-label"><i class="bi bi-building"></i> Site Type</span>
                        <span class="vl-field-value">{{ $lead->roof_site_type ?? '--' }}</span>
                    </div>
                    
                    <div class="vl-field-row">
                        <span class="vl-field-label"><i class="bi bi-thermometer-half vl-field-icon"></i>Temperature</span>
                        <span class="vl-field-value">{{$lead->priorityStatus->name  ?? '--'}}</span>
                    </div>
                    
                    <div class="vl-field-row">
                        <span class="vl-field-label"><i class="bi bi-geo-alt-fill vl-field-icon"></i>Address</span>
                        <span class="vl-field-value">{{ implode(', ', array_filter([$lead->address, $lead->city, $lead->state, $lead->postcode])) }}</span>
                    </div>
                    
                    @if(!empty($lead->others['address']) && is_array($lead->others['address']))
                        <div class="gs-field gs-field--full">
                        <label class="gs-label" for="company_name">Site Address(s) </label>
                        @foreach($lead->others['address'] as $index => $extraAddress)
                            <div type="text" class="gs-input ">{{ implode(', ', array_filter([$extraAddress['address'], $extraAddress['city'], $extraAddress['state'], $extraAddress['postcode']])) }}</div>
                        @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="gs-second-grid">
        <div class="gs-panel">
            <div class="gs-panel-header">
                <h5 class="gs-panel-title">Add RFQ Details</h5>
            </div>
            <div class="gs-panel-body" style="padding:12px;">
                <form method="POST" action="{{ route('rfq_quotes.store') }}" class="rfq-form cs-form" id="RFQform">
                    @csrf
                    
                    <input type="hidden" name="lead_id" id="lead_id" value="{{ $lead->id ?? '' }}" />
                    
                    <div class="gs-form-grid">
                        <div class="gs-field gs-field--full">
                            <label class="gs-label" for="title">RFQ Title <span class="gs-required">*</span></label>
                            <input type="text" class="gs-input @error('title') is-invalid @enderror" name="title" id="title" required placeholder="Title" value="{{ old('title') }}" />
                            @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                
                        <div class="gs-field">
                            <label class="gs-label" for="supplier_name">Supplier Name <span class="gs-required">*</span></label>
                            <input type="text" class="gs-input @error('supplier_name') is-invalid @enderror" name="supplier_name" id="supplier_name" required placeholder="Supplier Name" value="{{ old('supplier_name') }}" />
                            @error('supplier_name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="gs-field">
                            <label class="gs-label" for="phone">Phone Number</label>
                            <input type="tel" class="gs-input" name="phone" id="phone" placeholder="Phone Number" value="{{ old('phone') }}" />
                        </div>
                        
                        <div class="gs-field">
                            <label class="gs-label" for="email">Email Address</label>
                            <input type="email" class="gs-input" name="email" id="email" placeholder="Email Address" value="{{ old('email') }}" />
                        </div>
                        
                        <div class="gs-field">
                            <label class="gs-label" for="delivery_timeline">Exp. Delivery Time</label>
                            <input type="text" class="gs-input" name="delivery_timeline" id="delivery_timeline" placeholder="e.g. 2-3 Weeks" value="{{ old('delivery_timeline') }}" />
                        </div>
                
                        <div class="gs-field">
                            <label class="gs-label" for="price">Price</label>
                            <input type="number" class="gs-input" name="price" id="price" placeholder="Price" step="0.01" value="{{ old('price') }}" />
                        </div>
                
                        <div class="gs-field">
                            <label class="gs-label" for="warranty">Warranty Period</label>
                            <input type="text" class="gs-input" name="warranty" id="warranty" placeholder="e.g. 12 Months" value="{{ old('warranty') }}" />
                        </div>
                        
                        <div class="gs-field gs-field--full">
                            <label class="gs-label" for="description">Other Details</label>
                            <textarea class="gs-input" name="description" id="description" rows="4" placeholder="Enter any additional details">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    @can('create rfq')
                        <button type="submit" class="gs-btn gs-btn--primary w-100 d-flex justify-content-center mt-2">
                            <i class="bi bi-plus-lg"></i> Add Quote
                        </button>
                    @endcan
                </form>
            </div>
        </div>
    </div>
    
    <div class="gs-third-grid">
        <div class="gs-panel">
            <div class="gs-panel-header">
                <h5 class="gs-panel-title">Follow Up</h5>
            </div>
            <div class="gs-panel-body" style="padding:12px;">
                
                <textarea id="noteData"  class="gs-textarea" style="min-height:72px;" placeholder="Add internal note for your team..."></textarea>
                    <button type="button" onclick="saveNote()" class="gs-btn gs-btn--primary mt-2 w-100 justify-content-center">
                      Add Note
                    </button>
                  <div id="notesFeed" class="vl-task-notes mt-3">
                      <p class="text-muted text-center small">Loading notes...</p>
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
   fetchNotes();
});
</script>
@endpush

@endsection
