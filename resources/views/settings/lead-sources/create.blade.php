@extends('layouts.app')
@section('title', 'Add Lead Source')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Add Lead Source</h2>
      <p>Create a new lead source option</p>
    </div>
    @can('view lead-sources')
      <div class="gs-page-topbar-actions">
        <a href="{{ route('settings.lead-sources.index') }}" class="gs-btn gs-btn--outline">
          <i class="bi bi-arrow-left"></i> Back
        </a>
      </div>
    @endcan
  </div>

  @if($errors->any())
    <div class="gs-alert gs-alert--error" style="margin-bottom:16px; display:flex; flex-direction:column; gap:4px;">
      @foreach($errors->all() as $error)
        <span><i class="bi bi-exclamation-circle-fill"></i> {{ $error }}</span>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('settings.lead-sources.store') }}">
    @csrf
    <div class="gs-common-grid">
      <div class="gs-left-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Lead Source Details</h5>
          </div>
          <div class="gs-panel-body">

            <div class="gs-field">
              <label class="gs-label">Name <span class="gs-required">*</span></label>
              <input type="text" name="name" class="gs-input @error('name') gs-input--error @enderror"
                value="{{ old('name') }}" placeholder="e.g. Website, Referral, Call">
              @error('name')
                <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
              @enderror
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Bootstrap Icon Class</label>
              <div style="display:flex; gap:8px; align-items:center;">
                <input type="text" name="icon" id="iconInput"
                  class="gs-input" value="{{ old('icon') }}"
                  placeholder="e.g. bi-globe, bi-telephone, bi-people"
                  oninput="previewIcon(this.value)">
                <i id="iconPreview" class="bi" style="font-size:22px; color:var(--primary-color); min-width:24px;"></i>
              </div>
              <span class="gs-role-hint">
                Find icons at <a href="https://icons.getbootstrap.com" target="_blank">icons.getbootstrap.com</a>
              </span>
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Sort Order</label>
              <input type="number" name="sort_order" class="gs-input"
                value="{{ old('sort_order', 0) }}" min="0" placeholder="0">
              <span class="gs-role-hint">Lower number = appears first in dropdowns.</span>
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Status <span class="gs-required">*</span></label>
              <select name="status" class="gs-select">
                <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

          </div>
        </div>
      </div>

      <div class="gs-right-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Save</h5>
          </div>
          <div class="gs-panel-body" style="display:flex; flex-direction:column; gap:10px;">
            @can('create lead-sources')
              <button type="submit" class="gs-btn gs-btn--primary" style="width:100%; justify-content:center;">
                <i class="bi bi-check-lg"></i> Create Lead Source
              </button>
            @endcan
            @can('view lead-sources')
              <a href="{{ route('settings.lead-sources.index') }}" class="gs-btn gs-btn--outline" style="width:100%; justify-content:center;">
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
  function previewIcon(val) {
    const el = document.getElementById('iconPreview');
    el.className = 'bi ' + val;
  }
</script>
@endpush
