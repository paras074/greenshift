<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TempLeads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;
use App\Models\Lead;
use App\Models\Note;
use Illuminate\Support\Facades\DB;

class GatherCompanyDataController extends Controller {

    public function parseUKAddress(string $address): array
    {
        $postcode = '';
        $city     = '';
        if (preg_match('/\b([A-Z]{1,2}\d{1,2}[A-Z]?\s*\d[A-Z]{2})\b/i', $address, $m)) {
            $postcode = strtoupper(trim($m[1]));
        }
        $parts = array_map('trim', explode(',', $address));
        foreach ($parts as $part) {
            $stripped = trim(preg_replace('/\b[A-Z]{1,2}\d{1,2}[A-Z]?\s*\d[A-Z]{2}\b/i', '', $part));
            if (!empty($stripped) && !in_array(strtoupper($stripped), ['UK', 'UNITED KINGDOM', 'ENGLAND', 'SCOTLAND', 'WALES'])) {
                $city = $stripped; // last non-empty, non-country segment wins
            }
        }
        return ['city' => $city, 'postcode' => $postcode];
    }


    public function search_companies_data(Request $request){
        $this->authorize('fetch-leads leads');
        $query    = $request->input('query');
        $type     = $request->input('type');
        $category = $request->input('category');

        $get_setting_data = get_setting_data(['google_api_key', 'max_result_from_google_api']);
        $google_api_key = $get_setting_data['google_api_key'] ?? env('GOOGLE_MAPS_API_KEY');
        $max_result_from_google_api = $get_setting_data['max_result_from_google_api'] ?? 50;

        $restts = session('company_results');
        if ($restts) {
            // return response()->json([
            //     'status'  => 'success',
            //     'country' => 'United Kingdom',
            //     'count'   => count($restts),
            //     'results' => $restts,
            // ]);
        }

        $searchQuery = $query;
        if ($category) $searchQuery .= ' ' . $category;

        // Build params — only include `type` if actually provided
        $params = [
            'query'  => $searchQuery . ' United Kingdom',
            'region' => 'gb',
            'key'    => $google_api_key,
        ];
        if (!empty($type)) {
            $params['type'] = $type;
        }

        // Step 1: Text Search restricted to UK
        $searchResponse = Http::get(
            'https://maps.googleapis.com/maps/api/place/textsearch/json',
            $params
        );

        $places = $searchResponse->json()['results'] ?? [];

        // Step 2: Get details — remove the broken address filter,
        // rely on Google's region=gb + "United Kingdom" in query instead
        $results = [];

        foreach (array_slice($places, 0, $max_result_from_google_api) as $place) {

            // Soft UK check — postcode pattern OR any UK keyword
            $address = $place['formatted_address'] ?? '';
            $isUK    = preg_match('/\b[A-Z]{1,2}\d{1,2}[A-Z]?\s*\d[A-Z]{2}\b/i', $address) // UK postcode
                    || str_contains($address, 'UK')
                    || str_contains($address, 'United Kingdom')
                    || str_contains($address, 'England')
                    || str_contains($address, 'Scotland')
                    || str_contains($address, 'Wales')
                    || str_contains($address, 'Northern Ireland');

            if (!$isUK) continue;

            $detailResponse = Http::get(
                'https://maps.googleapis.com/maps/api/place/details/json',
                [
                    'place_id' => $place['place_id'],
                    'key'      => $google_api_key,
                    'fields'   => 'place_id,name,formatted_phone_number,international_phone_number,opening_hours,types,rating,user_ratings_total,formatted_address,website,business_status',
                ]
            );

            $detail = $detailResponse->json()['result'] ?? [];

            $results[] = [
                'name'                => $detail['name'] ?? 'N/A',
                'place_id'            => $detail['place_id'] ?? null,
                'category'            => implode(', ', $detail['types'] ?? []),
                'type'                => $place['types'][0] ?? 'N/A',
                'phone'               => $detail['formatted_phone_number'] ?? 'N/A',
                'international_phone' => $detail['international_phone_number'] ?? 'N/A',
                'address'             => $detail['formatted_address'] ?? 'N/A',
                'opening_hours'       => $detail['opening_hours']['weekday_text'] ?? [],
                'open_now'            => $detail['opening_hours']['open_now'] ?? null,
                'rating'              => $detail['rating'] ?? 'N/A',
                'total_reviews'       => $detail['user_ratings_total'] ?? 0,
                'website'             => $detail['website'] ?? 'N/A',
                'business_status'     => $detail['business_status'] ?? 'N/A',
            ];
        }

        session([
            'company_results' => $results
        ]);

        $data = ['s' => $query, 'count' => count($results)];

        log_timeline(null, $data, auth()->id(), 'leads_fetched_google');

        return response()->json([
            'status'  => 'success',
            'country' => 'United Kingdom',
            'count'   => count($results),
            'results' => $results,
        ]);
    }

