@extends('layouts.app')
@section('title', 'Existing Temporary Leads')
@section('content')

<div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
        <div class="page-title-bar">
            <h2>Temporary Leads</h2>
        </div>
        <p>View and manage all saved temporary leads from Google Places & Companies House</p>
    </div>
    <div class="gs-page-topbar-actions">
        <a href="{{ route('api.index') }}" class="gs-btn gs-btn--outline">
            <i class="bi bi-arrow-left"></i> Back to Search
        </a>
        <a href="{{route('leads.create')}}" class="gs-btn gs-btn--primary">
            <i class="bi bi-plus-lg"></i> Create Lead Manually
        </a>
    </div>
</div>

<!-- ── STATS BAR ──────────────────────────────────────── -->
<div class="gs-stat-row gs-stat-row--6">
    <div class="gs-stat-card gs-stat-card--blue">
		<div class="gs-stat-top">
			<div class="dash-inner-data">
				<p class="gs-stat-label">Total Saved</p>
				<p class="gs-stat-value">{{ $total }}</p>
			</div>
			<div class="gs-stat-icon gs-stat-icon--blue">
				<i class="bi bi-buildings"></i>
			</div>
		</div>
	</div>
	<div class="gs-stat-card gs-stat-card--teal">
		<div class="gs-stat-top">
			<div class="dash-inner-data">
				<p class="gs-stat-label">From Google API</p>
				<p class="gs-stat-value">{{ $fromGoogle }}</p>
			</div>
			<div class="gs-stat-icon gs-stat-icon--blue">
				 <i class="bi bi-google"></i>
			</div>
		</div>
	</div>
 <!--    <div class="gs-stat-card gs-stat-card--blue">-->
	<!--	<div class="gs-stat-top">-->
	<!--		<div class="dash-inner-data">-->
	<!--			<p class="gs-stat-label">Companies House</p>-->
	<!--			<p class="gs-stat-value">{{ $fromHouses }}</p>-->
	<!--		</div>-->
	<!--		<div class="gs-stat-icon gs-stat-icon--blue">-->
	<!--			 <i class="bi bi-globe2"></i>-->
	<!--		</div>-->
	<!--	</div>-->
	<!--</div>-->
	<div class="gs-stat-card gs-stat-card--teal">
		<div class="gs-stat-top">
			<div class="dash-inner-data">
				<p class="gs-stat-label">Added Today</p>
				<p class="gs-stat-value">{{ $today }}</p>
			</div>
			<div class="gs-stat-icon gs-stat-icon--blue">
				 <i class="bi bi-calendar3"></i>
			</div>
		</div>
	</div>
</div>

<!-- ── TABS ──────────────────────────────────────────── -->
<div class="api-tabs mt-4" id="tempLeadTabs">
    <button class="api-tab active" data-tab="all">
        <i class="bi bi-grid-3x3-gap"></i> All Leads
        <span class="api-tab-badge">{{ $total }}</span>
    </button>
    <button class="api-tab" data-tab="google">
        <i class="bi bi-google"></i> Google API
        <span class="api-tab-badge">{{ $fromGoogle }}</span>
    </button>
    <!--<button class="api-tab" data-tab="houses">-->
    <!--    <i class="bi bi-globe2"></i> Companies House-->
    <!--    <span class="api-tab-badge">{{ $fromHouses }}</span>-->
    <!--</button>-->
</div>

<!-- ══════════════════════════════════════════════════════
  SHARED SEARCH + BULK ACTION BAR
════════════════════════════════════════════════════════ -->
<div class="api-search-wrap">
    <div class="api-search-row" style="gap:10px;">
        <input type="text" class="api-search-input gs-input" id="tempLeadSearch" placeholder="Filter by company name, phone, address…" oninput="filterTempLeads()" />
        <button class="gs-btn gs-btn--outline" onclick="bulkDeleteSelected()"><i class="bi bi-trash"></i> Delete Selected</button>
        <button class="gs-btn gs-btn--primary" onclick="bulkPromoteSelected()"><i class="bi bi-arrow-up-circle"></i> Promote to Leads</button>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
  TAB PANEL — ALL
