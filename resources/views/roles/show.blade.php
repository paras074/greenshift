@extends('layouts.app')
@section('title', 'View Role')
@section('content')
  {{-- Top Bar --}}
  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Role: {{ ucwords(str_replace('_', ' ', $role->name)) }}</h2>
      <p>View permissions and assigned users for this role</p>
    </div>
    <div class="gs-page-topbar-actions">
      @can('edit roles')
        @if($role->name !== 'superadmin')
          <a href="{{ route('roles.edit', $role) }}" class="gs-btn gs-btn--outline">
            <i class="bi bi-pencil-fill"></i> Edit Role
          </a>
        @endif
      @endcan
      @can('view roles')
        <a href="{{ route('roles.index') }}" class="gs-btn gs-btn--primary">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      @endcan
    </div>
  </div>
  <div class="gs-common-grid">
    {{-- LEFT: Permissions --}}
    <div class="gs-left-grid">
      <div class="gs-panel">
        <div class="gs-panel-header">
          <h5 class="gs-panel-title">Permissions <span style="font-family:var(--body-font); font-size:var(--fs-xs); color:var(--text-secondary); font-weight:500;">({{ $role->permissions->count() }} total)</span></h5>
        </div>
        <div class="gs-panel-body">
          @if($role->name === 'superadmin')
            <div class="gs-alert gs-alert--success" style="display:flex;">
              <i class="bi bi-star-fill"></i>
              Superadmin has <strong>all permissions</strong> via Gate bypass — no restrictions apply.
            </div>
          @else
            <div class="gs-perm-modules">
              @forelse($permissions as $module => $modulePermissions)
              <div class="gs-perm-module">
                <div class="gs-perm-module-header">
                  <span class="gs-perm-module-name">{{ ucfirst($module) }}</span>
                </div>
                <div class="gs-perm-list">
                  @foreach($modulePermissions as $permission)
                    {{-- Show only the action word (first word), not full permission name --}}
                    <span class="gs-status gs-status--active" style="margin:2px;">
                      {{ ucfirst(explode(' ', $permission->name)[0]) }}
                    </span>
                  @endforeach
                </div>
              </div>
              @empty
                <p style="color:var(--text-secondary); font-size:var(--fs-sm);">No permissions assigned.</p>
              @endforelse
            </div>
          @endif
        </div>
      </div>
    </div>
    {{-- RIGHT: Assigned Users --}}
    <div class="gs-right-grid">
      <div class="gs-panel">
        <div class="gs-panel-header">
          <h5 class="gs-panel-title">Assigned Users <span style="font-family:var(--body-font); font-size:var(--fs-xs); color:var(--text-secondary); font-weight:500;">({{ $role->users->count() }} total)</span></h5>
        </div>
        <div class="gs-panel-body" style="padding:0;">
          @forelse($role->users as $user)
          <div class="gs-role-user-row">
            <div class="gs-role-user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="gs-role-user-info">
              <span class="gs-role-user-name">{{ $user->name }}</span>
              <span class="gs-role-user-email">{{ $user->email }}</span>
            </div>
            <span class="gs-status {{ $user->status === 'active' ? 'gs-status--active' : 'gs-status--lost' }}">
              {{ ucfirst($user->status) }}
            </span>
          </div>
          @empty
            <div style="padding:20px; color:var(--text-secondary); font-size:var(--fs-sm);">No users assigned to this role.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
@endsection