    public function save_temp_lead(Request $request){
        $this->authorize('fetch-leads leads');
        $data = $request->validate([
            'name'      => 'required|string',
            'place_id'  => 'required|string',
            'phone'     => 'nullable|string',
            'address'   => 'nullable|string',
        ]);
        // Check duplicate by google_place_id
        $exists = TempLeads::where('google_place_id', $request->place_id)->exists();
        if ($exists) {
            return response()->json([
                'status'  => 'duplicate',
                'message' => '"' . $request->name . '" is already saved as a temporary lead.',
            ]);
        }
        $addressParts = $this->parseUKAddress($request->address ?? '');
        $temporary_lead = TempLeads::create([
            'company_name'      => $request->name,
            'google_place_id'   => $request->place_id,
            'phone'             => $request->international_phone ?? $request->phone,
            'email'             => $request->email ?? null,
            'address'           => $request->address,
            'city'              => $addressParts['city'],
            'postcode'          => $addressParts['postcode'],
            'lead_gathering_from' => 'google_api',
            'created_by'        => Auth::id(),
            'status'            => 'active',
            'others'            => $request->all()
        ]);

        log_timeline(null, $temporary_lead->toArray(), auth()->id(), 'saved_temp_lead');

        return response()->json([
            'status'  => 'success',
            'message' => '"' . $request->name . '" saved as temporary lead.',
        ]);
    }

    public function save_all_main_leads(Request $request){
        $this->authorize('fetch-leads leads');
        $companies = $request->validate([
            'companies'             => 'required|array|min:1',
            'companies.*.name'      => 'required|string',
            'companies.*.place_id'  => 'required|string',
            'companies.*.phone'     => 'nullable|string',
            'companies.*.address'   => 'nullable|string',
        ])['companies'];
        $saved = 0;
        foreach ($companies as $company) {
            $exists = Lead::where('google_place_id', $company['place_id'])->exists();
            if ($exists) continue;

            $exists_2 = TempLeads::where('google_place_id', $company['place_id'])->exists();
            if ($exists_2) {
                $company['name'] = $exists_2->company_name ?? $company['name'];
                $company['international_phone'] = $exists_2->phone ?? $company['phone'];
                if (!empty($exists_2->email)) {
                    $company['email'] = $exists_2->email;
                } else {
                    $company['email'] = $company['email'] ?? null;
                }
                $company['address'] = $exists_2->address ?? $company['address'];
            }
            $addressParts = $this->parseUKAddress($company['address'] ?? '');
            $newLead = Lead::create([
                'company_name'      => $company['name'],
                'google_place_id'   => $company['place_id'],
                'phone'             => $company['international_phone'] ?? $company['phone'] ?? null,
                'email'             => $company['email'] ?? null,
                'lead_status_id'    => 1,
                'priority_status_id'=> 3,
                'address'           => $company['address'] ?? null,
                'city'              => $addressParts['city'],
                'postcode'          => $addressParts['postcode'],
                'lead_gathering_from' => 'google_api',
                'created_by'        => Auth::id(),
                'status'            => 'active',
                'others'            => $company
            ]);

            $createdLeadId = $newLead->id;

            if ($exists_2) {
                $tempNotes = TempLeads::where('google_place_id', $company['place_id'])->first()->notes ?? [];
                foreach ($tempNotes as $tempNote) {
                    Note::create([
                        'lead_id'      => $newLead->id,
                        'user_id'      => $tempNote['uid'] ?? Auth::id(),
                        'data'         => $tempNote['note'],
                        'mentioned_id' => null,
                        'others' => null,
                    ]);
                }

                $tempLead = TempLeads::where('google_place_id', $company['place_id'])->first();

                $tempCallLogs = DB::table('temporary_call_logs')->where('lead_id', $tempLead->id)->get();

                foreach ($tempCallLogs as $log) {
                    $logData = (array) $log;
                    unset($logData['id']);
                    $logData['lead_id'] = $createdLeadId; 
                    DB::table('call_logs')->insert($logData);
                }

                DB::table('temporary_call_logs')->where('lead_id', $tempLead->id)->delete();

                $deletedLeadData = [
                    'id' => $tempLead->id,
                    'company_name' => $tempLead->company_name,
                    'new_lead_id' => $createdLeadId
                ];

                $tempLead->delete();

                log_timeline(null, $deletedLeadData, auth()->id(), 'saved_main_lead_delete_temporary');                
            } else {
                log_timeline($createdLeadId, $newLead->toArray(), auth()->id(), 'lead_created');
            }
            
            $saved++;
        }
        if($saved > 0){
            log_timeline(null, $saved, auth()->id(), 'saved_main_google_leads_count');
        }
        return response()->json([
            'status'  => 'success',
            'message' => $saved . ' lead(s) saved successfully.',
        ]);
    }

