<header class="dashboard-navbar sticky-top">
  <div class="dashboard-navbar-left">
    <!--<button class="dashboard-hamburger" id="sidebarToggle" aria-label="Toggle Sidebar">-->
    <!--  <i class="bi bi-list"></i>-->
    <!--</button>-->
    <!--<div class="dashboard-search-wrap dashboard-search-wrap--desktop">-->
    <!--  <i class="bi bi-search dashboard-search-icon"></i>-->
    <!--  <input type="text" class="dashboard-search-input" placeholder="Search anything..."/>-->
    <!--</div>-->
    <div class="header-brand">
        <h2>Greenshift</h2>
        <p>Energy Consulting CRM</p>
    </div>
  </div>
  <div class="dashboard-navbar-right">
    <button class="dashboard-icon-btn dashboard-mobile-search-btn" id="mobileSearchBtn" title="Search">
      <i class="bi bi-search"></i>
    </button>

    {{-- Notification Bell — triggers offcanvas --}}
    <button
      class="dashboard-icon-btn"
      title="Notifications"
      data-bs-toggle="offcanvas"
      data-bs-target="#notificationPanel"
      aria-controls="notificationPanel"
    >
      <i class="bi bi-bell-fill"></i>
      @php $unread_c = unread_notifications_count(); @endphp

      <span class="dashboard-dot"
            id="notification-count"
            style="{{ $unread_c > 0 ? '' : 'display:none;' }}">
          {{ $unread_c > 9 ? '9+' : $unread_c }}
      </span>
    </button>

    {{-- {{ dd(getDashboardTimeline()) }} --}}

    <div class="dropdown">
      <button class="dashboard-profile-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="gs-profile-avatar-ring">
          <div class="gs-profile-avatar-initials">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
          </div>
        </div>
        <span class="dashboard-nav-username d-none d-md-inline">{{ Auth::user()->name ?? 'User' }}</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end dashboard-dropdown">
        <li>
          <a class="dropdown-item" href="{{ route('profile.edit') }}">
            <i class="bi bi-person-fill"></i> Profile
          </a>
        </li>
        <li><hr class="dropdown-divider"/></li>
        <li>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link class="dropdown-item dashboard-dropdown-logout" :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
              <i class="bi bi-box-arrow-right"></i> {{ __('Log Out') }}
            </x-dropdown-link>
          </form>
        </li>
      </ul>
    </div>
  </div>
</header>


{{-- ============================================================
     NOTIFICATION OFFCANVAS PANEL
     ============================================================ --}}
<div class="offcanvas offcanvas-end gs-notif-panel" tabindex="-1" id="notificationPanel" aria-labelledby="notificationPanelLabel">

  {{-- Header --}}
  <div class="offcanvas-header gs-notif-header">
    <div class="gs-notif-header-left" id="notify-header">
      <div class="gs-notif-header-icon">
        <i class="bi bi-bell-fill"></i>
      </div>
      <div>
        <h5 class="offcanvas-title gs-notif-title" id="notificationPanelLabel">Notifications</h5>
      </div>
      <span id="unread_count"> (-) </span>
    </div>

    <button type="button" class="gs-notif-close" data-bs-dismiss="offcanvas" aria-label="Close">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>



  {{-- Body / Scrollable list --}}
  <div class="offcanvas-body gs-notif-body" id="notifications-body">
    <div id="" class="text-center py-5">
        <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
  </div>


</div>


