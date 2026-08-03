@extends('layouts.app')
@section('title', 'Lead List')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Lead List</h2>
      <p>View and manage your leads</p>
    </div>

    <div class="gs-page-topbar-actions">
      @can('manage kanban')
          <a href="{{ route('leads.funnel') }}" class="gs-btn gs-btn--primary">
            <i class="bi bi-funnel"></i> Lead Funnel
          </a>
      @endcan
      @can('create leads')
          <a href="{{ route('leads.create') }}" class="gs-btn gs-btn--primary">
            <i class="bi bi-plus-lg"></i> Add Lead
          </a>
      @endcan
    </div>
  </div>
  
	{{-- Fetch Things Before --}}
    @php
        $get_all_lead_status = get_all_lead_status();
        $get_all_priority_status = get_all_priority_status();
    @endphp

  <div class="gs-filter-bar">
    <select class="gs-filter-select" id="filterStatus" name="lead_status_id">
      <option value="">Status</option>.
		@foreach($get_all_lead_status as $key => $value)
			<option value="{{ $value['id'] }}">
				{{ $value['name'] }}
			</option>
		@endforeach
    </select>

    <select class="gs-filter-select" id="filterMode" name="status">
      <option value="">Mode</option>
      <option value="active">Publish</option>
      <option value="draft">Draft</option>
    </select>

    <select class="gs-filter-select" id="filtertype" name="energy_type">
      <option value="">Energy Type</option>
		  <option value="electricity">Electricity</option>
		  <option value="gas">Gas</option>
    </select>
	
    <select class="gs-filter-select" id="filterTemperature" name="priority_status_id">
		<option value="">Temperature</option>
		@foreach($get_all_priority_status as $key => $value)
			<option value="{{ $value['id'] }}"
				{{ old('priority_status_id', $lead->priority_status_id ?? '') == $value['id'] ? 'selected' : '' }}>
				{{ $value['name'] }}
			</option>
		@endforeach
    </select>

    @if(is_superadmin() || (auth()->check() && !auth()->user()->can('view-own leads')))
    @php 
        $allUsers = getAllAssignedUsers(); 
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
    <button class="gs-filter-btn-apply" id="applyFilter" onclick="ApplyFilter()">Apply</button>
    <button class="gs-filter-btn-clear" id="clearFilter" onclick="ClearFilter()">Clear</button>
  </div>


  <div class="gs-lead-tabs">
    <div class="gs-lead-tabs-inner">
    <button class="gs-lead-tab gs-lead-tab--active" data-filter="">
		  <span class="gs-lead-dot gs-lead-dot--blue"></span> All <span class="leads-count-tb">({{ $leads->count() }})</span>
    </button>
	@foreach($get_all_lead_status as $key => $value)
		@if(($statusCounts[$value['id']] ?? 0) > 0)
		<button class="gs-lead-tab" data-filter="{{ $value['id'] }}">
			<span class="gs-lead-dot" style="background:{{ $value['color'] }};"></span> {{ $value['name'] }} <span class="leads-count-tb">({{ $statusCounts[$value['id']] ?? 0 }})</span>
		</button>
		@endif
	@endforeach
  </div>
    <div class="gs-lead-action">

      @can('create leads')
          <button type="button" class="gs-btn gs-btn--primary" onclick="AssignLeads()"><i class="bi bi-plus-lg"></i> Assign Leads</button>
      @endcan
    
      @can('delete leads')
        <button type="button" class="gs-btn gs-btn--danger" onclick="DeleteLeads()"><i class="bi bi-trash"></i> Delete Selected</button>
      @endcan
    </div>
  </div>


  <div class="gs-table-wrap">
    <table class="gs-table" id="leadsTable" style="width:100%;">
      <thead>
        <tr>
          <th><input type="checkbox" class="gs-table-checkbox" id="selectAll"/></th>
          <th>ID</th>
          <th>Company Details</th>
          <th>Status</th>
          <th>kWh</th>
          <th>Energy Type</th>
          <th>Temperature</th>
          <th>Assigned To</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @include('leads.partials.row', ['leads' => $leads])
      </tbody>
    </table>
  </div>

  <div class="modal fade ModalMain" id="assignedUsersModal" tabindex="-1" aria-labelledby="assignedUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold text-dark" id="assignedUsersModalLabel">
                    <i class="bi bi-building-fill text-primary me-2"></i>
                    <span id="modalCompanyName">Company Name</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="modalLoader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted fw-medium">Fetching assigned team...</p>
                </div>

                <div id="modalTableContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-uppercase text-secondary small fw-bolder">ID</th>
                                    <th class="text-uppercase text-secondary small fw-bolder">Name</th>
                                    <th class="text-uppercase text-secondary small fw-bolder">Email</th>
                                    <th class="text-uppercase text-secondary small fw-bolder">Role</th>
                                </tr>
                            </thead>
                            <tbody id="assignedUsersList">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 bg-light py-2">
                <button type="button" class="gs-btn gs-btn--primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
  </div>

  <div class="modal fade ModalMain" id="Assignmodel" tabindex="-1" aria-labelledby="AssignmodelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold text-dark" id="AssignmodelLabel">
                    <i class="bi bi-people-fill text-primary me-2"></i>
                    <span id="modalCompanyName">Assign Leads to Users</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="assignmodalLoader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted fw-medium">Fetching team Members...</p>
                </div>
                <div id="assignmodalbody" class="d-none">
                  @php
                    $users = GetAllUsersByRoleId(4)->concat(GetAllUsersByRoleId(5));
                  @endphp
                  <div class="gs-panel assignselect2Popup">
                    <div class="gs-panel-body">
                      <div class="gs-form-grid">
                        <div class="gs-field gs-field--full">
                            <label class="gs-label" for="assigned_users">Assign Users</label>

                            <select
                                name="assigned_users[]"
                                id="assigned_users"
                                class="gs-select2 form-select js-select2"
                                multiple
                            >
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 bg-light py-2">
                <button type="button" class="gs-btn gs-btn--primary" onclick="AssignLeadsFinal()" id="finalAssignBtn"><i class="bi bi-plus-lg"></i> Assign</button>
                <button type="button" class="gs-btn gs-btn--danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  <script>
    $(document).ready(function () {
		let table;

		function initDataTable() {
		  table = $('#leadsTable').DataTable({
        retrieve: true,
        pageLength: 50,
        lengthMenu: [[5, 10, 25, 50], ['5', '10', '25', '50']],
        ordering:   true,
        order:      [[1, 'desc']],
        columnDefs: [
          { orderable: false, targets: [0, 7] }
        ],
        scrollX:   true,
        autoWidth: false,
        searching: true,
        language: {
          lengthMenu:  'Rows per page: _MENU_',
          info:        'Showing _START_–_END_ of _TOTAL_ leads',
          infoEmpty:   'No leads found',
          zeroRecords: 'No matching leads found',
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
        url: "{{ route('leads.index') }}",
        type: "POST",
        data: data,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            SetTableState('#leadsTable', 'loader', 'Loading leads...');            
        },
        success: function(response) {
            if ($.fn.DataTable.isDataTable('#leadsTable')) {
                table.destroy();
            }
            $('#leadsTable tbody').html(response);
            initDataTable();
        },
        error: function(xhr) {
            SetTableState('#leadsTable', 'error', 'Failed to load leads. Check your connection.');
            setTimeout(function() {
                //location.reload();
            }, 1000);
        }
      });
		};

    $(document).on('click', '.view-assigned-users', function() {
      let leadId = $(this).data('lead-id');
      let company = $(this).data('company');
      
      // Prepare Route URL
      let url = "{{ route('leads.assigned.members', ':id') }}";
      url = url.replace(':id', leadId);

      // Initial UI reset
      $('#modalCompanyName').text(company);
      $('#assignedUsersList').empty();
      $('#modalTableContent').hide();
      $('#modalLoader').show();
      
      // Open Modal
      var myModal = new bootstrap.Modal(document.getElementById('assignedUsersModal'));
      myModal.show();

      $.ajax({
          url: url,
          type: 'GET',
          success: function(response) {
              $('#modalLoader').hide();
              $('#modalTableContent').fadeIn();

              if (response.data && response.data.length > 0) {
                  $.each(response.data, function(index, item) {
                      // Since your JSON uses user_name and user_email directly:
                      let userName = item.user_name;
                      let userEmail = item.user_email;
                      let userId = item.id;
                      
                      // Get the first role from the array
                      let roleName = item.roles.length > 0 ? item.roles[0] : 'No Role';

                      // Format the role name for display (e.g., sales_manager -> Sales Manager)
                      let formattedRole = roleName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

                      let row = `
                          <tr>
                              <td class="ps-4 text-muted small">#${userId}</td>
                              <td>
                                  <div class="d-flex align-items-center">
                                      <span class="fw-bold text-dark">${userName}</span>
                                  </div>
                              </td>
                              <td>${userEmail}</td>
                              <td>
                                  <span class="badge bg-light-primary text-primary text-uppercase">
                                      ${formattedRole}
                                  </span>
                              </td>
                          </tr>`;
                          
                      $('#assignedUsersList').append(row);
                  });
              } else {
                  $('#assignedUsersList').append('<tr><td colspan="3" class="text-center py-4 text-muted italic">No users currently assigned to this lead.</td></tr>');
              }
          },
          error: function() {
              $('#modalLoader').html('<div class="p-4 text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Error fetching data.</div>');
          }
      });
  });

    window.DeleteLeads = function () {

      let selected = [];

      $('#leadsTable .row-check:checked').each(function () {
          selected.push($(this).val());
      });

      if (selected.length === 0) {
          alert('Please select at least one lead.');
          return;
      }

      if (!confirm('Are you sure you want to delete selected leads?')) {
          return;
      }

      $.ajax({
          url: "{{ route('leads.bulk-delete') }}",
          type: "POST",
          data: {
              _token: "{{ csrf_token() }}",
              lead_ids: selected
          },
          success: function (res) {
              if (res.status) {
                  ApplyFilter(); 
              }
          },
          error: function (err) {
              alert(err.responseJSON?.message || 'Something went wrong.');
          }
      });
  };

  
    
    window.AssignLeads = function () {
      let selected = [];

      $('#leadsTable .row-check:checked').each(function () {
          selected.push($(this).val());
      });

      if (selected.length === 0) {
          alert('Please select at least one lead.');
          return;
      }
      var myassignModal = new bootstrap.Modal(document.getElementById('Assignmodel'));
      myassignModal.show();

      setTimeout(function () {
          $('#assignmodalbody').removeClass('d-none');
          $('#assignmodalLoader').addClass('d-none');
      }, 1500);

    };
    $('#assigned_users').select2({
        placeholder: 'Search and select users',
        allowClear: true,
        width: '100%'
    });

    window.AssignLeadsFinal = function() {
      let selectedUsers = $('#assigned_users').val();
      if (!selectedUsers || selectedUsers.length === 0) {
          alert('Please select at least one user to assign.');
          return;
      }
      let button = $('#finalAssignBtn');
      button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Assigning...');
      $.ajax({
          url: "{{ route('leads.bulk-assign') }}",
          type: "POST",
          data: {
              _token: "{{ csrf_token() }}",
              lead_ids: $('#leadsTable .row-check:checked').map(function() { return $(this).val(); }).get(),
              user_ids: selectedUsers
          },
          success: function (res) {
              if (res.status) {
                  ApplyFilter(); 
                  $('#Assignmodel').modal('hide');
              } else {
                  alert(res.message || 'Failed to assign leads.');
              }
          },
          error: function (err) {
              alert(err.responseJSON?.message || 'Something went wrong.');
          },
          complete: function() {
              button.prop('disabled', false).html('<i class="bi bi-plus-lg"></i> Assign');  
          }
    });

    };
  });
  </script>
@endpush
