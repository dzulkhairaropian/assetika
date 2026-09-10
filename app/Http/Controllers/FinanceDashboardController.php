<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Core Financial Stat Metrics
        $totalPurchaseCost = (float) Asset::whereNotNull('purchase_cost')->sum('purchase_cost');

        $assetsWithDepr = Asset::with('depreciation')->whereNotNull('purchase_cost')->get();
        $totalBookValue = (float) $assetsWithDepr->sum(fn ($a) => $a->book_value ?? $a->purchase_cost);
        $totalDepreciated = max(0, $totalPurchaseCost - $totalBookValue);

        $totalAssetsWithCost = Asset::whereNotNull('purchase_cost')->where('purchase_cost', '>', 0)->count();
        $avgAssetCost = $totalAssetsWithCost > 0 ? ($totalPurchaseCost / $totalAssetsWithCost) : 0;

        // 2. Acquisition Spend Timeline Chart (Filtered by Year)
        $purchaseYears = Asset::whereNotNull('purchase_date')
            ->selectRaw('YEAR(purchase_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $selectedYear = (string) $request->get('purchase_year', $purchaseYears->first() ?? now()->year);

        $monthlySpend = Asset::whereNotNull('purchase_date')
            ->whereRaw('YEAR(purchase_date) = ?', [$selectedYear])
            ->whereNotNull('purchase_cost')
            ->selectRaw('MONTH(purchase_date) as month, SUM(purchase_cost) as total_spend, COUNT(id) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $spendTimelineLabels = [];
        $spendTimelineData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create()->month($m)->format('M');
            $spendTimelineLabels[] = $monthName;
            $spendTimelineData[] = (float) ($monthlySpend->get($m)->total_spend ?? 0);
        }

        $spendTimelineChartData = [
            'labels' => $spendTimelineLabels,
            'data' => $spendTimelineData,
        ];

        // 3. Purchase Cost Distribution by Category (Top 5 Categories)
        $costByCategory = Category::query()
            ->select('categories.id', 'categories.name')
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('assets', 'assets.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM(assets.purchase_cost), 0) as total_cost')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        $categoryChartData = [
            'labels' => $costByCategory->pluck('name')->toArray(),
            'data' => $costByCategory->pluck('total_cost')->map(fn ($v) => (float) $v)->toArray(),
        ];

        // 4. Asset Value by Department (Top 5 Departments)
        $costByDepartment = Department::query()
            ->select('departments.id', 'departments.name')
            ->leftJoin('assets', 'assets.department_id', '=', 'departments.id')
            ->selectRaw('COALESCE(SUM(assets.purchase_cost), 0) as total_cost')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        $departmentChartData = [
            'labels' => $costByDepartment->pluck('name')->toArray(),
            'data' => $costByDepartment->pluck('total_cost')->map(fn ($v) => (float) $v)->toArray(),
        ];

        // 5. Recent High-Value Asset Purchases (Top 5 Highest Cost Assets)
        $recentHighValueAssets = Asset::with(['product.brand', 'department', 'vendor'])
            ->whereNotNull('purchase_cost')
            ->orderByDesc('purchase_cost')
            ->limit(5)
            ->get();

        // 6. Fully Depreciated Assets (Book Value == 0 or elapsed >= months)
        $fullyDepreciatedAssets = $assetsWithDepr->filter(fn ($a) => $a->book_value !== null && $a->book_value <= 0)->take(5);

        return view('pages.dashboard.finance', compact(
            'totalPurchaseCost',
            'totalBookValue',
            'totalDepreciated',
            'avgAssetCost',
            'purchaseYears',
            'selectedYear',
            'spendTimelineChartData',
            'categoryChartData',
            'departmentChartData',
            'recentHighValueAssets',
            'fullyDepreciatedAssets'
        ));
    }
}
