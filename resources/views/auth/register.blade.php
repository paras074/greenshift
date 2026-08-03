{{-- resources/views/auth/register.blade.php --}}

<x-guest-layout>
<div class="auth-card">

  <div class="auth-card-icon">
    <i class="bi bi-person-plus-fill"></i>
  </div>
  <h2 class="auth-card-title">Create Account</h2>
  <p class="auth-card-sub">Join Greenshift and start managing your energy business.</p>

  <form method="POST" action="{{ route('register') }}">
    @csrf

    {{-- Name --}}
    <div class="auth-field">
      <label class="auth-label" for="name">Full Name</label>
      <input id="name"
             class="auth-input"
             type="text"
             name="name"
             value="{{ old('name') }}"
             required
             autofocus
             autocomplete="name"
             placeholder="Alex Monroe"/>
      @error('name')
        <p class="auth-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
      @enderror
    </div>

    {{-- Email --}}
    <div class="auth-field">
      <label class="auth-label" for="email">Email Address</label>
      <input id="email"
             class="auth-input"
             type="email"
             name="email"
             value="{{ old('email') }}"
             required
             autocomplete="username"
             placeholder="you@example.com"/>
      @error('email')
        <p class="auth-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
      @enderror
    </div>

    {{-- Password --}}
    <div class="auth-field">
      <label class="auth-label" for="password">Password</label>
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
      <label class="auth-label" for="password_confirmation">Confirm Password</label>
      <div class="auth-input-wrap">
        <input id="password_confirmation"
               class="auth-input"
               type="password"
               name="password_confirmation"
               required
               autocomplete="new-password"
               placeholder="Re-enter your password"/>
        <button type="button" class="auth-eye-btn" onclick="togglePassword('password_confirmation', this)">
          <i class="bi bi-eye-fill"></i>
        </button>
      </div>
      @error('password_confirmation')
        <p class="auth-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="auth-btn-primary" style="margin-top:6px;">
      <i class="bi bi-person-check-fill"></i> Create Account
    </button>

  </form>

</div>

<p class="auth-footer-text">
  Already have an account? <a href="{{ route('login') }}" class="auth-link">Sign in</a>
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