════════════════════════════════════════════════════════ -->
<div class="api-tab-panel active" id="tabpanel-all">
    @include('existing-temp-leads.partials.temp-leads-table', [
        'leads'   => $allLeads,
        'tableId' => 'allTable',
        'selectAllId' => 'allSelectAll',
    ])
</div>

<!-- ══════════════════════════════════════════════════════
  TAB PANEL — GOOGLE
════════════════════════════════════════════════════════ -->
<div class="api-tab-panel" id="tabpanel-google">
    @include('existing-temp-leads.partials.temp-leads-table', [
        'leads'   => $googleLeads,
        'tableId' => 'googleTable',
        'selectAllId' => 'googleSelectAll',
    ])
</div>

<!-- ══════════════════════════════════════════════════════
  TAB PANEL — COMPANIES HOUSE
════════════════════════════════════════════════════════ -->
<div class="api-tab-panel" id="tabpanel-houses">
    @include('existing-temp-leads.partials.temp-leads-table', [
        'leads'   => $housesLeads,
        'tableId' => 'housesTable',
        'selectAllId' => 'housesSelectAll',
    ])
</div>

<!-- ══════════════════════════════════════════════════════
  DETAIL OFFCANVAS — view/edit a single temp lead
════════════════════════════════════════════════════════ -->
<div class="offcanvas offcanvas-end API-Modal" tabindex="-1" id="detailOffcanvas" aria-labelledby="detailOffcanvasLabel" style="max-width:1200px;">
    <div class="offcanvas-header gs-notif-header">
        <div class="gs-notif-header-left">
            <div class="gs-notif-header-icon"><i class="bi bi-buildings"></i></div>
            <h5 class="offcanvas-title gs-notif-title" id="detailOffcanvasLabel">Temp Lead Details</h5>
        </div>
        <button type="button" class="gs-notif-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="offcanvas-body" style="">
        <div class="oc-two-col">
            <div class="oc-col-left">
				 <div class="oc-col-body">
					<input type="hidden" name="oc_company_id" id="oc_company_id" value=''>
					<!-- Source badge -->
					<div class="mb-4 api-source-badge">
						<span id="oc_source_badge" class="gs-status gs-status--active"></span>
						<span id="oc_saved_at"></span>
					</div>

					<!-- Company header -->
					<div class="company-gs-header">
						<div class="cgs-header-head" id="oc_avatar">A</div>
						<div class="cgs-header-text">
							<h5 id="oc_company_name">—</h5>
							<p id="oc_address_short">—</p>
						</div>
					</div>

					<!-- Info grid -->
					<div class="temp-grid">
						<div class="vl-field-row">
							<span class="vl-field-label"><i class="bi bi-telephone vl-field-icon"></i> Phone</span>
							<span class="vl-field-value" id="oc_phone">—</span>
						</div>
						<div class="vl-field-row">
							<span class="vl-field-label"><i class="bi bi-geo-alt vl-field-icon"></i> Address</span>
							<span class="vl-field-value" id="oc_address">—</span>
						</div>
						<div class="vl-field-row">
							<span class="vl-field-label"><i class="bi bi-building vl-field-icon"></i> City</span>
							<span class="vl-field-value" id="oc_city">—</span>
						</div>
						<div class="vl-field-row">
							<span class="vl-field-label"><i class="bi bi-mailbox vl-field-icon"></i> Postcode</span>
							<span class="vl-field-value" id="oc_postcode">—</span>
						</div>
						<div class="vl-field-row">
							<span class="vl-field-label"><i class="bi bi-globe2 vl-field-icon"></i> Website</span>
							<span class="vl-field-value" id="oc_website">—</span>
						</div>
						<div class="vl-field-row">
							<span class="vl-field-label"><i class="bi bi-star-fill vl-field-icon" style="color:#f4a821;"></i> Rating</span>
							<span class="vl-field-value" id="oc_rating">—</span>
						</div>
						<div class="vl-field-row">
							<span class="vl-field-label"><i class="bi bi-clock vl-field-icon"></i> Opening Hours</span>
							<span class="vl-field-value" id="oc_hours">—</span>
						</div>
					</div>
				</div>
				<div class="canvas-footer-flex">
                    <button class="gs-btn gs-btn--outline" id="oc_call_btn">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                    <button class="gs-btn gs-btn--primary" id="oc_promote_btn">
                        <i class="bi bi-arrow-up-circle"></i> Promote to Lead
                    </button>
                    <button class="gs-edit-btn" style=" border-color: rgba(220, 38, 38, 0.3); color: #dc2626;" id="oc_delete_btn">
                        <i class="bi bi-trash"></i>
                    </button>
	            </div>
            </div>

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
                    <button class="gs-btn gs-btn--primary" onclick="startCallLead()"><i class="bi bi-telephone-outbound"></i> Start your call here</button>
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
/* ════════════════════════════════════════════════════════
   TAB SWITCHING
════════════════════════════════════════════════════════ */
document.querySelectorAll('#tempLeadTabs .api-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('#tempLeadTabs .api-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.api-tab-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tabpanel-' + this.dataset.tab).classList.add('active');
        // Re-apply filter to the newly visible tab
        filterTempLeads();
    });
});

