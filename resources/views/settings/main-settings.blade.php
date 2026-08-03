@extends('layouts.app')
@section('title', 'Main Settings')
@section('content')
  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>Main Settings</h2>
      <p>Update main application settings</p>
    </div>
  </div>

  @if($errors->any())
    <div class="gs-alert gs-alert--error" style="margin-bottom:16px; display:flex; flex-direction:column; gap:4px;">
      @foreach($errors->all() as $error)
        <span><i class="bi bi-exclamation-circle-fill"></i> {{ $error }}</span>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('settings.store') }}">
    @csrf
    <div class="gs-common-grid">
      <div class="gs-left-grid">

        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">Settings</h5></div>
          <div class="gs-panel-body">
            <div class="gs-form-grid">
              <div class="gs-field gs-field--full">
                <label class="gs-label">Commission (in %)</label>
                <input type="number" step="0.01" name="commission" class="gs-input" id="commission" value="{{ old('commission', $settings['commission'] ?? '') }}">
              </div>
            </div>
          </div>
        </div>

        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">Inactivity On Lead Settings</h5></div>
          <div class="gs-panel-body">
            <div class="gs-form-grid">
                <div class="gs-field gs-field--full">
                  <label class="gs-label">Inactivity Timeout (Days)</label>
                  <div style="">
                      <input type="number" name="lead_reversion_days" class="gs-input" value="{{ old('lead_reversion_days', $settings['lead_reversion_days'] ?? 7) }}" min="0">
                      <span class="gs-role-hint" style="margin:0;">
                        Leads with no activity for this many days will return to the Admin Pool. Set to 0 to disable.
                      </span>
                  </div>
                </div>
                <div class="gs-field gs-field--full">
                  <label class="gs-label">Select status to Exclude</label>
                  <select name="excluded_statuses[]" class="gs-select select2" multiple data-placeholder="Select status">
                      @foreach(get_all_lead_status() as $status)
                        <option value="{{ $status['id'] }}" 
                          {{ in_array($status['id'], old('excluded_statuses', $settings['excluded_statuses'] ?? [])) ? 'selected' : '' }}>
                          {{ $status['name'] }}
                        </option>
                      @endforeach
                  </select>
              </div>
              <div class="gs-field gs-field--full" style="margin-top:16px;">
                <label class="gs-label">Exclude Scheduled Leads</label>
                <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                  <input type="hidden" name="exclude_scheduled_leads" value="0">
                  <label class="vl-switch">
                    <input type="checkbox" name="exclude_scheduled_leads" value="1" {{ old('exclude_scheduled_leads', $settings['exclude_scheduled_leads'] ?? false) ? 'checked' : '' }}>
                    <span class="vl-slider"></span>
                  </label>
                  <span class="gs-role-hint" style="margin:0;">Enable this to Exclude Scheduled Leads</span>
                </div>
              </div>           
            </div>
          </div>
        </div>

        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">Pusher Settings</h5></div>
          <div class="gs-panel-body">
            <div class="gs-form-grid">
                <div class="gs-field gs-field--full">
                  <label class="gs-label">Pusher App ID</label>
                  <input type="text" name="pusher_app_id" class="gs-input" id="pusher_app_id" value="{{ old('pusher_app_id', $settings['pusher_app_id'] ?? '') }}">
                </div>
                <div class="gs-field gs-field--full">
                  <label class="gs-label">Pusher App Key</label>
                  <input type="text" name="pusher_app_key" class="gs-input" id="pusher_app_key" value="{{ old('pusher_app_key', $settings['pusher_app_key'] ?? '') }}">
                </div>
                <div class="gs-field gs-field--full">
                  <label class="gs-label">Pusher App Secret</label>
                  <input type="password" name="pusher_app_secret" class="gs-input" id="pusher_app_secret" value="{{ old('pusher_app_secret', $settings['pusher_app_secret'] ?? '') }}">
                </div>
                <div class="gs-field gs-field--full">
                  <label class="gs-label">Pusher App Cluster</label>
                  <input type="text" name="pusher_app_cluster" class="gs-input" id="pusher_app_cluster" value="{{ old('pusher_app_cluster', $settings['pusher_app_cluster'] ?? '') }}">
                </div>
            </div>
          </div>
        </div>

        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">SendGrid / Mail Settings</h5></div>
          <div class="gs-panel-body">
              <div class="gs-form-grid">
                  <div class="gs-field gs-field--full">
                    <label class="gs-label">SendGrid API Key</label>
                    <input type="password" name="sendgrid_api_key" class="gs-input" value="{{ old('sendgrid_api_key', $settings['sendgrid_api_key'] ?? '') }}">
                  </div>
                  <div class="gs-field">
                    <label class="gs-label">From Email Address</label>
                    <input type="email" name="mail_from_address" class="gs-input" placeholder="hello@yourdomain.com" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}">
                  </div>
                  <div class="gs-field">
                    <label class="gs-label">From Name</label>
                    <input type="text" name="mail_from_name" class="gs-input" placeholder="Greenshift" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}">
                  </div>
                  <div class="gs-field">
                    <label class="gs-label">Mail Host</label>
                    <input type="text" name="mail_host" class="gs-input" placeholder="smtp.sendgrid.net" value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.sendgrid.net') }}">
                  </div>
                  <div class="gs-field">
                    <label class="gs-label">Mail Port</label>
                    <input type="text" name="mail_port" class="gs-input" placeholder="587" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}">
                  </div>
                  <div class="gs-field">
                    <label class="gs-label">Mail Encryption</label>
                      <select name="mail_encryption" class="gs-input">
                        <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ ($settings['mail_encryption'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                      </select>
                  </div>
              </div>
          </div>
        </div>

        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">Google API</h5></div>
          <div class="gs-panel-body">
            <div class="gs-form-grid">
              <div class="gs-field gs-field--full">
                <label class="gs-label">Google API Key</label>
                <input type="text" name="google_api_key" class="gs-input" id="google_api_key" value="{{ old('google_api_key', $settings['google_api_key'] ?? '') }}">
              </div>
              <div class="gs-field gs-field--full">
                <label class="gs-label">Max result showing from Google API</label>
                <input type="number" name="max_result_from_google_api" class="gs-input" id="max_result_from_google_api" value="{{ old('max_result_from_google_api', $settings['max_result_from_google_api'] ?? '') }}">
              </div>
            </div>
          </div>
        </div>

        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">Companies House API</h5></div>
          <div class="gs-panel-body">
            <div class="gs-form-grid">
              <div class="gs-field gs-field--full">
                <label class="gs-label">Companies House API Key</label>
                <input type="text" name="companies_house_api_key" class="gs-input" id="companies_house_api_key" value="{{ old('companies_house_api_key', $settings['companies_house_api_key'] ?? '') }}">
              </div>
              <div class="gs-field gs-field--full">
                <label class="gs-label">Max result showing from Companies House API</label>
                <input type="number" name="max_result_from_companies_house_api" class="gs-input" id="max_result_from_companies_house_api" value="{{ old('max_result_from_companies_house_api', $settings['max_result_from_companies_house_api'] ?? '') }}">
              </div>
            </div>
          </div>
        </div>

        
        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">Dialpad API</h5></div>
          <div class="gs-panel-body">
            <div class="gs-form-grid">
              <div class="gs-field gs-field--full">
                <label class="gs-label">Dialpad CTI Key</label>
                <input type="text" name="dialpad_api_key" class="gs-input" id="dialpad_api_key" value="{{ old('dialpad_api_key', $settings['dialpad_api_key'] ?? '') }}">
              </div>
            </div>
          </div>
        </div>

        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">Signable API</h5></div>
          <div class="gs-panel-body">
            <div class="gs-form-grid">
              <div class="gs-field gs-field--full">
                <label class="gs-label">Signable API Key</label>
                <input type="text" name="signable_api_key" class="gs-input" id="signable_api_key" value="{{ old('signable_api_key', $settings['signable_api_key'] ?? '') }}">
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="gs-right-grid">
        <div class="gs-panel">
          <div class="gs-panel-header"><h5 class="gs-panel-title">Update settings</h5></div>
          <div class="gs-panel-body" style="display:flex; flex-direction:column; gap:10px;">
            @can('edit settings')
              <button type="submit" class="gs-btn gs-btn--primary" style="width:100%; justify-content:center;">
                <i class="bi bi-check-lg"></i> Update
              </button>
            @endcan
            @can('view settings')
              <a href="{{ route('settings.index') }}" class="gs-btn gs-btn--outline" style="width:100%; justify-content:center;">
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
  $(document).ready(function () {
      $('.select2').select2({
        placeholder: "Select status",
        width: '100%',
        allowClear: true
      });
      $('.select2').on('select2:clear', function () {
        $(this).val(null).trigger('change');
    });
  });
</script>
@endpush
