@extends('layouts.app')
@section('title', 'Reports - Greenshift Energy Consulting')

@section('content')

<div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
        <h2>Reports</h2>
        <p>Analyze and Track leads and their sources</p>
    </div>

    <div class="gs-page-topbar-actions">
        <a href="{{ route('dashboard') }}" class="gs-btn gs-btn--outline">
            <i class="bi bi-arrow-left"></i> Go Back
        </a>
    </div>
</div>

@php
    $get_all_lead_status = get_all_lead_status();
    $get_all_priority_status = get_all_priority_status();
@endphp

<div class="gs-filter-bar reports-filter justify-content-between">
    <div class="filter-initial d-flex gap-3">
        @if(is_superadmin() || (auth()->check() && !auth()->user()->can('view-own leads')))
            @php 
                $allUsers = getAllLeadUsers(); 
            @endphp
        
            <select class="gs-filter-select" id="filterUser" name="assigned_to">
              <option value="">Users</option>
              @foreach($allUsers as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
        @endif
    
        <select class="gs-filter-select" id="filterRange" name="created_at">
            <option value="">Range</option>
            <option value="1">This Week</option>
            <option value="2">This Month</option>
            <option value="3">Custom</option>
        </select>
        
        <div class="range-select-wrapper">
    		<input type="text" id="dateRange" name="created_at_range" placeholder="Select date range" class="gs-filter-select">
    	</div>
	</div>
	<div class="filter-buttons d-flex gap-3">
        <button class="gs-filter-btn-apply" id="applyFilter" onclick="LoadData()">Apply</button>
        <button class="gs-filter-btn-clear" id="clearFilter" onclick="ClearFilter()">Clear</button>
    </div>
</div>

<div>
    <div class="d-flex justify-content-center align-items-center py-5 my-5" id="statsLoader">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading stats...</span>
        </div>
    </div>
    <div id="Leads-stats">
        
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let reportPieChartInstance = null;
    
    window.initStats = function(statusesBreakdown) {
        if (typeof ChartDataLabels !== 'undefined') {
            Chart.unregister(ChartDataLabels);
        }
        
        const ctx = document.getElementById('rptPieChart');
        if (!ctx) return;
    
        if (reportPieChartInstance) {
            reportPieChartInstance.destroy();
        }
    
        let chartLabels = [];
        let chartCounts = [];
        let chartColors = [];
        let totalLeads = 0;
    
        statusesBreakdown.forEach(function(item) {
            chartLabels.push(item.name);  
            chartCounts.push(item.count);
            chartColors.push(item.color); 
            totalLeads += parseInt(item.count) || 0;
        });
        
        let finalLabels = chartLabels;
        let finalCounts = chartCounts;
        let finalColors = chartColors;
        let isEmptyState = (totalLeads === 0);
    
        if (isEmptyState) {
            finalLabels = ['No Data Found'];
            finalCounts = [1];          
            finalColors = ['#e2e8f0']; 
        }
    
        reportPieChartInstance = new Chart(ctx, {
            type: 'pie', 
            data: {
                labels: finalLabels,
                datasets: [{
                    data: finalCounts,
                    backgroundColor: finalColors,
                    borderWidth: isEmptyState ? 0 : 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                hover: { mode: isEmptyState ? null : 'nearest' }, 
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                if (isEmptyState) {
                                    return ' No data matching chosen filters';
                                }
                                
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return ' ' + label + ': ' + value + ' Leads';
                            }
                        }
                    }
                }
            }
        });
    };
    
    let reportLineChartInstance = null;
    window.initSecondStats = function(trends) {
        // 1. Guard clause in case the trends data structure or element doesn't exist
        if (!trends || !trends.labels) return;
    
        var lineCtx = document.getElementById('rptLineChart');
        if (!lineCtx) return;
    
        // 2. Destroy the old instance if it exists to avoid buggy hover overlays
        if (reportLineChartInstance) {
            reportLineChartInstance.destroy();
        }
    
        // 3. Dynamically calculate a healthy Y-axis maximum with a 15% padding ceiling
        let maxVal = Math.max(...(trends.leads || [0]), ...(trends.conversions || [0]));
        let yAxisMax = maxVal > 0 ? Math.ceil((maxVal + (maxVal * 0.15)) / 10) * 10 : 50;
    
        // 4. Instantiate the dynamic Chart.js layout
        reportLineChartInstance = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: trends.labels, // Expects dynamic array: ["Jan 2026", "Feb 2026", ...]
                datasets: [
                    {
                        label: 'Leads',
                        data: trends.leads, // Inject dynamic leads counts array
                        borderColor: '#4A7FD6',
                        backgroundColor: 'rgba(74, 127, 214, 0.10)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#4A7FD6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Conversion rate', // Inject dynamic status 5 counts array
                        data: trends.conversions,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.10)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#F59E0B',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: yAxisMax, // Applied dynamic calculated max height step limit
                        grid: {
                            color: 'rgba(0, 0, 0, 0.06)',
                            borderDash: [4, 4]
                        },
                        ticks: {
                            font: { family: 'Montserrat', size: 11 },
                            color: '#6b7a99'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Montserrat', size: 11 },
                            color: '#6b7a99'
                        }
                    }
                }
            }
        });
    };
    
    window.LoadData = function(manualData = null) {
        let data = {};
        
        if (manualData && Object.keys(manualData).length > 0) {
            data = manualData;
        } else {
            $('.gs-filter-bar').find('input, select, textarea').each(function() {
                let name = $(this).attr('name');
                if (!name) return;
                data[name] = $(this).val();
            });
        }
        
        Object.keys(data).forEach(key => {
            if (data[key] === null || data[key] === undefined || data[key] === '') {
                delete data[key];
            }
        });
        
        let queryString = $.param(data);
        let newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
    
        if (window.location.search !== (queryString ? '?' + queryString : '')) {
            window.history.pushState({ filterData: data }, "", newUrl);
        }
        
        
        $.ajax({
            url: "{{ route('reports.index') }}",
            type: "POST",
            data: data,
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#Leads-stats').empty(); 
                $('#statsLoader').removeClass('d-none');          
            },
            success: function(response) {
                $('#Leads-stats').html(response.html);
                if (typeof initStats === "function") {
                    if (response.data && response.data.statusesBreakdown) {
                        initStats(response.data.statusesBreakdown); 
                    }
                }
                if (typeof initSecondStats === "function") {
                    if (response.data && response.data.trends) {
                        initSecondStats(response.data.trends); 
                    }
                }
            },
            error: function(xhr) {
                $('#Leads-stats').html('<div class="alert alert-danger">Failed to load reports layout. Please try again.</div>');
            },
            complete: function() {
                // Always hide loader upon completion
                $('#statsLoader').addClass('d-none');
            }
        });
    };
	
	const datePickerInstance = flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        showMonths: 2
	});
	
	$('#filterRange').on('change', function() {
		$('.range-select-wrapper').toggleClass('active', this.value === '3');
	});
	
	
	let urlParams = new URLSearchParams(window.location.search);
    let initialFilterData = {};
    
    urlParams.forEach(function(value, key) {
        initialFilterData[key] = value;
        
        let $filterInput = $('[name="' + key + '"]');
        if ($filterInput.length) {
            if ($filterInput.attr('id') === 'dateRange') {
                if (value) {
                    datePickerInstance.setDate(value, false);
                } else {
                    datePickerInstance.clear();
                }
            } else {
                $filterInput.val(value);
            }
            $filterInput.trigger('change');
        }
    });
    
    if (Object.keys(initialFilterData).length > 0) {
        window.history.replaceState({ filterData: initialFilterData }, "", window.location.href);
        window.LoadData(initialFilterData);
    } else {
        window.LoadData();
    }
    
    window.ClearFilter = function() {
        let wasChanged = false;
    
        $('.gs-filter-bar').find('input, select, textarea').each(function() {
            if ($(this).val() !== "") {
                $(this).val('');
                wasChanged = true; 
            }
            
            $(this).trigger('change');
            
            if ($(this).hasClass('select2-hidden-accessible') || $(this).data('select2')) {
                $(this).trigger('change.select2');
            }
        });
    
        if (typeof datePickerInstance !== 'undefined' && datePickerInstance !== null) {
            datePickerInstance.clear();
        }
    
        if (window.location.search !== "") {
            window.history.pushState({ filterData: {} }, "", window.location.pathname);
            wasChanged = true;
        }
    
        if (wasChanged) {
            if (typeof window.LoadData === "function") {
                window.LoadData({}); 
            }
        }
    };
});
</script>
@endpush
