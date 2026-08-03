@foreach($notifications as $notification)

    <a href="{{ $notification->url ? url($notification->url) : 'javascript:void(0)' }}" data-id="{{ $notification->id }}" class="gs-notif-item {{ !$notification->is_read ? 'gs-notif-item--unread' : '' }}">
        
        {{-- Icon based on kind --}}
        <div class="gs-notif-icon-wrap gs-notif-icon-wrap--teal">
            {!! notification_icon($notification->kind) !!}
        </div>

        {{-- Content --}}
        <div class="gs-notif-content">
            <p class="gs-notif-text">
                {{ $notification->message }}
            </p>

            <span class="gs-notif-time">
                <i class="bi bi-clock"></i>
                {{ $notification->created_at->diffForHumans() }}
            </span>
        </div>

        {{-- Unread dot --}}
        @if(!$notification->is_read)
            <span class="gs-notif-unread-dot"></span>
        @endif

    </a>

@endforeach