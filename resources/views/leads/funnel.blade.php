<div class="gs-funnel">
    <div class="gs-page-topbar">
        <div class="gs-page-topbar-left">
            <h2>Lead Funnel</h2>
            <p>Manage your leads by drag drop to their desired position</p>
        </div>
        <div class="gs-page-topbar-right gs-lead-action">
    
            <a href="{{ route('leads.index') }}" class="gs-btn gs-btn--primary">
                <i class="bi bi-arrow-left"></i> Go Back
            </a>
        </div>
  </div>
	<div class="gs-filter-bar gs-amount-bar">
		<div class="gs-amount-wrapper">
			<div class="gs-amount-card">
				<p class="gs-amount-label">Total Deal Amount</p>
				<h4 class="gs-total-amount my-2"> {{ currency_symbol() }} {{ number_format($totalBudget, 2) }}</h4>
				<p class="gs-avg">Average per deal</p>
				<p class="gs-per-amount">{{ currency_symbol() }} {{ number_format($averageBudget, 2) }}</p>
			</div>
			<div class="gs-amount-card">
				<p class="gs-amount-label">Total Deal Amount</p>
				<h4 class="gs-total-amount my-2">€851.66K</h4>
				<p class="gs-avg">Average per deal</p>
				<p class="gs-per-amount">€25.06K</p>
			</div>
			<div class="gs-amount-card">
				<p class="gs-amount-label">Total Deal Amount</p>
				<h4 class="gs-total-amount my-2">€851.66K</h4>
				<p class="gs-avg">Average per deal</p>
				<p class="gs-per-amount">€25.06K</p>
			</div>
			<div class="gs-amount-card">
				<p class="gs-amount-label">Total Deal Amount</p>
				<h4 class="gs-total-amount my-2">€851.66K</h4>
				<p class="gs-avg">Average per deal</p>
				<p class="gs-per-amount">€25.06K</p>
			</div>
			<div class="gs-amount-card">
				<p class="gs-amount-label ">Total Deal Amount</p>
				<h4 class="gs-total-amount my-2">€851.66K</h4>
			</div>
			<div class="gs-amount-card">
				<p class="gs-amount-label">AVERAGE DEAL AGE</p>
				<h4 class="gs-total-amount my-2">4.6 months</h4>
			</div>
		</div>
	</div>
    <div class="py-6 overflow-x-auto">
        <div wire:sortable="updateTaskOrder" 
            wire:sortable-group="updateTaskOrder" 
            class="gs-funnel-dragger d-grid flex-nowrap gap-2 pb-4 gse-funnel" 
            style="min-width: min-content;">
            
            @foreach ($stages as $stage)
                <div wire:key="stage-{{ $stage['id'] }}" class="flex-shrink-0 gs-funnel-stage">
                    
                    <div class="gs-funnel-header"  style=" background:{{ $stage['color'] }}1a; color: {{ $stage['color'] }}; border: 1px solid {{ $stage['color'] }}40;">
                        <div class="gs-funnel-head">
							<span class="font-bold text-blue-900 text-sm uppercase">{{ $stage['title'] }}</span>
							<span class="small" style="color: #fff;background:{{ $stage['color'] }};">
								{{ $stage['count'] }}
							</span>
						</div>
						<i class="bi bi-chevron-left"></i>
                    </div>
					

                    <div wire:sortable-group.item-group="{{ $stage['id'] }}" 
                        class="gs-stage-block">
                        
                        @forelse ($stage['tasks'] as $task)
                        <div wire:key="task-{{ $task['id'] }}" 
                            wire:sortable-group.item="{{ $task['id'] }}" 
                            class="gs-fstage-card cursor-grab active:cursor-grabbing">
                            
                            {{-- 1. Company Name --}}
                            <p class="gs-company-name">
                                {{ $task['company_name'] }}
                            </p>

                            {{-- 2. Contact (Phone or Email) --}}
                            <div class="gs-stage-phone">
                                @if(!empty($task['phone']))
                                    <i class="bi bi-telephone"></i>
                                    <span>{{ $task['phone'] }}</span>
                                @else
                                    <i class="bi bi-envelope"></i>
                                    <span>{{ $task['email'] }}</span>
                                @endif
                            </div>

                            {{-- 3. Budget and Stage Badge --}}
                            <div class="gs-stage-badge">
                                <span class="font-bold text-gray-900">
                                    @if(!empty($task['budget_range']))
                                        £ {{ formatBudgetRange($task['budget_range']) }}
                                    @else
                                        <span class="text-gray-400 fw-normal">N/A</span>
                                    @endif
                                </span>

                                {{-- Status Badge --}}
                                <span class="px-2 py-1 rounded-pill bg-gray-100 text-gray-600 font-medium" style="font-size: 0.65rem; background:{{ $stage['color'] }}1a; color: {{ $stage['color'] }}; font-weight: 500;border: 1px solid {{ $stage['color'] }}40;">
                                    {{ $stage['title'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                        
                    @endforelse

                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>