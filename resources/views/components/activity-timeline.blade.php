{{-- resources/views/components/activity-timeline.blade.php --}}
@props([
    'limit' => null,
    'title' => null,
    'lead_id' => null,
])

@can('view-timeline leads')
    <div class="gs-activity-card">
        <div class="gs-chart-header">
            <h5><i class="bi bi-clock-history"></i> {{ $title ?? 'Activity Timeline' }}</h5>
            @if($limit)
                <a href="{{ route('activities.index') }}" class="gs-btn gs-btn--outline" style="padding:6px 12px;">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            @endif
        </div>
        <div class="gs-activity-body">
            @php
                // We use the $limit prop here
                $activities = getDashboardTimeline($limit, $lead_id);
            @endphp
            
            @foreach($activities as $act)
                <div class="gs-act-item">
                    <div class="gs-act-icon">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div class="gs-act-content">
                        <p class="gs-act-title"><strong>{{ $act['date'] }}</strong> – {{ $act['head'] }}</p>
                        <p class="gs-act-desc">{!! $act['text'] !!}</p>
                    </div>
                </div>
            @endforeach
            @if($limit)
                <div class="gs-act-footer" style="padding: 12px 12px 10px; text-align: center; border-top: 1px solid rgba(0,0,0,0.05);">
                    <a href="{{ route('activities.index') }}" class="gs-btn-link" style="color: #0d9488; font-weight: 600; text-decoration: none; font-size: 14px;">
                        View All Activity <i class="bi bi-arrow-right" style="margin-left: 4px;"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endcan