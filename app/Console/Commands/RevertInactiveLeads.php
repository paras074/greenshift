<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\CoreSetting;
use App\Models\User;
use Carbon\Carbon;

class RevertInactiveLeads extends Command
{
    // The name you use to run it manually
    protected $signature = 'leads:revert-inactive';
    protected $description = 'Revert inactive leads to superadmin';

    public function handle()
    {
        $daysSetting = CoreSetting::where('setting_key', 'lead_reversion_days')->first();
        $days = $daysSetting ? (int)$daysSetting->setting_value : 0;

        if ($days <= 0) {
            $this->info('Reversion disabled (days set to 0).');
            return;
        }
        // 2. Get excluded status IDs
        $excludedSetting = CoreSetting::where('setting_key', 'excluded_statuses')->first();
        $excludedIds = $excludedSetting ? (array)$excludedSetting->setting_value : [];

        // 3. Find first Superadmin
        $superAdmin = User::role('superadmin')->first();
        if (!$superAdmin) {
            $this->error('No Superadmin found.');
            return;
        }

        $cutoff = Carbon::now()->subDays($days);

        // 4. Update the Leads
        $query = Lead::whereNotNull('created_by')->where('updated_at', '<', $cutoff);
    

        if (!empty($excludedIds)) {
            $query->whereNotIn('lead_status_id', $excludedIds);
        }

        $count = $query->count();
        
        $query->update(['created_by' => $superAdmin->id]);

        $this->info("Moved {$count} leads to {$superAdmin->name}.");
    }
}