{{-- resources/views/auth/confirm-password.blade.php --}}

<x-guest-layout>
<div class="auth-card">

  <div class="auth-card-icon">
    <i class="bi bi-lock-fill"></i>
  </div>
  <h2 class="auth-card-title">Confirm Password</h2>

  <div class="auth-info-box">
    <i class="bi bi-info-circle-fill"></i>
    This is a secure area. Please confirm your password before continuing.
  </div>

  <form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    {{-- Password --}}
    <div class="auth-field">
      <label class="auth-label" for="password">Password</label>
      <div class="auth-input-wrap">
        <input id="password"
               class="auth-input"
               type="password"
               name="password"
               required
               autocomplete="current-password"
               placeholder="Enter your password"/>
        <button type="button" class="auth-eye-btn" onclick="togglePassword('password', this)">
          <i class="bi bi-eye-fill"></i>
        </button>
      </div>
      @error('password')
        <p class="auth-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="auth-btn-primary">
      <i class="bi bi-shield-check"></i> Confirm & Continue
    </button>

  </form>

</div>

<script>
function togglePassword(id, btn) {
  const input = document.getElementById(id);
  const icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash-fill';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye-fill';
  }
}
</script>
</x-guest-layout>
