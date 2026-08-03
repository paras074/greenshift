{{-- resources/views/tasks/partials/row.blade.php --}}
@foreach($tasks as $task)
<tr>
	<td><input type="checkbox" class="gs-table-checkbox row-check"/></td>
	<td data-order="{{$task->id}}"><span class="gs-table-company">#{{$task->id}}</span></td>
	<td><span class="gs-table-company">{{$task->title ?? '--'}}</span></td>
    <td>
		@php $users = $task->assignedUsers(); @endphp
		@forelse($users as $user)
			<div style="display:flex; align-items:center; gap:10px;">
				<div class="gs-user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
				<span class="gs-table-company">{{ $user->name ?? 'Unassigned' }}</span>
			</div>
			{!! !$loop->last ? '<br>' : '' !!}
		@empty
			<div style="display:flex; align-items:center; gap:10px;">
				<div class="gs-user-avatar">{{ strtoupper(substr('Unassigned', 0, 1)) }}</div>
				<span class="gs-table-company">Unassigned</span>
			</div>
		@endforelse
        
    </td>
	<td><span class="gs-status" style="">{{ tasksStatus()[$task->status] ?? '--' }}</span></td>
	
	<td>{{ $task->end_date?->format('d M Y') ?? '--' }}</td>

	<td>
		<div style="display:flex; align-items:center; gap:6px">
			@can('view tasks')
				{{-- <a href="{{ route('tasks.show', $task) }}" class="gs-edit-btn">
					<i class="bi bi-eye-fill"></i>
				</a> --}}
			@endcan
			@can('edit tasks')
				@if(!is_superadmin() && auth()->user() && auth()->user()->can('view-own tasks'))
					<button type="button" title="Edit {{$task->title ?? 'Task'}}" data-bs-toggle="offcanvas" data-id="{{$task->id}}" data-bs-target="#TaskPanel" ria-controls="TaskPanel" class="gs-edit-btn"><i class="bi bi-pencil-fill"></i></button>
				@else
					<a href="{{ route('tasks.edit', $task->id) }}" class="gs-edit-btn"><i class="bi bi-pencil-fill"></i></a>
				@endif
			@endcan
			@can('delete tasks')
			<a href="javascript:void(0);" class="gs-edit-btn" onclick="DeleteTask('{{$task->id}}')" style="border-color:rgba(220,38,38,0.3); color:#dc2626;"><i class="bi bi-trash"></i></a>
			<form method="POST" action="{{ route('tasks.delete', $task) }}" id="DeleteTaskForm-{{$task->id}}" style="display:none;">
				@csrf
				<input type="hidden" name="_action" value="delete">
			</form>
			@endcan
		</div>
    </td>
</tr>
@endforeach