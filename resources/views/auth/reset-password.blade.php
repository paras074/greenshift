{{-- resources/views/auth/reset-password.blade.php --}}

<x-guest-layout>
<div class="auth-card">

  <div class="auth-card-icon">
    <i class="bi bi-shield-lock-fill"></i>
  </div>
  <h2 class="auth-card-title">Reset Password</h2>
  <p class="auth-card-sub">Enter your new password below to regain access.</p>

  <form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}"/>

    {{-- Email --}}
    <div class="auth-field">
      <label class="auth-label" for="email">Email Address</label>
      <input id="email"
             class="auth-input"
             type="email"
             name="email"
             value="{{ old('email', $request->email) }}"
             required
             autofocus
             autocomplete="username"
             placeholder="you@example.com"/>
      @error('email')
        <p class="auth-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
      @enderror
    </div>

    {{-- New Password --}}
    <div class="auth-field">
      <label class="auth-label" for="password">New Password</label>
      <div class="auth-input-wrap">
        <input id="password"
               class="auth-input"
               type="password"
               name="password"
               required
               autocomplete="new-password"
               placeholder="Min. 8 characters"/>
        <button type="button" class="auth-eye-btn" onclick="togglePassword('password', this)">
          <i class="bi bi-eye-fill"></i>
        </button>
      </div>
      @error('password')
        <p class="auth-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
      @enderror
    </div>

    {{-- Confirm Password --}}
    <div class="auth-field">
      <label class="auth-label" for="password_confirmation">Confirm New Password</label>
      <div class="auth-input-wrap">
        <input id="password_confirmation"
               class="auth-input"
               type="password"
               name="password_confirmation"
               required
               autocomplete="new-password"
               placeholder="Re-enter new password"/>
        <button type="button" class="auth-eye-btn" onclick="togglePassword('password_confirmation', this)">
          <i class="bi bi-eye-fill"></i>
        </button>
      </div>
      @error('password_confirmation')
        <p class="auth-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="auth-btn-primary">
      <i class="bi bi-check-circle-fill"></i> Reset Password
    </button>

  </form>

</div>

<p class="auth-footer-text">
  <a href="{{ route('login') }}" class="auth-link">Back to Sign In</a>
</p>

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
