<aside class="dashboard-sidebar" id="dashboardSidebar">
  <div class="dashboard-sidebar-logo">
    <a class="d-l" href="{{ url('/dashboard') }}">
      <img src="{{ asset('images/site-logo.png') }}" alt="Greenshift" class="dashboard-logo-img"/>
    </a>
    <a class="m-l" href="{{ url('/dashboard') }}">
      <img src="{{ asset('images/site-fav-icon.png') }}" alt="Greenshift" class="dashboard-logo-img--icon"/>
    </a>
    <button class="dashboard-sidebar-close" id="sidebarClose" aria-label="Close Sidebar">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  <button class="dashboard-sidebar-collapser" id="sidebarCollapser" aria-label="Collapse Sidebar">
    <i class="bi bi-chevron-left"></i>
  </button>

  <nav class="dashboard-sidebar-nav" id="sidebarNav">

    {{-- ── MAIN MENU ── --}}
    <p class="dashboard-nav-label">Main Menu</p>
    <ul class="dashboard-nav-list">

      @can('view dashboard')
        <li class="dashboard-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <a href="{{ route('dashboard') }}" class="dashboard-nav-link" data-tooltip="Dashboard">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
          </a>
        </li>
      @endcan

      @can('fetch-leads leads')
       <li class="dashboard-nav-item dashboard-nav-item--has-dropdown {{ request()->routeIs('api.index', 'gather_companies_data.existing_temp_leads') ? 'dashboard-nav-item--open active' : '' }}">
          <a href="#" class="dashboard-nav-link dashboard-nav-link--dropdown" data-tooltip="Leads">
            <i class="bi bi-funnel-fill"></i>
            <span>Fetch Leads</span>
            <i class="bi bi-chevron-down dashboard-nav-arrow"></i>
          </a>
          <ul class="dashboard-sub-menu">
              <li>
                <a href="{{ route('api.index') }}" class="dashboard-sub-link {{ request()->routeIs('api.index') ? 'active' : '' }}">
                  <i class="bi bi-list-ul"></i> Fetch Leads
                </a>
              </li>
              <li>
                <a href="{{ route('gather_companies_data.existing_temp_leads') }}" class="dashboard-sub-link {{ request()->routeIs('gather_companies_data.existing_temp_leads') ? 'active' : '' }}">
                  <i class="bi bi-funnel"></i> Saved Leads
                </a>
              </li>
          </ul>
        </li>
      @endcan


      @can('view leads')
        <li class="dashboard-nav-item dashboard-nav-item--has-dropdown {{ request()->routeIs('leads.*') ? 'dashboard-nav-item--open active' : '' }}">
          <a href="#" class="dashboard-nav-link dashboard-nav-link--dropdown" data-tooltip="Leads">
            <i class="bi bi-funnel-fill"></i>
            <span>Leads</span>
            <i class="bi bi-chevron-down dashboard-nav-arrow"></i>
          </a>
          <ul class="dashboard-sub-menu">
            @can('view leads')
              <li>
                <a href="{{ route('leads.index') }}" class="dashboard-sub-link {{ request()->routeIs('leads.index') ? 'active' : '' }}">
                  <i class="bi bi-list-ul"></i> Lead List
                </a>
              </li>
            @endcan
            @can('manage kanban')
              <li>
                <a href="{{ route('leads.funnel') }}" class="dashboard-sub-link {{ request()->routeIs('leads.funnel') ? 'active' : '' }}">
                  <i class="bi bi-funnel"></i> Lead Funnel
                </a>
              </li>
            @endcan
            @can('create leads')
              <li>
                <a href="{{ route('leads.create') }}" class="dashboard-sub-link {{ request()->routeIs('leads.create', 'leads.store', 'leads.edit', 'leads.update') ? 'active' : '' }}">
                  <i class="bi bi-plus-circle-fill"></i> Add Lead
                </a>
              </li>
            @endcan
          </ul>
        </li>
      @endcan

      @can('manage dialer')
        <li class="dashboard-nav-item {{ request()->routeIs('dialer.*') ? 'active' : '' }}">
          <a href="{{ route('dialer.index') }}" class="dashboard-nav-link" data-tooltip="Dialer">
            <i class="bi bi-telephone-fill"></i>
            <span>Dialer</span>
          </a>
        </li>
      @endcan

      @can('view deals')
        <li class="dashboard-nav-item {{ request()->routeIs('deals.*') ? 'active' : '' }}">
          <a href="#" class="dashboard-nav-link" data-tooltip="Deals">
            <i class="bi bi-briefcase-fill"></i>
            <span>Deals</span>
          </a>
        </li>
      @endcan

      @can('view invoices')
        <!--<li class="dashboard-nav-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">-->
        <!--  <a href="#" class="dashboard-nav-link" data-tooltip="Invoices">-->
        <!--    <i class="bi bi-receipt-cutoff"></i>-->
        <!--    <span>Invoices</span>-->
        <!--  </a>-->
        <!--</li>-->
      @endcan

      @can('view tasks')
        <li class="dashboard-nav-item dashboard-nav-item--has-dropdown {{ request()->routeIs('tasks.*') ? 'dashboard-nav-item--open active' : '' }}">
          <a href="#" class="dashboard-nav-link dashboard-nav-link--dropdown" data-tooltip="Tasks">
            <i class="bi bi-check2-square"></i>
            <span>Tasks</span>
            <i class="bi bi-chevron-down dashboard-nav-arrow"></i>
          </a>
          <ul class="dashboard-sub-menu">
            @can('view tasks')
              <li>
                <a href="{{ route('tasks.index') }}" class="dashboard-sub-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                  <i class="bi bi-list-ul"></i> Task List
                </a>
              </li>
            @endcan
            @can('create tasks')
              <li>
                <a href="{{ route('tasks.create') }}" class="dashboard-sub-link {{ request()->routeIs('tasks.create', 'tasks.store', 'tasks.edit', 'tasks.update') ? 'active' : '' }}">
                  <i class="bi bi-plus-circle-fill"></i> Add Task
                </a>
              </li>
            @endcan
          </ul>
        </li>
      @endcan

       @can('view-timeline leads')
        <li class="dashboard-nav-item dashboard-nav-item--has-dropdown {{ request()->routeIs('activities.*', 'reports.*') ? 'dashboard-nav-item--open active' : '' }}">
          <a href="{{ route('activities.index') }}" class="dashboard-nav-link dashboard-nav-link--dropdown" data-tooltip="Reports">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span>Reports</span>
            <i class="bi bi-chevron-down dashboard-nav-arrow"></i>
          </a>
          <ul class="dashboard-sub-menu">
            @can('view reports')
              <li>
                <a href="{{ route('reports.index') }}" class="dashboard-sub-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                  <i class="bi bi-file-earmark-text-fill"></i> Reports
                </a>
              </li>
            @endcan
            @can('view-timeline leads')
              <li>
                <a href="{{ route('activities.index') }}" class="dashboard-sub-link {{ request()->routeIs('activities.index') ? 'active' : '' }}">
                  <i class="bi bi-clock-fill"></i> Activity Timeline
                </a>
              </li>
            @endcan
          </ul>
        </li>
      @endcan

      @can('view reports')
        {{-- <li class="dashboard-nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
          <a href="#" class="dashboard-nav-link" data-tooltip="Reports">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span>Reports</span>
            <i class="bi bi-chevron-down dashboard-nav-arrow"></i>
          </a>
        </li> --}}
      @endcan

      @can('view templates')
         <li class="dashboard-nav-item dashboard-nav-item--has-dropdown {{ request()->routeIs('templates.*') ? 'dashboard-nav-item--open active' : '' }}">
          <a href="#" class="dashboard-nav-link dashboard-nav-link--dropdown" data-tooltip="Leads">
            <i class="bi bi-funnel-fill"></i>
            <span>Templates</span>
            <i class="bi bi-chevron-down dashboard-nav-arrow"></i>
          </a>
          <ul class="dashboard-sub-menu">
              <li>
                <a href="{{ route('templates.index') }}" class="dashboard-sub-link {{ request()->routeIs('templates.index') ? 'active' : '' }}">
                  <i class="bi bi-list-ul"></i> All Templates
                </a>
              </li>
              <li>
                <a href="{{ route('templates.create') }}" class="dashboard-sub-link {{ request()->routeIs('templates.create') ? 'active' : '' }}">
                  <i class="bi bi-plus-lg"></i> Add New Template
                </a>
              </li>
          </ul>
        </li>
      @endcan




    </ul>

    {{-- ── USER MANAGEMENT ── --}}
    @can('view users')
      <p class="dashboard-nav-label">User Management</p>
      <ul class="dashboard-nav-list">
        <li class="dashboard-nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
          <a href="{{ route('users.index') }}" class="dashboard-nav-link" data-tooltip="Users">
            <i class="bi bi-people-fill"></i>
            <span>Users</span>
          </a>
        </li>
      </ul>
    @endcan

    {{-- ── ROLE MANAGEMENT ── --}}
    @can('view roles')
      <p class="dashboard-nav-label">Role Management</p>
      <ul class="dashboard-nav-list">
        <li class="dashboard-nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
          <a href="{{ route('roles.index') }}" class="dashboard-nav-link" data-tooltip="Roles">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Roles</span>
          </a>
        </li>
      </ul>
    @endcan

    {{-- ── SETTINGS ── --}}
    @canany(['view settings', 'view lead-sources', 'view lead-statuses', 'view priority-statuses'])
      <p class="dashboard-nav-label">Settings</p>
      <ul class="dashboard-nav-list">

        {{-- Settings Dropdown --}}
        <li class="dashboard-nav-item dashboard-nav-item--has-dropdown {{ request()->routeIs('settings', 'settings.*') ? 'dashboard-nav-item--open active' : '' }}">
          <a href="#" class="dashboard-nav-link dashboard-nav-link--dropdown" data-tooltip="Settings">
            <i class="bi bi-gear-fill"></i>
            <span>Settings</span>
            <i class="bi bi-chevron-down dashboard-nav-arrow"></i>
          </a>
          <ul class="dashboard-sub-menu">

            @can('view settings')
              <li>
                <a href="{{ route('settings.index') }}" class="dashboard-sub-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                  <i class="bi bi-sliders"></i> Main Settings
                </a>
              </li>
            @endcan

            @can('view lead-sources')
              <li>
                <a href="{{ route('settings.lead-sources.index') }}" class="dashboard-sub-link {{ request()->routeIs('settings.lead-sources.*') ? 'active' : '' }}">
                  <i class="bi bi-funnel-fill"></i> Lead Sources
                </a>
              </li>
            @endcan
            {{--
            @can('view manage-lead-funnel')
              <li>
                <a href="{{ route('settings.lead-steps.index') }}" class="dashboard-sub-link {{ request()->routeIs('settings.lead-steps.*') ? 'active' : '' }}">
                  <i class="bi bi-bar-chart-steps"></i> Lead Steps
                </a>
              </li>
            @endcan --}}

            @can('view lead-statuses')
              <li>
                <a href="{{ route('settings.lead-statuses.index') }}" class="dashboard-sub-link {{ request()->routeIs('settings.lead-statuses.*') ? 'active' : '' }}">
                  <i class="bi bi-bookmark-fill"></i> Lead Status
                </a>
              </li>
            @endcan

            @can('view priority-statuses')
              <li>
                <a href="{{ route('settings.priority-statuses.index') }}" class="dashboard-sub-link {{ request()->routeIs('settings.priority-statuses.*') ? 'active' : '' }}">
                  <i class="bi bi-flag-fill"></i> Priority Status
                </a>
              </li>
            @endcan

          </ul>
        </li>

      </ul>
    @endcanany

  </nav>

  <div class="dashboard-user-box">
    <div class="gs-profile-avatar-ring">
      <div class="gs-profile-avatar-initials">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
      </div>
    </div>
    <div class="dashboard-user-info">
      <span class="dashboard-user-name">{{ Auth::user()->name ?? 'User' }}</span>
      <small class="text-white">{{ Auth::user()->getRoleNames()->first() ? ucwords(str_replace('_', ' ', Auth::user()->getRoleNames()->first())) : '' }}</small>
    </div>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <x-dropdown-link class="p-0 dropdown-item dashboard-dropdown-logout" :href="route('logout')" onclick="event.preventDefault(); var f=this.closest('form'); if(window.miniConfirm){ window.miniConfirm('Are you sure you want to log out?', 'Log Out', function(){ f.submit(); }); } else { f.submit(); }">
        <i class="bi bi-box-arrow-right"></i>
      </x-dropdown-link>
    </form>
  </div>
</aside>

{{-- ── Active item scroll fix ── --}}
<script>
  (function () {
    var nav = document.getElementById('sidebarNav');
    if (!nav) return;

    // Step 1: Force open the active submenu immediately
    // (dashboard.js opens it via transition, but we need it open before scrolling)
    var openItem = nav.querySelector('.dashboard-nav-item--open');
    if (openItem) {
      var subMenu = openItem.querySelector('.dashboard-sub-menu');
      if (subMenu) {
        subMenu.style.maxHeight = subMenu.scrollHeight + 'px';
      }
    }

    // Step 2: After browser reflows the now-open submenu, scroll to active link
    setTimeout(function () {
      var activeEl = nav.querySelector('.dashboard-sub-link.active')
                  || nav.querySelector('.dashboard-nav-item.active > .dashboard-nav-link');

      if (!activeEl) return;

      var navRect  = nav.getBoundingClientRect();
      var elRect   = activeEl.getBoundingClientRect();
      var elRelTop = elRect.top - navRect.top + nav.scrollTop;

      nav.scrollTop = elRelTop - (nav.clientHeight / 2) + (activeEl.clientHeight / 2);
    }, 50);

  })();
</script>
