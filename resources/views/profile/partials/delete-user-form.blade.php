<section class="gs-profile-section">

  {{-- Section Header --}}
  <div class="gs-profile-section-header">
    <div class="gs-profile-section-icon gs-profile-section-icon--red">
      <i class="bi bi-trash3-fill"></i>
    </div>
    <div>
      <h2 class="gs-profile-section-title gs-profile-section-title--red">{{ __('Delete Account') }}</h2>
      <p class="gs-profile-section-desc">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
    </div>
  </div>

  {{-- Danger Warning Box --}}
  <div class="gs-danger-warning">
    <i class="bi bi-exclamation-triangle-fill gs-danger-warning-icon"></i>
    <div>
      <p class="gs-danger-warning-title">{{ __('This action is irreversible') }}</p>
      <p class="gs-danger-warning-text">{{ __('Before deleting your account, please download any data or information that you wish to retain. All associated records will be permanently removed.') }}</p>
    </div>
  </div>

  {{-- Trigger Button --}}
  <div class="gs-profile-form-footer" style="margin-top: 0;">
    <button
      type="button"
      class="gs-btn gs-btn--danger"
      x-data=""
      x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
      <i class="bi bi-trash3-fill"></i> {{ __('Delete My Account') }}
    </button>
  </div>

  {{-- Confirmation Modal --}}
  <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="gs-delete-modal-form">
      @csrf
      @method('delete')

      {{-- Modal Header --}}
      <div class="gs-delete-modal-header">
        <div class="gs-delete-modal-icon">
          <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div>
          <h3 class="gs-delete-modal-title">{{ __('Delete Account?') }}</h3>
          <p class="gs-delete-modal-desc">{{ __('This action cannot be undone. All data will be permanently removed.') }}</p>
        </div>
      </div>

      {{-- Password Confirmation --}}
      <div class="gs-field" style="margin-bottom: 20px;">
        <label class="gs-label" for="password">
          {{ __('Confirm with your password') }} <span class="gs-required">*</span>
        </label>
        <div class="gs-input-wrap">
          <i class="bi bi-lock gs-input-icon"></i>
          <input
            id="password"
            name="password"
            type="password"
            class="gs-input gs-input--icon {{ $errors->userDeletion->get('password') ? 'gs-input--error' : '' }}"
            placeholder="{{ __('Enter your password to confirm') }}"
          />
        </div>
        @foreach ($errors->userDeletion->get('password') as $message)
          <span class="gs-field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</span>
        @endforeach
      </div>

      {{-- Modal Actions --}}
      <div class="gs-delete-modal-actions">
        <button type="button" class="gs-btn gs-btn--outline" x-on:click="$dispatch('close')">
          <i class="bi bi-x-lg"></i> {{ __('Cancel') }}
        </button>
        <button type="submit" class="gs-btn gs-btn--danger">
          <i class="bi bi-trash3-fill"></i> {{ __('Yes, Delete Account') }}
        </button>
      </div>

    </form>
  </x-modal>

</section>
