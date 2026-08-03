@extends('layouts.app')
@section('title', 'Gather Companies Data From API')
@section('content')
<div class="gs-page-topbar">
	<div class="gs-page-topbar-left">
		<div class="page-title-bar">
			<h2>Google API</h2>
		</div>
		<p>Search and import company data into leads</p>
	</div>
	<div class="gs-page-topbar-actions">
		<a href="/leads/create" class="gs-btn gs-btn--outline">
		  <i class="bi bi-arrow-left"></i> Go Back
		</a>
	</div>
</div>


<!-- ── TABS ────────────────────────────────────────────── -->
<div class="api-tabs" id="apiTabs">
  <button class="api-tab active" data-tab="google_api"><i class="bi bi-google"></i> Google API</button>
  {{-- <button class="api-tab" data-tab="direct"><i class="bi bi-globe2"></i> Companies House (UK)</button> --}}
</div>

<!-- ══════════════════════════════════════════════════════
  TAB 1 — Companies House (UK) Direct Search
═════════════════════════════════════════════════════ -->
<div class="api-tab-panel active" id="tabpanel-google_api">

  <!-- Search Bar -->
  <div class="api-search-wrap">
    <div class="api-search-row">
      <input type="text" class="api-search-input gs-input" id="googleSearchInput" placeholder="Search company name, type or category e.g: steel industries, solar companies…" />
      <button class="api-search-btn gs-btn gs-btn--primary" id="googleSearchBtn" onclick="doGoogleSearch()"><i class="bi bi-google"></i> Search</button>
    </div>
    <p class="api-search-hint"><i class="bi bi-info-circle"></i> Live search from company name, company type or company categories — results are filtered to United Kingdom only.</p>
  </div>

  <!-- Loading State -->
  <div id="googleLoadingState" style="display:none; padding:48px 0; text-align:center;">
    <div class="spinner-border" role="status" style="width:2.2rem;height:2.2rem;color:var(--primary-color,#0d6efd);"></div>
    <p style="margin-top:14px; color:var(--text-muted,#6c757d); font-size:.9rem;">Searching Google Places for UK companies…</p>
  </div>

  <!-- Empty / Error State -->
  <div id="googleEmptyState" style="display:none; padding:48px 0; text-align:center;">
    <i class="bi bi-search" style="font-size:2.8rem; color:var(--text-muted,#bbb);"></i>
    <p id="googleEmptyMsg" style="margin-top:12px; color:var(--text-muted,#6c757d); font-size:.95rem;">No results found. Try a different search.</p>
  </div>

  <!-- Results Panel -->
  <div class="api-results" id="googleResults" style="display:none;">
    <div class="api-table-bar">
      <div class="api-table-bar-left">
        <span class="api-result-count" id="googleCount">Showing 0 results</span>
      </div>
      <div class="gs-page-topbar-actions">
        <button class="gs-btn gs-btn--outline" id="set-temp-multibtn" onclick="addSelectedGoogleLeads()"> <!--- onclick="saveAllGoogleLeads()" --->
          <i class="bi bi-floppy-fill"></i> Add Selected as Temporary Leads
        </button>
        <button class="gs-btn gs-btn--primary" id="add-main-leads-btn" onclick="addSelectedGoogleMainLeads()">
          <i class="bi bi-plus-lg"></i> Add Selected as Leads
        </button>
      </div>
    </div>

    <div class="gs-table-wrap">
      <div style="overflow-x:auto;">
        <table class="gs-table" id="googleTable">
          <thead>
            <tr>
              <th style="width:40px;">
                <input type="checkbox" class="gs-table-checkbox" id="googleSelectAll"/>
              </th>
              <th>Company Name</th>
              <th>Category / Type</th>
              <th>Phone</th>
              <!-- <th>Rating</th> -->
              <th>Status</th>
              <th>Address</th>
              <th style="text-align:center;">Actions</th>
            </tr>
          </thead>
          <tbody id="googleTbody">
            <!-- Rows injected dynamically by JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- /Results Panel -->

</div>
<!-- /TAB 1 -->

<!-- ══════════════════════════════════════════════════════
  TAB 2 — Google API
════════════════════════════════════════════════════════ -->
<div class="api-tab-panel" id="tabpanel-direct">
    <div class="api-search-wrap">
      <div class="api-search-row">
        <input type="text" class="api-search-input gs-input" id="directSearchInput" placeholder="Search directly on Companies House (UK)…"/>
        <button class="api-search-btn gs-btn gs-btn--primary" onclick="doHousesSearch()" id="directSearchButton"><i class="bi bi-globe2"></i> Search</button>      </div>
      <p class="api-search-hint"><i class="bi bi-info-circle"></i>Live search against the official Companies House register</p>
    </div>

    <!-- Loading State -->
    <div id="HousingLoadingState" style="display:none; padding:48px 0; text-align:center;">
      <div class="spinner-border" role="status" style="width:2.2rem;height:2.2rem;color:var(--primary-color,#0d6efd);"></div>
      <p style="margin-top:14px; color:var(--text-muted,#6c757d); font-size:.9rem;">Searching Companies House for UK companies…</p>
    </div>

      <!-- Empty / Error State -->
    <div id="HousingEmptyState" style="display:none; padding:48px 0; text-align:center;">
      <i class="bi bi-search" style="font-size:2.8rem; color:var(--text-muted,#bbb);"></i>
      <p id="HousingEmptyMsg" style="margin-top:12px; color:var(--text-muted,#6c757d); font-size:.95rem;">No results found. Try a different search.</p>
    </div>

    <div class="api-results" id="directResults" style="display:none;">
      <div class="api-table-bar">
        <div class="api-table-bar-left"><span class="api-result-count" id="directCount">Showing 2 results</span></div>
        <div class="gs-page-topbar-actions">
          <button class="gs-btn gs-btn--outline"><i class="bi bi-floppy-fill"></i> Save Temporary Lead</button>
          <button class="gs-btn gs-btn--primary" onclick="deleteSelected('directTable')"><i class="bi bi-plus-lg"></i> Add Insert Lead</button>
        </div>
      </div>
      <div class="gs-table-wrap">
        <div style="overflow-x:auto;">
          <table class="gs-table" id="directTable">
            <thead>
              <tr>
                <th style="width:40px;"><input type="checkbox" class="gs-table-checkbox" id="directSelectAll"/></th>
                <th>Company Name</th>
                <th>Company No</th>
                <th>Status</th>
                <th>Address</th>
                <th>Date of Creation</th>
                <th style="text-align:center;">Choose</th>
              </tr>
            </thead>
            <tbody id="HousingTbody">
              <!-- Sample static rows for demonstration -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
<!-- /TAB 2 -->

<!-- ══════════════════════════════════════════════════════
  CALL OFFCANVAS
