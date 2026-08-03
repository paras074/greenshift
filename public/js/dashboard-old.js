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

  if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
      sidebar.classList.add('dashboard-sidebar--open');
      overlay.classList.add('dashboard-overlay--visible');
    });
  }

  if (sidebarClose) {
    sidebarClose.addEventListener('click', () => {
      sidebar.classList.remove('dashboard-sidebar--open');
      overlay.classList.remove('dashboard-overlay--visible');
    });
  }

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('dashboard-sidebar--open');
      overlay.classList.remove('dashboard-overlay--visible');
    });
  }

  if (desktopToggle) {
    desktopToggle.addEventListener('click', () => {
      const isCollapsed = sidebar.classList.toggle('dashboard-sidebar--collapsed');
      wrapper.classList.toggle('dashboard-main-wrapper--collapsed', isCollapsed);
      if (isCollapsed) closeUsersDropdown();
    });
  }

  const usersToggle  = document.getElementById('usersDropdownToggle');
  const usersSubMenu = document.getElementById('usersSubMenu');
  const usersItem    = document.getElementById('usersDropdownItem');

  function closeUsersDropdown() {
    if (usersItem)    usersItem.classList.remove('dashboard-nav-item--open');
    if (usersSubMenu) usersSubMenu.style.maxHeight = '0';
  }

  if (usersToggle && usersSubMenu && usersItem) {
    usersToggle.addEventListener('click', (e) => {
      e.preventDefault();
      const isOpen = usersItem.classList.toggle('dashboard-nav-item--open');
      usersSubMenu.style.maxHeight = isOpen ? usersSubMenu.scrollHeight + 'px' : '0';
    });
  }

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

  document.querySelectorAll('.dashboard-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.dashboard-panel-actions')
         .querySelectorAll('.dashboard-tab-btn')
         .forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

});
