@extends('layouts.app')
@section('title', isset($task) && $task->exists ? 'Edit Task — ' . $task->title : 'Add New Task')
@section('content')

    {{-- Determine form action & method --}}
    @php
        $isEdit = isset($task) && $task->exists;
        $formAction = $isEdit ? route('tasks.update', $task) : route('tasks.store');
        $tasktypes = taskTypes();
        $salesManagers = GetAllUsersByRoleId(4);
        $salesexecutives = GetAllUsersByRoleId(5);
    @endphp
    <form method="POST" action="{{ $formAction }}" class="lead-form cs-form" id="taskForm">
        @csrf
		{{-- ── Top Bar ─────────────────────────────────────────────── --}}
        <div class="gs-page-topbar">
            <div class="gs-page-topbar-left">
				<div class="page-title-bar">
					<h2>{{ $isEdit ? 'Edit Task' : 'Add New Task' }}</h2>
				</div>
                <p>{{ $isEdit ? 'Update the task information below' : 'Enter basic details to create a new Task' }}</p>
            </div>

            <div class="gs-page-topbar-actions">
					<a type="button" href="{{ route('tasks.index') }}" class="gs-btn gs-btn--outline" id="backbtn">
						 <i class="bi bi-arrow-left"></i> Back
					</a>
                    @if($isEdit)
                        @can('edit tasks')
                             <button type="submit" name="form_status" value="update" class="gs-btn gs-btn--primary">
                                <i class="bi bi-check-lg"></i> Update Task
                            </button>
                        @endcan
                    @else
                        @can('create tasks')
                            <button type="submit" name="form_status" value="active" class="gs-btn gs-btn--primary">
                                <i class="bi bi-check-lg"></i> Save Task
                            </button>
                        @endcan
                    @endif
                    @if($isEdit)
                        @can('delete tasks')
                            <button type="button" class="gs-btn gs-btn--danger" onclick="DeleteTask('{{$task->id}}')"  id="btnDeleteTask">
                                <i class="bi bi-trash-fill"></i> Delete Task
                            </button>
                        @endcan
                    @endif
            </div>
        </div>
		
		{{-- ── Main Grid ───────────────────────────────────────────── --}}
        <div class="gs-common-grid gs-Add-Tasks">

            {{-- ════ LEFT COLUMN ════ --}}
            <div class="gs-left-grid">

                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Task Details</h5>
                    </div>
                    <div class="gs-panel-body">
                        <div class="gs-form-grid"> 

                           {{-- Choose Lead --}}
							<div class="gs-field gs-field--full">
								<label class="gs-label" for="lead_id">Select Lead </label>
                                @if($isEdit && !is_superadmin() && auth()->user() && auth()->user()->can('view-own tasks'))
                                    <div class="gs-input" style="cursor: not-allowed;">
                                        {{ $task->lead->company_name ?? 'No Lead Assigned' }}
                                    </div>
                                    <input type="hidden" name="lead_id" value="{{ $task->lead_id }}" disabled />
                                @else
                                    <select class="gs-select2" name="lead_id" id="lead_id">
                                        <option value="">Select a lead...</option>
                                        @foreach($leads as $lead)
                                            <option value="{{ $lead->id }}" {{ old('lead_id', optional($task)->lead_id ?? request('lead_id')) == $lead->id ? 'selected' : '' }}>
                                                {{ $lead->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            {{-- task title --}}
                            <div class="gs-field gs-field--full">
                                <label class="gs-label" for="title">Title <span class="gs-required">*</span></label>
                                <input type="text" class="gs-input" name="title" required id="title" placeholder="Enter task title" value="{{ old('title', $task->title ?? '') }}" />
                            </div>

                            {{-- Contract End Date --}}
                            <div class="gs-field">
                                <label class="gs-label" for="end_date">Task End Date</label>
                                <input type="date" class="gs-input" name="end_date" id="end_date" value="{{ old('end_date', $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('Y-m-d') : '') }}" />
                            </div>

                            {{-- task Status --}}
                            <div class="gs-field">
                                <label class="gs-label" for="type">Task type <span class="gs-required">*</span></label>
                                <select class="gs-select" name="type" id="type" required>
                                    <option value="" disabled selected>Select Task type...</option>
                                    @foreach($tasktypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $task->type ?? request('type')) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            
                        </div>
                    </div>
                </div>

                {{-- Lead Description Panel --}}
                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Task Description</h5>
                    </div>
                    <div class="gs-panel-body">
                        <div class="gs-field">
                            <textarea class="gs-textarea" id="description" name="description" rows="5">{{ old('description', $task->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                @if($isEdit)
                    <div class="gs-panel">
                        <div class="gs-panel-header">
                            <h5 class="gs-panel-title">Notes</h5>
                        </div>
                        <div class="gs-panel-body">
                            <div class="gs-field">
                                <textarea id="noteData"  class="gs-textarea" style="min-height:100px;" placeholder="Add internal note for your team..."></textarea>
                                <button type="button" onclick="AddTaskNote()" class="gs-btn gs-btn--primary mt-2" style="width:100%;justify-content:center;">
                                <i class="bi bi-plus-lg"></i> Add Note
                                    </button>
                                <div id="notesFeed" class="vl-task-notes mt-1">
                                     @include('tasks.partials.task_per_row', ['notes' => $notes])
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- ════ RIGHT COLUMN ════ --}}
            <div class="gs-right-grid">

                {{-- Lead Source Panel --}}
                <div class="gs-panel">
                    <div class="gs-panel-header">
                        <h5 class="gs-panel-title">Task Source</h5>
                    </div>
                    <div class="gs-panel-body">
                        <div class="gs-form-grid gs-form-grid--full" style="gap:14px;">

                            {{-- Task Status --}}
                            <div class="gs-field">
                                <label class="gs-label" for="status">Task Status</label>
                                <select class="gs-select" name="status" id="status">
                                    @foreach(tasksStatus() as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $task->status ?? '') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- task priority --}}
                            <div class="gs-field">
                                <label class="gs-label" for="priority">Task Priority</label>
                                <select class="gs-select" name="priority" id="priority">
                                    @foreach(taskPriorities() as $key => $label)
                                        <option value="{{ $key }}" {{ old('priority', $task->priority ?? '') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
               
                @can('assign tasks')
                    <div class="gs-panel">
                        <div class="gs-panel-header">
                            <h5 class="gs-panel-title">Assign Team</h5>
                        </div>
                        <div class="gs-panel-body">
                            <div class="gs-form-grid gs-form-grid--full" style="gap:14px;">
                                {{-- Sales Manager --}}
                                <div class="gs-field">
                                    <label class="gs-label" for="assigned_to">Assign Collaborator</label>
                                    <div class="custom-multiselect">
                                        <div class="multiselect-search-container">
                                            <input type="search" class="search-bar" id="sale_managers_search" placeholder="Search Sales Manager...">
                                        </div>

                                        <div class="multiselect-list sales-manager">
                                            @foreach($salesManagers as $manager)
                                                <div class="item-group">
                                                    <label class="checkbox-container">
                                                        <input type="checkbox" class="gs-input" name="assigned_manager[]" value="{{ $manager->id }}" @checked(in_array($manager->id, old('assigned_manager', $assignedUserIds ?? [])))>  <span class="checkmark"></span> {{ $manager->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                            <div class="no-results" style="display: none; padding: 10px; color: #888; text-align: center;">
                                                No users found.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Sales Executives --}}
                                <div class="gs-field">
                                    <label class="gs-label" for="assigned_to">Assign Member</label>
                                    <div class="custom-multiselect">
                                        <div class="multiselect-search-container">
                                            <input type="search" class="search-bar" id="sale_members_search" placeholder="Search Sales Members...">
                                        </div>

                                        <div class="multiselect-list sales-member">
                                            @foreach($salesexecutives as $executive)
                                                <div class="item-group">
                                                    <label class="checkbox-container">
                                                        <input type="checkbox" class="gs-input" name="assigned_executive[]" value="{{ $executive->id }}" @checked(in_array($executive->id, old('assigned_executive', $assignedUserIds ?? [])))> <span class="checkmark"></span>{{ $executive->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                            <div class="no-results" style="display: none; padding: 10px; color: #888; text-align: center;">
                                                No users found.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
        </div>
        
    </form>

    {{-- ── Delete Confirmation Form (separate, hidden) ─────────────── --}}
    @if($isEdit)
        @can('delete tasks')
            <form method="POST" action="{{ route('tasks.delete', $task) }}" id="DeleteTaskForm-{{$task->id}}" style="display:none;">
                @csrf
                <input type="hidden" name="_action" value="delete">
            </form>
        @endcan
        <script>
            const csrfToken = "{{ csrf_token() }}";
            const taskId = {{ $task->id }};
        </script>
    @endif

@endsection

@push('scripts')
	    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // ── CKEditor on description ──────────────────────────────
            if (document.querySelector('#description')) {
                ClassicEditor
                    .create(document.querySelector('#description'), {
                        toolbar: [
                            'heading', '|',
                            'bold', 'italic', '|',
                            'bulletedList', 'numberedList', '|',
                            'link', 'blockQuote', '|',
                            'undo', 'redo'
                        ],
                        placeholder: 'Describe the lead requirements, goals, notes…'
                    })
                    .catch(error => console.error(error));
            }
			
			// ── Select2 on lead_id ──────────────────────────────────
			if (typeof $.fn.select2 !== 'undefined' && document.getElementById('lead_id')) {
				$('#lead_id').select2({
					placeholder: 'Select lead...',
					allowClear: true,
					width: '100%',
				});
			}

            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);

            // Check if either parameter exists
            if (params.has('lead_id') || params.has('type')) {
                params.delete('lead_id');
                params.delete('type');

                // Reconstruct URL with remaining params
                const newSearch = params.toString();
                const newPath = url.pathname + (newSearch ? '?' + newSearch : '');

                window.history.replaceState({}, document.title, newPath);
            }	

            window.AddTaskNote = function() {
                const noteData = document.getElementById('noteData').value.trim();
                if(!noteData){
                    window.miniAlert('Please enter a note before adding.');
                    return;
                }

                if(!taskId){
                    window.miniAlert('Task ID is missing. Please save the task before adding notes.');
                    return;
                }

                const url = "{{ route('tasks.add-note', ':taskId') }}".replace(':taskId', taskId);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ data: noteData })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        document.getElementById('noteData').value = '';
                        const notesFeed = document.getElementById('notesFeed');
                        if(notesFeed) {
                            notesFeed.innerHTML = data.html;
                        }
                    } else {
                        window.miniAlert('Failed to add note. Please try again.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    window.miniAlert('An error occurred while adding the note. Please try again.');
                });
            };		

        });
    </script>

@endpush