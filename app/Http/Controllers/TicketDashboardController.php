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

        $palette = ['#3b82f6', '#22c55e', '#f97316', '#06b6d4', '#8b5cf6', '#ef4444'];
        $colors = [];
        $i = 0;
        foreach ($ticketsByStatus->keys() as $id) {
            $statusObj = $statuses[$id] ?? null;
            $color = $this->statusColor($statusObj?->color ?? '', $statusObj?->name ?? '');
            if ($color === '#64748b') {
                $color = $palette[$i % count($palette)];
            }
            $colors[] = $color;
            $i++;
        }

        $chartStatusData = [
            'labels' => $ticketsByStatus->keys()->map(fn($id) => $statuses[$id]?->name ?? 'Unknown')->values()->toArray(),
            'data' => $ticketsByStatus->values()->toArray(),
            'colors' => $colors,
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

    private function statusColor(string $color, string $name = ''): string
    {
        $color = trim(strtolower($color));
        $name = trim(strtolower($name));

        if (str_starts_with($color, '#')) {
            return $color;
        }

        // Map by status name first if available
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
