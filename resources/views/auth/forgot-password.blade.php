{{-- resources/views/auth/forgot-password.blade.php --}}

<x-guest-layout>
<div class="auth-card">

  <div class="auth-card-icon">
    <i class="bi bi-key-fill"></i>
  </div>
  <h2 class="auth-card-title">Forgot Password?</h2>
  <p class="auth-card-sub">No worries. Enter your email and we'll send you a reset link.</p>

  {{-- Session Status --}}
  @if (session('status'))
    <div class="auth-status">
      <i class="bi bi-check-circle-fill"></i>
      {{ session('status') }}
    </div>
  @endif

  <form method="POST" action="{{ route('password.email') }}">
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
             placeholder="you@example.com"/>
      @error('email')
        <p class="auth-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="auth-btn-primary">
      <i class="bi bi-send-fill"></i> Send Reset Link
    </button>

  </form>

</div>

<p class="auth-footer-text">
  Remembered it? <a href="{{ route('login') }}" class="auth-link">Back to Sign In</a>
</p>
</x-guest-layout>
