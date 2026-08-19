@extends('layouts.app')
@section('title', 'Add Template')
@section('content')

  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Add New Template</h2>
      <p>Create an Email or LOA template</p>
    </div>
    <div class="gs-page-topbar-actions">
      <a href="{{ route('templates.index') }}" class="gs-btn gs-btn--outline">
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

  <form method="POST" action="{{ route('templates.store') }}">
    @csrf
    <div class="gs-common-grid">
      <div class="gs-left-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Template Content</h5>
          </div>
          <div class="gs-panel-body">

            <div class="gs-field">
              <label class="gs-label">Template Name <span class="gs-required">*</span></label>
              <input type="text" name="name" class="gs-input @error('name') gs-input--error @enderror"
                value="{{ old('name') }}" placeholder="e.g. Default LOA, Welcome Email">
              @error('name')
                <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
              @enderror
            </div>

            <div class="gs-field" id="subjectField" style="margin-top:16px;">
              <label class="gs-label">Email Subject</label>
              <input type="text" name="subject" class="gs-input" value="{{ old('subject') }}"
                placeholder="Only used for Email templates">
              <span class="gs-role-hint">Leave blank for LOA templates.</span>
            </div>

            <div class="gs-field" style="margin-top:16px;">
              <label class="gs-label">Content <span class="gs-required">*</span></label>
              <textarea name="content" id="editor" rows="18">{{ old('content') }}</textarea>
              <span class="gs-role-hint">For the LOA, this text appears between the Broker details table and the signature block. The customer/broker tables and signature are added automatically.</span>
            </div>

          </div>
        </div>
      </div>

      <div class="gs-right-grid">
        <div class="gs-panel">
          <div class="gs-panel-header">
            <h5 class="gs-panel-title">Settings</h5>
          </div>
          <div class="gs-panel-body" style="display:flex; flex-direction:column; gap:14px;">

            <div class="gs-field">
              <label class="gs-label">Type <span class="gs-required">*</span></label>
              <select name="type" id="typeSelect" class="gs-select">
                <option value="loa"   {{ old('type', 'loa') === 'loa' ? 'selected' : '' }}>LOA</option>
                <option value="email" {{ old('type') === 'email' ? 'selected' : '' }}>Email</option>
              </select>
            </div>

            <div class="gs-field">
              <label class="gs-label" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                Set as active template
              </label>
              <span class="gs-role-hint">The active template of each type is the one actually used. Only one can be active per type.</span>
            </div>

            <button type="submit" class="gs-btn gs-btn--primary" style="width:100%; justify-content:center;">
              <i class="bi bi-check-lg"></i> Create Template
            </button>
            <a href="{{ route('templates.index') }}" class="gs-btn gs-btn--outline" style="width:100%; justify-content:center;">
              <i class="bi bi-x-lg"></i> Cancel
            </a>
          </div>
        </div>
      </div>
    </div>
  </form>

@endsection

@push('scripts')
<script>
  let templateEditor;
  ClassicEditor
    .create(document.querySelector('#editor'))
    .then(editor => { templateEditor = editor; })
    .catch(error => console.error(error));

  // Sync CKEditor content back to the textarea before submitting.
  document.querySelector('form').addEventListener('submit', function () {
    if (templateEditor) {
      document.querySelector('#editor').value = templateEditor.getData();
    }
  });

  // Show the subject field only for Email templates.
  const typeSelect = document.getElementById('typeSelect');
  const subjectField = document.getElementById('subjectField');
  function toggleSubject() {
    subjectField.style.display = typeSelect.value === 'email' ? 'block' : 'none';
  }
  typeSelect.addEventListener('change', toggleSubject);
  toggleSubject();
</script>
@endpush
