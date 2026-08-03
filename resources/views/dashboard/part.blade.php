{{-- STAT CARDS --}}
<div class="gs-stat-row gs-stat-row--6" id="dashboard-stats">

	@can('view leads')
		<div class="gs-stat-card gs-stat-card--blue">
			<div class="gs-stat-top">
				<div class="dash-inner-data">
					<p class="gs-stat-label">Total Leads</p>
					<p class="gs-stat-value">{{ number_format($todayLeadsCount) }}</p>
				</div>
				<div class="gs-stat-icon gs-stat-icon--blue">
					<i class="bi bi-people-fill"></i>
				</div>
			</div>
		</div>
	@endcan

	@can('view deals')
		<div class="gs-stat-card gs-stat-card--teal">
			<div class="gs-stat-top">
				<div class="dash-inner-data">
					<p class="gs-stat-label">Active Deals</p>
					<p class="gs-stat-value">{{ number_format($activeLeadsCount) }}</p>
				</div>
				<div class="gs-stat-icon gs-stat-icon--teal">
					<i class="bi bi-briefcase-fill"></i>
				</div>
			</div>
		</div>
	@endcan

	@can('view deals')
		<div class="gs-stat-card gs-stat-card--orange">
			<div class="gs-stat-top">
				<div class="dash-inner-data">
					<p class="gs-stat-label">Deal Value (£)</p>
					<p class="gs-stat-value">{{ number_format($todayBudgetTotal) }}</p>
				</div>
				<div class="gs-stat-icon gs-stat-icon--orange">
					<i class="bi bi-currency-rupee"></i>
				</div>
			</div>
		</div>
	@endcan

	@can('view leads')
		<div class="gs-stat-card gs-stat-card--green">
			<div class="gs-stat-top">
				<div class="dash-inner-data">
					<p class="gs-stat-label">Supplier Engagement</p>
					<p class="gs-stat-value">0</p>
				</div>
				<div class="gs-stat-icon gs-stat-icon--green">
					<i class="bi bi-building-fill"></i>
				</div>
			</div>
		</div>
	@endcan

	@can('view deals')
		<div class="gs-stat-card gs-stat-card--blue">
			<div class="gs-stat-top">
				<div class="dash-inner-data">
					<p class="gs-stat-label">Pending RFQ's</p>
					<p class="gs-stat-value">0</p>
				</div>
				<div class="gs-stat-icon gs-stat-icon--blue">
					<i class="bi bi-hourglass-split"></i>
				</div>
			</div>
		</div>
	@endcan

	@can('view deals')
		<div class="gs-stat-card gs-stat-card--teal">
			<div class="gs-stat-top">
				<div class="dash-inner-data">
					<p class="gs-stat-label">Deals Closed This Month</p>
					<p class="gs-stat-value">
						{{ number_format($closedLeadsCount) }}
					</p>
				</div>
				<div class="gs-stat-icon gs-stat-icon--teal">
					<i class="bi bi-trophy-fill"></i>
				</div>
			</div>
		</div>
	@endcan

</div>{{-- /gs-stat-row --}}





{{-- CHARTS + ACTIVITY TIMELINE --}}
<div class="gs-dash-bottom-row">

	<div class="gs-charts-col">
	
		{{-- SALES PIPELINE OVERVIEW --}}
			@can('view leads')
			<div class="gs-pipeline-wrap" id="sales-pipeline">
				<div class="gs-pipeline-card">
					<div class="gs-chart-header">
						<h5><i class="bi bi-kanban-fill"></i> Sales Pipeline Overview</h5>
					</div>
					<div class="gs-pipeline-stages">
						<div class="gs-pipeline-stage gs-pipeline-stage--blue">
							<span class="gs-pipeline-stage-label">Commission Amount</span>
							<span class="gs-pipeline-stage-count">£ 145,000</span>
							<span class="gs-pipeline-stage-value">10 leads</span>
						</div>
						<div class="gs-pipeline-stage gs-pipeline-stage--blue">
							<span class="gs-pipeline-stage-label">New Lead</span>
							<span class="gs-pipeline-stage-count">{{ number_format($NewLeadsCount) }}</span>
							<span class="gs-pipeline-stage-value">£ {{ number_format($todayBudgetTotal) }}</span>
						</div>
						<div class="gs-pipeline-stage gs-pipeline-stage--teal">
							<span class="gs-pipeline-stage-label">Requirement Analysis</span>
							<span class="gs-pipeline-stage-count">0</span>
							<span class="gs-pipeline-stage-value">£ {{ number_format($todayBudgetTotal) }}</span>
						</div>
						<div class="gs-pipeline-stage gs-pipeline-stage--orange">
							<span class="gs-pipeline-stage-label">Supplier Selected</span>
							<span class="gs-pipeline-stage-count">0</span>
							<span class="gs-pipeline-stage-value">£ {{ number_format($todayBudgetTotal) }}</span>
						</div>
						<div class="gs-pipeline-stage gs-pipeline-stage--yellow">
							<span class="gs-pipeline-stage-label">Negotiation</span>
							<span class="gs-pipeline-stage-count">0</span>
							<span class="gs-pipeline-stage-value">£ {{ number_format($todayBudgetTotal) }}</span>
						</div>
						<div class="gs-pipeline-stage gs-pipeline-stage--purple">
							<span class="gs-pipeline-stage-label">Final Deal</span>
							<span class="gs-pipeline-stage-count">{{ number_format($todayClosedWonLeadsCount) }}</span>
							<span class="gs-pipeline-stage-value">£ {{ number_format($todayBudgetTotal) }}</span>
						</div>
					</div>
				</div>
			</div>
			@endcan

		{{-- CHARTS --}}
		<div class="gs-charts-row">

			@can('view leads')
				<div class="gs-chart-card">
					<div class="gs-chart-header">
						<h5><i class="bi bi-bar-chart-fill"></i> Daily Leads</h5>
						<span>{{ \Carbon\Carbon::now()->startOfMonth()->format('d') }} – {{ \Carbon\Carbon::now()->endOfMonth()->format('d M Y') }}</span>
					</div>
					<div class="gs-chart-body">
						<div style="position:relative; height:240px;">
							<canvas id="chartLeads"></canvas>
						</div>
					</div>
				</div>
			@endcan

			@can('view deals')
				<div class="gs-chart-card">
					<div class="gs-chart-header">
						<h5><i class="bi bi-pie-chart-fill"></i> Deal Status</h5>
						<span>All time</span>
					</div>
					<div class="gs-chart-body">
						<div class="gs-doughnut-wrap">
							<div style="position:relative; height:200px; width:200px;">
								<canvas id="chartDeals"></canvas>
							</div>
							<div class="gs-doughnut-legend" id="doughnut-legend"></div>
						</div>
					</div>
				</div>
			@endcan

		</div>{{-- /gs-charts-row --}}

	</div>{{-- /gs-charts-col --}}

	<x-activity-timeline :limit="50" /> {{-- timeline component with a limit of 50 activities --}}

</div>{{-- /gs-dash-bottom-row --}}
