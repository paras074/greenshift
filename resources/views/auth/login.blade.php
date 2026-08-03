{{-- resources/views/auth/login.blade.php --}}

<x-guest-layout>
<div class="auth-card">

  <div class="auth-card-icon">
    <i class="bi bi-box-arrow-in-right"></i>
  </div>
  <h2 class="auth-card-title">Welcome Back</h2>
  <p class="auth-card-sub">Sign in to your Greenshift account to continue.</p>

  {{-- Session Status --}}
  @if (session('status'))
    <div class="auth-status">
      <i class="bi bi-check-circle-fill"></i>
      {{ session('status') }}
    </div>
  @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf

    {{-- Email --}}
    <div class="auth-field">
      <label class="auth-label" for="email">Email Address</label>
      <input id="email"
             class="auth-input"
             type="email"
             name="email"
             value="{{ old('email') }}"
             required
             autofocus
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

    {{-- Remember + Forgot --}}
    <div class="auth-row">
      <div class="auth-remember">
        <input id="remember_me" type="checkbox" name="remember"/>
        <label for="remember_me">Remember me</label>
      </div>
      @if (Route::has('password.request'))
        <a href="{{ route('password.request') }}" class="auth-link">Forgot password?</a>
      @endif
    </div>

    <button type="submit" class="auth-btn-primary">
      <i class="bi bi-box-arrow-in-right"></i> Sign In
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
