<?php

namespace App\Http\Controllers;

use App\Models\GaAsset;
use App\Models\GaAssetAssignment;
use App\Models\GaMaintenanceLog;
use App\Models\GaAssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GaDashboardController extends Controller
{
    public function index()
    {
        // 1. Core Metrics
        $totalAssets = GaAsset::sum('qty');
        
        $totalAssetValue = GaAsset::all()->sum(function ($asset) {
            return $asset->total_asset_value * $asset->qty;
        });

        $activeAssignments = GaAssetAssignment::whereNull('returned_at')->count();
        $pendingMaintenance = GaMaintenanceLog::whereNull('completed_date')->count();

        // 2. Status Chart Data (Donut)
        $statusesRaw = GaAsset::select('status', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('status')
            ->get();

        $statusChart = [
            'labels' => $statusesRaw->pluck('status'),
            'data' => $statusesRaw->pluck('total_qty')->map(fn($val) => (int)$val),
            'colors' => $statusesRaw->map(function ($item) {
                return match (strtolower($item->status)) {
                    'available' => '#12B76A', // green
                    'deployed' => '#465FFF',  // blue
                    'maintenance' => '#F79009', // orange
                    'retired' => '#F04438',    // red
                    default => '#98A2B3'       // gray
                };
            })
        ];

        // 3. Category Chart Data (Bar/Pie/Donut)
        $categoriesRaw = GaAsset::with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($group) {
                return $group->sum('qty');
            });

        $categoryChart = [
            'labels' => $categoriesRaw->keys(),
            'data' => $categoriesRaw->values()->map(fn($val) => (int)$val),
        ];

        // 4. Spend Trend / Purchase Timeline (Area Chart)
        $purchaseTimelineRaw = GaAsset::whereNotNull('purchase_date')
            ->orderBy('purchase_date', 'asc')
            ->get()
            ->groupBy(function ($asset) {
                return $asset->purchase_date->format('Y-m');
            })
            ->map(function ($group) {
                return [
                    'label' => $group->first()->purchase_date->format('M Y'),
                    'value' => (float) $group->sum(fn($a) => $a->total_asset_value * $a->qty)
                ];
            })
            ->values();

        $purchaseChart = [
            'labels' => $purchaseTimelineRaw->pluck('label'),
            'data' => $purchaseTimelineRaw->pluck('value'),
        ];

        // 5. Recent Assignments Table
        $recentAssignments = GaAssetAssignment::with(['asset.category', 'user'])
            ->latest('assigned_at')
            ->limit(5)
            ->get();

        // 6. Recent Maintenance Table
        $recentMaintenance = GaMaintenanceLog::with(['asset'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('pages.dashboard.ga-assets', compact(
            'totalAssets',
            'totalAssetValue',
            'activeAssignments',
            'pendingMaintenance',
            'statusChart',
            'categoryChart',
            'purchaseChart',
            'recentAssignments',
            'recentMaintenance'
        ));
    }
}
