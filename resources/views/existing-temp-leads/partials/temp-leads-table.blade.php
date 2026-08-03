@if($leads->isEmpty())
    <div class="tl-empty-state">
        <i class="bi bi-inbox"></i>
        <p>No temporary leads found in this category.</p>
        <a href="{{ route('api.index') }}" class="gs-btn gs-btn--outline" style="margin-top:14px;display:inline-flex;">
            <i class="bi bi-search"></i> Search & Import Companies
        </a>
    </div>
@else
    <div class="api-results" style="display:block;">
        <div class="api-table-bar">
            <div class="api-table-bar-left">
                <span class="api-result-count">Showing {{ $leads->count() }} lead(s)</span>
            </div>
        </div>
        <div class="gs-table-wrap">
            <div style="overflow-x:auto;">
                <table class="gs-table" id="{{ $tableId }}">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" class="gs-table-checkbox" id="{{ $selectAllId }}"
                                    onchange="document.querySelectorAll('#{{ $tableId }} .row-check').forEach(cb => cb.checked = this.checked)"/>
                            </th>
                            <th>Company Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Source</th>
                            <th>Saved At</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            @php
                                $othersData = $lead->others;
                                
                                $detailId  = 'tl-detail-' . $lead->id;
                                $leadJson  = json_encode([
                                    'id'                 => $lead->id,
                                    'company_name'       => $lead->company_name,
                                    'phone'              => $lead->phone,
                                    'address'            => $lead->address,
                                    'city'               => $lead->city,
                                    'postcode'           => $lead->postcode,
                                    'lead_gathering_from'=> $lead->lead_gathering_from,
                                    'created_at'         => $lead->created_at ? $lead->created_at->format('d M Y, H:i') : '',
                                    'others_parsed'      => $othersData,
                                ]);

                                $website  = $othersData['website'] ?? null;
                                $rating   = $othersData['rating'] ?? null;
                                $hours    = $othersData['opening_hours'] ?? [];
                                $openNow  = $othersData['open_now'] ?? null;
                            @endphp

                            {{-- MAIN ROW --}}
                            <tr class="tl-main-row"
                                id="row-tl-{{ $lead->id }}"
                                data-lead-id="{{ $lead->id }}"
                                data-detail-id="{{ $detailId }}">

                                <td><input type="checkbox" class="gs-table-checkbox row-check" value="{{ $lead->id }}"/></td>

                                <td>
                                    <p class="api-company-name">{{ $lead->company_name ?? 'N/A' }}</p>
                                    @if($website && $website !== 'N/A')
                                        <a href="{{ $website }}" target="_blank" class="api-company-no" style="color:var(--primary-color,#0d6efd);font-size:.78rem;">
                                            {{ Str::limit(str_replace(['http://','https://'], '', $website), 36) }}
                                        </a>
                                    @else
                                        <p class="api-company-no" style="color:var(--text-muted,#aaa);">No website</p>
                                    @endif
                                </td>

                                <td style="white-space:nowrap;">
                                    @if($lead->phone)
                                        <a href="javascript:void(0);" style="color:var(--primary-color,#0d6efd);" onclick="callPhone('{{ $lead->phone }}')">{{ $lead->phone }}</a>
                                    @else
                                        <span style="color:var(--text-muted,#aaa);">—</span>
                                    @endif
                                </td>

                                <td style="font-size:.83rem;max-width:200px;">{{ $lead->address ?? '—' }}</td>

                                <td>
                                    @if($lead->lead_gathering_from === 'google_api')
                                        <span class="tl-source-badge tl-source-badge--google">
                                            <i class="bi bi-google"></i> Google
                                        </span>
                                    @elseif($lead->lead_gathering_from === 'companies_house')
                                        <span class="tl-source-badge tl-source-badge--houses">
                                            <i class="bi bi-globe2"></i> Companies House
                                        </span>
                                    @else
                                        <span class="tl-source-badge tl-source-badge--manual">
                                            <i class="bi bi-person"></i> Manual
                                        </span>
                                    @endif
                                </td>

                                <td style="font-size:.82rem;white-space:nowrap;color:var(--text-muted,#6c757d);">
                                    {{ $lead->created_at ? $lead->created_at->format('d M Y') : '—' }}<br>
                                    <small>{{ $lead->created_at ? $lead->created_at->format('H:i') : '' }}</small>
                                </td>

                                <td>
                                    <div style="display:flex;align-items:center;gap:6px;justify-content:center;">
                                        {{-- View details --}}
                                        <button class="gs-btn gs-btn--outline"
                                            onclick="openDetailOffcanvas({{ Js::from($leadJson) }})">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        
                                        {{-- Expand detail --}}
                                        <button class="gs-edit-btn" title="Quick Preview"
                                            onclick="toggleDetail(this, '{{ $detailId }}')">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- DETAIL ROW --}}
                            <tr class="api-detail-row" id="{{ $detailId }}">
                                <td colspan="8" class="api-detail-cell">
                                    <div class="api-lead-panel">

                                        <div class="api-lead-panel-header">
                                            <div>
                                                <p class="api-lead-panel-title">
                                                    Temporary Lead Details
                                                    <small style="font-weight:400;opacity:.65;">
                                                        — {{ $lead->lead_gathering_from === 'google_api' ? 'Google Places' : 'Companies House' }}
                                                    </small>
                                                </p>
                                                <h6>{{ strtoupper($lead->company_name ?? 'N/A') }}</h6>
                                            </div>
                                            <div class="gs-page-topbar-actions">
                                                @if($lead->phone)
                                                    <button class="gs-btn gs-btn--outline" onclick="openDetailOffcanvas({{ Js::from($leadJson) }})">
                                                        <i class="bi bi-telephone-fill"></i> Call
                                                    </button>
                                                @endif
                                                <button class="gs-btn gs-btn--primary"
                                                    onclick="promoteToLead({{ $lead->id }})">
                                                    <i class="bi bi-arrow-up-circle"></i> Promote to Lead
                                                </button>
                                                <button class="gs-btn"
                                                    style="background:#dc354522;color:#dc3545;border:1px solid #dc354544;border-radius:8px;padding:0 12px;"
                                                    onclick="deleteTempLead({{ $lead->id }}, '{{ addslashes($lead->company_name) }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="api-lead-panel-body">
                                            <div class="vl-info-grid">

                                                {{-- Left col --}}
                                                <div class="vl-info-col">
                                                    <p class="vl-section-title">CONTACT DETAILS</p>

                                                    <div class="vl-field-row">
                                                        <span class="vl-field-label"><i class="bi bi-telephone vl-field-icon"></i> Phone</span>
                                                        <span class="vl-field-value">
                                                            @if($lead->phone)
                                                                <a href="javascript:void(0);" style="color:var(--primary-color,#0d6efd);" onclick="callPhone('{{ $lead->phone }}')">{{ $lead->phone }}</a>
                                                            @else
                                                                <span class="vl-field-value--empty">--</span>
                                                            @endif
                                                        </span>
                                                    </div>

                                                    <div class="vl-field-row">
                                                        <span class="vl-field-label"><i class="bi bi-geo-alt vl-field-icon"></i> Address</span>
                                                        <span class="vl-field-value">{{ $lead->address ?? '--' }}</span>
                                                    </div>

                                                    <div class="vl-field-row">
                                                        <span class="vl-field-label"><i class="bi bi-building vl-field-icon"></i> City</span>
                                                        <span class="vl-field-value">{{ $lead->city ?? '--' }}</span>
                                                    </div>

                                                    <div class="vl-field-row">
                                                        <span class="vl-field-label"><i class="bi bi-mailbox vl-field-icon"></i> Postcode</span>
                                                        <span class="vl-field-value">{{ $lead->postcode ?? '--' }}</span>
                                                    </div>

                                                    @if($website && $website !== 'N/A')
                                                        <div class="vl-field-row">
                                                            <span class="vl-field-label"><i class="bi bi-globe2 vl-field-icon"></i> Website</span>
                                                            <span class="vl-field-value">
                                                                <a href="{{ $website }}" target="_blank" style="color:var(--primary-color,#0d6efd);">
                                                                    {{ Str::limit(str_replace(['http://','https://'], '', $website), 40) }}
                                                                </a>
                                                            </span>
                                                        </div>
                                                    @endif

                                                    @if($rating && $rating !== 'N/A')
                                                        <div class="vl-field-row">
                                                            <span class="vl-field-label"><i class="bi bi-star-fill vl-field-icon" style="color:#f4a821;"></i> Rating</span>
                                                            <span class="vl-field-value">
                                                                @for($s = 1; $s <= 5; $s++)
                                                                    <i class="bi bi-star{{ $s <= round($rating) ? '-fill' : '' }}" style="color:#f4a821;font-size:.75rem;"></i>
                                                                @endfor
                                                                <small style="margin-left:4px;color:var(--text-muted,#888);">({{ $othersData['total_reviews'] ?? 0 }})</small>
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Right col — opening hours --}}
                                                <div class="vl-info-col">
                                                    <p class="vl-section-title">
                                                        OPENING HOURS
                                                        @if($openNow === true)
                                                            <span class="gs-status gs-status--active" style="font-size:.72rem;padding:2px 8px;margin-left:6px;">Open Now</span>
                                                        @elseif($openNow === false)
                                                            <span class="gs-status gs-status--lost" style="font-size:.72rem;padding:2px 8px;margin-left:6px;">Closed Now</span>
                                                        @endif
                                                    </p>
                                                    @if(count($hours))
                                                        <ul style="margin:8px 0 0;padding-left:16px;font-size:.82rem;line-height:2;">
                                                            @foreach($hours as $h)
                                                                <li>{{ $h }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span style="color:var(--text-muted,#aaa);font-size:.85rem;">Not available</span>
                                                    @endif
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif