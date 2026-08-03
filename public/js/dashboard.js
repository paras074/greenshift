/**
 * dashboard.js — public/js/dashboard.js
 */
document.addEventListener('DOMContentLoaded', () => {

  const mobileToggle  = document.getElementById('sidebarToggle');
  const sidebarClose  = document.getElementById('sidebarClose');
  const desktopToggle = document.getElementById('sidebarCollapser');
  const sidebar       = document.getElementById('dashboardSidebar');
  const overlay       = document.getElementById('dashboardOverlay');
  const wrapper       = document.querySelector('.dashboard-main-wrapper');

  // ── Mobile sidebar open ──
  if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
      sidebar.classList.add('dashboard-sidebar--open');
      overlay.classList.add('dashboard-overlay--visible');
    });
  }

  // ── Mobile sidebar close ──
  if (sidebarClose) {
    sidebarClose.addEventListener('click', () => {
      sidebar.classList.remove('dashboard-sidebar--open');
      overlay.classList.remove('dashboard-overlay--visible');
    });
  }

  // ── Overlay click closes sidebar ──
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('dashboard-sidebar--open');
      overlay.classList.remove('dashboard-overlay--visible');
    });
  }

  // ── Desktop collapse toggle ──
  if (desktopToggle) {
    desktopToggle.addEventListener('click', () => {
      const isCollapsed = sidebar.classList.toggle('dashboard-sidebar--collapsed');
      wrapper.classList.toggle('dashboard-main-wrapper--collapsed', isCollapsed);
      // Close all dropdowns when sidebar collapses
      if (isCollapsed) closeAllDropdowns();
    });
  }

  // ── Generic dropdown handler — works for ALL .dashboard-nav-link--dropdown ──
  function closeAllDropdowns() {
    document.querySelectorAll('.dashboard-nav-item--has-dropdown').forEach(item => {
      item.classList.remove('dashboard-nav-item--open');
      const sub = item.querySelector('.dashboard-sub-menu');
      if (sub) sub.style.maxHeight = '0';
    });
  }

  document.querySelectorAll('.dashboard-nav-link--dropdown').forEach(toggle => {
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      const item = toggle.closest('.dashboard-nav-item--has-dropdown');
      const sub  = item.querySelector('.dashboard-sub-menu');
      if (!item || !sub) return;

      const isOpen = item.classList.toggle('dashboard-nav-item--open');
      sub.style.maxHeight = isOpen ? sub.scrollHeight + 'px' : '0';
    });
  });

  // ── On page load: if a dropdown has an active sub-link, keep it open ──
  document.querySelectorAll('.dashboard-nav-item--has-dropdown').forEach(item => {
    const hasActiveChild = item.querySelector('.dashboard-sub-link.active');
    if (hasActiveChild) {
      const sub = item.querySelector('.dashboard-sub-menu');
      item.classList.add('dashboard-nav-item--open');
      if (sub) sub.style.maxHeight = sub.scrollHeight + 'px';
    }
  });

  // ── Mobile Search ──
  const mobileSearchBtn   = document.getElementById('mobileSearchBtn');
  const mobileSearchPopup = document.getElementById('mobileSearchPopup');
  const mobileSearchClose = document.getElementById('mobileSearchClose');
  const mobileSearchClear = document.getElementById('mobileSearchClear');
  const mobileSearchInput = document.getElementById('mobileSearchInput');

  if (mobileSearchBtn) {
    mobileSearchBtn.addEventListener('click', () => {
      mobileSearchPopup.classList.add('dashboard-mobile-search-popup--open');
      setTimeout(() => mobileSearchInput && mobileSearchInput.focus(), 300);
    });
  }
  if (mobileSearchClose) {
    mobileSearchClose.addEventListener('click', () => {
      mobileSearchPopup.classList.remove('dashboard-mobile-search-popup--open');
      if (mobileSearchInput) mobileSearchInput.value = '';
    });
  }
  if (mobileSearchClear) {
    mobileSearchClear.addEventListener('click', () => {
      if (mobileSearchInput) { mobileSearchInput.value = ''; mobileSearchInput.focus(); }
    });
  }

  // ── Tab buttons ──
  document.querySelectorAll('.dashboard-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.dashboard-panel-actions')
         .querySelectorAll('.dashboard-tab-btn')
         .forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

});
