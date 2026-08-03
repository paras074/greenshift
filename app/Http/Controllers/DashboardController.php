<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Lead;
use App\Models\CallLog;
use App\Models\LeadStatus;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller{
	
	public function main_dashboard()
	{
		$this->authorize('view dashboard');
		$todayLeads = Lead::whereDate('created_at', Carbon::today())->get();
		$todayBudgetTotal = $todayLeads->sum(function ($lead) {
			$cleanString = str_replace([',', ' '], '', $lead->budget_range);
			if (str_contains($cleanString, 'to')) {
				$parts = explode('to', $cleanString);
				return (float) end($parts);
			}
			return (float) $cleanString;
		});
		$activeLeadsCount = Lead::whereHas('leadStatus', function($query) {
			$query->where('name', 'Active');
		})->count();
		$NewLeadsCount = Lead::whereHas('leadStatus', function($query) {
            $query->where('name', 'New');
        })
        ->whereDate('created_at', Carbon::today())
        ->count();
		$todayLeadsCount = $todayLeads->count();

		$closedLeadsCount = Lead::whereHas('leadStatus', function($query) {
				$query->where('name', 'LIKE', '%Closed%');
			})
			->whereMonth('updated_at', Carbon::now()->month)
			->whereYear('updated_at', Carbon::now()->year)
			->count();

		$todayClosedWonLeads = Lead::with('leadStatus')
			->whereHas('leadStatus', function($query) {
				$query->where('name', 'Closed Won');
			})
			->whereDate('updated_at', Carbon::today())
			->get();

		$todayClosedWonLeadsCount = $todayClosedWonLeads->count();

		return view('dashboard', compact('todayLeadsCount', 'activeLeadsCount', 'todayBudgetTotal', 'NewLeadsCount', 'closedLeadsCount', 'todayClosedWonLeadsCount'));
	}

	public function getChartData($type) {
        $labels = [];
        $values = [];

        if ($type == '1') {
            $data = Lead::where('created_at', '>=', now()->startOfMonth())
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');

            $period = CarbonPeriod::create(now()->startOfMonth(), now());
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');
                $labels[] = $date->format('d M');
                $values[] = $data[$dateString] ?? 0;
            }

			$todayLeads = Lead::whereDate('created_at', Carbon::today())->get();
			$todayBudgetTotal = $todayLeads->sum(function ($lead) {
				$cleanString = str_replace([',', ' '], '', $lead->budget_range);
				if (str_contains($cleanString, 'to')) {
					$parts = explode('to', $cleanString);
					return (float) end($parts);
				}
				return (float) $cleanString;
			});
			$activeLeadsCount = Lead::whereHas('leadStatus', function($query) {
				$query->where('name', 'Active');
			})->count();
			$NewLeadsCount = Lead::whereHas('leadStatus', function($query) {
				$query->where('name', 'New');
			})
			->whereDate('created_at', Carbon::today())
			->count();
			$todayLeadsCount = $todayLeads->count();

			$closedLeadsCount = Lead::whereHas('leadStatus', function($query) {
					$query->where('name', 'LIKE', '%Closed%');
				})
				->whereMonth('updated_at', Carbon::now()->month)
				->whereYear('updated_at', Carbon::now()->year)
				->count();

			$todayClosedWonLeads = Lead::with('leadStatus')
			->whereHas('leadStatus', function($query) {
				$query->where('name', 'Closed Won');
			})
			->whereDate('updated_at', Carbon::today())
			->get();

			$todayClosedWonLeadsCount = $todayClosedWonLeads->count();
			
			$html = view('dashboard.part', compact('todayLeadsCount', 'activeLeadsCount', 'todayBudgetTotal', 'NewLeadsCount', 'closedLeadsCount', 'todayClosedWonLeadsCount'))->render();

        } else {
			$data = Lead::selectRaw('YEARWEEK(created_at, 1) as week_id, count(*) as count')
				->where('created_at', '>=', now()->subWeeks(6))
				->groupBy('week_id')
				->pluck('count', 'week_id'); // e.g., ['202615' => 10]

			$labels = [];
			$values = [];

			// 2. Loop exactly 8 times to create the 8 bars
			for ($i = 5; $i >= 0; $i--) {
				$date = now()->subWeeks($i);
        
				// Calculate Start (Monday) and End (Sunday) of that week
				$startOfWeek = $date->copy()->startOfWeek();
				$endOfWeek = $date->copy()->endOfWeek();
				
				// Generate Label: "23 Feb - 01 Mar"
				// Note: 'j' is day without leading zero, 'M' is short month name
				$labels[] = $startOfWeek->format('j M') . ' - ' . $endOfWeek->format('j M');
				
				// Match with DB data
				$weekId = $date->format('oW'); 
				$values[] = $data[$weekId] ?? 0;
			}


			$todayLeads = Lead::where('created_at', '>=', now()->startOfWeek())->get();

			$todayBudgetTotal = $todayLeads->sum(function ($lead) {
				$cleanString = str_replace([',', ' '], '', $lead->budget_range);
				if (str_contains($cleanString, 'to')) {
					$parts = explode('to', $cleanString);
					return (float) end($parts);
				}
				return (float) $cleanString;
			});
			$activeLeadsCount = Lead::whereHas('leadStatus', function($query) {
				$query->where('name', 'Active');
			})->count();
			$NewLeadsCount = Lead::whereHas('leadStatus', function($query) {
				$query->where('name', 'New');
			})
			->where('created_at', '>=', now()->startOfWeek())
			->count();
			$todayLeadsCount = $todayLeads->count();

			$closedLeadsCount = Lead::whereHas('leadStatus', function($query) {
					$query->where('name', 'LIKE', '%Closed%');
				})
				->whereMonth('updated_at', Carbon::now()->month)
				->whereYear('updated_at', Carbon::now()->year)
				->count();

			$todayClosedWonLeads = Lead::with('leadStatus')
				->whereHas('leadStatus', function($query) {
					$query->where('name', 'Closed Won');
				})
				->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
				->get();

			$todayClosedWonLeadsCount = $todayClosedWonLeads->count();

			$html = view('dashboard.part', compact('todayLeadsCount', 'activeLeadsCount', 'todayBudgetTotal', 'NewLeadsCount', 'closedLeadsCount', 'todayClosedWonLeadsCount'))->render();

        }

		$startDate = ($type == '2') ? now()->startOfWeek() : now()->startOfDay();
		$activeStatuses = LeadStatus::where('status', 'active')
				->orderBy('sort_order', 'asc')
				->withCount(['leads' => function($query) use ($startDate) {
					$query->where('created_at', '>=', $startDate);
				}])
				->get();
		$statuses = [];
		$status_colors = [];
		$counts = [];

		foreach ($activeStatuses as $item) {
			$statuses[] = $item->name;
			$status_colors[] = $item->color;
			$counts[] = $item->leads_count; 
		}

		$second_graph_data = [
			'labels' => $statuses,
			'values' => $counts,
			'colors' => $status_colors
		];

		return response()->json([
			'graphData' => [
				'labels' => $labels,
				'values' => $values
			],
			'html' => $html,
			'secondGraphData' => $second_graph_data,
			'status' => 'success'
		]);
    }

	public function dialer_index()
	{
		$this->authorize('manage dialer');
		return view('dialer.index');
	}

	public function reports(Request $request)
    {
        $this->authorize('view reports');
    
        $user = auth()->user();
    
        if ($request->isMethod('post') || $request->ajax()) {
    
            $leadQuery = Lead::query();
            $callQuery = CallLog::query();
    
            if (!is_superadmin()) {
                if ($user->can('view-own leads')) {
                    $leadQuery->whereHas('assignments', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
    
                    $callQuery->where('user_id', $user->id);
                } else {
                    $leadQuery->whereRaw('1=0');
                    $callQuery->whereRaw('1=0');
                }
            }
    
            $userId = $request->input('assigned_to');
    
            if ($userId) {
                $leadQuery->where('created_by', $userId);
                $callQuery->where('user_id', $userId);
            }
    
            $trendBaseLeadQuery = clone $leadQuery;
    
            if ($request->filled('status')) {
                $leadQuery->where('status', $request->status);
            }
    
            if ($request->filled('source_id')) {
                $leadQuery->where('source_id', $request->source_id);
            }
    
            if ($request->filled('created_at')) {
    
                if ($request->created_at == 1) {
    
                    $start = Carbon::now()->startOfWeek();
                    $end = Carbon::now()->endOfWeek();
    
                    $leadQuery->whereBetween('created_at', [$start, $end]);
                    $callQuery->whereBetween('created_at', [$start, $end]);
    
                } elseif ($request->created_at == 2) {
    
                    $leadQuery
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
    
                    $callQuery
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
    
                } elseif ($request->created_at == 3 && $request->filled('created_at_range')) {
    
                    $range = $request->created_at_range;
    
                    if (str_contains($range, ' to ')) {
    
                        [$from, $to] = explode(' to ', $range);
    
                        $start = Carbon::parse($from)->startOfDay();
                        $end = Carbon::parse($to)->endOfDay();
    
                        $leadQuery->whereBetween('created_at', [$start, $end]);
                        $callQuery->whereBetween('created_at', [$start, $end]);
    
                    } else {
    
                        $date = Carbon::parse($range);
    
                        $leadQuery->whereDate('created_at', $date);
                        $callQuery->whereDate('created_at', $date);
                    }
                }
            }
    
            $leads = $leadQuery->get();
    
            $totalCount = $leads->count();
            $qualifiedCount = $leads->where('lead_status_id', 5)->count();
            $lostCount = $leads->where('lead_status_id', 6)->count();
    
            $callsCount = $callQuery->count();
    
            $qualifiedPercentage = $totalCount
                ? round(($qualifiedCount / $totalCount) * 100)
                : 0;
    
            $lostPercentage = $totalCount
                ? round(($lostCount / $totalCount) * 100)
                : 0;
    
            $statusesBreakdown = LeadStatus::orderBy('sort_order')
                ->get()
                ->map(function ($status) use ($leads) {
                    return [
                        'name' => $status->name,
                        'color' => $status->color ?? '#64748b',
                        'sort_order' => $status->sort_order,
                        'count' => $leads->where('lead_status_id', $status->id)->count(),
                    ];
                })
                ->toArray();
    
            $targetInput = $request->input(
                'trend_target_month',
                now()->format('Y-m')
            );
    
            $targetDate = Carbon::createFromFormat('Y-m-d', $targetInput . '-01');
    
            $trendLabels = [];
            $trendLeadsData = [];
            $trendConversionsData = [];
    
            for ($i = 5; $i >= 0; $i--) {
    
                $month = (clone $targetDate)->subMonths($i);
    
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();
    
                $trendLabels[] = $month->format('M Y');
    
                $trendLeadsData[] = (clone $trendBaseLeadQuery)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
    
                $trendConversionsData[] = (clone $trendBaseLeadQuery)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('lead_status_id', 5)
                    ->count();
            }
    
            $htmlOutput = view('reports.partials.main', compact(
                'leads',
                'totalCount',
                'qualifiedCount',
                'callsCount',
                'lostCount',
                'qualifiedPercentage',
                'lostPercentage',
                'statusesBreakdown'
            ))->render();
    
            return response()->json([
                'html' => $htmlOutput,
                'data' => [
                    'statusesBreakdown' => $statusesBreakdown,
                    'trends' => [
                        'labels' => $trendLabels,
                        'leads' => $trendLeadsData,
                        'conversions' => $trendConversionsData,
                    ],
                ],
            ]);
        }
    
        return view('reports.index');
    }
}
