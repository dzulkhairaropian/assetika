<?php

namespace App\Http\Controllers;

use App\Models\GaAsset;
use App\Models\GaAssetAssignment;
use App\Models\GaMaintenanceLog;
use App\Models\GaAssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GaDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Core Metrics
        $realTotalAssets = (int) GaAsset::sum('qty');
        $isTotalAssetsDummy = $realTotalAssets === 0;
        $totalAssets = $isTotalAssetsDummy ? 142 : $realTotalAssets;

        $realAssetValue = (float) GaAsset::all()->sum(fn($a) => $a->total_asset_value * $a->qty);
        $isTotalAssetValueDummy = $realAssetValue === 0.0;
        $totalAssetValue = $isTotalAssetValueDummy ? 850000000 : $realAssetValue;

        $realActiveAssignments = GaAssetAssignment::whereNull('returned_at')->count();
        $isActiveAssignmentsDummy = $realActiveAssignments === 0;
        $activeAssignments = $isActiveAssignmentsDummy ? 28 : $realActiveAssignments;

        $realPendingMaintenance = GaMaintenanceLog::whereNull('completed_date')->count();
        $isPendingMaintenanceDummy = $realPendingMaintenance === 0;
        $pendingMaintenance = $isPendingMaintenanceDummy ? 6 : $realPendingMaintenance;

        // Additional GA Fleet & Facilities Metrics (Dummy Placeholders)
        $totalVehicles = 12;
        $upcomingVehicleTaxes = 3;
        $activeReservations = 9;
        $monthlyMaintenanceSpend = 24500000;

        // Available years for GA asset purchase filtering
        $purchaseYears = GaAsset::whereNotNull('purchase_date')
            ->selectRaw('YEAR(purchase_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        if ($purchaseYears->isEmpty()) {
            $purchaseYears = collect([now()->year, now()->year - 1, now()->year - 2]);
        }

        $monthList = [
            'all' => 'All Months',
            '1' => 'Jan', '2' => 'Feb', '3' => 'Mar', '4' => 'Apr',
            '5' => 'May', '6' => 'Jun', '7' => 'Jul', '8' => 'Aug',
            '9' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec',
        ];

        // 2. Status Chart Data (Donut)
        $statusYear = (string) $request->get('status_year', 'all');
        $statusMonth = (string) $request->get('status_month', 'all');

        $statusQuery = GaAsset::select('status', DB::raw('SUM(qty) as total_qty'));
        if ($statusYear !== 'all') {
            $statusQuery->whereRaw('YEAR(created_at) = ?', [$statusYear]);
        }
        if ($statusMonth !== 'all') {
            $statusQuery->whereRaw('MONTH(created_at) = ?', [$statusMonth]);
        }
        $statusesRaw = $statusQuery->groupBy('status')->get();

        $isStatusChartDummy = $statusesRaw->isEmpty();
        if ($isStatusChartDummy) {
            $statusChart = [
                'labels' => ['Available', 'Deployed', 'Maintenance', 'Retired'],
                'data' => [65, 45, 18, 14],
                'colors' => ['#22c55e', '#3b82f6', '#f97316', '#ef4444']
            ];
        } else {
            $statusChart = [
                'labels' => $statusesRaw->pluck('status')->map(fn($s) => ucfirst($s))->toArray(),
                'data' => $statusesRaw->pluck('total_qty')->map(fn($val) => (int)$val)->toArray(),
                'colors' => $statusesRaw->map(function ($item) {
                    return match (strtolower($item->status)) {
                        'available', 'ready' => '#22c55e',
                        'deployed', 'in use', 'assigned' => '#3b82f6',
                        'maintenance', 'service' => '#f97316',
                        'retired', 'disposal', 'broken' => '#ef4444',
                        default => '#8b5cf6'
                    };
                })->toArray()
            ];
        }

        // 3. Category Chart Data (Donut)
        $categoryYear = (string) $request->get('category_year', 'all');
        $categoryMonth = (string) $request->get('category_month', 'all');

        $categoryQuery = GaAsset::with('category');
        if ($categoryYear !== 'all') {
            $categoryQuery->whereRaw('YEAR(created_at) = ?', [$categoryYear]);
        }
        if ($categoryMonth !== 'all') {
            $categoryQuery->whereRaw('MONTH(created_at) = ?', [$categoryMonth]);
        }

        $categoriesRaw = $categoryQuery->get()
            ->groupBy(fn($a) => $a->category->name ?? 'Uncategorized')
            ->map(fn($group) => $group->sum('qty'));

        $isCategoryChartDummy = $categoriesRaw->isEmpty();
        if ($isCategoryChartDummy) {
            $categoryChart = [
                'labels' => ['Office Furniture', 'Vehicles & Fleet', 'HVAC & Aircon', 'Facility & Safety', 'Building Fixtures'],
                'data' => [48, 24, 32, 20, 18],
            ];
        } else {
            $categoryChart = [
                'labels' => $categoriesRaw->keys()->toArray(),
                'data' => $categoriesRaw->values()->map(fn($val) => (int)$val)->toArray(),
            ];
        }

        // 4. Asset Condition Chart Data (Donut - Dummy Value)
        $conditionChart = [
            'labels' => ['Excellent', 'Good', 'Fair', 'Needs Repair', 'Critical'],
            'data' => [52, 48, 26, 12, 4],
            'colors' => ['#22c55e', '#3b82f6', '#06b6d4', '#f97316', '#ef4444']
        ];

        // 5. Spend Trend / Purchase Timeline (Line Chart)
        $selectedYear = (string) $request->get('purchase_year', $purchaseYears->first() ?? now()->year);

        $monthlySpend = GaAsset::whereNotNull('purchase_date')
            ->whereRaw('YEAR(purchase_date) = ?', [$selectedYear])
            ->get()
            ->groupBy(fn($a) => (int) $a->purchase_date->format('m'))
            ->map(fn($group) => $group->sum(fn($a) => $a->total_asset_value * $a->qty));

        $purchaseLabels = [];
        $purchaseData = [];
        $sampleMonthlyData = [45000000, 32000000, 78000000, 25000000, 92000000, 60000000, 48000000, 115000000, 38000000, 54000000, 82000000, 67000000];

        $isPurchaseChartDummy = array_sum($monthlySpend->toArray()) === 0;
        for ($m = 1; $m <= 12; $m++) {
            $purchaseLabels[] = Carbon::create()->month($m)->format('M');
            $val = (float) ($monthlySpend[$m] ?? 0);
            $purchaseData[] = $val > 0 ? $val : $sampleMonthlyData[$m - 1];
        }

        $purchaseChart = [
            'labels' => $purchaseLabels,
            'data' => $purchaseData,
        ];

        // 6. Maintenance Cost by Category (Bar Chart - Dummy Value)
        $maintCategoryChart = [
            'labels' => ['AC & HVAC Servicing', 'Vehicle Maintenance', 'Plumbing & Water', 'Electrical & Generator', 'Building & Paint Work'],
            'data' => [18500000, 34000000, 8500000, 22000000, 14200000],
        ];

        // 7. Recent Assignments Table Data
        $recentAssignments = GaAssetAssignment::with(['asset.category', 'user'])
            ->latest('assigned_at')
            ->limit(5)
            ->get();

        // 8. Recent Maintenance Table Data
        $recentMaintenance = GaMaintenanceLog::with(['asset'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        // 9. Vehicle Fleet & Tax Expiry Tracker (Dummy Value)
        $vehicleFleet = [
            ['name' => 'Toyota Avanza 1.5 G', 'plate' => 'B 1284 PQA', 'type' => 'Operational Car', 'tax_expiry' => 'Sep 15, 2026', 'status' => 'Active', 'status_color' => 'success'],
            ['name' => 'Honda Blind Van', 'plate' => 'B 9031 WQK', 'type' => 'Logistics Van', 'tax_expiry' => 'Oct 02, 2026', 'status' => 'Active', 'status_color' => 'success'],
            ['name' => 'Mitsubishi L300 Box', 'plate' => 'B 9412 SF', 'type' => 'Delivery Truck', 'tax_expiry' => 'Aug 29, 2026', 'status' => 'Tax Due Soon', 'status_color' => 'warning'],
            ['name' => 'Honda Vario 160', 'plate' => 'B 4102 TZR', 'type' => 'Messenger Bike', 'tax_expiry' => 'Nov 18, 2026', 'status' => 'Active', 'status_color' => 'success'],
            ['name' => 'Toyota Innova Zenix', 'plate' => 'B 1001 RFS', 'type' => 'Executive Car', 'tax_expiry' => 'Dec 12, 2026', 'status' => 'Active', 'status_color' => 'success'],
        ];

        // 10. Active Facility Reservations (Dummy Value)
        $facilityReservations = [
            ['facility' => 'Main Conference Room (Lt. 3)', 'reserved_by' => 'Anita Rahmawati (HR)', 'date' => 'Today, 14:00 - 16:00', 'purpose' => 'Townhall Meeting', 'status' => 'Confirmed'],
            ['facility' => 'Executive Boardroom', 'reserved_by' => 'Budi Santoso (Director)', 'date' => 'Tomorrow, 09:00 - 12:00', 'purpose' => 'Quarterly Review', 'status' => 'Confirmed'],
            ['facility' => 'Operational Van B 9031 WQK', 'reserved_by' => 'Dedi Kurniawan (GA)', 'date' => 'Aug 28, 08:00 - 17:00', 'purpose' => 'Branch Audit Visit', 'status' => 'In Use'],
            ['facility' => 'Training Center Lab 1', 'reserved_by' => 'Siti Nurhaliza (Training)', 'date' => 'Aug 29, 09:00 - 15:00', 'purpose' => 'New Hire Onboarding', 'status' => 'Scheduled'],
        ];

        $userName = auth()->check() ? auth()->user()->name : 'User';
        $greetings = [
            'Here is the latest overview of your General Affairs assets and facilities.',
            'Track GA inventory, assignments, vehicles, and scheduled maintenance.',
            'Here is what is happening across your GA facilities today.'
        ];
        $randomGreeting = $greetings[array_rand($greetings)];

        return view('pages.dashboard.ga-assets', compact(
            'userName',
            'randomGreeting',
            'totalAssets',
            'isTotalAssetsDummy',
            'totalAssetValue',
            'isTotalAssetValueDummy',
            'activeAssignments',
            'isActiveAssignmentsDummy',
            'pendingMaintenance',
            'isPendingMaintenanceDummy',
            'totalVehicles',
            'upcomingVehicleTaxes',
            'activeReservations',
            'facilityReservations',
            'monthlyMaintenanceSpend',
            'statusChart',
            'isStatusChartDummy',
            'categoryChart',
            'isCategoryChartDummy',
            'conditionChart',
            'purchaseChart',
            'isPurchaseChartDummy',
            'maintCategoryChart',
            'recentAssignments',
            'recentMaintenance',
            'vehicleFleet',
            'purchaseYears',
            'selectedYear',
            'monthList',
            'statusYear',
            'statusMonth',
            'categoryYear',
            'categoryMonth'
        ));
    }
}
