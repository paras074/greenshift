<div class="gs-panel rounded-0">
    <div class="gs-panel-body">
        <div class="gs-form-grid gs-form-grid--full" style="gap:14px;">
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
        </div>
    </div>
    <div class="gs-panel-body pt-1">
        <textarea id="noteData"  class="gs-textarea" style="min-height:72px;" placeholder="Add internal note for your team..."></textarea>
        <button type="button" onclick="AddTaskNote('{{ $task->id }}')" class="gs-btn gs-btn--primary mt-2" style="width:100%;justify-content:center;">
           <i class="bi bi-plus-lg"></i> Add Note
        </button>
        <div id="notesFeed" class="vl-task-notes mt-3">
            @forelse($notes as $note)
                <div class="d-flex mb-0 border-top pt-3">
                    <div class="gs-user-avatar me-3" width="40" height="40">{{ strtoupper(substr($note->user->name ?? 'S', 0, 1)) }}</div>
                    
                    <div class="w-100">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold small">{{ $note->user->name ?? 'System' }}</span>
                            <span class="text-muted small" style="font-size: 0.75rem;">{{ $note->created_at->format('d M Y') }}</span>
                        </div>
                        <p class="text-secondary small mb-0 mt-1">
                            {!! nl2br(e($note->data)) !!}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-muted small text-center mt-3">No notes available for this task.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="gs-panel-footer rounded-0">
    <div class="status-message-area mt-2" style="min-height: 20px;"></div>
	<div class="gs-page-topbar-actions">
		<button type="submit" name="status" value="draft" class="gs-btn gs-btn--outline" data-bs-dismiss="offcanvas" aria-label="Close">
			<i class="bi bi-x-circle"></i> Cancel
		</button>
		<button type="submit" name="status" value="update_status" onclick="SaveTaskSt('{{ $task->id }}')" class="gs-btn gs-btn--primary">
			<i class="bi bi-floppy-fill"></i> Update Status
		</button>
	</div>
</div>