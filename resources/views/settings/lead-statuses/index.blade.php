@extends('layouts.app')
@section('title', 'Lead Status')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Lead Status</h2>
      <p>Manage lead status options</p>
    </div>
    <div class="gs-page-topbar-actions">
      @can('create lead-statuses')
        <a href="{{ route('settings.lead-statuses.create') }}" class="gs-btn gs-btn--primary">
          <i class="bi bi-plus-lg"></i> Add Lead Status
        </a>
      @endcan
    </div>
  </div>

  <div class="gs-table-wrap">
    <table class="gs-table" id="leadStatusTable" style="width:100%;">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Color</th>
          <th>Preview</th>
          <th>Sort Order</th>
          <th>Kanban</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($statuses as $status)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td class="gs-table-company">{{ $status->name }}</td>
          <td>
            <div style="display:flex; align-items:center; gap:6px;">
              <div style="width:20px; height:20px; border-radius:4px; background:{{ $status->color }}; border:1px solid var(--border-color);"></div>
              <!--<code style="font-size:var(--fs-xs);">{{ $status->color }}</code>-->
            </div>
          </td>
          <td>
            <span class="gs-status"
              style="background:{{ $status->color }}1a; color:{{ $status->color }}; border:1px solid {{ $status->color }}40;">
              {{ $status->name }}
            </span>
          </td>
          <td>{{ $status->sort_order }}</td>
          
          <td>
            @if($status->show_kanban)
              <span class="text-success fw-bold" style="font-size: 0.85rem;"><i class="bi bi-check-circle-fill"></i> Yes</span>
            @else
              <span class="text-muted" style="font-size: 0.85rem;"><i class="bi bi-x-circle-fill"></i> No</span>
            @endif
          </td>
          <td data-search="{{ ucfirst($status->status) }}">
            @can('edit lead-statuses')
              <form method="POST" action="{{ route('settings.lead-statuses.toggle-status', $status) }}" style="display:inline;">
                @csrf @method('PATCH')
                <button type="submit"
                  class="gs-status {{ $status->status === 'active' ? 'gs-status--active' : 'gs-status--lost' }}"
                  style="border:none; cursor:pointer;">
                  {{ ucfirst($status->status) }}
                </button>
              </form>
            @else
              <span class="gs-status {{ $status->status === 'active' ? 'gs-status--active' : 'gs-status--lost' }}">
                {{ ucfirst($status->status) }}
              </span>
            @endcan
          </td>
          <td>
            <div style="display:flex; align-items:center; gap:6px;">
              @can('edit lead-statuses')
                <a href="{{ route('settings.lead-statuses.edit', $status) }}" class="gs-edit-btn" title="Edit">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              @endcan
              @can('delete lead-statuses')
                <form method="POST" action="{{ route('settings.lead-statuses.destroy', $status) }}"
                      data-confirm="Delete {{ $status->name }}?" data-confirm-btn="Delete" style="display:inline;">
                  @csrf @method('DELETE')
                  <button type="submit" class="gs-edit-btn" title="Delete"
                    style="border-color:rgba(220,38,38,0.3); color:#dc2626;">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </form>
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

      var table = $('#leadStatusTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], ['5', '10', '25', '50']],
        ordering:   true,
        order:      [[4, 'asc']],
        columnDefs: [
          { orderable: false, targets: [6] },
          { type: 'string', targets: [5] }
        ],
        scrollX:   true,
        autoWidth: false,
        language: {
          lengthMenu:  'Rows per page: _MENU_',
          info:        'Showing _START_–_END_ of _TOTAL_ statuses',
          infoEmpty:   'No statuses found',
          zeroRecords: 'No matching statuses found',
          paginate: {
            previous: '<i class="bi bi-chevron-left"></i>',
            next:     '<i class="bi bi-chevron-right"></i>'
          }
        },
      });

      // Status filter using data-search attribute
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var $filterStatus = $('#filterStatus');
        if (!$filterStatus.length) return true; // no status-filter UI on this page
        var statusFilter = ($filterStatus.val() || '').toLowerCase();
        if (!statusFilter) return true;
        var statusCell = $(table.row(dataIndex).node()).find('td').eq(5).data('search') || '';
        return statusCell.toLowerCase().indexOf(statusFilter) !== -1;
      });

      $('#applyFilter').on('click', function () { table.draw(); });
      $('#clearFilter').on('click', function () {
        $('#filterStatus').val('');
        table.draw();
      });
    });
  </script>
@endpush
