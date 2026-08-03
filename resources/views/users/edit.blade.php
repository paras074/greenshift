@extends('layouts.app')
@section('title', 'Edit User')
@section('content')

  {{-- Top Bar --}}
  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Edit User — {{ $user->name }}</h2>
      <p>Update user details, role and password</p>
    </div>
    @can('view users')
      <div class="gs-page-topbar-actions">
        <a href="{{ route('users.index') }}" class="gs-btn gs-btn--outline">
          <i class="bi bi-arrow-left"></i> Back to Users
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

  <form method="POST" action="{{ route('users.update', $user) }}">
    @csrf @method('PUT')
    <div class="gs-common-grid">

      {{-- LEFT: User Details --}}
      <div class="gs-left-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">User Details</h5>
          </div>
          <div class="gs-panel-body">
            <div class="gs-form-grid">

              <div class="gs-field gs-field--full">
                <label class="gs-label">Full Name <span class="gs-required">*</span></label>
                <input type="text" name="name" class="gs-input @error('name') gs-input--error @enderror"
                  value="{{ old('name', $user->name) }}">
                @error('name')
                  <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
                @enderror
              </div>

              <div class="gs-field">
                <label class="gs-label">Email Address <span class="gs-required">*</span></label>
                <input type="email" name="email" class="gs-input @error('email') gs-input--error @enderror"
                  value="{{ old('email', $user->email) }}">
                @error('email')
                  <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
                @enderror
              </div>

              <div class="gs-field">
                <label class="gs-label">Phone</label>
                <input type="text" name="phone" class="gs-input"
                  value="{{ old('phone', $user->phone) }}">
              </div>

            </div>
          </div>
        </div>

        {{-- Password Section --}}
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Change Password</h5>
          </div>
          <div class="gs-panel-body">
            <p style="font-size:var(--fs-xs); color:var(--text-secondary); margin-bottom:16px;">Leave password fields blank to keep the current password.</p>
            <div class="gs-form-grid">

              <div class="gs-field">
                <label class="gs-label">New Password</label>
                <input type="password" name="password" class="gs-input @error('password') gs-input--error @enderror"
                  placeholder="Leave blank to keep current">
                @error('password')
                  <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
                @enderror
              </div>

              <div class="gs-field">
                <label class="gs-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="gs-input"
                  placeholder="Repeat new password">
              </div>

            </div>
          </div>
        </div>
      </div>

      {{-- RIGHT: Role & Status --}}
      <div class="gs-right-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Role & Status</h5>
          </div>
          <div class="gs-panel-body">
            <div class="gs-form-grid gs-form-grid--full" style="gap:14px;">

              <div class="gs-field">
                <label class="gs-label">Role <span class="gs-required">*</span></label>
                <select name="role" class="gs-select @error('role') gs-input--error @enderror">
                  <option value="">Select Role</option>
                  @foreach($roles as $role)
                    <option value="{{ $role->name }}"
                      {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                      {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </option>
                  @endforeach
                </select>
                @error('role')
                  <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
                @enderror
              </div>

              <div class="gs-field">
                <label class="gs-label">Status <span class="gs-required">*</span></label>
                <select name="status" class="gs-select">
                  <option value="active"   {{ old('status', $user->status) == 'active'   ? 'selected' : '' }}>Active</option>
                  <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>

            </div>
          </div>
        </div>

        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Update User</h5>
          </div>
          <div class="gs-panel-body" style="display:flex; flex-direction:column; gap:10px;">
            @can('edit users')
              <button type="submit" class="gs-btn gs-btn--primary" style="width:100%; justify-content:center;">
                <i class="bi bi-check-lg"></i> Update User
              </button>
            @endcan
            @can('view users')
              <a href="{{ route('users.index') }}" class="gs-btn gs-btn--outline" style="width:100%; justify-content:center;">
                <i class="bi bi-x-lg"></i> Cancel
              </a>
            @endcan
          </div>
        </div>
      </div>

    </div>
  </form>

@endsection
