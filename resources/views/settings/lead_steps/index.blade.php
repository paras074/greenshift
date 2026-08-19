@extends('layouts.app')
@section('title', 'Lead Steps')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Lead Steps</h2>
      <p>Manage lead step options (drag and drop to reorder)</p>
    </div>
    <div class="gs-page-topbar-actions">
      @can('view manage-lead-funnel')
        <a href="{{ route('settings.lead-steps.create') }}" class="gs-btn gs-btn--primary">
          <i class="bi bi-plus-lg"></i> Add Lead Step
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
          <th>Status</th>
          <th style="width: 100px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($steps as $step)
        <tr data-id="{{ $step->id }}">
            <td class="reorder-handle"><i class="bi bi-grip-vertical"></i> {{ $loop->iteration }}</td>
            <td>{{ $step->name }}</td>
            <td>
                <span class="badge {{ $step->status ? 'bg-success' : 'bg-secondary' }}">
                    {{ $step->status ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td>
                <div style="display:flex; align-items:center; gap:6px;">
                @can('edit lead-steps')
                    <a href="{{ route('settings.lead-steps.edit', $step) }}" class="gs-edit-btn" title="Edit">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                @endcan
                
                @can('delete lead-steps')
                    <form method="POST" action="{{ route('settings.lead-steps.destroy', $step) }}"
                        data-confirm="Delete {{ $step->name }}?" data-confirm-btn="Delete" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="gs-edit-btn" title="Delete"
                            style="border-color:rgba(220,38,38,0.3); color:#dc2626; background:none; cursor:pointer;">
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
<style>
    .reorder-handle { cursor: move; color: #ccc; }
    tr.reorder-target-row { background-color: #f0f7ff !important; }
</style>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
 <script>
$(document).ready(function () {
    // Initialize the table ONCE with all settings
    var table = $('#leadSourceTable').DataTable({
        // 1. Drag and Drop Settings
        rowReorder: {
            selector: 'td:first-child', 
            update: false 
        },
        // 2. Display & Pagination Settings
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        ordering: true,
        order: [[0, 'asc']],
        // 3. Column Logic
        columnDefs: [
            { orderable: false, targets: [0, 3] }
        ],
        scrollX: true,
        autoWidth: false,
        // 4. Custom Language/Labels
        language: {
            lengthMenu: 'Rows per page: _MENU_',
            info: 'Showing _START_–_END_ of _TOTAL_ steps',
            infoEmpty: 'No Steps found',
            zeroRecords: 'No matching Steps found',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            }
        },
    });

    // 5. AJAX Reorder Event
    table.on('row-reorder', function (e, diff, edit) {
        var newOrder = [];
        
        for (var i = 0; i < diff.length; i++) {
            var rowData = $(diff[i].node);
            newOrder.push({
                id: rowData.data('id'),
                position: diff[i].newPosition + 1
            });
        }

        if (newOrder.length > 0) {
            $.ajax({
                url: "{{ route('settings.lead-steps.reorder') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order: newOrder
                },
                success: function(response) {
                    console.log('Order updated');
                    // Optional: show a quick toast notification here
                },
                error: function() {
                    window.miniAlert('Error updating order');
                }
            });
        }
    });
});
</script> 
@endpush