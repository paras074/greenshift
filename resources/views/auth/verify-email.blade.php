{{-- resources/views/auth/verify-email.blade.php --}}

<x-guest-layout>
<div class="auth-card">

  <div class="auth-card-icon">
    <i class="bi bi-envelope-check-fill"></i>
  </div>
  <h2 class="auth-card-title">Verify Your Email</h2>
  <p class="auth-card-sub">Thanks for signing up! Please verify your email address to get started.</p>

  <div class="auth-info-box">
    <i class="bi bi-info-circle-fill"></i>
    A verification link has been sent to your registered email address. Click the link to activate your account.
  </div>

  {{-- Resent success message --}}
  @if (session('status') == 'verification-link-sent')
    <div class="auth-status">
      <i class="bi bi-check-circle-fill"></i>
      A new verification link has been sent to your email address.
    </div>
  @endif

  <div class="auth-verify-row">
    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="auth-btn-primary" style="width:auto; padding: 11px 20px;">
        <i class="bi bi-send-fill"></i> Resend Email
      </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="auth-link--muted" style="background:none; border:none; cursor:pointer; font-family: var(--body-font);">
        <i class="bi bi-box-arrow-right"></i> Log Out
      </button>
    </form>
  </div>

</div>
</x-guest-layout>
