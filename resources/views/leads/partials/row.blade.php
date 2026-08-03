{{-- resources/views/leads/partials/row.blade.php --}}
@foreach($leads as $lead)
<tr>
	<td><input type="checkbox" class="gs-table-checkbox row-check" value="{{ $lead->id }}"/></td>
	<td data-order="{{$lead->id}}"><span class="gs-table-company">#{{$lead->id}}</span></td>
	<td><span class="gs-table-company">{{$lead->company_name ?? '--'}}</span>@if($lead->status == 'draft')<span class="status-draft">draft</span>@endif
	<br><span>{{$lead->phone ?? '--'}}</span></td>
	<td><span class="gs-status" style="background:{{ $lead->leadStatus->color }}1a; color:{{ $lead->leadStatus->color }}; border:1px solid {{ $lead->leadStatus->color }}40;">{{$lead->leadStatus->name ?? '--'}}</span></td>
	<td>{{$lead->annual_consumption ?? '--'}}</td>
	<td>{{ ucfirst($lead->energy_type) ?? '--'}}</td>
	<td><span class="gs-status" style="background:{{ $lead->priorityStatus->color }}1a; color:{{ $lead->priorityStatus->color }}; border:1px solid {{ $lead->priorityStatus->color }}40;">{{$lead->priorityStatus->name  ?? '--'}}</span></td>
    <td class="text-center">
        @if($lead->assignments->count() > 0)
            <button type="button" 
                    class="btn btn-link p-0 text-primary view-assigned-users" 
                    data-lead-id="{{ $lead->id }}" 
                    data-company="{{ $lead->company_name }}"
                    style="font-size: 1.2rem; text-decoration: none;">
                <i class="bi bi-people-fill"></i>
                <span style="font-size: 0.75rem; vertical-align: super;">{{ $lead->assignments->count() }}</span>
            </button>
        @else
            <span class="text-muted">--</span>
        @endif
    </td>
	<td>
		<div style="display:flex; align-items:center; gap:6px">
			@can('view leads')
				<a href="{{ route('leads.show', $lead) }}" class="gs-edit-btn">
					<i class="bi bi-eye-fill"></i>
				</a>
			@endcan
			@can('edit leads')
			<a href="{{ route('leads.edit', $lead->id) }}" class="gs-edit-btn"><i class="bi bi-pencil-fill"></i></a>
			@endcan
			@can('delete leads')
			<a href="javascript:void(0);" class="gs-edit-btn" onclick="DeleteLead('{{$lead->id}}')" style="border-color:rgba(220,38,38,0.3); color:#dc2626;"><i class="bi bi-trash"></i></a>
			<form method="POST" action="{{ route('leads.delete', $lead) }}" id="DeleteLeadForm-{{$lead->id}}" style="display:none;">
				@csrf
				<input type="hidden" name="_action" value="delete">
			</form>
			@endcan
		</div>
	</td>
</tr>
@endforeach