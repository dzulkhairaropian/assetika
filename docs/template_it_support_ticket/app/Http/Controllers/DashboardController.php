<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\SlaStatus;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $openStatusIds = TicketStatus::whereIn('code', ['NEW', 'IN-PROGRESS', 'PENDING'])->pluck('id');
        $completedStatusIds = TicketStatus::where('is_closed', true)->pluck('id');
        $breachedSlaId = SlaStatus::where('code', 'BREACHED')->value('id');

        $total = Ticket::count();
        $open = Ticket::whereIn('status_id', $openStatusIds)->count();
        $breached = Ticket::where('sla_status_id', $breachedSlaId)->count();
        $unassigned = Ticket::whereNull('assigned_to_id')->count();
        $myTickets = Ticket::where('assigned_to_id', auth()->id())->count();

        $avgResolution = Ticket::whereIn('status_id', $completedStatusIds)
            ->whereNotNull('resolution_time_hours')
            ->avg('resolution_time_hours') ?? 0;

        $recentTickets = Ticket::with(['category', 'priority', 'status', 'slaStatus', 'assignedTo'])
            ->latest()
            ->limit(5)
            ->get();

        // Monthly ticket trend (last 6 months)
        $trend = Ticket::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Tickets by status (donut chart)
        $statuses = TicketStatus::all()->keyBy('id');
        $ticketsByStatus = Ticket::select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->pluck('count', 'status_id');

        // Top categories (bar chart)
        $ticketsByCategory = Ticket::select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->pluck('count', 'category_id');
        $categoryNames = Category::whereIn('id', $ticketsByCategory->keys())
            ->pluck('name', 'id');

        // Top departments (bar chart)
        $ticketsByDepartment = Ticket::select('requester_department_id', DB::raw('count(*) as count'))
            ->whereNotNull('requester_department_id')
            ->groupBy('requester_department_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->pluck('count', 'requester_department_id');
        $departmentNames = Department::whereIn('id', $ticketsByDepartment->keys())
            ->pluck('name', 'id');

        // Top requesters (bar chart)
        $ticketsByUser = Ticket::select('requester_user_id', DB::raw('count(*) as count'))
            ->whereNotNull('requester_user_id')
            ->groupBy('requester_user_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->pluck('count', 'requester_user_id');
        $userNames = User::whereIn('id', $ticketsByUser->keys())
            ->pluck('name', 'id');

        // IT Staff Workload (Assigned tickets count)
        $workload = Ticket::select('assigned_to_id', DB::raw('count(*) as count'))
            ->whereNotNull('assigned_to_id')
            ->groupBy('assigned_to_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->get();
        $itStaffNames = User::whereIn('id', $workload->pluck('assigned_to_id'))->pluck('name', 'id');

        // Top Problematic Assets (Assets with most tickets)
        $problematicAssets = Ticket::select('asset_id', DB::raw('count(*) as count'))
            ->whereNotNull('asset_id')
            ->groupBy('asset_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(5)
            ->get();
        $assetDetails = Asset::whereIn('id', $problematicAssets->pluck('asset_id'))->get()->keyBy('id');

        // Tickets by Priority
        $priorities = Priority::all()->keyBy('id');
        $ticketsByPriority = Ticket::select('priority_id', DB::raw('count(*) as count'))
            ->groupBy('priority_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->pluck('count', 'priority_id');

        $systemInfo = [
            'total_users' => User::count(),
            'total_assets' => Asset::count(),
            'last_sync' => cache()->get('assetika_last_sync_at', 'Never'),
        ];

        $metrics = [
            'total' => $total,
            'open' => $open,
            'breached' => $breached,
            'unassigned' => $unassigned,
            'my_tickets' => $myTickets,
            'avg_resolution' => round($avgResolution, 1),
        ];

        return view('dashboard', compact(
            'metrics',
            'recentTickets',
            'trend',
            'statuses',
            'ticketsByStatus',
            'ticketsByCategory',
            'categoryNames',
            'ticketsByDepartment',
            'departmentNames',
            'ticketsByUser',
            'userNames',
            'workload',
            'itStaffNames',
            'problematicAssets',
            'assetDetails',
            'priorities',
            'ticketsByPriority',
            'systemInfo',
        ));
    }
}
