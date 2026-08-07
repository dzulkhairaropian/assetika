<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Priority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Http\Request;
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

        $startOfMonth = now()->startOfMonth();

        $openStatusIds = TicketStatus::where('is_closed', false)->pluck('id')->toArray();
        $completedStatusIds = TicketStatus::where('is_closed', true)->pluck('id')->toArray();

        $totalTickets = Ticket::count();
        $totalTicketsPrev = Ticket::where('created_at', '<', $startOfMonth)->count();
        $totalTicketsTrend = $this->trend($totalTickets, $totalTicketsPrev);

        $openTickets = Ticket::whereIn('status_id', $openStatusIds)->count();
        $openTicketsPrev = Ticket::whereIn('status_id', $openStatusIds)
            ->where('created_at', '<', $startOfMonth)->count();
        $openTicketsTrend = $this->trend($openTickets, $openTicketsPrev);

        $unassigned = Ticket::whereNull('assigned_to_id')->count();
        $unassignedPrev = Ticket::whereNull('assigned_to_id')
            ->where('created_at', '<', $startOfMonth)->count();
        $unassignedTrend = $this->trend($unassigned, $unassignedPrev);

        $resolved = Ticket::whereIn('status_id', $completedStatusIds)->count();
        $resolvedPrev = Ticket::whereIn('status_id', $completedStatusIds)
            ->where('created_at', '<', $startOfMonth)->count();
        $resolvedTrend = $this->trend($resolved, $resolvedPrev);

        $avgResolution = Ticket::whereIn('status_id', $completedStatusIds)
            ->whereNotNull('resolution_time_hours')
            ->avg('resolution_time_hours') ?? 0;

        $myTickets = Ticket::where('assigned_to_id', $user->id)->count();
        $myOpenTickets = Ticket::where('assigned_to_id', $user->id)
            ->whereIn('status_id', $openStatusIds)->count();

        $recentTickets = Ticket::with(['priority', 'status', 'assignedTo', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        $unassignedTickets = Ticket::with(['priority', 'category'])
            ->whereNull('assigned_to_id')
            ->whereIn('status_id', $openStatusIds)
            ->latest()
            ->limit(5)
            ->get();

        // Tickets by Status (donut chart)
        $ticketsByStatus = Ticket::select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->pluck('count', 'status_id');
        $statuses = TicketStatus::all()->keyBy('id');
        $chartStatusData = [
            'labels' => $ticketsByStatus->keys()->map(fn($id) => $statuses[$id]?->name ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByStatus->values()->toArray(),
            'colors' => $ticketsByStatus->keys()->map(fn($id) => $this->statusColor($statuses[$id]?->color ?? 'gray'))->values()->toArray(),
        ];

        // Monthly ticket trend (area chart)
        $trendData = Ticket::select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');
        $monthNames = [];
        $monthsCount = [];
        $cursor = now()->subMonths(5)->startOfMonth();
        for ($i = 0; $i < 6; $i++) {
            $key = $cursor->format('Y-m');
            $monthNames[] = $cursor->format('M');
            $monthsCount[] = $trendData[$key] ?? 0;
            $cursor->addMonth();
        }
        $trendChart = ['labels' => $monthNames, 'data' => $monthsCount];

        // Tickets by Priority
        $ticketsByPriority = Ticket::select('priority_id', DB::raw('count(*) as count'))
            ->groupBy('priority_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->pluck('count', 'priority_id');
        $priorities = Priority::all()->keyBy('id');
        $priorityChart = [
            'labels' => $ticketsByPriority->keys()->map(fn($id) => $priorities[$id]?->name ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByPriority->values()->toArray(),
        ];

        // Top categories
        $ticketsByCategory = Ticket::select('category_id', DB::raw('count(*) as count'))
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->pluck('count', 'category_id');
        $categoryNames = \App\Models\Category::whereIn('id', $ticketsByCategory->keys())->pluck('name', 'id');
        $categoryChart = [
            'labels' => $ticketsByCategory->keys()->map(fn($id) => $categoryNames[$id] ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByCategory->values()->toArray(),
        ];

        // Top requesters
        $ticketsByUser = Ticket::select('requester_user_id', DB::raw('count(*) as count'))
            ->whereNotNull('requester_user_id')
            ->groupBy('requester_user_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->pluck('count', 'requester_user_id');
        $userNames = User::whereIn('id', $ticketsByUser->keys())->pluck('name', 'id');
        $requesterChart = [
            'labels' => $ticketsByUser->keys()->map(fn($id) => $userNames[$id] ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByUser->values()->toArray(),
        ];

        // Staff workload
        $workload = Ticket::select('assigned_to_id', DB::raw('count(*) as count'))
            ->whereNotNull('assigned_to_id')
            ->whereIn('status_id', $openStatusIds)
            ->groupBy('assigned_to_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(8)
            ->get();
        $staffNames = User::whereIn('id', $workload->pluck('assigned_to_id'))->pluck('name', 'id');

        // Top problematic assets
        $problemAssets = Ticket::select('asset_id', DB::raw('count(*) as count'))
            ->whereNotNull('asset_id')
            ->groupBy('asset_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->get();
        $assetDetails = Asset::whereIn('id', $problemAssets->pluck('asset_id'))->get()->keyBy('id');

        // Ticket type distribution
        $ticketsByType = Ticket::select('ticket_type_id', DB::raw('count(*) as count'))
            ->whereNotNull('ticket_type_id')
            ->groupBy('ticket_type_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->pluck('count', 'ticket_type_id');
        $ticketTypes = TicketType::all()->keyBy('id');
        $typeChart = [
            'labels' => $ticketsByType->keys()->map(fn($id) => $ticketTypes[$id]?->name ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByType->values()->toArray(),
        ];

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
        ));
    }

    private function trend(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function statusColor(string $color): string
    {
        return match ($color) {
            'info' => '#465FFF',
            'warning' => '#F79009',
            'success' => '#12B76A',
            'danger' => '#F04438',
            'gray' => '#667085',
            default => '#667085',
        };
    }
}
