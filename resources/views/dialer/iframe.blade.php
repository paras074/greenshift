<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dialpad Dialer Panel</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #fff;
        }
        .dialpad-iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
    </style>
</head>
<body>

    @php
        $get_setting = get_setting_data(['dialpad_api_key']);
        $dialpad_id = $get_setting['dialpad_api_key'] ?? '';
        $iframeUrl = "https://dialpad.com/apps/" . $dialpad_id;
    @endphp

    @if(!empty($dialpad_id))
        <iframe 
            src="{{ $iframeUrl }}" 
            title="Dialpad" 
            id="dialpadFrame" 
            class="dialpad-iframe" 
            allow="microphone; speaker-selection; autoplay; camera; display-capture; hid" 
            sandbox="allow-popups allow-scripts allow-same-origin allow-forms">
        </iframe>
    @else
        <div style="padding: 20px; text-align: center; font-family: sans-serif; color: #cc0000;">
            <strong>Configuration Error:</strong> Dialpad API Key is missing.
        </div>
    @endif

</body>
</html>