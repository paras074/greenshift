<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Lead;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PDFController extends Controller
{
    /**
     * Handle the generation and transmission workflow for LOA PDFs.
     */
    public function GenerateSendLoaPdf(Request $request)
    {        
        $leadId = $request->input('lead_id');
        $lead = Lead::findOrFail($leadId);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 404);
        }

        $others = $lead->others ?? [];
        $email = $lead->email;

        if (empty($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Lead does not have an email address.'
            ], 400);
        }
        
        $name = $lead->decision_maker_name ?? 'Valued Customer';

        if (!empty($others['loa_generated'])) {
            // Grab the dynamic config API Key
            $apiKey = get_setting_data(['signable_api_key'])['signable_api_key'];
            
            // 1. Get the absolute path to the local PDF file
            // $others['loa_generated'] usually stores relative paths like 'uploads/loa/file.pdf'
            $relativeFilePath = ltrim($others['loa_generated'], '/'); 
            $absolutePath = public_path($relativeFilePath);

            if (!file_exists($absolutePath)) {
                Log::error("Signable Upload Failed: File not found at {$absolutePath}");
                return response()->json([
                    'success' => false,
                    'message' => 'The generated LOA file could not be located on the server.'
                ], 404);
            }

            // 2. Read the file content and encode it into a Base64 string wrapper
            $fileContent = file_get_contents($absolutePath);
            $base64Content = base64_encode($fileContent);
            $fileName = basename($absolutePath);

            // 3. Dispatch payload out directly to Signable API
            $response = Http::withBasicAuth($apiKey, '')
                ->asJson() // This forces content-type: application/json
                ->post('https://api.signable.co.uk/v1/envelopes', [
                    'envelope_title' => 'Letter of Authority (LOA) for ' . $name,
                    'envelope_parties' => [
                        [
                            'party_name' => $name,
                            'party_email' => $email,
                            'party_role' => 'signer1'
                        ]
                    ],
                    'envelope_documents' => [
                        [
                            'document_title' => 'Letter of Authority',
                            'document_file_name' => $fileName,
                            'document_file_content' => $base64Content,
                            'document_auto_parse_tags' => true
                        ]
                    ],
                    'envelope_meta' => [
                        'lead_id' => $leadId,
                        'context' => 'loa_signature_request'
                    ]
                ]);

            // 4. Handle API state responses
            if ($response->successful()) {
                $responseData = $response->json();
                $others['signable_status'] = 'sent';
                $lead->others = $others;
                $lead->save();

                return response()->json([
                    'success' => true,
                    'message' => 'LOA document sent successfully via Signable for signing!'
                ], 200);
            }

            // Catch and log external connection errors 
            Log::error("Signable API payload dispatch failure", [
                'lead_id' => $leadId,
                'response' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Signable service returned an error. Check logs for details.'
            ], 500);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'LOA PDF has not been generated yet.'
            ], 400);
        }
    }

    public function GenerateLoaPdf(Request $request)
    {
        $leadId = 80; // Example lead ID for testing
        $lead = Lead::findOrFail($leadId);  
        return view('components.loa-template', compact('lead'));
    }

    public function GenerateLoaPdflead(Request $request)
    {
        $leadId = $request->input('lead_id');

        $lead = Lead::findOrFail($leadId);

        $pdf = Pdf::loadView('components.loa-template', compact('lead'));
        $pdf->setPaper('a4', 'portrait');

        $destinationFolder = storage_path('app/public/leads/attachments');

        if (!File::exists($destinationFolder)) {
            File::makeDirectory($destinationFolder, 0755, true, true);
        }

        $others = $lead->others ?? [];

        if (!empty($others['loa_generated'])) {
            $oldFilePath = public_path($others['loa_generated']);
            if (File::exists($oldFilePath)) {
                $deleted = File::delete($oldFilePath);
            }
        }

        $fileName = 'loa_document_' . $lead->id . '_' . time() . '.pdf';
        $fullFilePath = $destinationFolder . '/' . $fileName;
        File::put($fullFilePath, $pdf->output());
        $loa_path = 'storage/leads/attachments/' . $fileName;
        $save_loa_path = 'leads/attachments/' . $fileName;
        $others['loa_generated'] = $loa_path;
        $this->addattachment($lead, $save_loa_path, $fullFilePath);
        $lead->others = $others;
        $lead->save();

        $fileUrl = asset($loa_path);

        return response()->json([
            'success' => true,
            'message' => 'PDF document compiled successfully!',
            'download_url' => $fileUrl
        ]);
    }

    public function addattachment($lead, $save_loa_path, $fullFilePath)
    {
        $fileSize = filesize($fullFilePath); 

        $lead->attachments()->where('others->is_loa', 1)->delete();
    
        $lead->attachments()->create([
            'uploaded_by' => auth()->id(),
            'file_path'   => $save_loa_path,
            'file_name'   => basename($fullFilePath),
            'file_type'   => 'application/pdf',
            'file_size'   => $fileSize,
            'description' => 'Admin Generated LOA PDF',
            'others' => ['is_loa' => 1],
        ]);

        log_timeline($lead->id, asset('storage/' . $save_loa_path), null, 'admin_loa_generated');

    }
}