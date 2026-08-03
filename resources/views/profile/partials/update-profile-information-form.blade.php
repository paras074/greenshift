<section class="gs-profile-section">

  {{-- Section Header --}}
  <div class="gs-profile-section-header">
    <div class="gs-profile-section-icon">
      <i class="bi bi-person-fill"></i>
    </div>
    <div>
      <h2 class="gs-profile-section-title">{{ __('Profile Information') }}</h2>
      <p class="gs-profile-section-desc">{{ __("Update your account's profile information and email address.") }}</p>
    </div>
  </div>

  <form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
  </form>

  <form method="post" action="{{ route('profile.update') }}" class="gs-profile-form">
    @csrf
    @method('patch')

    {{-- Name Field --}}
    <div class="gs-field">
      <label class="gs-label" for="name">
        {{ __('Name') }} <span class="gs-required">*</span>
      </label>
      <div class="gs-input-wrap">
        <i class="bi bi-person gs-input-icon"></i>
        <input
          id="name"
          name="name"
          type="text"
          class="gs-input gs-input--icon {{ $errors->get('name') ? 'gs-input--error' : '' }}"
          value="{{ old('name', $user->name) }}"
          required
          autofocus
          autocomplete="name"
          placeholder="Enter your full name"
        />
      </div>
      @foreach ($errors->get('name') as $message)
        <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
      @endforeach
    </div>

    {{-- Email Field --}}
    <div class="gs-field">
      <label class="gs-label" for="email">
        {{ __('Email') }} <span class="gs-required">*</span>
      </label>
      <div class="gs-input-wrap">
        <i class="bi bi-envelope gs-input-icon"></i>
        <input
          id="email"
          name="email"
          type="email"
          class="gs-input gs-input--icon {{ $errors->get('email') ? 'gs-input--error' : '' }}"
          value="{{ old('email', $user->email) }}"
          required
          autocomplete="username"
          placeholder="Enter your email address"
        />
        @if ($user->hasVerifiedEmail())
          <span class="gs-input-badge gs-input-badge--verified" title="Email verified">
            <i class="bi bi-patch-check-fill"></i>
          </span>
        @else
          <span class="gs-input-badge gs-input-badge--unverified" title="Email unverified">
            <i class="bi bi-patch-exclamation-fill"></i>
          </span>
        @endif
      </div>
      @foreach ($errors->get('email') as $message)
        <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
      @endforeach

      {{-- Unverified Email Notice --}}
      @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="gs-verify-notice">
          <i class="bi bi-shield-exclamation gs-verify-notice-icon"></i>
          <div class="gs-verify-notice-body">
            <p class="gs-verify-notice-text">{{ __('Your email address is unverified.') }}</p>
            <button form="send-verification" class="gs-verify-link">
              <i class="bi bi-send"></i> {{ __('Re-send verification email') }}
            </button>
          </div>
        </div>
        @if (session('status') === 'verification-link-sent')
          <div class="gs-alert gs-alert--success">
            <i class="bi bi-check-circle-fill"></i>
            {{ __('A new verification link has been sent to your email address.') }}
          </div>
        @endif
      @endif
    </div>

    {{-- Form Footer --}}
    <div class="gs-profile-form-footer">
      <button type="submit" class="gs-btn gs-btn--teal">
        <i class="bi bi-floppy-fill"></i> {{ __('Save Changes') }}
      </button>

      @if (session('status') === 'profile-updated')
        <div
          class="gs-alert gs-alert--success gs-alert--inline"
          x-data="{ show: true }"
          x-show="show"
          x-transition
          x-init="setTimeout(() => show = false, 2000)"
        >
          <i class="bi bi-check-circle-fill"></i> {{ __('Saved successfully.') }}
        </div>
      @endif
    </div>

  </form>
</section>