/* ════════════════════════════════════════════════════════
   LIVE FILTER — searches all rows in the active tab
════════════════════════════════════════════════════════ */
function filterTempLeads() {
    var q = document.getElementById('tempLeadSearch').value.toLowerCase().trim();
    var activePanel = document.querySelector('.api-tab-panel.active');
    if (!activePanel) return;

    activePanel.querySelectorAll('tbody tr.tl-main-row').forEach(function(row) {
        var text = row.textContent.toLowerCase();
        var detailRow = document.getElementById(row.dataset.detailId);
        var show = !q || text.includes(q);
        row.style.display = show ? '' : 'none';
        if (detailRow) detailRow.style.display = (show && detailRow.classList.contains('open')) ? '' : 'none';
    });
}

document.getElementById('tempLeadSearch').addEventListener('keyup', filterTempLeads);

/* ════════════════════════════════════════════════════════
   TOGGLE DETAIL ROW
════════════════════════════════════════════════════════ */
function toggleDetail(btn, detailId) {
    var detailRow = document.getElementById(detailId);
    var isOpen    = detailRow.classList.contains('open');

    var table = btn.closest('table');
    table.querySelectorAll('.api-detail-row.open').forEach(r => r.classList.remove('open'));
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
   DETAIL OFFCANVAS — open with lead data
════════════════════════════════════════════════════════ */
function openDetailOffcanvas(leadJson) {
    var lead   = JSON.parse(leadJson);
    var others = lead.others_parsed || {};

    // Avatar initial
    document.getElementById('oc_avatar').textContent = (lead.company_name || '?').charAt(0).toUpperCase();
    document.getElementById('oc_company_name').textContent = lead.company_name || '—';
    document.getElementById('oc_address_short').textContent = lead.city || lead.postcode || lead.address || '—';
    document.getElementById('oc_phone').textContent   = lead.phone || '—';
    document.getElementById('oc_address').textContent = lead.address || '—';
    document.getElementById('oc_city').textContent    = lead.city || '—';
    document.getElementById('oc_postcode').textContent = lead.postcode || '—';
    document.getElementById('oc_saved_at').textContent = 'Saved: ' + (lead.created_at || '');
    document.getElementById('oc_company_id').value = lead.id || '';

    window.fetchLeadNotes(lead.id);

    // Source badge
    var sourceBadge = document.getElementById('oc_source_badge');
    if (lead.lead_gathering_from === 'google_api') {
        sourceBadge.className = 'gs-status gs-status--active';
        sourceBadge.innerHTML = '<i class="bi bi-google"></i> Google API';
    } else {
        sourceBadge.className = 'gs-status gs-status--pending';
        sourceBadge.innerHTML = '<i class="bi bi-globe2"></i> Companies House';
    }

    // Website from others
    var websiteEl = document.getElementById('oc_website');
    if (others.website && others.website !== 'N/A') {
        websiteEl.innerHTML = '<a href="' + others.website + '" target="_blank" style="color:var(--primary-color,#0d6efd);">' + others.website.replace(/^https?:\/\//, '') + '</a>';
    } else {
        websiteEl.textContent = '—';
    }

    // Rating from others
    var ratingEl = document.getElementById('oc_rating');
    if (others.rating && others.rating !== 'N/A') {
        var filled = Math.round(parseFloat(others.rating));
        var stars  = '';
        for (var s = 1; s <= 5; s++) {
            stars += '<i class="bi bi-star' + (s <= filled ? '-fill' : '') + '" style="color:#f4a821;font-size:.8rem;"></i>';
        }
        ratingEl.innerHTML = stars + ' <small style="color:var(--text-muted,#888);">(' + (others.total_reviews || 0) + ' reviews)</small>';
    } else {
        ratingEl.textContent = '—';
    }

    // Opening hours from others
    var hoursEl = document.getElementById('oc_hours');
    if (others.opening_hours && others.opening_hours.length) {
        hoursEl.innerHTML = '<ul style="margin:4px 0 0;padding-left:16px;font-size:.82rem;line-height:1.9;">'
            + others.opening_hours.map(h => '<li>' + h + '</li>').join('')
            + '</ul>';
    } else {
        hoursEl.textContent = '—';
    }

    // Wire action buttons
    document.getElementById('oc_call_btn').onclick = function() {
        setCallData(lead.company_name, lead.phone, lead.address);
        var detailBS = bootstrap.Offcanvas.getInstance(document.getElementById('detailOffcanvas'));
        if (detailBS) detailBS.hide();
    };

    document.getElementById('oc_promote_btn').onclick = function() {
        promoteToLead(lead.id);
    };

    document.getElementById('oc_delete_btn').onclick = function() {
        deleteTempLead(lead.id, lead.company_name);
    };

    var offcanvas = new bootstrap.Offcanvas(document.getElementById('detailOffcanvas'));
    offcanvas.show();
}

/* ════════════════════════════════════════════════════════
   CALL OFFCANVAS
════════════════════════════════════════════════════════ */
function setCallData(company, phone, address) {
    document.getElementById('callCompanyName').textContent  = company || '—';
    document.getElementById('callPhoneDisplay').textContent = phone || '—';
    document.getElementById('call_company_name').value = company || '';
    document.getElementById('call_phone').value = phone || '';
    document.getElementById('call_address').value = address || '';
}

/* ════════════════════════════════════════════════════════
   DELETE SINGLE TEMP LEAD
════════════════════════════════════════════════════════ */
function deleteTempLead(id, name) {
    if (!confirm('Delete "' + name + '" from temporary leads?')) return;

    fetch('{{ url("/temp-leads") }}/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.status === 'success') {
            // Remove all rows with this lead id across all tables
            document.querySelectorAll('[data-lead-id="' + id + '"]').forEach(function(el) {
                var detailId = el.dataset.detailId;
                var detailRow = detailId ? document.getElementById(detailId) : null;
                el.remove();
                if (detailRow) detailRow.remove();
            });
            // Close offcanvas if open
            var oc = bootstrap.Offcanvas.getInstance(document.getElementById('detailOffcanvas'));
            if (oc) oc.hide();
            showToast('success', '"' + name + '" deleted successfully.');
            updateCountBadges();
        } else {
            showToast('error', data.message || 'Delete failed.');
        }
    })
    .catch(function() {
        showToast('error', 'Delete failed. Please try again.');
    });
}

