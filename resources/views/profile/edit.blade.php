@extends('layouts.app')
@section('title', 'User Profile')

@section('content')

  {{-- Page Top Bar --}}
  <div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
      <h2>My Profile</h2>
      <p>Manage your account settings and preferences</p>
    </div>
    <div class="gs-page-topbar-actions">
      <a href="{{ url()->previous() }}" class="gs-btn gs-btn--outline">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>
  </div>

  {{-- Profile Page Grid --}}
  <div class="gs-profile-page-grid">

    {{-- LEFT: Avatar / Quick Info Card --}}
    <div class="gs-profile-sidebar-card">
      <div class="gs-profile-avatar-wrap">
        <div class="gs-profile-avatar-ring">
          <div class="gs-profile-avatar-initials">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
          </div>
        </div>
        <div class="gs-profile-avatar-badge">
          <i class="bi bi-patch-check-fill"></i>
        </div>
      </div>
      <div class="gs-profile-quick-name">{{ auth()->user()->name }}</div>
      <small>({{ Auth::user()->getRoleNames()->first() ? ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) : '' }})</small>
      <div class="gs-profile-quick-email mt-2">{{ auth()->user()->email }}</div>
      <div class="gs-profile-quick-divider"></div>
      <ul class="gs-profile-nav-list">
        <li>
          <a href="#section-profile" class="gs-profile-nav-link gs-profile-nav-link--active">
            <i class="bi bi-person-fill"></i> Profile Info
          </a>
        </li>
        <li>
          <a href="#section-password" class="gs-profile-nav-link">
            <i class="bi bi-shield-lock-fill"></i> Password
          </a>
        </li>
      </ul>
    </div>

    {{-- RIGHT: Form Panels --}}
    <div class="gs-profile-panels">

      {{-- Panel 1: Profile Info --}}
      <div class="gs-panel" id="section-profile">
        @include('profile.partials.update-profile-information-form')
      </div>

      {{-- Panel 2: Update Password --}}
      <div class="gs-panel" id="section-password">
        @include('profile.partials.update-password-form')
      </div>

    </div>

  </div>

@endsection
