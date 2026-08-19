@extends('layouts.app')
@section('title', 'Roles Management')
@section('content')

  {{-- Top Bar --}}
  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Roles Management</h2>
      <p>Manage system roles and their permissions</p>
    </div>
    <div class="gs-page-topbar-actions">
      @can('create roles')
        <a href="{{ route('roles.create') }}" class="gs-btn gs-btn--primary">
          <i class="bi bi-plus-lg"></i> Add Role
        </a>
      @endcan
    </div>
  </div>

  {{-- Roles Grid --}}
  <div class="gs-roles-grid">
    @forelse($roles as $role)
    <div class="gs-role-card gs-role-card--{{ $role->name === 'superadmin' ? 'purple' : ($role->name === 'admin' ? 'blue' : ($role->name === 'manager' ? 'green' : 'default')) }}">
      <div class="gs-role-card-header">
        <div>
          <h5 class="gs-role-card-name">{{ ucwords(str_replace('_', ' ', $role->name)) }}</h5>
          <p class="gs-role-card-meta">{{ $role->users_count }} user{{ $role->users_count !== 1 ? 's' : '' }} assigned</p>
        </div>
        @php if($role->name == 'superadmin'){ @endphp
          <span class="gs-status gs-status--pending">All permissions</span>
        @php } else {@endphp
        <span class="gs-status gs-status--pending">{{ $role->permissions_count }} permissions</span>
        @php } @endphp
      </div>
      <div class="gs-role-card-actions">
        @can('view roles')
          <a href="{{ route('roles.show', $role) }}" class="gs-btn gs-btn--outline gs-btn--sm">
            <i class="bi bi-eye-fill"></i> View
          </a>
        @endcan
        @can('edit roles')
          @if($role->name !== 'superadmin')
            <a href="{{ route('roles.edit', $role) }}" class="gs-btn gs-btn--outline gs-btn--sm">
              <i class="bi bi-pencil-fill"></i> Edit
            </a>
          @endif
        @endcan
        @can('delete roles')
          @if(!in_array($role->name, ['superadmin']))
            <form method="POST" action="{{ route('roles.destroy', $role) }}" data-confirm="Delete role {{ $role->name }}?" data-confirm-btn="Delete">
              @csrf @method('DELETE')
              <button type="submit" class="gs-btn gs-btn--danger gs-btn--sm">
                <i class="bi bi-trash-fill"></i> Delete
              </button>
            </form>
          @endif
        @endcan
      </div>
    </div>
    @empty
    <div class="gs-table-wrap" style="grid-column:1/-1;">
      <div class="dataTables_wrapper">
        <div class="dataTables_empty">No roles found.</div>
      </div>
    </div>
    @endforelse
  </div>

@endsection
