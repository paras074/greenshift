<section class="gs-profile-section">

  {{-- Section Header --}}
  <div class="gs-profile-section-header">
    <div class="gs-profile-section-icon gs-profile-section-icon--navy">
      <i class="bi bi-shield-lock-fill"></i>
    </div>
    <div>
      <h2 class="gs-profile-section-title">{{ __('Update Password') }}</h2>
      <p class="gs-profile-section-desc">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </div>
  </div>

  <form method="post" action="{{ route('password.update') }}" class="gs-profile-form">
    @csrf
    @method('put')

    {{-- Current Password --}}
    <div class="gs-field">
      <label class="gs-label" for="update_password_current_password">
        {{ __('Current Password') }} <span class="gs-required">*</span>
      </label>
      <div class="gs-input-wrap">
        <i class="bi bi-lock gs-input-icon"></i>
        <input
          id="update_password_current_password"
          name="current_password"
          type="password"
          class="gs-input gs-input--icon gs-input--password {{ $errors->updatePassword->get('current_password') ? 'gs-input--error' : '' }}"
          autocomplete="current-password"
          placeholder="Enter your current password"
        />
        <button type="button" class="gs-pw-toggle" data-target="update_password_current_password" tabindex="-1">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      @foreach ($errors->updatePassword->get('current_password') as $message)
        <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
      @endforeach
    </div>

    {{-- New Password --}}
    <div class="gs-field">
      <label class="gs-label" for="update_password_password">
        {{ __('New Password') }} <span class="gs-required">*</span>
      </label>
      <div class="gs-input-wrap">
        <i class="bi bi-key gs-input-icon"></i>
        <input
          id="update_password_password"
          name="password"
          type="password"
          class="gs-input gs-input--icon gs-input--password {{ $errors->updatePassword->get('password') ? 'gs-input--error' : '' }}"
          autocomplete="new-password"
          placeholder="Enter a strong new password"
        />
        <button type="button" class="gs-pw-toggle" data-target="update_password_password" tabindex="-1">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      @foreach ($errors->updatePassword->get('password') as $message)
        <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
      @endforeach
    </div>

    {{-- Confirm Password --}}
    <div class="gs-field">
      <label class="gs-label" for="update_password_password_confirmation">
        {{ __('Confirm Password') }} <span class="gs-required">*</span>
      </label>
      <div class="gs-input-wrap">
        <i class="bi bi-key-fill gs-input-icon"></i>
        <input
          id="update_password_password_confirmation"
          name="password_confirmation"
          type="password"
          class="gs-input gs-input--icon gs-input--password {{ $errors->updatePassword->get('password_confirmation') ? 'gs-input--error' : '' }}"
          autocomplete="new-password"
          placeholder="Re-enter your new password"
        />
        <button type="button" class="gs-pw-toggle" data-target="update_password_password_confirmation" tabindex="-1">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      @foreach ($errors->updatePassword->get('password_confirmation') as $message)
        <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
      @endforeach
    </div>

    {{-- Form Footer --}}
    <div class="gs-profile-form-footer">
      <button type="submit" class="gs-btn gs-btn--primary">
        <i class="bi bi-shield-check"></i> {{ __('Update Password') }}
      </button>

      @if (session('status') === 'password-updated')
        <div
          class="gs-alert gs-alert--success gs-alert--inline"
          x-data="{ show: true }"
          x-show="show"
          x-transition
          x-init="setTimeout(() => show = false, 2000)"
        >
          <i class="bi bi-check-circle-fill"></i> {{ __('Password updated successfully.') }}
        </div>
      @endif
    </div>

  </form>

</section>

@push('scripts')
<script>
  document.querySelectorAll('.gs-pw-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var targetId = this.getAttribute('data-target');
      var input = document.getElementById(targetId);
      var icon = this.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
      }
    });
  });
</script>
@endpush