════════════════════════════════════════════════════════ -->
<div class="offcanvas offcanvas-end API-Modal" tabindex="-1" id="callOffcanvas" aria-labelledby="callOffcanvasLabel">
  <div class="offcanvas-header gs-notif-header">
    <div class="gs-notif-header-left">
      <div class="gs-notif-header-icon"><i class="bi bi-telephone-fill"></i></div>
      <h5 class="offcanvas-title gs-notif-title" id="callOffcanvasLabel">Make a Call</h5>
    </div>
    <button type="button" class="gs-notif-close" data-bs-dismiss="offcanvas" aria-label="Close"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="offcanvas-body" style="padding:0;">
    <div class="oc-two-col">
      <!-- LEFT COL -->
      <div class="oc-col-left">
		<div class="oc-col-body">
			<div class="vl-profile">
			  <div class="gs-user-avatar vl-avatar-lg">
				<img src="/images/site-logo.png">
			  </div>
			  <div class="vl-profile-info">
				<h2 id="callCompanyName">—</h2>
				<p id="callPhoneDisplay">—</p>
			  </div>
			</div>
			<div class="gs-panel-header mb-3">
			  <h5 class="gs-panel-title">Lead Details</h5>
			</div>
			<div class="gs-form-grid">
			  <input type="hidden" name="oc_company_id" id="oc_company_id" value=''>
			  <div class="gs-field gs-field--full">
				<label class="gs-label">Company Name <span class="gs-required">*</span></label>
				<input type="text" class="gs-input" name="company_name" id="company_name" placeholder="Company name" value="">
			  </div>
			  <div class="gs-field gs-field--full">
				<label class="gs-label">Phone Number <span class="gs-required">*</span></label>
				<input type="tel" class="gs-input" name="phone" id="phone" placeholder="+44 7911 123456" value="">
			  </div>
			  <div class="gs-field gs-field--full">
				<label class="gs-label">Email</label>
				<input type="email" class="gs-input" id="oc_email" placeholder="email@company.com"/>
			  </div>
			  <div class="gs-field gs-field--full">
				<label class="gs-label">Address</label>
				<input type="text" class="gs-input" id="oc_address" placeholder="Address…"/>
			  </div>
			</div>
			<div style="flex:1;"></div>
		</div>
       <div class="canvas-footer-flex">
          <button class="gs-btn gs-btn--outline" style="flex:1;justify-content:center;" onclick="saveTemporaryLead()"><i class="bi bi-floppy"></i> Save Temporary</button>
          <button class="gs-btn gs-btn--primary" style="flex:1;justify-content:center;" onclick="addLead()"><i class="bi bi-plus-lg"></i> Add Lead</button>
        </div>
      </div>
      <!-- RIGHT COL -->
      <div class="oc-col-right">
          @php
              $get_dialpad_id = get_setting_data(['dialpad_api_key']);
              $dialpad_id = $get_dialpad_id['dialpad_api_key'] ?? '';
          @endphp
          <div class="call-dialer-box d-none" id="call-dialer" style="min-height: 520px;">
              <!-- Try running it without the sandbox restrictions to check if it's blocking the postMessage handshake -->
              <iframe id="dialpadFrame" data-src="https://dialpad.com/apps/{{ $dialpad_id }}" src="" title="Dialpad" allow="microphone; speaker-selection; autoplay; camera; display-capture; hid" frameborder="0" style="width:100%;height:520px;"></iframe>
          </div>
          <div class="call-dialer-box-before d-flex align-items-center justify-content-center" style="min-height: 150px;">
              <button class="gs-btn gs-btn--primary" id="startCallButton" onclick="startCallLead()"><i class="bi bi-telephone-outbound"></i> Start your call here</button>
          </div>
          <div style="margin-top:20px;">
              <label class="gs-label">Call Notes</label>
              <textarea class="call-notes-area" id="callNoteInput" placeholder="Talking With company :&#10;Discussing About Energy Requirements And Site Details." onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
              
              <div class="d-flex gap-8 mb-3 mt-2">
                  <button class="gs-btn gs-btn--teal" style="flex:1;justify-content:center;" onclick="saveCallNote()"><i class="bi bi-check-lg"></i> Save Note</button>
              </div>
          <div id="notes-container" class="notes-cnt">
                    </div>
                </div>
            </div>
      </div>
  </div>
</div>

@endsection

@push('scripts')

