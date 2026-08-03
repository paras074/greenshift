@extends('layouts.app')
@section('title', 'Edit Priority')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Edit Priority — {{ $priorityStatus->name }}</h2>
      <p>Update priority level details</p>
    </div>
    @can('view priority-statuses')
      <div class="gs-page-topbar-actions">
        <a href="{{ route('settings.priority-statuses.index') }}" class="gs-btn gs-btn--outline">
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

  <form method="POST" action="{{ route('settings.priority-statuses.update', $priorityStatus) }}">
    @csrf @method('PUT')
    <div class="gs-common-grid">
      <div class="gs-left-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Priority Details</h5>
          </div>
          <div class="gs-panel-body">

            <div class="gs-field">
              <label class="gs-label">Name <span class="gs-required">*</span></label>
              <input type="text" name="name" class="gs-input @error('name') gs-input--error @enderror"
                value="{{ old('name', $priorityStatus->name) }}">
              @error('name')
                <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
              @enderror
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Badge Color <span class="gs-required">*</span></label>
              <div style="display:flex; gap:10px; align-items:center;">
                <input type="color" name="color" id="colorPicker"
                  value="{{ old('color', $priorityStatus->color) }}"
                  style="width:44px; height:38px; border:1px solid var(--border-color); border-radius:6px; cursor:pointer; padding:2px;"
                  oninput="updateColorPreview(this.value)">
                <input type="text" id="colorText"
                  class="gs-input" style="max-width:120px;"
                  value="{{ old('color', $priorityStatus->color) }}"
                  placeholder="#6c757d"
                  oninput="syncColorPicker(this.value)">
                <span id="colorPreviewBadge" class="gs-status"
                  style="background:{{ old('color', $priorityStatus->color) }}1a; color:{{ old('color', $priorityStatus->color) }}; border:1px solid {{ old('color', $priorityStatus->color) }}40;">
                  {{ $priorityStatus->name }}
                </span>
              </div>
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Sort Order</label>
              <input type="number" name="sort_order" class="gs-input"
                value="{{ old('sort_order', $priorityStatus->sort_order) }}" min="0">
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Status <span class="gs-required">*</span></label>
              <select name="status" class="gs-select">
                <option value="active"   {{ old('status', $priorityStatus->status) === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $priorityStatus->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

          </div>
        </div>
      </div>

      <div class="gs-right-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Update</h5>
          </div>
          <div class="gs-panel-body" style="display:flex; flex-direction:column; gap:10px;">
            @can('edit priority-statuses')
              <button type="submit" class="gs-btn gs-btn--primary" style="width:100%; justify-content:center;">
                <i class="bi bi-check-lg"></i> Update Priority
              </button>
            @endcan
            @can('view priority-statuses')
              <a href="{{ route('settings.priority-statuses.index') }}" class="gs-btn gs-btn--outline" style="width:100%; justify-content:center;">
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
