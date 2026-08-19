@extends('layouts.app')
@section('title', 'Lead Sources')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Lead Sources</h2>
      <p>Manage lead source options</p>
    </div>
    <div class="gs-page-topbar-actions">
      @can('create lead-sources')
        <a href="{{ route('settings.lead-sources.create') }}" class="gs-btn gs-btn--primary">
          <i class="bi bi-plus-lg"></i> Add Lead Source
        </a>
      @endcan
    </div>
  </div>

  <div class="gs-table-wrap">
    <table class="gs-table" id="leadSourceTable" style="width:100%;">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Icon</th>
          <th>Sort Order</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($sources as $source)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>
            <div style="display:flex; align-items:center; gap:8px;">
              @if($source->icon)
                <i class="bi {{ $source->icon }}" style="font-size:16px; color:var(--primary-color);"></i>
              @endif
              <span class="gs-table-company">{{ $source->name }}</span>
            </div>
          </td>
          <td>
            @if($source->icon)
                <i class="bi {{ $source->icon }}" style="font-size:16px; color:var(--primary-color);"></i>
            @endif
            <!--<code style="font-size:var(--fs-xs); background:var(--bg-page); padding:2px 6px; border-radius:4px;">-->
            <!--  {{ $source->icon ?? '—' }}-->
            <!--</code>-->
          </td>
          <td>{{ $source->sort_order }}</td>
          <td data-search="{{ ucfirst($source->status) }}">
            @can('edit lead-sources')
              <form method="POST" action="{{ route('settings.lead-sources.toggle-status', $source) }}" style="display:inline;">
                @csrf @method('PATCH')
                <button type="submit"
                  class="gs-status {{ $source->status === 'active' ? 'gs-status--active' : 'gs-status--lost' }}"
                  style="border:none; cursor:pointer;">
                  {{ ucfirst($source->status) }}
                </button>
              </form>
            @else
              <span class="gs-status {{ $source->status === 'active' ? 'gs-status--active' : 'gs-status--lost' }}">
                {{ ucfirst($source->status) }}
              </span>
            @endcan
          </td>
          <td>
            <div style="display:flex; align-items:center; gap:6px;">
              @can('edit lead-sources')
                <a href="{{ route('settings.lead-sources.edit', $source) }}" class="gs-edit-btn" title="Edit">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              @endcan
              @can('delete lead-sources')
                <form method="POST" action="{{ route('settings.lead-sources.destroy', $source) }}"
                      data-confirm="Delete {{ $source->name }}?" data-confirm-btn="Delete" style="display:inline;">
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

      var table = $('#leadSourceTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], ['5', '10', '25', '50']],
        ordering:   true,
        order:      [[3, 'asc']],
        columnDefs: [
          { orderable: false, targets: [5] },
          { type: 'string', targets: [4] }
        ],
        scrollX:   true,
        autoWidth: false,
        language: {
          lengthMenu:  'Rows per page: _MENU_',
          info:        'Showing _START_–_END_ of _TOTAL_ sources',
          infoEmpty:   'No sources found',
          zeroRecords: 'No matching sources found',
          paginate: {
            previous: '<i class="bi bi-chevron-left"></i>',
            next:     '<i class="bi bi-chevron-right"></i>'
          }
        },
      });

      // Status filter using data-search attribute
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var statusFilter = $('#filterStatus').val().toLowerCase();
        if (!statusFilter) return true;
        var statusCell = $(table.row(dataIndex).node()).find('td').eq(4).data('search') || '';
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
