<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Lead; // Make sure your Lead model path matches your app structure

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class SignableWebhookController extends Controller
{
    /**
     * Get the dedicated logging channel instance.
     * Drops logs explicitly into storage/logs/signable_logs.log
     */
    protected function logger()
    {
        return Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/signable_logs.log'),
            'level' => 'debug',
        ]);
    }

    /**
     * Handle incoming Signable webhooks
     */
    public function handle(Request $request)
    {
        $event = $request->input('event') ?? $request->input('action');
        $envelopeId = $request->input('envelope_id') ?? $request->input('envelope_fingerprint');

        $this->logger()->info("Signable Webhook Captured - Event: {$event} | Envelope: {$envelopeId}");
        
        $this->logger()->debug("Raw Payload Context:", $request->all());

        $metaRaw = $request->input('envelope_meta');
        $meta = !empty($metaRaw) ? json_decode($metaRaw, true) : null;
        
        $leadId = $meta['lead_id'] ?? null;
        $context = $meta['context'] ?? null;

        if ($leadId && $context === 'loa_signature_request') {
            switch ($event) {
                case 'envelope_signed':
                case 'signed-envelope':
                case 'signed-envelope-complete':
                    $this->processSignedDocument($leadId, $request->all());
                    break;

                case 'envelope_rejected':
                case 'rejected-envelope':
                    $this->processRejectedDocument($leadId, $request->all());
                    break;

                default:
                    $this->logger()->debug("Signable event explicitly skipped for Lead #{$leadId}: {$event}");
                    break;
            }
        } else {
            $this->logger()->warning("Signable webhook ignored: Missing valid lead_id or context metadata.", [
                'meta' => $meta
            ]);
        }

        // Signable demands an explicit HTTP 200 to confirm receipt
        return response()->json(['status' => 'acknowledged'], 200);
    }

    /**
     * Process logic when the document signing loop finishes successfully
     */
    protected function processSignedDocument($leadId, array $payload)
    {
        $this->logger()->info("Processing signed logic for Lead ID: {$leadId}");

        $lead = Lead::find($leadId);

        if ($lead) {
            $others = $lead->others ?? [];
            
            $documentsRaw = $payload['envelope_documents'] ?? '[]';
            $documents = json_decode($documentsRaw, true);

            $documentData = $documents[0] ?? null;
            $downloadUrl = $documentData['document_download'] ?? null;

            if (!$downloadUrl) {
                $this->logger()->error("Download Failed: No document_download URL found in the webhook payload context.");
                return;
            }

            // 2. Setup your path directory layout (matching your exact layout logic)
            $destinationFolder = storage_path('app/public/leads/attachments');
            if (!File::exists($destinationFolder)) {
                File::makeDirectory($destinationFolder, 0755, true, true);
            }

            $fileName = 'loa_signed_' . $lead->id . '_' . time() . '.pdf';
            $fullFilePath = $destinationFolder . '/' . $fileName;

            try {
                // 3. Fetch the binary file stream contents over from Signable secure servers
                $this->logger()->info("Downloading signed PDF from: {$downloadUrl}");
                $fileResponse = Http::get($downloadUrl);

                if ($fileResponse->failed()) {
                    throw new \Exception("Signable file server returned HTTP status: " . $fileResponse->status());
                }

                // 4. Save the binary stream context to your storage disk directory
                File::put($fullFilePath, $fileResponse->body());

                // Relative path strings matching your exact database preferences
                $loa_path = 'storage/leads/attachments/' . $fileName;
                $save_loa_path = 'leads/attachments/' . $fileName;

                // 5. Check if there's an older file you want to delete to keep things clean
                $others = $lead->others ?? [];
                if (!empty($others['loa_signed_file'])) {
                    $oldFilePath = public_path($others['loa_signed_file']);
                    if (File::exists($oldFilePath)) {
                        File::delete($oldFilePath);
                    }
                }

                // Update your lead model meta tracking matrix array keys
                $others['loa_signed'] = '1';
                $others['loa_signed_file'] = $loa_path; // Track the signed file separately from generated file
                $others['signable_status'] = 'signed';
                $others['loa_signed_at'] = now()->toDateTimeString();

                // 6. Invoke your internal attachment mapping wrapper logic engine
                $this->addattachment($lead, $save_loa_path, $fullFilePath);

                $lead->others = $others;
                $lead->save();

                broadcast(new \App\Events\LoaSignedRealtimeUpdate([
                    'document_signed' => '1',
                    'url'             => asset($loa_path),
                    'lead_id'         => $lead->id    
                ]));

                $this->logger()->info("Database Sync & File Download Complete: Saved to {$fullFilePath}");

            } catch (\Exception $e) {
                $this->logger()->error("Failed to download and store signed LOA document: " . $e->getMessage());
            }

            
            $lead->others = $others;
            $lead->save();

            $this->logger()->info("Database Sync Complete: Lead #{$leadId} updated to 'loa_signed' => '1'.");
        } else {
            $this->logger()->error("Database Sync Failed: Lead record #{$leadId} not found in the table.");
        }
    }

    /**
     * Process logic if the client rejects or declines the document
     */
    protected function processRejectedDocument($leadId, array $payload)
    {
        $this->logger()->warning("Processing rejected logic for Lead ID: {$leadId}");

        $lead = Lead::find($leadId);

        if ($lead) {
            $others = $lead->others ?? [];
            
            // Flag it as rejected in your database track logs
            $others['signable_status'] = 'rejected';
            
            $lead->others = $others;
            $lead->save();

            $this->logger()->info("Database Sync Complete: Lead #{$leadId} marked as rejected.");
        }
    }

    public function addattachment($lead, $save_loa_path, $fullFilePath)
    {
        $fileSize = filesize($fullFilePath); 

        $lead->attachments()->where('others->is_signed_loa', 1)->delete();
    
        $lead->attachments()->create([
            'uploaded_by' => auth()->id(),
            'file_path'   => $save_loa_path,
            'file_name'   => basename($fullFilePath),
            'file_type'   => 'application/pdf',
            'file_size'   => $fileSize,
            'description' => 'customer signed LOA document via Signable',
            'others' => ['is_signed_loa' => 1],
        ]);

        log_timeline($lead->id, asset('storage/' . $save_loa_path), null, 'signed_loa_customer');

    }
}