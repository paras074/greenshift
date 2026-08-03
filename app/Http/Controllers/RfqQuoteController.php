<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\RfqQuote;
use Illuminate\Http\Request;

class RfqQuoteController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('edit rfq');
        $validatedData = $request->validate([
            'title'             => 'required|string|max:255',
            'supplier_name'     => 'required|string|max:255',
            'lead_id'           => 'required|integer',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'delivery_timeline' => 'nullable|string|max:255', 
            'price'             => 'nullable|numeric|min:0', 
            'warranty'          => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'others'            => 'nullable|array',
        ]);

        $lead = Lead::findOrFail($validatedData['lead_id']);

        RfqQuote::create($validatedData);
        
        log_timeline($lead->id, $validatedData, null, 'added_quote_to_lead');

        return redirect()->back()->with('success', 'Quote added successfully!');
    }
    
    public function view(Request $request, $lead_id)
    {
        $this->authorize('view rfq');
        $lead = Lead::findOrFail($lead_id);
        $quotes = $lead->rfqQuotes()->latest()->get();
        return view('rfq.view', compact('lead', 'quotes'));
    }
    
    public function update(Request $request, $id)
    {
        $this->authorize('edit rfq');
        // 1. Validate the incoming request data
        $validatedData = $request->validate([
            'title'             => 'required|string|max:255',
            'supplier_name'     => 'required|string|max:255',
            'lead_id'           => 'required|integer',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'delivery_timeline' => 'nullable|string|max:255', 
            'price'             => 'nullable|numeric|min:0', 
            'warranty'          => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'others'            => 'nullable|array',
        ]);
    
        $quote = RfqQuote::findOrFail($id);
        $quote->update($validatedData);
        
        log_timeline($quote->lead_id, $validatedData , null, 'updated_quote_on_lead');
        return redirect()->back()->with('success', 'Quote updated successfully!');
    }
    
    public function destroy($id)
    {
        $this->authorize('delete rfq');
        $quote = RfqQuote::findOrFail($id);
        $leadId = $quote->lead_id;
        log_timeline($leadId, $quote->toArray(), null, 'deleted_quote_from_lead');
        $quote->delete();
        return redirect()->back()->with('success', 'Quote deleted successfully!');
    }
    
    public function select(Request $request, $id)
    {
        $this->authorize('manage rfq');
        $selectedQuote = RfqQuote::findOrFail($id);
        $leadId = $selectedQuote->lead_id;
    
        $quotes = RfqQuote::where('lead_id', $leadId)->get();
    
        foreach ($quotes as $quote) {
            $others = $quote->others ?? [];
            
            if ($quote->id == $id) {
                $others['is_selected'] = 1;
            } else {
                unset($others['is_selected']); 
            }
    
            $quote->update(['others' => $others]);
        }
    
        return response()->json([
            'success' => true, 
            'message' => 'Vendor selection updated successfully!'
        ]);
    }
    
    public function assignSupplier(Request $request)
    {
        $this->authorize('manage rfq');
        $request->validate([
            'lead_id' => 'required|integer'
        ]);
    
        $lead = Lead::findOrFail($request->lead_id);
    
        $quotes = $lead->rfqQuotes()->get();
    
        if ($quotes->isEmpty()) {
            return redirect()->back()->with('error', 'No quotes available to assign.');
        }
    
        $assignedQuote = $quotes->first(function($quote) {
            return isset($quote->others['is_selected']) && $quote->others['is_selected'] == 1;
        });
    
        if (!$assignedQuote) {
            $assignedQuote = $quotes->first();
        }
    
        $leadOthers = $lead->others ?? [];
        $leadOthers['supplier'] = $assignedQuote->id;
        
        $lead->update([
            'others' => $leadOthers,
            'current_supplier' => $assignedQuote->supplier_name
        ]);
    
        log_timeline($lead->id, "Assigned supplier ({$assignedQuote->supplier_name}) to Lead", null, 'assigned_supplier_to_lead');
        
        return redirect()->route('leads.show', ['lead' => $lead])->with('success', 'Supplier successfully Added to this lead!');
    }
}