<?php 

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class LeadFunnel extends Component
{
    public $stages = [];

    public function mount()
    {
        $this->loadLeads();
    }

    public function loadLeads()
    {
        $statuses = KanbanLeadstatus();
        
        $leadsByStep = Lead::get()->groupBy('lead_status_id');

        $this->stages = collect($statuses)->map(function ($data, $id) use ($leadsByStep) {
            $currentStepLeads = $leadsByStep->get($id, collect());
            
            return [
                'id'    => $id,
                'title' => $data['name'],   // Extract name from the array
                'color' => $data['color'],   // Pass the color through
                'count' => $currentStepLeads->count(),
                'tasks' => $currentStepLeads->toArray()
            ];
        })->values()->toArray();
    }

    public function updateTaskOrder($groups)
    {
        $ideal_status = KanbanLeadstatus();
        
        foreach ($groups as $group) {
            $newStepId = (int)$group['value'];
            $newStatusName = $ideal_status[$newStepId]['name'] ?? 'Unknown';    

            foreach ($group['items'] as $item) {
                $leadId = (int)$item['value'];
                $lead = Lead::find($leadId);
                if ($lead && $lead->lead_status_id != $newStepId) {
                    $oldStatusName = $ideal_status[$lead->lead_status_id]['name'] ?? 'Initial';
                    $lead->update([
                        'lead_status_id' => $newStepId
                    ]);
                    $array_data = [
                        'old' => $oldStatusName, 
                        'new' => $newStatusName
                    ];
                    log_timeline($lead->id, $array_data, null, 'lead_status_updated');
                }
            }
        }
        $this->loadLeads();
    }

    public function render()
    {
        $this->authorize('kanban manage');
        
        $totalBudget = Lead::where('budget_range', '!=', '')
            ->whereNotNull('budget_range')
            ->sum(DB::raw('CAST(budget_range AS DECIMAL(15,2))')) ?: 0;

        // 2. Get the Count of leads that have a budget
        $leadCount = Lead::where('budget_range', '!=', '')
            ->whereNotNull('budget_range')
            ->count();

        // 3. Calculate Average (Prevent division by zero)
        $averageBudget = $leadCount > 0 ? ($totalBudget / $leadCount) : 0;

        return view('leads.funnel', [
            'totalBudget'   => $totalBudget,
            'averageBudget' => $averageBudget,
            'leadCount'     => $leadCount
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}