/* ════════════════════════════════════════════════════════
   BULK DELETE
════════════════════════════════════════════════════════ */
function bulkDeleteSelected() {
    var activePanel = document.querySelector('.api-tab-panel.active');
    var checked = activePanel ? activePanel.querySelectorAll('.row-check:checked') : [];
    if (!checked.length) { showToast('warning', 'Select at least one lead to delete.'); return; }
    if (!confirm('Delete ' + checked.length + ' selected lead(s)?')) return;

    var ids = Array.from(checked).map(cb => cb.value);

    fetch('{{ url("/temp-leads/bulk-delete") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.status === 'success') {
            ids.forEach(function(id) {
                document.querySelectorAll('[data-lead-id="' + id + '"]').forEach(function(el) {
                    var detailId = el.dataset.detailId;
                    var detailRow = detailId ? document.getElementById(detailId) : null;
                    el.remove();
                    if (detailRow) detailRow.remove();
                });
            });
            showToast('success', data.message);
            updateCountBadges();
        } else {
            showToast('error', data.message || 'Bulk delete failed.');
        }
    })
    .catch(function() {
        showToast('error', 'Bulk delete failed. Please try again.');
    });
}

/* ════════════════════════════════════════════════════════
   PROMOTE SINGLE LEAD
════════════════════════════════════════════════════════ */
function promoteToLead(id) {
    if (!confirm('Promote this temporary lead to a full lead?')) return;

    fetch('{{ route("gather_companies_data.google_api.make_into_leads") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ companies: [id] })
      })
      .then(r => r.json())
      .then(function(data) {
        if (data.status === 'success') {
            document.querySelectorAll('[data-lead-id="' + id + '"]').forEach(function(el) {
                var detailId = el.dataset.detailId;
                var detailRow = detailId ? document.getElementById(detailId) : null;
                el.remove();
                if (detailRow) detailRow.remove();
            });
            // Close offcanvas if open
            var oc = bootstrap.Offcanvas.getInstance(document.getElementById('detailOffcanvas'));
            if (oc) oc.hide();
            updateCountBadges();
            showToast('success', data.message);
        } else {
          showToast('error', data.message || 'Promotion failed.');
        }
      })
      .catch(function() {
        showToast('error', 'Promotion failed. Please try again.');
      });
}

