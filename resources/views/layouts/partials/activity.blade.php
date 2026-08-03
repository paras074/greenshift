@extends('layouts.app')
@section('title', 'Activity Timeline')

@section('content')
{{-- PAGE TOPBAR --}}
<div class="gs-page-topbar">
  <div class="gs-page-topbar-left">
    <h2>Activity Timeline</h2>
    <p>Track all system activity across <span>leads, deals & team members</span></p>
  </div>
  <div class="gs-page-topbar-actions">
    <a href="{{ route('dashboard') }}" class="gs-btn gs-btn--outline">
      <i class="bi bi-arrow-left"></i> Go Back
    </a>
  </div>
</div>

{{-- STATS ROW --}}
<div class="act-stats-row">
  <div class="act-stat-item">
    <div class="act-stat-icon act-stat-icon--teal">
      <i class="bi bi-activity"></i>
    </div>
    <div class="act-stat-body">
      <span class="act-stat-value">{{ number_format($stats['total']) }}</span>
      <span class="act-stat-label">Total Activities</span>
    </div>
  </div>
  <div class="act-stat-item">
    <div class="act-stat-icon act-stat-icon--blue">
      <i class="bi bi-people-fill"></i>
    </div>
    <div class="act-stat-body">
      <span class="act-stat-value">{{ number_format($stats['today']) }}</span>
      <span class="act-stat-label">Today</span>
    </div>
  </div>
  <div class="act-stat-item">
    <div class="act-stat-icon act-stat-icon--orange">
      <i class="bi bi-person-fill"></i>
    </div>
    <div class="act-stat-body">
      <span class="act-stat-value">{{ number_format($stats['week']) }}</span>
      <span class="act-stat-label">This Week</span>
    </div>
  </div>
  <div class="act-stat-item">
    <div class="act-stat-icon act-stat-icon--green">
      <i class="bi bi-calendar3"></i>
    </div>
    <div class="act-stat-body">
      <span class="act-stat-value">{{ number_format($stats['month']) }}</span>
      <span class="act-stat-label">This Month</span>
    </div>
  </div>
</div>

{{-- MAIN LAYOUT --}}
<div class="act-layout">

  {{-- SIDEBAR FILTERS --}}
  <aside class="act-sidebar">

    {{-- Filters Panel --}}
    <div class="gs-panel act-filter-panel">
      <div class="gs-panel-header">
        <span class="gs-panel-title"><i class="bi bi-funnel-fill" style="color:var(--primary-color);margin-right:6px;font-size:14px;"></i>Filters</span>
        <button class="act-clear-btn" id="clearFilters">Clear All</button>
      </div>
      <div class="gs-panel-body act-filter-body">

        {{-- Search --}}
        {{-- <div class="gs-field">
          <label class="gs-label">Search Activity</label>
          <div class="gs-input-wrap">
            <i class="bi bi-search gs-input-icon"></i>
            <input type="search" class="gs-input gs-input--icon" id="actSearch" placeholder="Search by keyword...">
          </div>
        </div> --}}

        {{-- Date Range --}}
        <div class="gs-field">
          <label class="gs-label">Date Range</label>
          <select class="gs-select act-filter-select" name="filterDate" id="filterDate">
            <option value="">All Time</option>
            <option value="1" {{ request('f') == '1' ? 'selected' : '' }}>Today</option>
            <option value="2" {{ request('f') == '2' ? 'selected' : '' }}>Yesterday</option>
            <option value="3" {{ request('f') == '3' ? 'selected' : '' }}>This Week</option>
            <option value="4" {{ request('f') == '4' ? 'selected' : '' }}>This Month</option>
            <option value="5" {{ request('f') == '5' ? 'selected' : '' }}>Custom Range</option>
          </select>
        </div>

        <div class="act-date-range" id="customDateRange" style="display:none;">
          <div class="gs-field">
            <label class="gs-label">From</label>
            <input type="date" class="gs-input" name="dateFrom" id="dateFrom" value="{{ request('df') }}">
          </div>
          <div class="gs-field" style="margin-top:10px;">
            <label class="gs-label">To</label>
            <input type="date" class="gs-input" name="dateTo" id="dateTo" value="{{ request('dt') }}">
          </div>
        </div>
		{{-- User Filter --}}
        {{-- <div class="gs-field">
          <label class="gs-label">Performed By</label>
          <select class="gs-select act-filter-select" id="filterUser">
            <option value="">All Users</option>
            <option value="1">John Doe</option>
            <option value="2">Jane Smith</option>
            <option value="3">Ali Hassan</option>
          </select>
        </div> --}}
        {{-- Activity Type --}}

        <button class="gs-btn gs-btn--primary mt-1" style="width:100%;justify-content:center;" id="applyFilters" onclick="applyFilters()">
          <i class="bi bi-funnel-fill"></i> Apply Filters
        </button>

      </div>
    </div>

  <div class="gs-panel">
    <div class="gs-panel-header">
      <span class="gs-panel-title">
        <i class="bi bi-bar-chart-fill" style="color:var(--primary-color);margin-right:6px;font-size:14px;"></i>
        Quick Stats
      </span>
    </div>

   @php
    $colors = [
      // Leads
      'lead_created'         => '#16a34a', // Emerald Green
      'lead_updated'         => '#2563eb', // Royal Blue
      'lead_deleted'         => '#fa0f0f', // Bright Red
      'lead_status_updated'  => '#0891b2', // Cyan/Teal

      // Attachments
      'attachment_added'     => '#0ea5e9', // Sky Blue
      'attachment_deleted'   => '#94a3b8', // Slate Gray

      // Members
      'member_assigned'      => '#f59e0b', // Amber
      'member_unassigned'    => '#ea580c', // Deep Orange

      // Tasks
      'task_created'         => '#8b5cf6', // Violet
      'task_updated'         => '#6366f1', // Indigo
      'task_deleted'         => '#ef4444', // Red
      'task_status_updated'  => '#d946ef', // Fuchsia
    ];
  @endphp

    <div class="act-quick-stats">
      @foreach($typeCounts as $type => $count)
        <div class="act-qs-row">
          <span class="act-qs-label">
            <i class="bi bi-circle-fill" style="color:{{ $colors[$type] ?? '#6b7280' }};font-size:7px;"></i>
            {{ ucwords(str_replace('_', ' ', $type)) }}
          </span>
          <span class="act-qs-val">{{ $count }}</span>
        </div>
      @endforeach
    </div>
  </div>



  </aside>

  {{-- TIMELINE MAIN Section--}}
  @include('layouts.partials.part.main', ['leads' => $stats])

</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
  window.toggleDateRange = function() {
    $('#customDateRange').toggle($('#filterDate').val() === '5');
  };
  $('#filterDate').on('change', function () {
    window.toggleDateRange();
  });

  $('#clearFilters').on('click', function () {
    $('#filterDate').val('');
    $('#customDateRange').hide();
    let url = window.location.pathname;
    window.location.href = url;
  });

  window.applyFilters = function() {
    let filterDate = $('#filterDate').val();
    let dateFrom = $('#dateFrom').val();
    let dateTo = $('#dateTo').val();

    let params = new URLSearchParams();
    if (filterDate) params.append('f', filterDate);
    if(filterDate && filterDate === '5') {
      if(dateFrom) params.append('df', dateFrom);
      if(dateTo) params.append('dt', dateTo);
    }

    window.location = '?' + params.toString();   

  };

  toggleDateRange();
});
</script>
@endpush
