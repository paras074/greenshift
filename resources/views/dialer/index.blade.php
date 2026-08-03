@extends('layouts.app')
@section('title', 'Dialpad Dialer')
@section('content')

<div class="gs-page-topbar">
    <div class="gs-page-topbar-left">
        <h2>Dialpad Dialer</h2>
        <p>View and manage Dialpad</p>
    </div>

    <div class="gs-page-topbar-actions">
        <a href="{{ route('dashboard') }}" class="gs-btn gs-btn--outline">
            <i class="bi bi-arrow-left"></i> Go Back
        </a>
    </div>
</div>

{{-- Centered Launch Section --}}
<div class="dialpad-launch-container" style="display: flex; justify-content: center; align-items: center; min-height: 400px;">
    <div style="text-align: center;">
        <button id="launchDialerBtn" class="gs-btn gs-btn--primary" style="padding: 15px 30px; font-size: 16px; font-weight: 600; cursor: pointer;">
            <i class="bi bi-telephone-outbound"></i> Launch Dialer
        </button>
        <p style="margin-top: 10px; color: #666; font-size: 14px;">Opens the communication panel in a secure, compact window.</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let dialerWindow = null;

        document.getElementById('launchDialerBtn').addEventListener('click', function () {
            const url = "{{ route('dialpad.iframe') }}"; // Dedicated route for the iframe content
            const windowName = "DialpadDialerWindow";
            
            // Specify exact sleek dimensions for a standalone dialer widget
            const width = 450;
            const height = 650;
            
            // Center the popup window dynamically on the user's screen
            const left = (screen.width / 2) - (width / 2);
            const top = (screen.height / 2) - (height / 2);
            
            const windowFeatures = `width=${width},height=${height},left=${left},top=${top},status=no,menubar=no,toolbar=no,location=no,resizable=yes,scrollbars=yes`;

            // Smart Window Check
            if (dialerWindow === null || dialerWindow.closed) {
                // Window doesn't exist or was closed, open a brand new one
                dialerWindow = window.open(url, windowName, windowFeatures);
            } else {
                // Window is already open, simply bring it to focus/active state
                dialerWindow.focus();
            }
        });
    });
</script>
@endpush