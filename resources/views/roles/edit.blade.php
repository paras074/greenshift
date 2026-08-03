@extends('layouts.app')
@section('title', 'Edit Role')
@section('content')

  {{-- Top Bar --}}
  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Edit Role — {{ ucwords(str_replace('_', ' ', $role->name)) }}</h2>
      <p>Update role name and permissions</p>
    </div>
    @can('view roles')
      <div class="gs-page-topbar-actions">
        <a href="{{ route('roles.index') }}" class="gs-btn gs-btn--outline">
          <i class="bi bi-arrow-left"></i> Back to Roles
        </a>
      </div>
    @endcan
  </div>

  {{-- Error Alert --}}
  @if($errors->any())
    <div class="gs-alert gs-alert--error" style="margin-bottom:16px; display:flex; flex-direction:column; gap:4px;">
      @foreach($errors->all() as $error)
        <span><i class="bi bi-exclamation-circle-fill"></i> {{ $error }}</span>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('roles.update', $role) }}">
    @csrf @method('PUT')
    <div class="gs-common-grid">

      {{-- LEFT --}}
      <div class="gs-left-grid">
        {{-- Role Details Panel --}}
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Role Details</h5>
          </div>
          <div class="gs-panel-body">
            <input type="hidden" name="name" value="{{ $role->name }}">
            <div class="gs-field">
              <label class="gs-label">Role Name</label>
              <input type="text"
                class="gs-input"
                value="{{ ucwords(str_replace('_', ' ', $role->name)) }}"
                disabled
                style="background: var(--bg-secondary); cursor: not-allowed; opacity: 0.7;">
              <span class="gs-role-hint">
                <i class="bi bi-lock-fill"></i> Role name cannot be changed to preserve system integrity.
              </span>
            </div>
          </div>
        </div>

        {{-- Permissions --}}
        @can('assign-permissions roles')
          <div class="gs-panel">
            <div class="gs-panel-header" style="display:flex; align-items:center; justify-content:space-between;">
              <h5 class="gs-panel-title">Assign Permissions</h5>
              <div class="gs-page-topbar-actions">
                <button type="button" onclick="toggleAll(true)" class="gs-btn gs-btn--outline gs-btn--sm">
                  <i class="bi bi-check-all"></i> Select All
                </button>
                <button type="button" onclick="toggleAll(false)" class="gs-btn gs-btn--outline gs-btn--sm">
                  <i class="bi bi-x-lg"></i> Deselect All
                </button>
              </div>
            </div>
            <div class="gs-panel-body">
              <div class="gs-perm-modules">
                @foreach($permissions as $module => $modulePermissions)
                <div class="gs-perm-module">
                  <div class="gs-perm-module-header">
                    <span class="gs-perm-module-name">{{ ucfirst($module) }}</span>
                    <button type="button" onclick="toggleModule('{{ $module }}')" class="gs-btn gs-btn--outline gs-btn--sm">
                      Toggle All
                    </button>
                  </div>
                  <div class="gs-perm-list" id="module-{{ $module }}">
                    @foreach($modulePermissions as $permission)
                    <label class="gs-perm-item">
                      <input type="checkbox"
                        name="permissions[]"
                        value="{{ $permission->id }}"
                        data-module="{{ $module }}"
                        class="gs-table-checkbox perm-checkbox"
                        {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                      <span class="text-capitalize">{{ explode(' ', $permission->name)[0] }}</span>
                    </label>
                    @endforeach
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        @endcan
      </div>

      {{-- RIGHT: Actions --}}
      <div class="gs-right-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Update Role</h5>
          </div>
          <div class="gs-panel-body" style="display:flex; flex-direction:column; gap:10px;">
            @can('edit roles')
            <button type="submit" class="gs-btn gs-btn--primary" style="width:100%; justify-content:center;">
              <i class="bi bi-check-lg"></i> Update Role
            </button>
            @endcan
            @can('view roles')
              <a href="{{ route('roles.index') }}" class="gs-btn gs-btn--outline" style="width:100%; justify-content:center;">
                <i class="bi bi-x-lg"></i> Cancel
              </a>
            @endcan
          </div>
        </div>
      </div>

    </div>
  </form>

@endsection

@push('scripts')
<script>
  function toggleAll(state) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = state);
  }
  function toggleModule(module) {
    const checkboxes = document.querySelectorAll(`[data-module="${module}"]`);
    const allChecked = [...checkboxes].every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
  }
</script>
@endpush