<script>
  window.userId = "{{ auth()->id() }}";
  const CLIENT_ID = '{{ $dialpad_id }}';
  const DIALPAD_FRAME = document.getElementById('dialpadFrame');

  /* ════════════════════════════════════════════════════════
    TAB SWITCHING
  ════════════════════════════════════════════════════════ */
  document.querySelectorAll('.api-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
      document.querySelectorAll('.api-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.api-tab-panel').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      document.getElementById('tabpanel-' + this.dataset.tab).classList.add('active');
    });
  });


  /* ════════════════════════════════════════════════════════
    TAB 1 & 2 — Static search (show pre-loaded results)
  ════════════════════════════════════════════════════════ */
  function doSearch(tab) {
    var input = document.getElementById(tab + 'SearchInput').value.trim();
    var results = document.getElementById(tab + 'Results');
    if (!input) {
      document.getElementById(tab + 'SearchInput').focus();
      return;
    }
    results.classList.add('visible');
  }


  ['fetchSearchInput', 'directSearchInput'].forEach(function(id) {
    var el = document.getElementById(id);
    if (!el) return;
    var tab = id.replace('SearchInput', '');
    el.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') doSearch(tab);
    });
  });


  /* ════════════════════════════════════════════════════════
    SELECT ALL CHECKBOXES
  ════════════════════════════════════════════════════════ */
  const fetchSelectAll = document.getElementById('fetchSelectAll');
  if (fetchSelectAll) {
    fetchSelectAll.addEventListener('change', function () {
      document.querySelectorAll('#fetchTable .row-check')
        .forEach(cb => cb.checked = this.checked);
    });
  }

  const directSelectAll = document.getElementById('directSelectAll');
  if (directSelectAll) {
    directSelectAll.addEventListener('change', function () {
      document.querySelectorAll('#directTable .row-check')
        .forEach(cb => cb.checked = this.checked);
    });
  }

  const googleSelectAll = document.getElementById('googleSelectAll');
  if (googleSelectAll) {
    googleSelectAll.addEventListener('change', function () {
      document.querySelectorAll('#googleTable .row-check')
        .forEach(cb => cb.checked = this.checked);
    });
  }


  /* ════════════════════════════════════════════════════════
    TOGGLE EXPANDABLE DETAIL ROW
  ════════════════════════════════════════════════════════ */
  function toggleDetail(btn, detailId) {
    var detailRow = document.getElementById(detailId);
    var isOpen    = detailRow.classList.contains('open');

    // Close all other open rows in same table
    var table = btn.closest('table');
    table.querySelectorAll('.api-detail-row.open').forEach(function(r) {
      r.classList.remove('open');
    });
    table.querySelectorAll('.gs-edit-btn.expanded').forEach(function(b) {
      b.classList.remove('expanded');
      b.innerHTML = '<i class="bi bi-plus-lg"></i>';
      b.title = 'View Details';
    });

    if (!isOpen) {
      detailRow.classList.add('open');
      btn.classList.add('expanded');
      btn.innerHTML = '<i class="bi bi-dash-lg"></i>';
      btn.title = 'Close';
    }
  }


  /* ════════════════════════════════════════════════════════
    DELETE SELECTED ROWS
  ════════════════════════════════════════════════════════ */
  function deleteSelected(tableId) {
    var checked = document.querySelectorAll('#' + tableId + ' .row-check:checked');
    if (!checked.length) { alert('Please select at least one row.'); return; }
    if (!confirm('Remove ' + checked.length + ' selected record(s)?')) return;
    checked.forEach(function(cb) {
      var mainRow   = document.getElementById('row-' + cb.value);
      var detailRow = mainRow ? mainRow.nextElementSibling : null;
      if (mainRow)   mainRow.remove();
      if (detailRow && detailRow.classList.contains('api-detail-row')) detailRow.remove();
    });
  }


  /* ════════════════════════════════════════════════════════
    CALL OFFCANVAS — populate fields
  ════════════════════════════════════════════════════════ */
  function setCallData(company, phone, address, index) {
    //initiateDialpadCall(phone);
    var nameEl  = document.getElementById('callCompanyName');
    var phoneEl = document.getElementById('callPhoneDisplay');
    var addressEl = document.getElementById('oc_address');
    var companyIdEl = document.getElementById('oc_company_id');
    if (nameEl)  nameEl.textContent  = company;
    if (phoneEl) phoneEl.textContent = phone;
    if (addressEl) addressEl.value = address;
    if (companyIdEl) companyIdEl.value = index;

    let container = document.getElementById('notes-container');
    container.innerHTML = ''; 

    var coField = document.getElementById('company_name');
    var phField = document.getElementById('phone');
    if (coField) coField.value = company;
    if (phField) phField.value = phone;



  }


  /* ════════════════════════════════════════════════════════
    GOOGLE API TAB — AJAX SEARCH
  ════════════════════════════════════════════════════════ */

  // Enter key on Google search input
  document.getElementById('googleSearchInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') doGoogleSearch();
  });
  document.getElementById('googleSearchInput').addEventListener('input', function () {
    if (this.value.length > 80) {
        this.value = this.value.slice(0, 80);
    }
});


  /**
   * Fires the AJAX request to the Google API controller.
   * Route: POST /search-companies-data-google
   */
  function doGoogleSearch() {
    var query = document.getElementById('googleSearchInput').value.trim();
    if (!query) {
      document.getElementById('googleSearchInput').focus();
      return;
    }

    setGoogleUI('loading');

    fetch('{{ route("gather_companies_data.google_api.search_companies_data") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ query: query })
    })
    .then(function(res) {
      if (!res.ok) throw new Error('Server error ' + res.status);
      return res.json();
    })
    .then(function(data) {
      if (data.status === 'success' && data.results && data.results.length > 0) {
        renderGoogleResults(data.results);
      } else {
        setGoogleUI('empty', 'No UK companies found for "' + query + '". Try a broader search term.');
      }
    })
    .catch(function(err) {
      console.error('Google search error:', err);
      setGoogleUI('empty', 'Something went wrong. Please try again.');
    });
  }


  /**
   * Controls which UI state is visible inside Tab 3.
   * @param {string} state  'loading' | 'empty' | 'results'
   * @param {string} [msg]  Custom message for empty state
   */
  function setGoogleUI(state, msg) {
    document.getElementById('googleLoadingState').style.display = 'none';
    document.getElementById('googleEmptyState').style.display = 'none';
    document.getElementById('googleResults').style.display = 'none';

    var btn = document.getElementById('googleSearchBtn');

    if (state === 'loading') {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Searching…';
      document.getElementById('googleLoadingState').style.display = 'block';

    } else if (state === 'empty') {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-google"></i> Search';
      document.getElementById('googleEmptyMsg').textContent = msg || 'No results found.';
      document.getElementById('googleEmptyState').style.display = 'block';

    } else if (state === 'results') {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-google"></i> Search';
      document.getElementById('googleResults').style.display = 'block';
    }
  }


  /**
   * Builds and injects table rows for every company returned by the controller.
   * Two <tr> elements per company: main row + collapsible detail row.
   * @param {Array} results  Array of company objects from the controller
   */
  function renderGoogleResults(results) {
    var tbody = document.getElementById('googleTbody');
    tbody.innerHTML = ''; // clear previous results

    results.forEach(function(company, index) {
      var rowId    = 'g' + index;
      var detailId = 'detail-g' + index;

      /* ── Business status → badge ── */
      var statusClass = 'gs-status--active';
      var statusLabel = company.business_status || 'N/A';
      if (statusLabel === 'OPERATIONAL') {
        statusLabel = 'Active';
        statusClass = 'gs-status--active';
      } else if (statusLabel === 'CLOSED_TEMPORARILY') {
        statusLabel = 'Temp Closed';
        statusClass = 'gs-status--pending';
      } else if (statusLabel === 'CLOSED_PERMANENTLY') {
        statusLabel = 'Closed';
        statusClass = 'gs-status--lost';
      }

      /* ── Star rating ── */
      var ratingHtml = '<span style="color:var(--text-muted,#aaa);">N/A</span>';
      if (company.rating && company.rating !== 'N/A') {
        var filled = Math.round(parseFloat(company.rating));
        var stars  = '';
        for (var s = 1; s <= 5; s++) {
          stars += '<i class="bi bi-star' + (s <= filled ? '-fill' : '') + '" style="color:#f4a821;font-size:.75rem;"></i>';
        }
        ratingHtml = '<span style="display:flex;align-items:center;gap:3px;">'
                  + stars
                  + '<small style="margin-left:4px;color:var(--text-muted,#888);">(' + (company.total_reviews || 0) + ')</small>'
                  + '</span>';
      }

      /* ── Category — clean up Google underscore slugs ── */
      var categoryDisplay = (company.category || company.type || 'N/A')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, function(l) { return l.toUpperCase(); });

      /* ── Opening hours ── */
      var hoursHtml = '<span style="color:var(--text-muted,#aaa);">Not available</span>';
      if (Array.isArray(company.opening_hours) && company.opening_hours.length) {
        hoursHtml = '<ul style="margin:0;padding-left:16px;font-size:.82rem;line-height:2;">'
          + company.opening_hours.map(function(h) { return '<li>' + escHtml(h) + '</li>'; }).join('')
          + '</ul>';
      }

      /* ── Open now badge ── */
      var openNowHtml = '';
      if (company.open_now === true) {
        openNowHtml = '<span class="gs-status gs-status--active" style="font-size:.72rem;padding:2px 8px;">Open Now</span>';
      } else if (company.open_now === false) {
        openNowHtml = '<span class="gs-status gs-status--lost" style="font-size:.72rem;padding:2px 8px;">Closed Now</span>';
      }

      /* ── Website ── */
      var websiteHtml = '<span style="color:var(--text-muted,#aaa);">N/A</span>';
      if (company.website && company.website !== 'N/A') {
        websiteHtml = '<a href="' + escHtml(company.website) + '" target="_blank" rel="noopener" style="color:var(--primary-color,#0d6efd);word-break:break-all;">'
                    + truncate(company.website.replace(/^https?:\/\//, ''), 32)
                    + '</a>';
      }

      /* ── Phone ── */
      var phoneRaw      = company.international_phone !== 'N/A' ? company.international_phone : (company.phone !== 'N/A' ? company.phone : null);
      var phoneDisplay  = phoneRaw
        ? '<a href="tel:' + escHtml(phoneRaw) + '" style="color:var(--primary-color,#0d6efd);white-space:nowrap;">' + escHtml(phoneRaw) + '</a>'
        : '<span style="color:var(--text-muted,#aaa);">N/A</span>';

      /* ══════════════════════════════════════════════════
        MAIN ROW
      ══════════════════════════════════════════════════ */
      var mainRow = document.createElement('tr');
      mainRow.id  = 'row-' + rowId;
      mainRow.innerHTML =
        '<td><input type="checkbox" class="gs-table-checkbox row-check" value="' + rowId + '"/></td>' +
        '<td>' +
          '<p class="api-company-name">' + escHtml(company.name) + '</p>' +
          (company.website && company.website !== 'N/A'
            ? '<a href="' + escHtml(company.website) + '" target="_blank" class="api-company-no" style="color:var(--primary-color,#0d6efd);font-size:.78rem;">' + truncate(company.website.replace(/^https?:\/\//, ''), 36) + '</a>'
            : '<p class="api-company-no" style="color:var(--text-muted,#aaa);">No website listed</p>'
          ) +
        '</td>' +
        '<td style="font-size:.83rem;max-width:160px;">' + escHtml(categoryDisplay) + '</td>' +
        '<td style="white-space:nowrap;">' + phoneDisplay + '</td>' +
        // '<td>' + ratingHtml + '</td>' +
        '<td><span class="gs-status ' + statusClass + '">' + escHtml(statusLabel) + '</span></td>' +
        '<td style="font-size:.83rem;max-width:200px;">' + escHtml(company.address) + '</td>' +
        '<td>' +
          '<div style="display:flex;align-items:center;gap:6px;justify-content:center;">' +
            '<button class="gs-btn gs-btn--outline" onclick="saveTempLead(\'' + rowId + '\', googleResultsData[' + index + '])">' +
              '<i class="bi bi-floppy-fill"></i> Save Temp' +
            '</button>' +
            '<button class="gs-edit-btn" title="View Details" onclick="toggleDetail(this, \'' + detailId + '\')">' +
              '<i class="bi bi-plus-lg"></i>' +
            '</button>' +
          '</div>' +
        '</td>';

      /* ══════════════════════════════════════════════════
        DETAIL ROW (collapsible panel)
      ══════════════════════════════════════════════════ */
      var detailRow = document.createElement('tr');
      detailRow.className = 'api-detail-row';
      detailRow.id        = detailId;
      detailRow.innerHTML =
        '<td colspan="8" class="api-detail-cell">' +
          '<div class="api-lead-panel">' +

            // Header
            '<div class="api-lead-panel-header">' +
              '<div>' +
                '<p class="api-lead-panel-title">Company Information <small style="font-weight:400;opacity:.65;">— via Google Places</small></p>' +
                '<h6>' + escHtml(company.name.toUpperCase()) + '</h6>' +
              '</div>' +
              '<div class="gs-page-topbar-actions">' +
                (phoneRaw
                  ? '<button class="gs-btn gs-btn--outline" data-bs-toggle="offcanvas" data-bs-target="#callOffcanvas" onclick="setCallData(\'' + escJs(company.name) + '\',\'' + escJs(phoneRaw) + '\',\'' + escJs(company.address) + '\',' + index + ')">' +
                      '<i class="bi bi-telephone-fill"></i> Call' +
                    '</button>'
                  : ''
                ) +
                '<button class="gs-btn gs-btn--primary" onclick="addAsLead(\'' + rowId + '\')">' +
                  '<i class="bi bi-plus-lg"></i> Add as Lead' +
                '</button>' +
              '</div>' +
            '</div>' +

            // Body
            '<div class="api-lead-panel-body">' +
              '<div class="vl-info-grid">' +

                '<div class="vl-info-col">' +
                  '<p class="vl-section-title">' + escHtml(company.name.toUpperCase()) + '</p>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-telephone vl-field-icon"></i> Phone</span>' +
                    '<span class="vl-field-value">' + phoneDisplay + '</span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-globe2 vl-field-icon"></i> Website</span>' +
                    '<span class="vl-field-value">' + websiteHtml + '</span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-geo-alt vl-field-icon"></i> Address</span>' +
                    '<span class="vl-field-value">' + escHtml(company.address) + '</span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-tag vl-field-icon"></i> Category</span>' +
                    '<span class="vl-field-value">' + escHtml(categoryDisplay) + '</span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-activity vl-field-icon"></i> Status</span>' +
                    '<span class="vl-field-value"><span class="gs-status ' + statusClass + '">' + escHtml(statusLabel) + '</span></span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-star-fill vl-field-icon" style="color:#f4a821;"></i> Rating</span>' +
                    '<span class="vl-field-value">' + ratingHtml + '</span>' +
                  '</div>' +
                '</div>' +

                // Right col — opening hours
                '<div class="vl-info-col">' +
                  '<p class="vl-section-title">OPENING HOURS <small style="margin-left:6px;">' + openNowHtml + '</small></p>' +
                  '<div style="margin-top:10px;">' + hoursHtml + '</div>' +
                '</div>' +

              '</div>' +
            '</div>' +

          '</div>' +
        '</td>';

      tbody.appendChild(mainRow);
      tbody.appendChild(detailRow);
    });

    // Update count and show table
    var n = results.length;
    document.getElementById('googleCount').textContent = 'Showing ' + n + ' result' + (n !== 1 ? 's' : '');
    setGoogleUI('results');
  }


  /* ════════════════════════════════════════════════════════
    GOOGLE — action handlers
  ════════════════════════════════════════════════════════ */


  // Store last results so action buttons can reference full data
  var googleResultsData = [];


  // Override renderGoogleResults to also cache data
  var _originalRender = renderGoogleResults;
  renderGoogleResults = function(results) {
    googleResultsData = results;
    _originalRender(results);
  };


  function saveTempLead(rowId, company) {
    var btn = document.querySelector('#row-' + rowId + ' .gs-btn--outline');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving…';
    }
    fetch('{{ route("gather_companies_data.google_api.save_temp_lead") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: JSON.stringify(company)
    })
    .then(r => r.json())
    .then(function(data) {
      if (data.status === 'duplicate') {
        showToast('warning', data.message);
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#f4a821;"></i> Already Saved';
        }
      } else {
        showToast('success', data.message);
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#28a745;"></i> Saved';
        }
      }
    })
    .catch(function(err) {
      console.error('saveTempLead error:', err);
      showToast('error', 'Failed to save. Please try again.');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy-fill"></i> Save Temp';
      }
    });
  }


  function saveAllGoogleLeads() {
    if (!googleResultsData.length) { showToast('warning', 'No results to save.'); return; }
    var btn = document.querySelector('[onclick="saveAllGoogleLeads()"]');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving…';
    }
    fetch('{{ route("gather_companies_data.google_api.save_all_temp_leads") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ companies: googleResultsData })
    })
    .then(r => r.json())
    .then(function(data) {
      showToast(data.saved > 0 ? 'success' : 'warning', data.message);
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy-fill"></i> Save All as Temporary Leads';
      }
      // Mark already-saved rows visually
      if (data.skipped && data.skipped.length) {
        markDuplicateRows(data.skipped);
      }
    })
    .catch(function(err) {
      console.error('saveAllGoogleLeads error:', err);
      showToast('error', 'Bulk save failed. Please try again.');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-floppy-fill"></i> Save All as Temporary Leads';
      }
    });
  }


  function addSelectedGoogleLeads() {
    var btn = document.getElementById('set-temp-multibtn');

    // Save original button HTML
    var originalBtnHtml = btn.innerHTML;

    // Disable button + show loader
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Adding To Temporary`;

    var checked = document.querySelectorAll('#googleTable .row-check:checked');
    if (!checked.length) {

        // Restore button
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;

        showToast('warning', 'Please select at least one company.');
        return;
    }
   
    var selected = Array.from(checked).map(function(cb) {
      return googleResultsData[parseInt(cb.value.replace('g', ''))];
    }).filter(Boolean);

    fetch('{{ route("gather_companies_data.google_api.save_all_temp_leads") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ companies: selected })
    })
    .then(r => r.json())
    .then(function(data) {
      showToast(data.saved > 0 ? 'success' : 'warning', data.message);
      if (data.skipped && data.skipped.length) {
        markDuplicateRows(data.skipped);
      }
    })
    .catch(function(err) {
      console.error('addSelectedGoogleLeads error:', err);
      showToast('error', 'Failed to save selected. Please try again.');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;
    });
  }


  function markDuplicateRows(skippedNames) {
    googleResultsData.forEach(function(company, index) {
      if (skippedNames.includes(company.name)) {
        var row = document.getElementById('row-g' + index);
        if (row) {
          var saveBtn = row.querySelector('.gs-btn--outline');
          if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#f4a821;"></i> Already Saved';
          }
        }
      }
    });
  }


  function showToast(type, message) {
    var colours = { success: '#28a745', warning: '#f4a821', error: '#dc3545' };
    var icons   = { success: 'bi-check-circle-fill', warning: 'bi-exclamation-triangle-fill', error: 'bi-x-circle-fill' };
    var toast = document.createElement('div');
    toast.style.cssText = [
      'position:fixed', 'bottom:24px', 'right:24px', 'z-index:9999',
      'background:#fff', 'border-left:4px solid ' + (colours[type] || '#333'),
      'padding:14px 20px', 'border-radius:6px',
      'box-shadow:0 4px 20px rgba(0,0,0,.15)',
      'display:flex', 'align-items:center', 'gap:10px',
      'font-size:.9rem', 'max-width:380px',
      'animation:slideIn .25s ease'
    ].join(';');
    toast.innerHTML =
      '<i class="bi ' + (icons[type] || 'bi-info-circle') + '" style="color:' + (colours[type] || '#333') + ';font-size:1.1rem;"></i>' +
      '<span>' + message + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 4000);
  }


  /* ════════════════════════════════════════════════════════
    UTILITY HELPERS
  ════════════════════════════════════════════════════════ */

  /** Escape a string for safe HTML injection */
  function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }


  /** Escape a string for safe inline JS string literals (inside onclick="...") */
  function escJs(str) {
    if (!str) return '';
    return String(str)
      .replace(/\\/g, '\\\\')
      .replace(/'/g, "\\'")
      .replace(/"/g, '\\"');
  }


  /** Truncate a string with an ellipsis */
  function truncate(str, len) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '…' : str;
  }


  // houses API Code specific to Tab 2 (Google API) to keep things organized


  window.setHouseUI = function(state, msg) {
    document.getElementById('HousingLoadingState').style.display = 'none';
    document.getElementById('HousingEmptyState').style.display = 'none';
    document.getElementById('directResults').style.display = 'none';

    var btn = document.getElementById('directSearchButton');

    if (state === 'loading') {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Searching…';
      document.getElementById('HousingLoadingState').style.display = 'block';
    } else if (state === 'empty') {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-google"></i> Search';
      document.getElementById('HousingEmptyMsg').textContent = msg || 'No results found.';
      document.getElementById('HousingEmptyState').style.display = 'block';
    } else if (state === 'results') {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-google"></i> Search';
      document.getElementById('directResults').style.display = 'block';
    }
  }


  window.doHousesSearch = function() {
    var query = document.getElementById('directSearchInput').value.trim();
    if (!query) {
      document.getElementById('directSearchInput').focus();
      return;
    }
    window.setHouseUI('loading');

    fetch('{{ route("gather_companies_data.housing_api.search_companies_data_housing") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ query: query })
    })
    .then(function(res) {
      if (!res.ok) throw new Error('Server error ' + res.status);
      return res.json();
    })
    .then(function(data) {
      if (data.status === 'success' && data.results && data.results.length > 0) {
        window.renderHousingResults(data.results);
        console.log('hereee');
      } else {
        window.setHouseUI('empty', 'No UK companies found for "' + query + '". Try a broader search term.');
      }
    })
    .catch(function(err) {
      console.error('Google search error:', err);
      window.setHouseUI('empty', 'Something went wrong. Please try again.');
    });
  }

  window.renderHousingResults = function(results) {
    var tbody = document.getElementById('HousingTbody');
    tbody.innerHTML = ''; // clear previous results

    results.forEach(function(company, index) {
      var rowId    = 'g' + index;
      var detailId = 'detail-g' + index;

      /* ── Business status → badge ── */
      var statusClass = 'gs-status--active';
      var statusLabel = company.business_status || 'N/A';
      if (statusLabel === 'OPERATIONAL') {
        statusLabel = 'Active';
        statusClass = 'gs-status--active';
      } else if (statusLabel === 'CLOSED_TEMPORARILY') {
        statusLabel = 'Temp Closed';
        statusClass = 'gs-status--pending';
      } else if (statusLabel === 'CLOSED_PERMANENTLY') {
        statusLabel = 'Closed';
        statusClass = 'gs-status--lost';
      }

      /* ── Star rating ── */
      var ratingHtml = '<span style="color:var(--text-muted,#aaa);">N/A</span>';
      if (company.rating && company.rating !== 'N/A') {
        var filled = Math.round(parseFloat(company.rating));
        var stars  = '';
        for (var s = 1; s <= 5; s++) {
          stars += '<i class="bi bi-star' + (s <= filled ? '-fill' : '') + '" style="color:#f4a821;font-size:.75rem;"></i>';
        }
        ratingHtml = '<span style="display:flex;align-items:center;gap:3px;">'
                  + stars
                  + '<small style="margin-left:4px;color:var(--text-muted,#888);">(' + (company.total_reviews || 0) + ')</small>'
                  + '</span>';
      }

      /* ── Category — clean up Google underscore slugs ── */
      var categoryDisplay = (company.category || company.type || 'N/A')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, function(l) { return l.toUpperCase(); });

      /* ── Opening hours ── */
      var hoursHtml = '<span style="color:var(--text-muted,#aaa);">Not available</span>';
      if (Array.isArray(company.opening_hours) && company.opening_hours.length) {
        hoursHtml = '<ul style="margin:0;padding-left:16px;font-size:.82rem;line-height:2;">'
          + company.opening_hours.map(function(h) { return '<li>' + escHtml(h) + '</li>'; }).join('')
          + '</ul>';
      }

      /* ── Open now badge ── */
      var openNowHtml = '';
      if (company.open_now === true) {
        openNowHtml = '<span class="gs-status gs-status--active" style="font-size:.72rem;padding:2px 8px;">Open Now</span>';
      } else if (company.open_now === false) {
        openNowHtml = '<span class="gs-status gs-status--lost" style="font-size:.72rem;padding:2px 8px;">Closed Now</span>';
      }

      /* ── Website ── */
      var websiteHtml = '<span style="color:var(--text-muted,#aaa);">N/A</span>';
      if (company.website && company.website !== 'N/A') {
        websiteHtml = '<a href="' + escHtml(company.website) + '" target="_blank" rel="noopener" style="color:var(--primary-color,#0d6efd);word-break:break-all;">'
                    + truncate(company.website.replace(/^https?:\/\//, ''), 32)
                    + '</a>';
      }

      /* ── Phone ── */
      var phoneRaw      = company.international_phone !== 'N/A' ? company.international_phone : (company.phone !== 'N/A' ? company.phone : null);
      var phoneDisplay  = phoneRaw
        ? '<a href="tel:' + escHtml(phoneRaw) + '" style="color:var(--primary-color,#0d6efd);white-space:nowrap;">' + escHtml(phoneRaw) + '</a>'
        : '<span style="color:var(--text-muted,#aaa);">N/A</span>';

      /* ══════════════════════════════════════════════════
        MAIN ROW
      ══════════════════════════════════════════════════ */
      var mainRow = document.createElement('tr');
      mainRow.id  = 'row-' + rowId;
      mainRow.innerHTML =
        '<td><input type="checkbox" class="gs-table-checkbox row-check" value="' + rowId + '"/></td>' +
        '<td>' +
          '<p class="api-company-name">' + escHtml(company.name) + '</p>' +
          (company.website && company.website !== 'N/A'
            ? '<a href="' + escHtml(company.website) + '" target="_blank" class="api-company-no" style="color:var(--primary-color,#0d6efd);font-size:.78rem;">' + truncate(company.website.replace(/^https?:\/\//, ''), 36) + '</a>'
            : '<p class="api-company-no" style="color:var(--text-muted,#aaa);">No website listed</p>'
          ) +
        '</td>' +
        '<td style="font-size:.83rem;max-width:160px;">' + escHtml(categoryDisplay) + '</td>' +
        '<td style="white-space:nowrap;">' + phoneDisplay + '</td>' +
        // '<td>' + ratingHtml + '</td>' +
        '<td><span class="gs-status ' + statusClass + '">' + escHtml(statusLabel) + '</span></td>' +
        '<td style="font-size:.83rem;max-width:200px;">' + escHtml(company.address) + '</td>' +
        '<td>' +
          '<div style="display:flex;align-items:center;gap:6px;justify-content:center;">' +
            '<button class="gs-btn gs-btn--outline" onclick="saveTempLead(\'' + rowId + '\', googleResultsData[' + index + '])">' +
              '<i class="bi bi-floppy-fill"></i> Save Temp' +
            '</button>' +
            '<button class="gs-edit-btn" title="View Details" onclick="toggleDetail(this, \'' + detailId + '\')">' +
              '<i class="bi bi-plus-lg"></i>' +
            '</button>' +
          '</div>' +
        '</td>';

      /* ══════════════════════════════════════════════════
        DETAIL ROW (collapsible panel)
      ══════════════════════════════════════════════════ */
      var detailRow = document.createElement('tr');
      detailRow.className = 'api-detail-row';
      detailRow.id        = detailId;
      detailRow.innerHTML =
        '<td colspan="8" class="api-detail-cell">' +
          '<div class="api-lead-panel">' +

            // Header
            '<div class="api-lead-panel-header">' +
              '<div>' +
                '<p class="api-lead-panel-title">Company Information <small style="font-weight:400;opacity:.65;">— via Google Places</small></p>' +
                '<h6>' + escHtml(company.name.toUpperCase()) + '</h6>' +
              '</div>' +
              '<div class="gs-page-topbar-actions">' +
                (phoneRaw
                  ? '<button class="gs-btn gs-btn--outline" data-bs-toggle="offcanvas" data-bs-target="#callOffcanvas" onclick="setCallData(\'' + escJs(company.name) + '\',\'' + escJs(phoneRaw) + '\',\'' + escJs(company.address) + '\',' + index + ')">' +
                      '<i class="bi bi-telephone-fill"></i> Call' +
                    '</button>'
                  : ''
                ) +
                '<button class="gs-btn gs-btn--primary" onclick="addAsLead(\'' + rowId + '\')">' +
                  '<i class="bi bi-plus-lg"></i> Add as Lead' +
                '</button>' +
              '</div>' +
            '</div>' +

            // Body
            '<div class="api-lead-panel-body">' +
              '<div class="vl-info-grid">' +

                '<div class="vl-info-col">' +
                  '<p class="vl-section-title">' + escHtml(company.name.toUpperCase()) + '</p>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-telephone vl-field-icon"></i> Phone</span>' +
                    '<span class="vl-field-value">' + phoneDisplay + '</span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-globe2 vl-field-icon"></i> Website</span>' +
                    '<span class="vl-field-value">' + websiteHtml + '</span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-geo-alt vl-field-icon"></i> Address</span>' +
                    '<span class="vl-field-value">' + escHtml(company.address) + '</span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-tag vl-field-icon"></i> Category</span>' +
                    '<span class="vl-field-value">' + escHtml(categoryDisplay) + '</span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-activity vl-field-icon"></i> Status</span>' +
                    '<span class="vl-field-value"><span class="gs-status ' + statusClass + '">' + escHtml(statusLabel) + '</span></span>' +
                  '</div>' +

                  '<div class="vl-field-row">' +
                    '<span class="vl-field-label"><i class="bi bi-star-fill vl-field-icon" style="color:#f4a821;"></i> Rating</span>' +
                    '<span class="vl-field-value">' + ratingHtml + '</span>' +
                  '</div>' +
                '</div>' +

                // Right col — opening hours
                '<div class="vl-info-col">' +
                  '<p class="vl-section-title">OPENING HOURS <small style="margin-left:6px;">' + openNowHtml + '</small></p>' +
                  '<div style="margin-top:10px;">' + hoursHtml + '</div>' +
                '</div>' +

              '</div>' +
            '</div>' +

          '</div>' +
        '</td>';

      tbody.appendChild(mainRow);
      tbody.appendChild(detailRow);
    });

    // Update count and show table
    var n = results.length;
    document.getElementById('directCount').textContent = 'Showing ' + n + ' result' + (n !== 1 ? 's' : '');
    window.setHouseUI('results');
  }

  window.saveCallNote = function() {
    var note = document.getElementById('callNoteInput').value.trim();
    if (!note) {
      document.getElementById('callNoteInput').focus();
      return;
    }
    let companyId = document.getElementById('oc_company_id').value;
    if(companyId){
      let company = googleResultsData[companyId];

      fetch('{{ route("gather_companies_data.google_api.save_call_note") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ company: company, note: note })
      })
      .then(r => r.json())
      .then(function(data) {
        if (data.status === 'success') {
          showToast('success', data.message);
          document.getElementById('callNoteInput').value = '';

          let container = document.getElementById('notes-container');
          container.innerHTML = ''; 

          // Reverse the array if you want the newest notes at the top
          data.notes.reverse().forEach(item => {
              const initial = item.user_name.charAt(0).toUpperCase();

              const card = `
                  <div class="notes-cards d-flex border-top mb-3 bg-light p-3">
                      <div class="gs-user-avatar me-3 d-flex align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold" 
                          style="width: 40px; height: 40px; min-width: 40px;">
                          ${initial}
                      </div>
                      <div class="w-100">
                          <div class="d-flex justify-content-between">
                              <span class="fw-bold small">${item.user_name}</span>
                              <span class="text-muted small" style="font-size: 0.75rem;">${item.date}</span>
                          </div>
                          <p class="text-secondary small mb-0 mt-1">${item.note}</p>
                      </div>
                  </div>
              `;
              container.insertAdjacentHTML('beforeend', card);
          });
          
        } else {
          showToast('error', data.message || 'Failed to save note.');
        }
      })
      .catch(function(err) {
        console.error('saveCallNote error:', err);
        showToast('error', 'Failed to save note. Please try again.');
      });
    } else {
      showToast('error', 'Company ID missing. Cannot save note.');
    }
  };

window.saveTemporaryLead = async function() {
  let companyId = document.getElementById('oc_company_id').value;
  if (companyId) {
    let company = googleResultsData[companyId];

    company.name = document.getElementById('company_name').value || company.name;
    company.international_phone = document.getElementById('phone').value || company.international_phone;
    company.email = document.getElementById('oc_email').value || '';
    company.address = document.getElementById('oc_address').value || company.address;

    try {
      // 1. Await the actual network request
      const response = await fetch('{{ route("gather_companies_data.google_api.save_update_temp_lead") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify(company)
      });

      // 2. Await the JSON parsing
      const data = await response.json();

      if (data.status === 'duplicate') {
        showToast('warning', data.message);
      } else {
        showToast('success', data.message);   
      }

      console.log('saveTemporaryLead response:', data);
      
      // 3. Return the data so startCallLead can catch it
      return data; 

    } catch (err) {
      console.error('saveTemporaryLead error:', err);
      showToast('error', 'Failed to save temporary lead. Please try again.');
      throw err; // Forwards the error to startCallLead's catch block
    }
  } else {
    showToast('error', 'Company ID missing. Cannot save temporary lead.');
    return null;
  }
};


  window.addSelectedGoogleMainLeads = function() {

    var btn = document.getElementById('add-main-leads-btn');

    // Save original button HTML
    var originalBtnHtml = btn.innerHTML;

    // Disable button + show loader
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> Adding To Leads`;

    var checked = document.querySelectorAll('#googleTable .row-check:checked');
     if (!checked.length) {
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;
        showToast('warning', 'Please select at least one company.');
        return;
    }
    
    var selected = Array.from(checked).map(function(cb) {
      return googleResultsData[parseInt(cb.value.replace('g', ''))];
    }).filter(Boolean);
    fetch('{{ route("gather_companies_data.google_api.save_all_main_leads") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ companies: selected })
    })
    .then(r => r.json())
    .then(function(data) {
      showToast(data.saved > 0 ? 'success' : 'warning', data.message);
      if (data.skipped && data.skipped.length) {
        markDuplicateRows(data.skipped);
      }
    })
    .catch(function(err) {
      console.error('addSelectedGoogleLeads error:', err);
      showToast('error', 'Failed to save selected. Please try again.');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;
    });
  }

  window.addAsLead = function(rowId) {
    var index = parseInt(rowId.replace('g', ''));
    var company = googleResultsData[index];
    var selected = company ? [company] : [];
    fetch('{{ route("gather_companies_data.google_api.save_all_main_leads") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ companies: selected })
    })
    .then(r => r.json())
    .then(function(data) {
      showToast(data.saved > 0 ? 'success' : 'warning', data.message);
      if (data.skipped && data.skipped.length) {
        markDuplicateRows(data.skipped);
      }
    })
    .catch(function(err) {
      console.error('addSelectedGoogleLeads error:', err);
      showToast('error', 'Failed to save selected. Please try again.');
    });
  };
  
  window.addLead = function() {
    let companyId = document.getElementById('oc_company_id').value;
    if(companyId){
      let company = googleResultsData[companyId];
      if(company){
        company.name = document.getElementById('company_name').value || company.name;
        company.international_phone = document.getElementById('phone').value || company.international_phone;
        company.email = document.getElementById('oc_email').value || '';
        company.address = document.getElementById('oc_address').value || company.address;

        var selected = [company];
        fetch('{{ route("gather_companies_data.google_api.save_all_main_leads") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ companies: selected })
        })
        .then(r => r.json())
        .then(function(data) {
          showToast(data.saved > 0 ? 'success' : 'warning', data.message);
          if (data.skipped && data.skipped.length) {
            markDuplicateRows(data.skipped);
          }
        })
        .catch(function(err) {
          console.error('addSelectedGoogleLeads error:', err);
          showToast('error', 'Failed to save selected. Please try again.');
        });
      }
    }
  };

  window.formatPhoneNumber = function(phone) {
    const digits = phone.replace(/\D/g, '');
    if (!digits.startsWith('+')) {
      return '+' + digits;
    }
    return '+' + digits;
  }

  window.initiateDialpadCall = function(phoneNumber) {
    // let formattedNumber = formatPhoneNumber(phoneNumber);
    let formattedNumber = '+918629061873';
    const message = {
      api: 'opencti_dialpad',
      version: '1.0',
      method: 'initiate_call',
      payload: {
        enable_current_tab: false,
        phone_number: formattedNumber,
        identity_type: 'CallCenter',
        identity_id: 1234567,
        custom_data: 'nothing',
      }
    };
    let ret = DIALPAD_FRAME.contentWindow.postMessage(message, 'https://dialpad.com');
    console.log('Call initiated to:', ret);
  }

  let currentCallPayload = null;
  let currentPhoneNumber = null;

  // 2. Clear and register the message handler globally so it catches Dialpad instantly
  window.handleDialpadMessages = function(event) {
      // Only listen to messages coming from Dialpad
      if (!event.origin.includes('dialpad.com')) return;

      const data = event.data;
      console.log("--> Received message from Dialpad Frame:", data);

      if (data && data.api === 'opencti_dialpad') {
          // Look for Dialpad's authentication verification signal
          if (data.method === 'user_authentication' && data.payload?.user_authenticated === true) {
              console.log("Dialpad Handshake Verified! User is logged in. System user_id:", data.payload.user_id);
              
              const iframe = document.getElementById('dialpadFrame');
              if (!iframe || !currentPhoneNumber) {
                  console.error("Dialpad Error: Active iframe context or telephone parameters missing.");
                  return;
              }

              // Step A: Request Dialpad to focus and capture control on this specific browser tab
              console.log("Sending: enable_current_tab");
              iframe.contentWindow.postMessage({
                  'api': 'opencti_dialpad',
                  'version': '1.0',
                  'method': 'enable_current_tab'
              }, 'https://dialpad.com');

              // Step B: Fire the call out to Dialpad with your custom CRM tracking metrics
              console.log("Sending: initiate_call targeting " + currentPhoneNumber);
              iframe.contentWindow.postMessage({
                  'api': 'opencti_dialpad',
                  'version': '1.0',
                  'method': 'initiate_call',
                  'payload': {
                      'phone_number': currentPhoneNumber,
                      'enable_current_tab': true,
                      'custom_data': JSON.stringify(currentCallPayload)
                  }
              }, 'https://dialpad.com');
          } else if (data.method === 'user_authentication' && data.payload?.user_authenticated === false) {
              console.warn("Dialpad warning: Agent is not logged into Dialpad inside the iframe workspace.");
              alert("Please log into your Dialpad account inside the dialer view first.");
          }
      }
  };

  window.callstarted = false;

  window.startCallLead = async function() {
    try {
        let buttn = document.getElementById('startCallButton');
        buttn.disabled = true;
        buttn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Please wait…';

        const result = await window.saveTemporaryLead(); 
        if (result && result.id) {
            const leadId = result.id;
     
          const companyIdInput = document.getElementById('oc_company_id');
          const phoneSpan = document.getElementById('phone');
          const dialerBox = document.getElementById('call-dialer');
          const beforeBox = document.querySelector('.call-dialer-box-before');
          const iframe = document.getElementById('dialpadFrame');

          if (!companyIdInput || !phoneSpan || !dialerBox || !beforeBox || !iframe) {
              console.error("Required DOM Elements for Dialpad initialization are missing.", {
                  companyIdInput: !!companyIdInput,
                  phoneSpan: !!phoneSpan,
                  dialerBox: !!dialerBox,
                  beforeBox: !!beforeBox,
                  iframe: !!iframe
              });
              return;
          }

          const phoneNumber = phoneSpan.value.trim();
          
          if (!phoneNumber) {
              alert("No target phone number found to connect.");
              return;
          }

          console.log("Validation Passed. Target Destination: " + phoneNumber);

          // Save configuration states to memory
          currentPhoneNumber = phoneNumber;
          currentPhoneNumber = '8629061873';
          currentCallPayload = {
              lead_id: parseInt(leadId, 10),
              user_id: window.userId || null, 
              is_temporary: true
          };
          console.log("Constructed Outbound payload context:", currentCallPayload);

          // Mount window message listener safely
          window.removeEventListener('message', handleDialpadMessages);
          window.addEventListener('message', handleDialpadMessages);

          // CRITICAL: Reveal the containers BEFORE setting iframe.src.
          // This forces the browser to evaluate the iframe rendering context instantly.
          beforeBox.classList.remove('d-flex');
          beforeBox.classList.add('d-none');
          dialerBox.classList.remove('d-none');

          window.callstarted = true;

          // Load or refresh the Dialpad app environment stream inside the visible iframe
          const targetSrc = iframe.getAttribute('data-src');
          if (targetSrc) {
              console.log("Loading Dialpad CTI URL: ", targetSrc);
              iframe.src = targetSrc;
          } else {
              console.error("Dialpad Error: data-src attribute is empty on the iframe.");
          }
        } else {
            console.error("Failed to save temporary lead. Cannot initiate call without a valid lead ID.");
        }
    } catch (error) {
        console.error("Process halted due to error:", error);
    }
}

document.getElementById('callOffcanvas').addEventListener('hidden.bs.offcanvas', function () {
    console.log("Call offcanvas closed. Cleaning up Dialpad state.");
    if(window.callstarted){
      currentCallPayload = null;
      window.callstarted = false;
      // here do the same thing like returnn the button text original and iframe set data=src agagin remove src to blank and also remove the event listener for message
      let buttn = document.getElementById('startCallButton');
      buttn.disabled = false;
      buttn.innerHTML = '<i class="bi bi-telephone-outbound"></i> Start your call here';

      const iframee = document.getElementById('dialpadFrame');
      if(iframee){
          iframee.src = '';
      }

      const dialerBoxx = document.getElementById('call-dialer');
      const beforeBoxx = document.querySelector('.call-dialer-box-before');

      beforeBoxx.classList.remove('d-none');
      beforeBoxx.classList.add('d-flex');

      dialerBoxx.classList.add('d-none');

      window.removeEventListener('message', handleDialpadMessages);
   
    }

});


</script>

@endpush