/* ════════════════════════════════════════════════════════
   BULK PROMOTE
════════════════════════════════════════════════════════ */
function bulkPromoteSelected() {
    var activePanel = document.querySelector('.api-tab-panel.active');
    var checked = activePanel ? activePanel.querySelectorAll('.row-check:checked') : [];
    if (!checked.length) { showToast('warning', 'Select at least one lead to promote.'); return; }
    var ids = Array.from(checked).map(cb => cb.value);
    
    fetch('{{ route("gather_companies_data.google_api.make_into_leads") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ companies: ids})
      })
      .then(r => r.json())
      .then(function(data) {
        if (data.status === 'success') {
            ids.forEach(function(id) {
                document.querySelectorAll('[data-lead-id="' + id + '"]').forEach(function(el) {
                    var detailId = el.dataset.detailId;
                    var detailRow = detailId ? document.getElementById(detailId) : null;
                    el.remove();
                    if (detailRow) detailRow.remove();
                });
            });
            // Close offcanvas if open
            var oc = bootstrap.Offcanvas.getInstance(document.getElementById('detailOffcanvas'));
            if (oc) oc.hide();
            updateCountBadges();
            showToast('success', data.message);       
        } else {
          showToast('error', data.message || 'Failed to save note.');
        }
      })
      .catch(function(err) {
        console.error('saveCallNote error:', err);
        showToast('error', 'Failed to save note. Please try again.');
      });
   
}

/* ════════════════════════════════════════════════════════
   UPDATE TAB BADGE COUNTS after delete
════════════════════════════════════════════════════════ */
function updateCountBadges() {
    ['all', 'google', 'houses'].forEach(function(tab) {
        var panel = document.getElementById('tabpanel-' + tab);
        if (!panel) return;
        var count = panel.querySelectorAll('tbody tr.tl-main-row').length;
        var btn   = document.querySelector('[data-tab="' + tab + '"] .api-tab-badge');
        if (btn) btn.textContent = count;
    });
}

