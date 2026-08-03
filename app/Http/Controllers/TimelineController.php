<?php

namespace App\Http\Controllers;

use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimelineController extends Controller
{
    /**
     * Display the activity timeline.
     */
    public function index(Request $request)
    {
        $this->authorize('view-timeline dashboard'); 
        $limit = 10;

        // Fetch the paginated activities for the timeline
        $query = Timeline::with(['user', 'lead'])->latest();

        switch ($request->f) {

            case '1': // Today
                $query->whereDate('created_at', now()->today());
                break;

            case '2': // Yesterday
                $query->whereDate('created_at', now()->yesterday());
                break;

            case '3': // This Week
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;

            case '4': // This Month
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;

            case '5': // Custom Range
                if ($request->df && $request->dt) {
                    $from = Carbon::parse($request->df)->startOfDay();
                    $to   = Carbon::parse($request->dt)->endOfDay();
                    $query->whereBetween('created_at', [$from, $to]);
                }
                break;
        }

        $activities = $query->paginate($limit);

        $stats = [
            'total' => Timeline::count(),
            'today' => Timeline::whereDate('created_at', now()->today())->count(),
            'week'  => Timeline::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month' => Timeline::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->count(),
        ];

        // Grouping for the initial UI
        $groupedActivities = $activities->getCollection()->sortByDesc('created_at')->groupBy(fn($item) => $item->created_at->format('Y-m-d'));

        $typeCounts = Timeline::select('other', \DB::raw('count(*) as total'))
            ->groupBy('other')
            ->pluck('total', 'other')
            ->toArray();

        return view('layouts.partials.activity', [
            'activities' => $activities,
            'groupedActivities' => $groupedActivities,
            'stats' => $stats,
            'typeCounts' => $typeCounts
        ]);
    }
}