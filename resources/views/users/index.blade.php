@extends('layouts.app')
@section('title', 'User Management')
@section('content')

  {{-- Top Bar --}}
  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>User Management</h2>
      <p>View and manage system users</p>
    </div>
    <div class="gs-page-topbar-actions">
      @can('create users')
        <a href="{{ route('users.create') }}" class="gs-btn gs-btn--primary">
          <i class="bi bi-plus-lg"></i> Add User
        </a>
      @endcan
    </div>
  </div>

  {{-- Filter Bar --}}
  <div class="gs-filter-bar">
    <select class="gs-filter-select" id="filterRole">
      <option value="">All Roles</option>
      @foreach($roles as $role)
        <option value="{{ ucfirst(str_replace('_', ' ', $role->name)) }}">
          {{ ucfirst(str_replace('_', ' ', $role->name)) }}
        </option>
      @endforeach
    </select>

    <select class="gs-filter-select" id="filterStatus">
      <option value="">All Status</option>
      <option value="Active">Active</option>
      <option value="Inactive">Inactive</option>
    </select>

    <select class="gs-filter-select" id="filterRange">
      <option value="">All Time</option>
      <option value="today">Today</option>
      <option value="week">This Week</option>
      <option value="month">This Month</option>
    </select>

    <button class="gs-filter-btn-apply" id="applyFilter">Apply</button>
    <button class="gs-filter-btn-clear" id="clearFilter">Clear</button>
  </div>

  {{-- DataTable --}}
  <div class="gs-table-wrap">
    <table class="gs-table" id="usersTable" style="width:100%;">
      <thead>
        <tr>
          <th><input type="checkbox" class="gs-table-checkbox" id="selectAll"/></th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Role</th>
          <th>Status</th>
          <th>Created</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
        <tr>
          <td><input type="checkbox" class="gs-table-checkbox row-check"/></td>
          <td>
            <div style="display:flex; align-items:center; gap:10px;">
              <div class="gs-user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
              <span class="gs-table-company">{{ $user->name }}</span>
            </div>
          </td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->phone ?? '—' }}</td>
          {{-- data-search holds plain text for DataTable role filtering --}}
          <td data-search="{{ $user->roles->map(fn($r) => ucfirst(str_replace('_', ' ', $r->name)))->join(' ') }}">
            @foreach($user->roles as $role)
              <span class="gs-status gs-status--new">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
            @endforeach
          </td>
          {{-- data-search holds plain text for DataTable status filtering --}}
          <td data-search="{{ ucfirst($user->status) }}">
            @can('edit users')
              <form method="POST" action="{{ route('users.toggle-status', $user) }}" style="display:inline;">
                @csrf @method('PATCH')
                <button type="submit" class="gs-status {{ $user->status === 'active' ? 'gs-status--active' : 'gs-status--lost' }}" style="border:none; cursor:pointer;">
                  {{ ucfirst($user->status) }}
                </button>
              </form>
            @else
              <span class="gs-status {{ $user->status === 'active' ? 'gs-status--active' : 'gs-status--lost' }}">
                {{ ucfirst($user->status) }}
              </span>
            @endcan
          </td>
          <td>{{ $user->created_at->format('d M Y') }}</td>
          <td>
            <div style="display:flex; align-items:center; gap:6px;">
              @can('edit users')
                <a href="{{ route('users.edit', $user) }}" class="gs-edit-btn" title="Edit">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              @endcan
              @can('delete users')
                @if($user->id !== auth()->id())
                  <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete {{ $user->name }}?')" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="gs-edit-btn" title="Delete" style="border-color:rgba(220,38,38,0.3); color:#dc2626;">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </form>
                @endif
              @endcan
            </div>
          </td>
        </tr>
        @empty
        @endforelse
      </tbody>
    </table>
  </div>

@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function () {

      // Use data-search attribute for role & status columns
      var table = $('#usersTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], ['5', '10', '25', '50']],
        ordering:   true,
        order:      [[1, 'asc']],
        columnDefs: [
          { orderable: false, targets: [0, 7] },
          // Use data-search value for role column (index 4) and status column (index 5)
          { type: 'string', targets: [4, 5] }
        ],
        scrollX:   true,
        autoWidth: false,
        language: {
          lengthMenu:  'Rows per page: _MENU_',
          info:        'Showing _START_–_END_ of _TOTAL_ users',
          infoEmpty:   'No users found',
          zeroRecords: 'No matching users found',
          paginate: {
            previous: '<i class="bi bi-chevron-left"></i>',
            next:     '<i class="bi bi-chevron-right"></i>'
          }
        },
      });

      // Override DataTable's cell content reader to use data-search for columns 4 & 5
      $.fn.dataTable.ext.order['dom-text'] = function(settings, col) {
        return this.api().column(col, {order:'index'}).nodes().map(function(td) {
          return $(td).data('search') || $('input', td).val() || td.innerText || '';
        });
      };

      // Custom search for role & status using data-search attribute
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, row, counter) {
        var roleFilter   = $('#filterRole').val().toLowerCase();
        var statusFilter = $('#filterStatus').val().toLowerCase();

        if (!roleFilter && !statusFilter) return true;

        var roleCell   = $(table.row(dataIndex).node()).find('td').eq(4).data('search') || '';
        var statusCell = $(table.row(dataIndex).node()).find('td').eq(5).data('search') || '';

        var roleMatch   = !roleFilter   || roleCell.toLowerCase().indexOf(roleFilter) !== -1;
        var statusMatch = !statusFilter || statusCell.toLowerCase().indexOf(statusFilter) !== -1;

        return roleMatch && statusMatch;
      });

      // Date range filter using created column (index 6)
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var range = $('#filterRange').val();
        if (!range) return true;

        var dateText = data[6]; // e.g. "05 Jan 2025"
        var rowDate  = new Date(dateText);
        var now      = new Date();
        var start    = new Date();

        if (range === 'today') {
          start.setHours(0,0,0,0);
        } else if (range === 'week') {
          start.setDate(now.getDate() - now.getDay());
          start.setHours(0,0,0,0);
        } else if (range === 'month') {
          start = new Date(now.getFullYear(), now.getMonth(), 1);
        }

        return rowDate >= start && rowDate <= now;
      });

      // Select All
      $('#selectAll').on('change', function () {
        $('.row-check').prop('checked', this.checked);
      });

      // Apply Filter
      $('#applyFilter').on('click', function () {
        table.draw();
      });

      // Clear Filter
      $('#clearFilter').on('click', function () {
        $('#filterRole, #filterStatus, #filterRange').val('');
        table.draw();
      });
    });
  </script>
@endpush