/* ════════════════════════════════════════════════════════
   TOAST NOTIFICATION
════════════════════════════════════════════════════════ */
function showToast(type, message) {
    var colours = { success: '#28a745', warning: '#f4a821', error: '#dc3545' };
    var icons   = { success: 'bi-check-circle-fill', warning: 'bi-exclamation-triangle-fill', error: 'bi-x-circle-fill' };
    var toast = document.createElement('div');
    toast.style.cssText = [
        'position:fixed','bottom:24px','right:24px','z-index:9999',
        'background:#fff','border-left:4px solid ' + (colours[type] || '#333'),
        'padding:14px 20px','border-radius:6px',
        'box-shadow:0 4px 20px rgba(0,0,0,.15)',
        'display:flex','align-items:center','gap:10px',
        'font-size:.9rem','max-width:380px'
    ].join(';');
    toast.innerHTML =
        '<i class="bi ' + (icons[type] || 'bi-info-circle') + '" style="color:' + (colours[type] || '#333') + ';font-size:1.1rem;"></i>' +
        '<span>' + message + '</span>';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

 window.saveCallNote = function() {
    var note = document.getElementById('callNoteInput').value.trim();
    if (!note) {
      document.getElementById('callNoteInput').focus();
      return;
    }
    let companyId = document.getElementById('oc_company_id').value;
    if(companyId){

      fetch('{{ route("gather_companies_data.google_api.save_temp_call_note") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ c_id: companyId, note: note })
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

  /**
    * Function to fetch all notes for a specific c_id
    */
    window.fetchLeadNotes = function(c_id) {
        if (!c_id) {
            console.error('No Company ID provided to fetch notes.');
            return;
        }

        // Replace this with your actual route for getting notes
        const url = '{{ route("gather_companies_data.google_api.get_temp_lead_notes") }}?c_id=' + c_id;

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(function(data) {
            if (data.status === 'success') {
                // Use the shared renderer to update the UI
                renderNotesList(data.notes);
            }
        })
        .catch(function(err) {
            console.error('fetchLeadNotes error:', err);
        });
    };

    /**
    * Shared Helper to render the notes HTML
    * This keeps your Save and Fetch functions DRY
    */
    function renderNotesList(notes) {
        let container = document.getElementById('notes-container');
        if (!container) return;

        container.innerHTML = ''; 

        // Note: If your controller already returns them reversed, 
        // you can remove .reverse() here.
        notes.forEach(item => {
            const initial = item.user_name ? item.user_name.charAt(0).toUpperCase() : '?';

            const card = `
                <div class="d-flex border-top mb-3 bg-light p-3">
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
    }

   // 1. Declare a mutable variable to hold our runtime metadata globally in the script scope
    // 1. Keep track of current state variables globally
let currentCallPayload = null;
let currentPhoneNumber = null;

// 2. Clear and register the message handler globally so it catches Dialpad instantly
function handleDialpadMessages(event) {
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
}
window.userId = "{{ auth()->id() }}";

// 3. Trigger action block bound to your button
window.startCallLead = function() {
    const companyIdInput = document.getElementById('oc_company_id');
    const phoneSpan = document.getElementById('oc_phone');
    const dialerBox = document.getElementById('call-dialer');
    const beforeBox = document.querySelector('.call-dialer-box-before');
    const iframe = document.getElementById('dialpadFrame');

    // Structural Safety Guard
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

    // Extraction & Validation
    const leadIdValue = companyIdInput.value.trim();
    if (!leadIdValue || !/^\d+$/.test(leadIdValue)) {
        alert("Please ensure a valid numerical Lead ID is active before calling.");
        return;
    }

    let phoneNumber = phoneSpan.innerText.trim().replace(/[\s\-\(\)]/g, '');
    if (!phoneNumber) {
        alert("No target phone number found to connect.");
        return;
    }

    console.log("Validation Passed. Target Destination: " + phoneNumber);

    // Save configuration states to memory
    currentPhoneNumber = phoneNumber;
    currentCallPayload = {
        lead_id: parseInt(leadIdValue, 10),
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

    // Load or refresh the Dialpad app environment stream inside the visible iframe
    const targetSrc = iframe.getAttribute('data-src');
    if (targetSrc) {
        console.log("Loading Dialpad CTI URL: ", targetSrc);
        iframe.src = targetSrc;
    } else {
        console.error("Dialpad Error: data-src attribute is empty on the iframe.");
    }
}

</script>
@endpush