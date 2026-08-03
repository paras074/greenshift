@extends('layouts.app')
@section('title', 'Add Lead Status')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Add Lead Status</h2>
      <p>Create a new lead status option</p>
    </div>
    @can('view lead-statuses')
      <div class="gs-page-topbar-actions">
        <a href="{{ route('settings.lead-statuses.index') }}" class="gs-btn gs-btn--outline">
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

  <form method="POST" action="{{ route('settings.lead-statuses.store') }}">
    @csrf
    <div class="gs-common-grid">
      <div class="gs-left-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Status Details</h5>
          </div>
          <div class="gs-panel-body">

            <div class="gs-field">
              <label class="gs-label">Name <span class="gs-required">*</span></label>
              <input type="text" name="name" class="gs-input @error('name') gs-input--error @enderror"
                value="{{ old('name') }}" placeholder="e.g. New, Active, Won, Lost">
              @error('name')
                <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
              @enderror
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Badge Color <span class="gs-required">*</span></label>
              <div style="display:flex; gap:10px; align-items:center;">
                <input type="color" name="color" id="colorPicker"
                  value="{{ old('color', '#6c757d') }}"
                  style="width:44px; height:38px; border:1px solid var(--border-color); border-radius:6px; cursor:pointer; padding:2px;"
                  oninput="updateColorPreview(this.value)">
                <input type="text" id="colorText"
                  class="gs-input" style="max-width:120px;"
                  value="{{ old('color', '#6c757d') }}"
                  placeholder="#6c757d"
                  oninput="syncColorPicker(this.value)">
                <span id="colorPreviewBadge" class="gs-status"
                  style="background:{{ old('color', '#6c757d') }}1a; color:{{ old('color', '#6c757d') }}; border:1px solid {{ old('color', '#6c757d') }}40;">
                  Preview
                </span>
              </div>
              @error('color')
                <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
              @enderror
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Sort Order</label>
              <input type="number" name="sort_order" class="gs-input"
                value="{{ old('sort_order', 0) }}" min="0">
              <span class="gs-role-hint">Lower number = appears first in dropdowns.</span>
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Status <span class="gs-required">*</span></label>
              <select name="status" class="gs-select">
                <option value="active"   {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Show on Kanban Board</label>
              <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                <input type="hidden" name="show_kanban" value="0">
                
                <label class="vl-switch">
                  <input type="checkbox" name="show_kanban" value="1" {{ old('show_kanban', 1) ? 'checked' : '' }}>
                  <span class="vl-slider"></span>
                </label>
                
                <span class="gs-role-hint" style="margin:0;">Enable this to display this status as a column in the Lead Funnel.</span>
              </div>
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
            @can('create lead-statuses')
              <button type="submit" class="gs-btn gs-btn--primary" style="width:100%; justify-content:center;">
                <i class="bi bi-check-lg"></i> Create Status
              </button>
            @endcan
            @can('view lead-statuses')
              <a href="{{ route('settings.lead-statuses.index') }}" class="gs-btn gs-btn--outline" style="width:100%; justify-content:center;">
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
  function updateColorPreview(hex) {
    document.getElementById('colorText').value = hex;
    const badge = document.getElementById('colorPreviewBadge');
    badge.style.background = hex + '1a';
    badge.style.color = hex;
    badge.style.border = '1px solid ' + hex + '40';
  }
  function syncColorPicker(hex) {
    if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
      document.getElementById('colorPicker').value = hex;
      updateColorPreview(hex);
    }
  }
</script>
@endpush
