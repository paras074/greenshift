@if(session('success'))
	<div class="gs-alert gs-alert--success" style="margin-bottom:16px; display:flex;">
		<i class="bi bi-check-circle-fill"></i> {{ session('success') }}
	</div>
@endif

@if(session('error'))
	<div class="gs-alert gs-alert--error" style="margin-bottom:16px; display:flex;">
		<i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
	</div>
@endif

@if(session('info'))
	<div class="gs-alert gs-alert--info" style="margin-bottom:16px; display:flex;">
		<i class="bi bi-info-circle-fill"></i> {{ session('info') }}
	</div>
@endif

@if(session('warning'))
	<div class="gs-alert gs-alert--warning" style="margin-bottom:16px; display:flex;">
		<i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}
	</div>
@endif	

@foreach(get_user_tasks_due_today() as $task)
    <div class="gs-alert gs-alert--warning due-task-badge {{ $loop->last ? 'mb-15' : 'mb-2' }}"">
        
        <div class="gs-alert__main">
            <i class="bi bi-exclamation-triangle-fill gs-alert__icon"></i> 
            <span class="gs-alert__text">
                Your task <strong>"{{ $task->title }}"</strong> is due Today. 
                <a href="{{ route('tasks.index') }}" class="gs-alert__link">
                    View task
                </a>
            </span>
        </div>

        <!-- Cross to Dismiss -->
        <button type="button" 
                class="gs-alert__close" 
                onclick="this.closest('.gs-alert').remove()" 
                aria-label="Close alert">
            <i class="bi bi-x"></i>
        </button>
    </div>
@endforeach