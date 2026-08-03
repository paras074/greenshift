{{-- resources/views/reports/partials/main.blade.php --}}

<div class="rpt-wrap">

    
    <div class="rpt-stat-row">

        {{-- Total Leads --}}
        <div class="rpt-stat-card">
            <div class="rpt-stat-top">
                <div class="rpt-stat-icon rpt-stat-icon--blue">
                    <i class="bi bi-table"></i>
                </div>
                <div class="rpt-stat-body">
                    <div class="rpt-stat-value">{{ $totalCount }}</div>
                    <div class="rpt-stat-label">Total Leads</div>
                </div>
            </div>
            <div class="rpt-stat-foot">
                <span class="rpt-foot-num rpt-foot-num--blue">{{ $totalCount }}</span>
                <span class="rpt-foot-text">Total leads</span>
                {{-- Percentage omitted for first card as requested --}}
            </div>
        </div>

        {{-- Qualified (status_id = 5) --}}
        <div class="rpt-stat-card">
            <div class="rpt-stat-top">
                <div class="rpt-stat-icon rpt-stat-icon--purple">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="rpt-stat-body">
                    <div class="rpt-stat-value">{{ $qualifiedCount }}</div>
                    <div class="rpt-stat-label">Qualified</div>
                </div>
            </div>
            <div class="rpt-stat-foot">
                <span class="rpt-foot-num rpt-foot-num--purple">{{ $qualifiedCount }}</span>
                <span class="rpt-foot-text">of total leads</span>
                <span class="rpt-foot-pct">{{ $qualifiedPercentage }}%</span>
            </div>
        </div>

        {{-- Calls (Kept Static) --}}
        <div class="rpt-stat-card">
            <div class="rpt-stat-top">
                <div class="rpt-stat-icon rpt-stat-icon--teal">
                    <i class="bi bi-telephone-fill"></i>
                </div>
                <div class="rpt-stat-body">
                    <div class="rpt-stat-value">{{ $callsCount }}</div>
                    <div class="rpt-stat-label">Calls</div>
                </div>
            </div>
            <div class="rpt-stat-foot">
                <span class="rpt-foot-num rpt-foot-num--teal">{{ $callsCount }}</span>
                <span class="rpt-foot-text">Total Calls made</span>
                <!--<span class="rpt-foot-pct">10%</span>-->
            </div>
        </div>

        {{-- Lost (status_id = 6) --}}
        <div class="rpt-stat-card">
            <div class="rpt-stat-top">
                <div class="rpt-stat-icon rpt-stat-icon--red">
                    <i class="bi bi-graph-down-arrow"></i>
                </div>
                <div class="rpt-stat-body">
                    <div class="rpt-stat-value">{{ $lostCount }}</div>
                    <div class="rpt-stat-label">Lost</div>
                </div>
            </div>
            <div class="rpt-stat-foot">
                <span class="rpt-foot-num rpt-foot-num--red">{{ $lostCount }}</span>
                <span class="rpt-foot-text">of total leads</span>
                <span class="rpt-foot-pct">{{ $lostPercentage }}%</span>
            </div>
        </div>

    </div>{{-- /rpt-stat-row --}}



    {{-- ═══════════════════════════════════
         CHARTS ROW
    ════════════════════════════════════ --}}
    <div class="rpt-charts-row">
        <div class="gs-chart-card">
            <div class="gs-chart-header">
                <h5>Leads Breakdown</h5>
            </div>
            <div class="gs-chart-body">
                <div class="rpt-pie-inner">
                    <div class="rpt-pie-canvas-wrap">
                        <canvas id="rptPieChart"></canvas>
                    </div>
                    <div class="rpt-pie-legend">
                        @foreach($statusesBreakdown as $status)
                            <div class="rpt-legend-item">
                                <span class="rpt-legend-dot" style="background: {{ $status['color'] }};"></span>
                                <span class="rpt-legend-name">{{ $status['name'] }}</span>
                                <span class="rpt-legend-count">{{ $status['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="gs-chart-card">
            <div class="gs-chart-header d-flex align-items-center justify-content-between">
                <h5>Lead &amp; Conversion trends</h5>
                <div class="year-yd">
                    {{ \Carbon\Carbon::now()->subMonths(5)->format('M Y') }} - {{ \Carbon\Carbon::now()->format('F Y') }}
                </div>
            </div>
            
            <div class="gs-chart-body">
                <div class="rpt-line-chart-wrap">
                    <canvas id="rptLineChart"></canvas>
                </div>
                <div class="rpt-line-legend">
                    <div class="rpt-line-legend-item">
                        <span class="rpt-line-legend-pill" style="background:#4A7FD6;"></span>
                        Leads
                    </div>
                    <div class="rpt-line-legend-item">
                        <span class="rpt-line-legend-pill" style="background:#F59E0B;"></span>
                        Conversion rate
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /rpt-charts-row --}}

</div>{{-- /rpt-wrap --}}
