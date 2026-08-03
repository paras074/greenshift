@extends('layouts.app')
@section('title', isset($lead) ? 'View Quotes — ' . $lead->company_name : 'View Quotes')

@push('styles')

@endpush

@section('content')
<div class="gs-dash-wrap view-page">

    {{-- ── TOP BAR ───────────────────────────────────────────────── --}}
    <div class="gs-page-topbar vl-topbar">
        <div class="gs-page-topbar-left">
            <div class="vl-profile">
                <div class="gs-user-avatar vl-avatar-lg">
                    <img src="/images/site-logo.png" alt="logo">
                </div>
                <div class="vl-profile-info">
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <h2>{{ $lead->company_name ?? 'ABC Solar Pvt Ltd' }}</h2>
                        
                    </div>
                    <p>{{ $lead->phone ?? 'Phone' }}</p>
                </div>
            </div>
        </div>
        <div class="gs-page-topbar-actions">
            <a href="{{ route('leads.index') }}" class="gs-btn gs-btn--outline">
                <i class="bi bi-arrow-left"></i>
            </a>
            @can('create rfq')
                <a type="button" class="gs-btn gs-btn--primary" href="{{ route('leads.add_rfq', $lead->id) }}">
                    <i class="bi bi-plus-lg"></i> Add Quote
                </a>
            @endcan
            @can('edit leads')
                <a href="{{ route('leads.edit', $lead->id) }}" class="gs-btn gs-btn--primary">
                    Edit Lead
                </a>
            @endcan
        </div>
    </div>

    <div class="gs-common-grid">

        {{-- ── LEFT PANEL ─────────────────────────────────────────── --}}
        <div class="gs-left-grid">
            <div class="gs-panel">
                <div class="gs-panel-header">
                    <h5 class="gs-panel-title">Suppliers List</h5>
                </div>
                <div class="gs-panel-body">

                    {{-- Stat Cards --}}
                    <div class="rfq-stats-row">
                        <div class="rfq-stat-card">
                            <div class="rfq-stat-icon orange">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <div class="rfq-stat-label">Total Vendors</div>
                                <div class="rfq-stat-value">{{ $quotes->count() }}</div>
                            </div>
                        </div>
                        <div class="rfq-stat-card">
                            <div class="rfq-stat-icon purple">
                                <i class="bi bi-chat-left-text-fill"></i>
                            </div>
                            <div>
                                <div class="rfq-stat-label">Quotes</div>
                                <div class="rfq-stat-value">{{ $quotes->count() }}</div>
                            </div>
                        </div>
                        <div class="rfq-stat-card">
                            <div class="rfq-stat-icon pink">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div>
                                <div class="rfq-stat-label">Pending</div>
                                <div class="rfq-stat-value">0</div>
                            </div>
                        </div>
                        <div class="rfq-stat-card">
                            <div class="rfq-stat-icon blue">
                                <i class="bi bi-clipboard2-check-fill"></i>
                            </div>
                            <div>
                                <div class="rfq-stat-label">RFQ Status</div>
                                <div class="rfq-stat-value">Evaluation Stage</div>
                            </div>
                        </div>
                    </div>
                    
                    <table class="rfq-vendor-table">
                        <thead>
                            <tr>
                                <th>Vendor</th>
                                <th>Price</th>
                                <th>Delivery</th>
                                <th>Warranty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $assignedSupplierId = data_get($lead, 'others.supplier');
                                $hasLeadAssignment = !empty($assignedSupplierId);
                        
                                $hasAnyQuoteFlagged = !$hasLeadAssignment && $quotes->contains(function($q) {
                                    return isset($q->others['is_selected']) && $q->others['is_selected'] == 1;
                                });
                            @endphp
                            @forelse($quotes as $quote)
                            @php
                                if ($hasLeadAssignment) {
                                    $isSelected = ($assignedSupplierId == $quote->id);
                                } else {
                                    $isQuoteFlagged = isset($quote->others['is_selected']) && $quote->others['is_selected'] == 1;
                                    $isSelected = $isQuoteFlagged || (!$hasAnyQuoteFlagged && $loop->first);
                                }
                            @endphp
                                <tr>
                                    <td>
                                        <div class="vendor-cell">
                                            <div class="vendor-check rfq-select-trigger {{ $isSelected ? 'active' : 'inactive' }}" 
                                                 style="cursor: pointer;"
                                                 data-vendor-name="{{ $quote->supplier_name }}"
                                                 data-price="{{ $quote->price ? '£' . number_format($quote->price, 0) : '-' }}"
                                                 data-quote-id="{{ $quote->id }}">
                                                <i class="bi bi-check2"></i>
                                            </div>
                                            
                                            <span class="vendor-name {{ $isSelected ? 'highlight' : '' }}">
                                                {{ $quote->supplier_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $quote->price ? '£' . number_format($quote->price, 0) : '-' }}
                                    </td>
                                    <td>{{ $quote->delivery_timeline ?? '-' }}</td>
                                    <td>{{ $quote->warranty ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @can('edit rfq')
                                                <div class="rfq-action-btn edit-quote-trigger" 
                                                     style="cursor: pointer;"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#rfqQuoteModal"
                                                     data-id="{{ $quote->id }}"
                                                     data-title="{{ $quote->title }}"
                                                     data-supplier_name="{{ $quote->supplier_name }}"
                                                     data-phone="{{ $quote->phone }}"
                                                     data-email="{{ $quote->email }}"
                                                     data-delivery_timeline="{{ $quote->delivery_timeline }}"
                                                     data-price="{{ $quote->price }}"
                                                     data-warranty="{{ $quote->warranty }}"
                                                     data-description="{{ $quote->description }}">
                                                    <span class="rfq-action-btn-label">Edit Quote</span>
                                                </div>
                                            @endcan
                                            
                                            @can('delete rfq')
                                                <form action="{{ route('rfq_quotes.destroy', $quote->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this quote?');" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn text-danger p-0 border-0" style="background: none; font-size: 1.2rem; line-height: 1;">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        No quotes found for this lead.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── RIGHT PANEL ─────────────────────────────────────────── --}}
        <div class="gs-right-grid">
            <div class="gs-panel">
                <div class="gs-panel-header">
                    <h5 class="gs-panel-title">Suppliers Comparison</h5>
                </div>
                <div class="gs-panel-body">

                   @php
                        $validQuotes = $quotes->filter(function($q) {
                            return !empty($q->price) && $q->price > 0;
                        });
                    
                        $lowestPriceQuote = $validQuotes->sortBy('price')->first();
                        
                        $fastestDeliveryQuote = $validQuotes->sortBy(function($q) {
                            return (int) filter_var($q->delivery_timeline, FILTER_SANITIZE_NUMBER_INT);
                        })->first();
                        
                        $bestWarrantyQuote = $validQuotes->sortByDesc(function($q) {
                            return (int) filter_var($q->warranty, FILTER_SANITIZE_NUMBER_INT);
                        })->first();
                    
                        $explicitSelected = $quotes->first(function($quote) {
                            return isset($quote->others['is_selected']) && $quote->others['is_selected'] == 1;
                        });
                        
                        $currentDecisionQuote = $explicitSelected ?? $quotes->first();
                    @endphp
                    
                    {{-- Comparison Snapshot --}}
                    <p class="rfq-section-title">Comparison Snapshot:</p>
                    <div class="rfq-kv-row">
                        <span class="rfq-kv-label">Lowest Price</span>
                        <span class="rfq-kv-value">
                            {{ $lowestPriceQuote ? $lowestPriceQuote->supplier_name . ' (£' . number_format($lowestPriceQuote->price, 0) . ')' : 'N/A' }}
                        </span>
                    </div>
                    <div class="rfq-kv-row">
                        <span class="rfq-kv-label">Fastest Delivery</span>
                        <span class="rfq-kv-value">
                            {{ $fastestDeliveryQuote ? $fastestDeliveryQuote->supplier_name . ' (' . $fastestDeliveryQuote->delivery_timeline . ')' : 'N/A' }}
                        </span>
                    </div>
                    <div class="rfq-kv-row">
                        <span class="rfq-kv-label">Best Warranty</span>
                        <span class="rfq-kv-value">
                            {{ $bestWarrantyQuote ? $bestWarrantyQuote->supplier_name . ' (' . $bestWarrantyQuote->warranty . ')' : 'N/A' }}
                        </span>
                    </div>
                    
                    <div class="rfq-divider"></div>
                    
                    {{-- Decision --}}
                    <p class="rfq-section-title">Decision</p>
                    <div class="rfq-kv-row">
                        <span class="rfq-kv-label">Selected Vendor</span>
                        <span class="rfq-kv-value" id="decision-vendor-name">
                            {{ $currentDecisionQuote->supplier_name ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="rfq-kv-row">
                        <span class="rfq-kv-label">Final Price</span>
                        <span class="rfq-kv-value" id="decision-final-price">
                            {{ $currentDecisionQuote && $currentDecisionQuote->price ? '£' . number_format($currentDecisionQuote->price, 0) : '-' }}
                        </span>
                    </div>
                    <div class="rfq-divider"></div>
                    @can('manage rfq')
                    <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded text-muted" style="background-color: #f8f9fa; border-left: 3px solid #0d6efd; font-size: 0.82rem; line-height: 1.4;">
                        <i class="bi bi-info-circle-fill text-primary"></i>
                        <span>The selected supplier will be Assigned as the main supplier for this lead. You can change this choice by clicking the check icon next to any Supplier name.</span>
                    </div>
                    
                        <form method="POST" action="{{ route('rfq_quotes.assign_supplier') }}">
                            @csrf
                            <!-- Pass the lead ID hidden -->
                            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                            
                            <button type="submit" id="submit-btn" class="gs-btn gs-btn--primary w-100 d-flex justify-content-center mt-3">
                                <i class="bi bi-save me-2"></i> Add Selected Supplier
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="rfqQuoteModal" tabindex="-1" aria-labelledby="rfqQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rfqQuoteModalLabel">Edit Quote Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" class="rfq-form cs-form" id="RFQform">
                    @csrf
                    <div id="method-field"></div>
                    
                    <input type="hidden" name="lead_id" id="lead_id" value="{{ $lead->id ?? '' }}" />
                    
                    <div class="gs-form-grid">
                        <div class="gs-field gs-field--full">
                            <label class="gs-label" for="title">RFQ Title <span class="gs-required">*</span></label>
                            <input type="text" class="gs-input" name="title" id="title" required placeholder="Title" />
                        </div>
                
                        <div class="gs-field">
                            <label class="gs-label" for="supplier_name">Supplier Name <span class="gs-required">*</span></label>
                            <input type="text" class="gs-input" name="supplier_name" id="supplier_name" required placeholder="Supplier Name" />
                        </div>
                        
                        <div class="gs-field">
                            <label class="gs-label" for="phone">Phone Number</label>
                            <input type="tel" class="gs-input" name="phone" id="phone" placeholder="Phone Number" />
                        </div>
                        
                        <div class="gs-field">
                            <label class="gs-label" for="email">Email Address</label>
                            <input type="email" class="gs-input" name="email" id="email" placeholder="Email Address" />
                        </div>
                        
                        <div class="gs-field">
                            <label class="gs-label" for="delivery_timeline">Exp. Delivery Time</label>
                            <input type="text" class="gs-input" name="delivery_timeline" id="delivery_timeline" placeholder="e.g. 2-3 Weeks" />
                        </div>
                
                        <div class="gs-field">
                            <label class="gs-label" for="price">Price</label>
                            <input type="number" class="gs-input" name="price" id="price" placeholder="Price" step="0.01" />
                        </div>
                
                        <div class="gs-field">
                            <label class="gs-label" for="warranty">Warranty Period</label>
                            <input type="text" class="gs-input" name="warranty" id="warranty" placeholder="e.g. 12 Months" />
                        </div>
                        
                        <div class="gs-field gs-field--full">
                            <label class="gs-label" for="description">Other Details</label>
                            <textarea class="gs-input" name="description" id="description" rows="4" placeholder="Enter any additional details"></textarea>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('rfq_quotes.assign_supplier') }}">
                        @csrf
                        <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                        
                        <button type="submit" id="submit-btn" class="gs-btn gs-btn--primary w-100 d-flex justify-content-center mt-3">
                            <i class="bi bi-save me-2"></i> Update Quote
                        </button>
                    </form>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('rfqQuoteModal');
    
    modalElement.addEventListener('show.bs.modal', function (event) {
        // Button that triggered the modal
        const button = event.relatedTarget;
        
        // Extract info from data-* attributes
        const id = button.getAttribute('data-id');
        const title = button.getAttribute('data-title');
        const supplierName = button.getAttribute('data-supplier_name');
        const phone = button.getAttribute('data-phone');
        const email = button.getAttribute('data-email');
        const deliveryTimeline = button.getAttribute('data-delivery_timeline');
        const price = button.getAttribute('data-price');
        const warranty = button.getAttribute('data-warranty');
        const description = button.getAttribute('data-description');

        const form = modalElement.querySelector('#RFQform');
        const methodField = modalElement.querySelector('#method-field');

        form.action = `/lead/rfq-quotes/${id}`; 
        
        methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        modalElement.querySelector('#title').value = title || '';
        modalElement.querySelector('#supplier_name').value = supplierName || '';
        modalElement.querySelector('#phone').value = phone || '';
        modalElement.querySelector('#email').value = email || '';
        modalElement.querySelector('#delivery_timeline').value = deliveryTimeline || '';
        modalElement.querySelector('#price').value = price || '';
        modalElement.querySelector('#warranty').value = warranty || '';
        modalElement.querySelector('#description').value = description || '';
    });
    
    const selectTriggers = document.querySelectorAll('.rfq-select-trigger');

    selectTriggers.forEach(trigger => {
        trigger.addEventListener('click', function () {
            document.querySelectorAll('.rfq-select-trigger').forEach(el => {
                el.classList.remove('active');
                el.classList.add('inactive');
            });

            document.querySelectorAll('.vendor-name').forEach(name => {
                name.classList.remove('highlight');
            });

            this.classList.remove('inactive');
            this.classList.add('active');

            const vendorNameLabel = this.nextElementSibling;
            if (vendorNameLabel && vendorNameLabel.classList.contains('vendor-name')) {
                vendorNameLabel.classList.add('highlight');
            }

            const quoteId = this.getAttribute('data-quote-id');
            
            fetch(`/rfq-quotes/${quoteId}/select`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    const selectedVendorName = this.getAttribute('data-vendor-name');
                    const selectedPrice = this.getAttribute('data-price');
            
                    document.getElementById('decision-vendor-name').textContent = selectedVendorName || 'N/A';
                    document.getElementById('decision-final-price').textContent = selectedPrice || '-';
                }
            })
            .catch(error => console.error('Error updating selection:', error));
        });
    });
});
</script>
@endpush