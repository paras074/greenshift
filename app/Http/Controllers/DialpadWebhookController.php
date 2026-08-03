<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DialpadWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();

        $formattedPayload = print_r($payload, true);

        // Log::build([
        //     'driver' => 'single',
        //     'path' => storage_path('logs/dialpad.log'),
        // ])->info("\n==================================================\nProcessing Webhook State \n==================================================\n{$formattedPayload}\n");

        $callId = $payload['call_id'] ?? null;
        $state = $payload['state'] ?? '';
        
        if (!$callId) {
            return response()->json(['status' => 'missing_call_id'], 400);
        }

        // 1. Parse the custom data packet out of Dialpad's nested array structure
        $leadId = null;
        $userId = null;
        $is_temporary = false;
        $customDataRaw = $payload['custom_data'] ?? null;

        if ($customDataRaw) {
            if (is_array($customDataRaw)) {
                $jsonString = $customDataRaw['open_cti'] ?? null;
            } else {
                $jsonString = $customDataRaw;
            }

            if ($jsonString) {
                $decodedCustom = json_decode($jsonString, true);
                if (is_array($decodedCustom)) {
                    $leadId = $decodedCustom['lead_id'] ?? null;
                    $userId = $decodedCustom['user_id'] ?? null;
                    $is_temporary = $decodedCustom['is_temporary'] ?? false;
                }
            }
        }

        // 2. ENFORCE SECURITY RULE: Drop webhooks missing either lead_id OR user_id
        if (empty($leadId) || empty($userId)) {
            return response()->json(['status' => 'ignored_missing_relations'], 200);
        }

        if($is_temporary) {
            $tableName = 'temporary_call_logs';
        } else {
            $tableName = 'call_logs';
        }

        // Keep explicit raw logs tracking inside storage/logs/dialpad.log for debugging
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/dialpad.log'),
        ])->info("Processing Webhook State [{$state}] for Call ID: {$callId}", [
            'payload' => $payload
        ]);

        // Convert Dialpad's millisecond epoch timestamps to MySQL structures
        $dateStarted = isset($payload['date_started']) ? date('Y-m-d H:i:s', $payload['date_started'] / 1000) : null;
        $dateEnded = isset($payload['date_ended']) ? date('Y-m-d H:i:s', $payload['date_ended'] / 1000) : null;
        $durationMs = $payload['duration'] ?? 0;

        // ── PHASE A: DATABASE INITIALIZATION & UPDATES ──
        if (in_array($state, ['offhook', 'ringing', 'connected', 'calling', 'call_transcription', 'admin_recording', 'recap_summary'])) { 
            DB::table($tableName)->updateOrInsert(
                ['dialpad_call_id' => (string)$callId], 
                array_filter([
                    'lead_id'         => (int)$leadId,
                    'user_id'         => (int)$userId,
                    'contact_number'  => $payload['external_number'] ?? null,
                    'direction'       => $payload['direction'] ?? null,
                    'state'           => $state,
                    'agent_name'      => $payload['target']['name'] ?? null,
                    'agent_email'     => $payload['target']['email'] ?? null,
                    'call_started_at' => $dateStarted,
                    'others'          => json_encode($payload),
                    'updated_at'      => now(),
                ]) + ['created_at' => now()]
            );
        }

        if ($state === 'hangup') {
            DB::table($tableName)->updateOrInsert(
                ['dialpad_call_id' => (string)$callId],
                array_filter([
                    'state'         => 'hangup',
                    'call_ended_at' => $dateEnded ?: now(),
                    'duration'      => $durationMs ?: null,
                    'others'        => json_encode($payload),
                    'updated_at'    => now(),
                ])
            );
        }

        if (in_array($state, ['recap_summary'])) {
            $recordingId = null;
            $recordingType = 'admincallrecording'; // FIX: Provided a safe default fallback value

            if (!empty($payload['recording_details'])) {
                $recordingId = $payload['recording_details'][0]['id'] ?? null;
                $recordingType = $payload['recording_details'][0]['recording_type'] ?? 'admincallrecording'; // FIX: Explicitly extracted out of nested metadata array
            }

            if ($recordingId) {
                try {
                    $apiKey = env('DIALPAD_SECRET_API_KEY');

                    if (empty($apiKey)) {
                        Log::build(['driver' => 'single', 'path' => storage_path('logs/dialpad.log')])
                            ->error("Download aborted: Missing dialpad_secret_api_key in settings or .env");
                        return response()->json(['status' => 'missing_api_key'], 500);
                    }

                    $shareResponse = Http::withToken($apiKey)
                        ->withHeaders(['accept' => 'application/json', 'content-type' => 'application/json'])
                        ->post('https://dialpad.com/api/v2/recordingsharelink', [
                            'recording_id'   => (string)$recordingId,
                            'recording_type' => $recordingType,
                            'privacy'        => 'public'
                        ]);

                    if (!$shareResponse->successful()) {
                        Log::build(['driver' => 'single', 'path' => storage_path('logs/dialpad.log')])
                            ->warning("Dialpad rejected share link creation for call {$callId}. Status: " . $shareResponse->status());
                        return response()->json(['status' => 'share_link_failed'], 200);
                    }

                    $responseData = $shareResponse->json();
                    $publicAccessLink = $responseData['access_link'] ?? null;

                    if (!$publicAccessLink) {
                        Log::build(['driver' => 'single', 'path' => storage_path('logs/dialpad.log')])
                            ->warning("No access_link key located within Dialpad array response context for call {$callId}");
                        return response()->json(['status' => 'empty_access_link'], 200);
                    }

                    $directory = 'recordings';
                    $timestamp = date('Ymd_His');
                    $randomString = Str::random(12);
                    $filename = "{$state}_{$callId}_{$timestamp}_{$randomString}.mp3";
                    
                    Storage::disk('public')->makeDirectory($directory);
                    $absolutePath = storage_path('app/public/' . $directory . '/' . $filename);

                    $contextOptions = [
                        'http' => [
                            'method' => 'GET',
                            'timeout' => 120,
                            'follow_location' => 1,
                            'max_redirects' => 5,
                            'header' => "User-Agent: Laravel-HTTP-Client\r\n" 
                        ]
                    ];
                    $context = stream_context_create($contextOptions);
                    $downloadSuccess = copy($publicAccessLink, $absolutePath, $context);

                    if ($downloadSuccess && file_exists($absolutePath)) {
                        
                        // FIX: Format relative browser folder route pattern explicitly matching your request:
                        // Saves exactly as: storage/recordings/admin_recording_5337519...mp3
                        $relativeDbPath = 'storage/' . $directory . '/' . $filename;

                        DB::table($tableName)->updateOrInsert(
                            ['dialpad_call_id' => (string)$callId],
                            [
                                'local_recording_path' => $relativeDbPath,
                                'others'               => json_encode($payload),
                                'updated_at'           => now(),
                            ]
                        );
                        
                        Log::build(['driver' => 'single', 'path' => storage_path('logs/dialpad.log')])
                            ->info("Audio asset successfully downloaded and recorded into database path tracking framework for call: {$callId}");
                    }

                } catch (\Exception $e) {
                    Log::build(['driver' => 'single', 'path' => storage_path('logs/dialpad.log')])
                        ->error("Exception caught downloading audio asset for call {$callId}: " . $e->getMessage());
                }
            } else {
                Log::build(['driver' => 'single', 'path' => storage_path('logs/dialpad.log')])
                    ->warning("No processing attempted: recording_details empty or missing id for call {$callId}.");
            }
        }

        return response()->json(['status' => 'processed'], 200);
    }
}