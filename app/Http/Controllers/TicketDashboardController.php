<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Priority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Asset;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $userName = $user->name ?? 'User';
        $greetings = [
            "Let's solve some tickets today!",
            'Issues stay small when you act fast.',
            'Every ticket closed is relief delivered.',
            'Support that scales starts with structured tracking.',
        ];
        $randomGreeting = $greetings[array_rand($greetings)];

        // Global Filter Parameters
        $filterStatusId = $request->get('status_id');
        $filterPriorityId = $request->get('priority_id');
        $filterCategoryId = $request->get('category_id');
        $filterYear = $request->get('year');
        $filterMonth = $request->get('month');

        // Global Query Filter Callback
        $applyGlobalFilters = function ($query) use ($filterStatusId, $filterPriorityId, $filterCategoryId, $filterYear, $filterMonth) {
            if (!empty($filterStatusId)) {
                $query->where('status_id', $filterStatusId);
            }
            if (!empty($filterPriorityId)) {
                $query->where('priority_id', $filterPriorityId);
            }
            if (!empty($filterCategoryId)) {
                $query->where('category_id', $filterCategoryId);
            }
            if (!empty($filterYear) && $filterYear !== 'all') {
                $query->whereRaw('YEAR(created_at) = ?', [$filterYear]);
            }
            if (!empty($filterMonth) && $filterMonth !== 'all') {
                $query->whereRaw('MONTH(created_at) = ?', [$filterMonth]);
            }
            return $query;
        };

        $startOfMonth = now()->startOfMonth();
        $openStatusIds = TicketStatus::where('is_closed', false)->pluck('id')->toArray();
        $completedStatusIds = TicketStatus::where('is_closed', true)->pluck('id')->toArray();

        // 1. Metric Cards
        $totalTicketsQuery = $applyGlobalFilters(Ticket::query());
        $totalTickets = $totalTicketsQuery->count();
        $totalTicketsPrev = $applyGlobalFilters(Ticket::where('created_at', '<', $startOfMonth))->count();
        $totalTicketsTrend = $this->trend($totalTickets, $totalTicketsPrev);

        $openTicketsQuery = $applyGlobalFilters(Ticket::whereIn('status_id', $openStatusIds));
        $openTickets = $openTicketsQuery->count();
        $openTicketsPrev = $applyGlobalFilters(Ticket::whereIn('status_id', $openStatusIds)->where('created_at', '<', $startOfMonth))->count();
        $openTicketsTrend = $this->trend($openTickets, $openTicketsPrev);

        $unassignedQuery = $applyGlobalFilters(Ticket::whereNull('assigned_to_id'));
        $unassigned = $unassignedQuery->count();
        $unassignedPrev = $applyGlobalFilters(Ticket::whereNull('assigned_to_id')->where('created_at', '<', $startOfMonth))->count();
        $unassignedTrend = $this->trend($unassigned, $unassignedPrev);

        $resolvedQuery = $applyGlobalFilters(Ticket::whereIn('status_id', $completedStatusIds));
        $resolved = $resolvedQuery->count();
        $resolvedPrev = $applyGlobalFilters(Ticket::whereIn('status_id', $completedStatusIds)->where('created_at', '<', $startOfMonth))->count();
        $resolvedTrend = $this->trend($resolved, $resolvedPrev);

        $avgResolution = $applyGlobalFilters(Ticket::whereIn('status_id', $completedStatusIds)->whereNotNull('resolution_time_hours'))
            ->avg('resolution_time_hours') ?? 0;

        $myTickets = $applyGlobalFilters(Ticket::where('assigned_to_id', $user->id))->count();
        $myOpenTickets = $applyGlobalFilters(Ticket::where('assigned_to_id', $user->id)->whereIn('status_id', $openStatusIds))->count();

        // Tables
        $recentTickets = $applyGlobalFilters(Ticket::with(['priority', 'status', 'assignedTo', 'category', 'ticketCategory']))
            ->latest()
            ->limit(8)
            ->get();

        $unassignedTickets = $applyGlobalFilters(Ticket::with(['priority', 'category'])->whereNull('assigned_to_id')->whereIn('status_id', $openStatusIds))
            ->latest()
            ->limit(5)
            ->get();

        // Master Data for Filters
        $statuses = TicketStatus::all();
        $priorities = Priority::all();
        $categories = Category::all();

        $ticketYears = Ticket::selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        if ($ticketYears->isEmpty()) {
            $ticketYears = collect([now()->year]);
        }

        $monthList = [
            'all' => 'All Months',
            '1' => 'Jan', '2' => 'Feb', '3' => 'Mar', '4' => 'Apr',
            '5' => 'May', '6' => 'Jun', '7' => 'Jul', '8' => 'Aug',
            '9' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec',
        ];

        // Chart Filter Callback (Supports per-chart filter fallback to global filter)
        $chartFilter = function ($query, string $yearParam, string $monthParam) use ($request, $filterYear, $filterMonth, $applyGlobalFilters) {
            $query = $applyGlobalFilters($query);

            $year = $request->get($yearParam);
            $month = $request->get($monthParam);

            if ($year && $year !== 'all') {
                $query->whereRaw('YEAR(created_at) = ?', [$year]);
            }
            if ($month && $month !== 'all') {
                $query->whereRaw('MONTH(created_at) = ?', [$month]);
            }
            return $query;
        };

        // 1. Monthly ticket trend
        $trendYear = (string) $request->get('trend_year', (!empty($filterYear) && $filterYear !== 'all') ? $filterYear : ($ticketYears->first() ?? now()->year));
        $trendQuery = $applyGlobalFilters(Ticket::selectRaw('MONTH(created_at) as m, count(*) as count')->whereRaw('YEAR(created_at) = ?', [$trendYear]));
        $monthlyCounts = $trendQuery->groupBy('m')->pluck('count', 'm');

        $trendMonthNames = [];
        $trendCounts = [];
        for ($m = 1; $m <= 12; $m++) {
            $trendMonthNames[] = Carbon::create()->month($m)->format('M');
            $trendCounts[] = (int) ($monthlyCounts[$m] ?? 0);
        }
        $trendChart = ['labels' => $trendMonthNames, 'data' => $trendCounts];

        // 2. Tickets by Status (donut chart)
        $statusYear = (string) $request->get('status_year', 'all');
        $statusMonth = (string) $request->get('status_month', 'all');
        $statusQuery = Ticket::select('status_id', DB::raw('count(*) as count'));
        $statusQuery = $chartFilter($statusQuery, 'status_year', 'status_month');
        $ticketsByStatus = $statusQuery->groupBy('status_id')->pluck('count', 'status_id');

        $statusesKeyed = $statuses->keyBy('id');
        $palette = ['#3b82f6', '#22c55e', '#f97316', '#06b6d4', '#8b5cf6', '#ef4444'];
        $colors = [];
        $i = 0;
        foreach ($ticketsByStatus->keys() as $id) {
            $statusObj = $statusesKeyed[$id] ?? null;
            $color = $this->statusColor($statusObj?->color ?? '', $statusObj?->name ?? '');
            if ($color === '#64748b') {
                $color = $palette[$i % count($palette)];
            }
            $colors[] = $color;
            $i++;
        }

        $chartStatusData = [
            'labels' => $ticketsByStatus->keys()->map(fn($id) => $statusesKeyed[$id]?->name ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByStatus->values()->toArray(),
            'colors' => $colors,
        ];

        // 3. Tickets by Priority
        $priorityYear = (string) $request->get('priority_year', 'all');
        $priorityMonth = (string) $request->get('priority_month', 'all');
        $priorityQuery = Ticket::select('priority_id', DB::raw('count(*) as count'));
        $priorityQuery = $chartFilter($priorityQuery, 'priority_year', 'priority_month');
        $ticketsByPriority = $priorityQuery->groupBy('priority_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->pluck('count', 'priority_id');

        $prioritiesKeyed = $priorities->keyBy('id');
        $priorityChart = [
            'labels' => $ticketsByPriority->keys()->map(fn($id) => $prioritiesKeyed[$id]?->name ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByPriority->values()->toArray(),
        ];

        // 4. Top categories
        $categoryYear = (string) $request->get('category_year', 'all');
        $categoryMonth = (string) $request->get('category_month', 'all');
        $catQuery = Ticket::select('category_id', DB::raw('count(*) as count'))->whereNotNull('category_id');
        $catQuery = $chartFilter($catQuery, 'category_year', 'category_month');
        $ticketsByCategory = $catQuery->groupBy('category_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->pluck('count', 'category_id');

        $categoryNames = Category::whereIn('id', $ticketsByCategory->keys())->pluck('name', 'id');
        $categoryChart = [
            'labels' => $ticketsByCategory->keys()->map(fn($id) => $categoryNames[$id] ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByCategory->values()->toArray(),
        ];

        // 5. Top requesters
        $requesterYear = (string) $request->get('requester_year', 'all');
        $requesterMonth = (string) $request->get('requester_month', 'all');
        $reqQuery = Ticket::select('requester_user_id', DB::raw('count(*) as count'))->whereNotNull('requester_user_id');
        $reqQuery = $chartFilter($reqQuery, 'requester_year', 'requester_month');
        $ticketsByUser = $reqQuery->groupBy('requester_user_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->pluck('count', 'requester_user_id');

        $userNames = User::whereIn('id', $ticketsByUser->keys())->pluck('name', 'id');
        $requesterChart = [
            'labels' => $ticketsByUser->keys()->map(fn($id) => $userNames[$id] ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByUser->values()->toArray(),
        ];

        // 6. Ticket type distribution
        $typeYear = (string) $request->get('type_year', 'all');
        $typeMonth = (string) $request->get('type_month', 'all');
        $typeQuery = Ticket::select('ticket_type_id', DB::raw('count(*) as count'))->whereNotNull('ticket_type_id');
        $typeQuery = $chartFilter($typeQuery, 'type_year', 'type_month');
        $ticketsByType = $typeQuery->groupBy('ticket_type_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->pluck('count', 'ticket_type_id');

        $ticketTypes = TicketType::all()->keyBy('id');
        $typeChart = [
            'labels' => $ticketsByType->keys()->map(fn($id) => $ticketTypes[$id]?->name ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByType->values()->toArray(),
        ];

        // Staff workload
        $workloadQuery = $applyGlobalFilters(Ticket::select('assigned_to_id', DB::raw('count(*) as count'))->whereNotNull('assigned_to_id')->whereIn('status_id', $openStatusIds));
        $workload = $workloadQuery->groupBy('assigned_to_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(8)
            ->get();
        $staffNames = User::whereIn('id', $workload->pluck('assigned_to_id'))->pluck('name', 'id');

        // Top problematic assets
        $problemAssetsQuery = $applyGlobalFilters(Ticket::select('asset_id', DB::raw('count(*) as count'))->whereNotNull('asset_id'));
        $problemAssets = $problemAssetsQuery->groupBy('asset_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->get();
        $assetDetails = Asset::whereIn('id', $problemAssets->pluck('asset_id'))->get()->keyBy('id');

        // Active filters count for UI badge indicator
        $activeFiltersCount = collect([$filterStatusId, $filterPriorityId, $filterCategoryId, $filterYear, $filterMonth])
            ->filter(fn($v) => !empty($v) && $v !== 'all')
            ->count();

        return view('pages.dashboard.tickets', compact(
            'userName',
            'randomGreeting',
            'totalTickets',
            'totalTicketsTrend',
            'openTickets',
            'openTicketsTrend',
            'unassigned',
            'unassignedTrend',
            'resolved',
            'resolvedTrend',
            'avgResolution',
            'myTickets',
            'myOpenTickets',
            'recentTickets',
            'unassignedTickets',
            'chartStatusData',
            'trendChart',
            'priorityChart',
            'categoryChart',
            'requesterChart',
            'workload',
            'staffNames',
            'problemAssets',
            'assetDetails',
            'typeChart',
            'statuses',
            'priorities',
            'categories',
            'ticketYears',
            'monthList',
            'filterStatusId',
            'filterPriorityId',
            'filterCategoryId',
            'filterYear',
            'filterMonth',
            'activeFiltersCount',
            'trendYear',
            'statusYear',
            'statusMonth',
            'priorityYear',
            'priorityMonth',
            'categoryYear',
            'categoryMonth',
            'requesterYear',
            'requesterMonth',
            'typeYear',
            'typeMonth'
        ));
    }

    private function trend(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function statusColor(string $color, string $name = ''): string
    {
        $color = trim(strtolower($color));
        $name = trim(strtolower($name));

        if (str_starts_with($color, '#')) {
            return $color;
        }

        if (str_contains($name, 'new') || str_contains($name, 'open')) {
            return '#06b6d4'; // Cyan
        }
        if (str_contains($name, 'progress')) {
            return '#f97316'; // Orange
        }
        if (str_contains($name, 'pending') || str_contains($name, 'hold')) {
            return '#8b5cf6'; // Purple
        }
        if (str_contains($name, 'resolved') || str_contains($name, 'closed') || str_contains($name, 'done')) {
            return '#22c55e'; // Green
        }

        return match ($color) {
            'primary'   => '#3b82f6',
            'success'   => '#22c55e',
            'danger', 'error' => '#ef4444',
            'warning', 'orange' => '#f97316',
            'info', 'cyan'      => '#06b6d4',
            'purple'    => '#8b5cf6',
            default     => '#64748b',
        };
    }
}
