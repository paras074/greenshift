@extends('layouts.app')
@section('title', 'Edit Lead Step')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Edit Lead Step — {{ $leadStep->name }}</h2>
      <p>Update lead step details</p>
    </div>
    <div class="gs-page-topbar-actions">
    <a href="{{ route('settings.lead-steps.index') }}" class="gs-btn gs-btn--outline">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    </div>
  </div>

  @if($errors->any())
    <div class="gs-alert gs-alert--error" style="margin-bottom:16px; display:flex; flex-direction:column; gap:4px;">
      @foreach($errors->all() as $error)
        <span><i class="bi bi-exclamation-circle-fill"></i> {{ $error }}</span>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('settings.lead-steps.update', $leadStep) }}">
    @csrf @method('PUT')
    <div class="gs-common-grid">
      <div class="gs-left-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Lead Step Details</h5>
          </div>
          <div class="gs-panel-body">

            <div class="gs-field">
              <label class="gs-label">Name <span class="gs-required">*</span></label>
              <input type="text" name="name" class="gs-input @error('name') gs-input--error @enderror"
                value="{{ old('name', $leadStep->name) }}">
              @error('name')
                <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
              @enderror
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Status <span class="gs-required">*</span></label>
              <select name="status" class="gs-select">
                <option value="1"   {{ old('status', $leadStep->status) === '1'   ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $leadStep->status) === '0' ? 'selected' : '' }}>Inactive</option>
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
                <button type="submit" class="gs-btn gs-btn--primary" style="width:100%; justify-content:center;">
                <i class="bi bi-check-lg"></i> Update Lead Step
                </button>
                <a href="{{ route('settings.lead-steps.index') }}" class="gs-btn gs-btn--outline" style="width:100%; justify-content:center;">
                <i class="bi bi-x-lg"></i> Cancel
                </a>
          </div>
        </div>
      </div>
    </div>
  </form>

@endsection
