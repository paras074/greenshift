@extends('layouts.app')
@section('title', 'Templates')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Templates</h2>
      <p>Manage your Email &amp; LOA templates</p>
    </div>
    <div class="gs-page-topbar-actions">
      @can('create templates')
        <a href="{{ route('templates.create') }}" class="gs-btn gs-btn--primary">
          <i class="bi bi-plus-lg"></i> Add New Template
        </a>
      @endcan
    </div>
  </div>

  <div class="gs-table-wrap">
    <table class="gs-table" id="templatesTable" style="width:100%;">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Type</th>
          <th>Subject</th>
          <th>Status</th>
          <th>Last Updated</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($templates as $template)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td><span class="gs-table-company">{{ $template->name }}</span></td>
          <td>
            <span class="gs-status {{ $template->type === 'loa' ? 'gs-status--active' : 'gs-status--new' }}">
              {{ strtoupper($template->type) }}
            </span>
          </td>
          <td>{{ $template->subject ?? '—' }}</td>
          <td data-search="{{ $template->is_active ? 'Active' : 'Inactive' }}">
            @if($template->is_active)
              <span class="gs-status gs-status--active">Active</span>
            @else
              @can('edit templates')
                <form method="POST" action="{{ route('templates.set-active', $template) }}" style="display:inline;">
                  @csrf @method('PATCH')
                  <button type="submit" class="gs-status gs-status--lost" style="border:none; cursor:pointer;"
                    title="Make this the active {{ $template->type }} template">
                    Inactive
                  </button>
                </form>
              @else
                <span class="gs-status gs-status--lost">Inactive</span>
              @endcan
            @endif
          </td>
          <td>{{ $template->updated_at ? $template->updated_at->format('d M Y, H:i') : '—' }}</td>
          <td>
            <div style="display:flex; align-items:center; gap:6px;">
              @can('edit templates')
                <a href="{{ route('templates.edit', $template) }}" class="gs-edit-btn" title="Edit">
                  <i class="bi bi-pencil-fill"></i>
                </a>
              @endcan
              @can('delete templates')
                <form method="POST" action="{{ route('templates.destroy', $template) }}"
                      onsubmit="return confirm('Delete template: {{ $template->name }}?')" style="display:inline;">
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
      $('#templatesTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], ['5', '10', '25', '50']],
        ordering:   true,
        order:      [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: [6] }],
        scrollX:   true,
        autoWidth: false,
        language: {
          lengthMenu:  'Rows per page: _MENU_',
          info:        'Showing _START_–_END_ of _TOTAL_ templates',
          infoEmpty:   'No templates found',
          zeroRecords: 'No matching templates found',
          paginate: {
            previous: '<i class="bi bi-chevron-left"></i>',
            next:     '<i class="bi bi-chevron-right"></i>'
          }
        }
      });
    });
  </script>
@endpush
