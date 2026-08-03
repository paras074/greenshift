<main class="act-main">
    <div class="gs-panel">

      {{-- Table Header --}}
      <div class="act-table-header gs-panel-header">
        <div class="act-header-left">
          <span class="gs-panel-title"><i class="bi bi-clock-history" style="color:var(--primary-color);margin-right:8px;font-size:15px;"></i>All Activities</span>
         
        </div>
        <div class="act-header-right">
            <span class="act-result-count" id="resultCount">
              Showing <strong id="visibleCount">{{ $activities->count() }}</strong> of <strong>{{ number_format($stats['total']) }}</strong> activities
            </span>
        </div>
      </div>

      {{-- Timeline List --}}
      <div class="act-timeline-wrap" id="activityList">
          @forelse($groupedActivities as $date => $items)
              @php
                  $carbonDate = \Carbon\Carbon::parse($date);

                  if ($carbonDate->isToday()) {
                      $label = 'Today — ' . $carbonDate->format('M d, Y');
                  } elseif ($carbonDate->isYesterday()) {
                      $label = 'Yesterday — ' . $carbonDate->format('M d, Y');
                  } else {
                      $label = $carbonDate->format('M d, Y');
                  }
              @endphp

              {{-- DAY GROUP --}}
              <div class="act-day-group">
                  <div class="act-day-label">
                      <span class="act-day-text">{{ $label }}</span>
                      <span class="act-day-count">{{ $items->count() }} activities</span>
                  </div>

                  {{-- ACTIVITIES --}}
                  @foreach($items as $activity)

                      @php
                          $type = $activity->other;

                          $map = [
                            // Lead Events
                            'lead_created'         => ['class' => 'green',  'icon' => 'bi-plus-circle-fill'],
                            'lead_updated'         => ['class' => 'blue',   'icon' => 'bi-pencil-fill'],
                            'lead_deleted'         => ['class' => 'red',    'icon' => 'bi-trash-fill'],
                            'lead_status_updated'  => ['class' => 'purple', 'icon' => 'bi-arrow-left-right'],

                            // Attachment Events
                            'attachment_added'     => ['class' => 'green', 'icon' => 'bi-paperclip'],
                            'attachment_deleted'   => ['class' => 'red',   'icon' => 'bi-file-earmark-x-fill'],

                            // Member Events
                            'member_assigned'      => ['class' => 'orange', 'icon' => 'bi-person-check-fill'],
                            'member_unassigned'    => ['class' => 'red',    'icon' => 'bi-person-dash-fill'],

                            // Task Events
                            'task_created'         => ['class' => 'teal',   'icon' => 'bi-check2-square'],
                            'task_updated'         => ['class' => 'blue',   'icon' => 'bi-card-list'],
                            'task_deleted'         => ['class' => 'red',    'icon' => 'bi-calendar-x-fill'],
                            'task_status_updated'  => ['class' => 'purple', 'icon' => 'bi-arrow-repeat'],

                            // Others from your list
                            'status_changed'       => ['class' => 'purple', 'icon' => 'bi-arrow-repeat'],
                            'deal_created'         => ['class' => 'teal',   'icon' => 'bi-briefcase-fill'],
                            'note_added'           => ['class' => 'orange', 'icon' => 'bi-chat-text-fill'],
                            'email_sent'           => ['class' => 'blue',   'icon' => 'bi-envelope-fill'],
                            'call_logged'          => ['class' => 'teal',   'icon' => 'bi-telephone-fill'],
                        ];

                          $ui = $map[$type] ?? ['class' => 'blue', 'icon' => 'bi-info-circle'];

                          $time = $activity->created_at->format('h:i A');

                          $companyid = "<span class='act-highlight act-highlight--lead'>#" . ($activity->lead->id ?? '-') . "</span>";

                          $actionUrl = null;
                          if ($activity->task_id && $activity->task) {
                              $actionUrl = route('tasks.show', $activity->task_id);
                          } elseif ($activity->lead_id && $activity->lead) {
                              $actionUrl = route('leads.show', $activity->lead_id);
                          }

                      @endphp

                      <div class="act-row" 
                          data-head="{{ str_replace('_',' ',$type) }}" 
                          data-text="{{ strtolower($activity->description ?? '') }}">

                          <div class="act-row-line">
                              <div class="act-row-icon act-row-icon--{{ $ui['class'] }}">
                                  <i class="bi {{ $ui['icon'] }}"></i>
                              </div>

                              @if(!$loop->last)
                                  <div class="act-row-connector"></div>
                              @endif
                          </div>

                          <div class="act-row-content">
                              <div class="act-row-head">
                                  <span class="act-row-title">
                                      {{ ucwords(str_replace('_', ' ', $type)) }} 
                                      @if($actionUrl)
                                          <a href="{{ $actionUrl }}" class="act-link-btn" title="View Details" style="margin-left: 8px; color: inherit; opacity: 0.9;" target="_blank">
                                              <i class="bi bi-box-arrow-up-right act-row-icon--green" style="font-size: 12px;"></i>
                                          </a>
                                      @endif
                                  </span>

                                  <span class="act-row-time">
                                      <i class="bi bi-clock" style="font-size:10px;"></i>
                                      {{ $time }}
                                  </span>
                              </div>

                              <p class="act-row-text">
                                  {!! $activity->formatted_description !!}
                              </p>
                          </div>
                      </div>

                  @endforeach

              </div>

          @empty 
          {{-- No results state --}}
            <div class="act-empty" id="noResults">
              <div class="act-empty-icon"><i class="bi bi-search"></i></div>
              <div class="act-empty-title">No Results Found</div>
              <div class="act-empty-sub">Try adjusting your search or filter criteria.</div>
            </div>
          @endforelse
      </div>

      {{-- ── PAGINATION ── --}}
      <div id="paginationWrap" style="display:flex;align-items:center;justify-content:space-between;padding:14px 24px;border-top:1px solid var(--border-color);flex-wrap:wrap;gap:10px;">
        <span style="font-size:var(--fs-xs);color:var(--text-secondary);font-weight:500;">
          Page <strong id="currentPageLabel">{{ $activities->currentPage() }}</strong> of <strong id="totalPagesLabel">{{ $activities->lastPage() }}</strong>
        </span>
        @php
            $query = request()->query();
            $current = $activities->currentPage();
            $last = $activities->lastPage();

            $start = max($current - 2, 1);
            $end = min($current + 2, $last);
        @endphp

        <div style="display:flex;gap:6px;" id="paginationBtns">

            {{-- Prev --}}
            <button 
                onclick="window.location='{{ $activities->appends($query)->previousPageUrl() }}'"
                style="padding: 5px 11px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: var(--bg-page); color: var(--text-secondary); font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.15s; {{ $current == 1 ? 'opacity:0.4;pointer-events:none;' : '' }}">
                <i class="bi bi-chevron-left"></i>
            </button>

            {{-- First --}}
            @if($start > 1)
                <button onclick="window.location='{{ $activities->appends($query)->url(1) }}'"
                    style="padding:5px 11px;border-radius:var(--radius-sm);border:1px solid var(--border-color);background:var(--bg-page);color:var(--text-secondary);font-size:13px;font-weight:600;cursor:pointer;">
                    1
                </button>
            @endif

            {{-- Dots before --}}
            @if($start > 2)
                <span style="padding:5px 6px;">...</span>
            @endif

            {{-- Middle Pages --}}
            @for($i = $start; $i <= $end; $i++)
                @php $isActive = $i == $current; @endphp

                <button 
                    onclick="window.location='{{ $activities->appends($query)->url($i) }}'"
                    style="
                        padding:5px 11px;
                        border-radius:var(--radius-sm);
                        border:1px solid {{ $isActive ? 'var(--primary-color)' : 'var(--border-color)' }};
                        background:{{ $isActive ? 'var(--primary-color)' : 'var(--bg-page)' }};
                        color:{{ $isActive ? '#fff' : 'var(--text-secondary)' }};
                        font-size:13px;
                        font-weight:600;
                        cursor:pointer;
                        transition:all .15s;
                    ">
                    {{ $i }}
                </button>
            @endfor

            {{-- Dots after --}}
            @if($end < $last - 1)
                <span style="padding:5px 6px;">...</span>
            @endif

            {{-- Last --}}
            @if($end < $last)
                <button onclick="window.location='{{ $activities->appends($query)->url($last) }}'"
                    style="padding:5px 11px;border-radius:var(--radius-sm);border:1px solid var(--border-color);background:var(--bg-page);color:var(--text-secondary);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;">
                    {{ $last }}
                </button>
            @endif

            {{-- Next --}}
            <button 
                onclick="window.location='{{ $activities->appends($query)->nextPageUrl() }}'"
                style="padding:5px 11px;border-radius:var(--radius-sm);border:1px solid var(--border-color);background:var(--bg-page);color:var(--text-secondary);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s; {{ $current == $last ? 'opacity:0.4;pointer-events:none;' : '' }}">
                <i class="bi bi-chevron-right"></i>
            </button>

        </div>
  
      </div>
    </div>
</main>