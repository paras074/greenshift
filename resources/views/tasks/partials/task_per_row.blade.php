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