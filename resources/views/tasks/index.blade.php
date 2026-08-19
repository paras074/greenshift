@extends('layouts.app')
@section('title', 'Tasks List')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Tasks List</h2>
      <p>View and manage your team tasks</p>
    </div>
    @can('create tasks')
      <div class="gs-page-topbar-actions">
        <a href="{{ route('tasks.create') }}" class="gs-btn gs-btn--primary">
          <i class="bi bi-plus-lg"></i> Add Task
        </a>
      </div>
    @endcan
  </div>
  
	{{-- Fetch Things Before --}}
    @php
        $get_all_task_status = tasksStatus();
        $get_all_task_priority = taskPriorities();
    @endphp


    <div class="gs-filter-bar">
        <select class="gs-filter-select" id="filterStatus" name="status">
        <option value="">Status</option>.
            @foreach($get_all_task_status as $key => $value)
                <option value="{{ $key }}">
                    {{ $value }}
                </option>
            @endforeach
        </select>

         <select class="gs-filter-select" id="filterPriority" name="priority">
          <option value="">Priority</option>
          @foreach($get_all_task_priority as $key => $value)
            <option value="{{ $key }}">
              {{ $value }}
            </option>
          @endforeach
          </select>
        

        @if(is_superadmin() || (auth()->check() && !auth()->user()->can('view tasks')))

        <select class="gs-filter-select" id="filterUser" name="assign_to">
        <option value="">Users</option>
        @foreach($assignedUsers as $user)
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

        <button class="gs-filter-btn-apply" id="applyFilter" onclick="ApplyFilter()">Apply</button>
        <button class="gs-filter-btn-clear" id="clearFilter" onclick="ClearFilter()">Clear</button>
    </div>

    <div class="gs-lead-tabs">
      <div class="gs-lead-tabs-inner">
        <button class="gs-lead-tab gs-lead-tab--active" data-filter="">
        <span class="gs-lead-dot gs-lead-dot--red"></span> All Tasks {{ $tasks->count() }}
        </button>
        @foreach($get_all_task_status as $key => $value)
          @if(($statusCounts[$key] ?? 0) > 0)
            <button class="gs-lead-tab" data-filter="{{ $key }}">
              <span class="gs-lead-dot gs-lead-dot--blue"></span> {{ $value }} Tasks {{ $statusCounts[$key] ?? 0 }}
            </button>
          @endif
        @endforeach
    </div>
    </div>


    <div class="gs-table-wrap">
      <table class="gs-table" id="tasksTable" style="width:100%;">
        <thead>
          <tr>
            <th><input type="checkbox" class="gs-table-checkbox" id="selectAll"/></th>
            <th>ID</th>
            <th>Task Title</th>
            <th>Assigned To</th>
            <th>Status</th>
            <th>Due On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @include('tasks.partials.row', ['tasks' => $tasks])
        </tbody>
      </table>
    </div>

      
@if(!is_superadmin() && auth()->user() && auth()->user()->can('edit tasks') && auth()->user()->can('view-own tasks'))
 
  <div class="offcanvas offcanvas-end gs-notif-panel Save-task-pop" tabindex="-1" id="TaskPanel" aria-labelledby="TaskPanelLabel">

    {{-- Header --}}
    <div class="offcanvas-header gs-notif-header">
      <div class="gs-notif-header-left" id="notify-header">
        <div class="gs-notif-header-icon">
          <i class="bi bi-check2-square"></i>
        </div>
        <div>
          <h5 class="offcanvas-title gs-notif-title" id="TaskPanelLabel">Edit </h5>
        </div>
      </div>

      <button type="button" class="gs-notif-close" data-bs-dismiss="offcanvas" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>



    {{-- Body / Scrollable list --}}
    <div class="offcanvas-body gs-notif-body" id="tasks-body">
      <div id="" class="text-center py-5">
          <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
              <span class="visually-hidden">Loading...</span>
          </div>
      </div>
    </div>


  </div>
@endif
@endsection


@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function () {
		let table;

		function initDataTable() {
		  table = $('#tasksTable').DataTable({
        retrieve: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], ['5', '10', '25', '50']],
        ordering:   true,
        order:      [[1, 'desc']],
        columnDefs: [
          { orderable: false, targets: [0, 6] }
        ],
        scrollX:   true,
        autoWidth: false,
        searching: true,
        language: {
          lengthMenu:  'Rows per page: _MENU_',
          info:        'Showing _START_–_END_ of _TOTAL_ Tasks',
          infoEmpty:   'No Tasks found',
          zeroRecords: 'No matching tasks found',
          paginate: {
          previous: '<i class="bi bi-chevron-left"></i>',
          next:     '<i class="bi bi-chevron-right"></i>'
          }
        },
		  });
		}
	  
	  initDataTable();
	  
      // ── Select All ──
      $('#selectAll').on('change', function () {
        $('.row-check').prop('checked', this.checked);
      });

      // ── Lead Tabs ──
      $('.gs-lead-tab').on('click', function () {
        {{-- $('.gs-lead-tab').removeClass('gs-lead-tab--active');
        $(this).addClass('gs-lead-tab--active');
        var f = $(this).data('filter');
        if (f !== "" && f !== null && f !== undefined) {
            f = Number(f);
        }
        let data = { 'lead_status_id': f };
        ApplyFilter(data); --}}
      });

      window.ClearFilter = function() {
        let wasChanged = false;

        $('.gs-filter-bar').find('input, select, textarea').each(function() {
            if ($(this).val() !== "") {
                $(this).val('');
                wasChanged = true; 
            }
            $(this).trigger('change');
        });

        if (wasChanged) {
            ApplyFilter(); 
        }
    };

		flatpickr("#dateRange", {
		  mode: "range",
		  dateFormat: "Y-m-d",
		  altInput: true,
		  altFormat: "F j, Y",
		  showMonths: 2
		});
	
		$('#filterRange').on('change', function() {
			$('.range-select-wrapper').toggleClass('active', this.value === '3');
		});
		
		window.ApplyFilter = function(manualData = null){
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
      $.ajax({
        url: "{{ route('tasks.index') }}",
        type: "POST",
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            SetTableState('#tasksTable', 'loader', 'Loading tasks...');            
        },
        success: function(response) {
            if ($.fn.DataTable.isDataTable('#tasksTable')) {
                table.destroy();
            }
            $('#tasksTable tbody').html(response);
            initDataTable();
        },
        error: function(xhr) {
            console.log(xhr);
            SetTableState('#tasksTable', 'error', 'Failed to load tasks. Check your connection.');
            setTimeout(function() {
                location.reload();
            }, 1000);
        }
      });
		};

    window.AddTaskNote = async function(taskid = null) {
        if (!taskid) return;

        const noteInput = document.getElementById('noteData');
        const data = noteInput.value;
        
        if (!data) return window.miniAlert("Please type a note.");

        try {
            let url = "{{ route('tasks.store-note', ':id') }}";
            url = url.replace(':id', taskid);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    data: data
                })
            });

            const result = await response.json();

            if (response.ok) {
                const container = document.getElementById('tasks-body');
                container.innerHTML = result.html;
                noteInput.value = ''; 
            } else {
                console.error('Error:', result.message);
            }
        } catch (error) {
            console.error('Error saving note:', error);
        }
    };
      
    });
  </script>
@endpush