    public function save_update_temp_lead(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string',
            'place_id' => 'required|string',
            'phone'    => 'nullable|string',
            'address'  => 'nullable|string',
            'email'    => 'nullable|email', // Good to validate email format if present
        ]);

        $addressParts = $this->parseUKAddress($request->address ?? '');
        
        // 1. Check if the lead already exists
        $lead = TempLeads::where('google_place_id', $request->place_id)->first();

        $attributes = [
            'company_name'        => $request->name,
            'phone'               => $request->international_phone ?? $request->phone,
            'email'               => $request->email ?? null,
            'address'             => $request->address,
            'city'                => $addressParts['city'],
            'postcode'            => $addressParts['postcode'],
            'lead_gathering_from' => 'google_api',
            'status'              => 'active',
            'others'              => $request->all() // Cast to array in Model for best results
        ];

        if ($lead) {
            // 2. If it exists, update it
            $lead->update($attributes);
            
            return response()->json([
                'status'  => 'updated',
                'id' => $lead->id,
                'message' => '"' . $request->name . '" has been updated successfully.',
            ]);
        }

        // 3. If it doesn't exist, create it
        $attributes['google_place_id'] = $request->place_id;
        $attributes['created_by']      = Auth::id();
        
        $lead = TempLeads::create($attributes);

        return response()->json([
            'status'  => 'success',
            'id' => $lead->id,
            'message' => '"' . $request->name . '" saved as temporary lead.',
        ]);
    }

    public function save_temp_call_note(Request $request)
    {
        // Validate the incoming request
        $data = $request->validate([
            'c_id' => 'required',
            'note' => 'required|string',
        ]);

        // 1. Find the lead using the c_id (assuming your model is named Lead)
        $lead = TempLeads::find($data['c_id']);

        if (!$lead) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lead not found.',
            ], 404);
        }

        // 2. Add the new note
        $notes = $lead->notes ?? [];
        $notes[] = [
            'uid'  => auth()->id(),
            'note' => $data['note'],
            'date' => now()->format('d M Y'),
        ];

        // Save back to the lead (assuming 'notes' is cast as an array/json in the Model)
        $lead->notes = $notes;
        $lead->save();

        $note_data = ['note' => $data['note'], 'temp_lead_id' => $lead->id];

        log_timeline(null, $note_data, auth()->id(), 'temp_note_added');

        // 3. Map User Names to the notes
        $userIds = collect($notes)->pluck('uid')->unique();
        $users = User::whereIn('id', $userIds)->pluck('name', 'id');

        // Attach the name to each note object for the frontend
        $formattedNotes = collect($notes)->map(function($note) use ($users) {
            $note['user_name'] = $users[$note['uid']] ?? 'Unknown User';
            return $note;
        })->reverse()->values(); // Optional: reverse to show newest notes first

        return response()->json([
            'status'  => 'success',
            'message' => 'Note saved successfully.',
            'notes'   => $formattedNotes,
        ]);
    }

    public function save_all_temp_leads(Request $request){
        $companies = $request->validate([
            'companies'             => 'required|array|min:1',
            'companies.*.name'      => 'required|string',
            'companies.*.place_id'  => 'required|string',
            'companies.*.phone'     => 'nullable|string',
            'companies.*.address'   => 'nullable|string',
        ])['companies'];
        $saved      = 0;
        $duplicates = 0;
        $skipped    = [];
        foreach ($companies as $company) {
            $exists = TempLeads::where('google_place_id', $company['place_id'])->exists();
            if ($exists) {
                $duplicates++;
                $skipped[] = $company['name'];
                continue;
            }
            $addressParts = $this->parseUKAddress($company['address'] ?? '');
            TempLeads::create([
                'company_name'        => $company['name'],
                'google_place_id'     => $company['place_id'],
                'phone'               => $company['international_phone'] ?? $company['phone'] ?? null,
                'address'             => $company['address'] ?? null,
                'city'                => $addressParts['city'],
                'postcode'            => $addressParts['postcode'],
                'lead_gathering_from' => 'google_api',
                'created_by'          => Auth::id(),
                'status'              => 'active',
                'others'              => $company,
            ]);
            $saved++;
        }
        if($saved > 0) {
            $note_data = ['count' => $saved, 'duplicates' => $duplicates, 'skipped' => $skipped];
            log_timeline(null, $note_data, auth()->id(), 'saved_temp_leads');
        }

        return response()->json([
            'status'     => 'success',
            'saved'      => $saved,
            'duplicates' => $duplicates,
            'skipped'    => $skipped,
            'message'    => $saved . ' lead(s) saved' . ($duplicates ? ', ' . $duplicates . ' duplicate(s) skipped.' : '.'),
        ]);
    }


    public function existing_temp_leads(){
        $this->authorize('fetch-leads leads');
        $allLeads    = TempLeads::latest()->get();
        $googleLeads = TempLeads::where('lead_gathering_from', 'google_api')->latest()->get();
        $housesLeads = TempLeads::where('lead_gathering_from', 'companies_house')->latest()->get();
        return view('existing-temp-leads.index', [
            'allLeads'    => $allLeads,
            'googleLeads' => $googleLeads,
            'housesLeads' => $housesLeads,
            'total'       => $allLeads->count(),
            'fromGoogle'  => $googleLeads->count(),
            'fromHouses'  => $housesLeads->count(),
            'today'       => TempLeads::whereDate('created_at', today())->count(),
        ]);
    }


    public function destroy_temp_lead($id){
        $lead = TempLeads::findOrFail($id);
        log_timeline(null, $lead->toArray(), auth()->id(), 'temp_lead_deleted');
        $lead->delete();
        return response()->json([
            'status'  => 'success',
            'message' => '"' . $lead->company_name . '" deleted successfully.',
        ]);
    }


    public function bulk_delete_temp_leads(Request $request){
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No IDs provided.'], 422);
        }
        $count = TempLeads::whereIn('id', $ids)->delete();
        $data = ['ids' => $ids, 'count' => $count];
        log_timeline(null, $data, auth()->id(), 'temp_leads_deleted');
        return response()->json([
            'status'  => 'success',
            'message' => $count . ' lead(s) deleted successfully.',
        ]);
    }

    /* housing api */

    public function search_companies_data_housing(Request $request){
        $query = $request->input('query');

        $get_setting_data = get_setting_data(['companies_house_api_key', 'max_result_from_companies_house_api']);
        $apiKey = $get_setting_data['companies_house_api_key'] ?? env('COMPANIES_HOUSE_API_KEY');
        $itemsPerPage = $get_setting_data['max_result_from_companies_house_api'] ?? 30;

        $url = "https://api.company-information.service.gov.uk/search/companies"
            . "?q=" . urlencode($query)
            . "&items_per_page=" . $itemsPerPage;

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
            'Accept'        => 'application/json',
        ])->get($url);

        if (!$response->successful()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch data from Companies House API',
            ], 500);
        }

        $companies = $response->json()['items'] ?? [];

        $results = [];

        foreach ($companies as $company) {

            $address = $company['address'] ?? [];

            $results[] = [
                'name'   => $company['title'] ?? 'N/A',
                'place_id' => $company['company_number'] ?? 'N/A',
                'status' => $company['company_status'] ?? 'N/A',
                'type'   => $company['company_type'] ?? 'N/A',
                'address' => implode(', ', array_filter([
                    $address['address_line_1'] ?? '',
                    $address['address_line_2'] ?? '',
                    $address['locality'] ?? '',
                    $address['region'] ?? '',
                    $address['postal_code'] ?? '',
                    $address['country'] ?? '',
                ])),
                'date_of_creation' => $company['date_of_creation'] ?? null,
                'links' => [
                    'self' => $company['links']['self'] ?? null,
                ],
                'others'   => $company,
            ];
        }

        return response()->json([
            'status'  => 'success',
            'count'   => count($results),
            'results' => $results,
        ]);
    }

    public function save_call_note(Request $request)
    {
        $data = $request->validate([
            'company' => 'required|array',
            'note'    => 'required|string',
        ]);

        $companyData = $data['company'];

        $lead = TempLeads::firstOrCreate(
            ['google_place_id' => $companyData['place_id']], 
            [
                'company_name'        => $companyData['name'] ?? 'N/A',
                'phone'               => $companyData['international_phone'] ?? null,
                'address'             => $companyData['address'] ?? null,
                'email'               => $companyData['email'] ?? null,
                'city'                => $this->parseUKAddress($companyData['address'] ?? '')['city'],
                'postcode'            => $this->parseUKAddress($companyData['address'] ?? '')['postcode'],
                'lead_gathering_from' => 'google_api',
                'created_by'          => auth()->id(),
                'status'              => 'active',
                'others'              => $companyData 
            ]
        );

        // 1. Add the new note
        $notes = $lead->notes ?? [];
        $notes[] = [
            'uid'  => auth()->id(),
            'note' => $data['note'],
            'date' => now()->format('d M Y'),
        ];

        $lead->notes = $notes;
        $lead->save();

        $note_data = ['note' => $data['note'], 'temp_lead_id' => $lead->id];

        log_timeline(null, $note_data, auth()->id(), 'temp_note_added');

        // 2. Map User Names to the notes
        // Get all unique UIDs from the notes array
        $userIds = collect($notes)->pluck('uid')->unique();
        
        // Fetch user names from the DB in one query
        $users = User::whereIn('id', $userIds)->pluck('name', 'id');

        // Attach the name to each note object for the frontend
        $formattedNotes = collect($notes)->map(function($note) use ($users) {
            $note['user_name'] = $users[$note['uid']] ?? 'Unknown User';
            return $note;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Note saved successfully.',
            'notes'   => $formattedNotes,
        ]);
    }

    /**
     * Get all notes for a specific lead
     */
    public function get_temp_lead_notes(Request $request)
    {
        $request->validate([
            'c_id' => 'required'
        ]);

        $lead = TempLeads::find($request->c_id);

        if (!$lead) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lead not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'notes'  => $this->format_lead_notes($lead->notes ?? []),
        ]);
    }

    /**
     * Shared Helper to format notes with User Names
     */
    private function format_lead_notes($notes)
    {
        if (empty($notes)) {
            return [];
        }

        // Get unique UIDs and fetch names in one query
        $userIds = collect($notes)->pluck('uid')->unique();
        $users = User::whereIn('id', $userIds)->pluck('name', 'id');

        return collect($notes)->map(function($note) use ($users) {
            $note['user_name'] = $users[$note['uid']] ?? 'Unknown User';
            return $note;
        })->reverse()->values(); // Newest first
    }

    public function make_into_leads(Request $request)
    {
        $data = $request->validate([
            'companies' => 'required|array|min:1'
        ]);

        $saved = 0;
        foreach ($data['companies'] as $id) {
            $company = TempLeads::find($id);
            if (!$company) continue;

            $exists = Lead::where('google_place_id', $company['google_place_id'])->exists();
            if ($exists) continue;

            $newLead = Lead::create([
                'company_name'      => $company->company_name,
                'google_place_id'   => $company->google_place_id,
                'phone'             => $company->phone,
                'email'             => $company->email,
                'address'           => $company->address,
                'city'              => $company->city,
                'postcode'          => $company->postcode,
                'lead_status_id'    => 1,
                'priority_status_id'=> 3,
                'lead_gathering_from' => 'google_api',
                'created_by'        => Auth::id(),
                'status'            => 'active',
                'others'            => $company->others,
            ]);

            $tempNotes = $company->notes ?? [];
            foreach ($tempNotes as $tempNote) {
                Note::create([
                    'lead_id'      => $newLead->id,
                    'user_id'      => $tempNote['uid'] ?? Auth::id(),
                    'data'         => $tempNote['note'],
                    'mentioned_id' => null,
                    'others' => null,
                ]);
            }

            $tempCallLogs = DB::table('temporary_call_logs')->where('lead_id', $company->id)->get();

            foreach ($tempCallLogs as $log) {
                $logData = (array) $log;
                unset($logData['id']);
                $logData['lead_id'] = $createdLeadId; 
                DB::table('call_logs')->insert($logData);
            }

            DB::table('temporary_call_logs')->where('lead_id', $company->id)->delete();

            $saved++;
            log_timeline($newLead->id, $newLead->toArray(), auth()->id(), 'lead_created');
            $company->delete();
        }

        if($saved > 0){
            log_timeline(null, $saved, auth()->id(), 'moved_temporary_leads_to_leads');
        }

        return response()->json([
            'status'  => 'success',
            'message' => "$saved lead(s) promoted successfully.",
        ]);
    }
}