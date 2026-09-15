<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ConsumableRequestItem;
use App\Models\ConsumableTransaction;
use App\Models\Department;
use App\Models\License;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private function calculateTrend($current, $previous)
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function index()
    {
        $startOfMonth = now()->startOfMonth();
        $cacheTtl = 60; // Cache heavy aggregation for 60 seconds

        $metrics = Cache::remember('dashboard_metrics_summary', $cacheTtl, function () use ($startOfMonth) {
            $totalAssets = Asset::count();
            $totalAssetsPrev = Asset::where('created_at', '<', $startOfMonth)->count();

            $readyToDeploy = Asset::whereHas('status', fn ($q) => $q->where('name', 'Ready to Deploy'))->count();
            $readyToDeployPrev = Asset::whereHas('status', fn ($q) => $q->where('name', 'Ready to Deploy'))->where('created_at', '<', $startOfMonth)->count();

            $deployed = Asset::whereHas('status', fn ($q) => $q->where('name', 'Deployed'))->count();
            $deployedPrev = Asset::whereHas('status', fn ($q) => $q->where('name', 'Deployed'))->where('created_at', '<', $startOfMonth)->count();

            $broken = Asset::whereHas('status', fn ($q) => $q->where('name', 'Broken'))->count();
            $brokenPrev = Asset::whereHas('status', fn ($q) => $q->where('name', 'Broken'))->where('created_at', '<', $startOfMonth)->count();

            $brokenDisposal = Asset::whereHas('status', fn ($q) => $q->whereIn('name', ['Broken', 'Retired']))->count();
            $brokenDisposalPrev = Asset::whereHas('status', fn ($q) => $q->whereIn('name', ['Broken', 'Retired']))->where('created_at', '<', $startOfMonth)->count();

            $service = Asset::whereHas('status', fn ($q) => $q->where('name', 'Service'))->count();
            $servicePrev = Asset::whereHas('status', fn ($q) => $q->where('name', 'Service'))->where('created_at', '<', $startOfMonth)->count();

            $readyForSale = Asset::whereHas('status', fn ($q) => $q->whereIn('name', ['Ready for Sale', 'For Sale', 'Available for Sale']))->count();
            $readyForSalePrev = Asset::whereHas('status', fn ($q) => $q->whereIn('name', ['Ready for Sale', 'For Sale', 'Available for Sale']))->where('created_at', '<', $startOfMonth)->count();

            return [
                'totalAssets' => $totalAssets,
                'totalAssetsTrend' => $this->calculateTrend($totalAssets, $totalAssetsPrev),
                'readyToDeploy' => $readyToDeploy,
                'readyToDeployTrend' => $this->calculateTrend($readyToDeploy, $readyToDeployPrev),
                'deployed' => $deployed,
                'deployedTrend' => $this->calculateTrend($deployed, $deployedPrev),
                'broken' => $broken,
                'brokenTrend' => $this->calculateTrend($broken, $brokenPrev),
                'brokenDisposal' => $brokenDisposal,
                'brokenDisposalTrend' => $this->calculateTrend($brokenDisposal, $brokenDisposalPrev),
                'service' => $service,
                'serviceTrend' => $this->calculateTrend($service, $servicePrev),
                'readyForSale' => $readyForSale,
                'readyForSaleTrend' => $this->calculateTrend($readyForSale, $readyForSalePrev),
            ];
        });

        extract($metrics);

        $totalConsumables = Consumable::sum('quantity');
        $consumablesBreakdown = Consumable::with(['product.brand', 'product.category'])
            ->orderBy('quantity', 'desc')
            ->limit(5)
            ->get();

        $recentCheckouts = AssetCheckout::with(['asset.product.brand', 'asset.department', 'asset.status', 'checkedOutToUser.department'])
            ->latest('checked_out_at')
            ->limit(5)
            ->get();

        // Recent Consumable Checkouts
        $recentConsumableCheckouts = ConsumableTransaction::with(['consumable.product', 'checkedOutToUser'])
            ->where('type', 'checkout')
            ->latest('transaction_date')
            ->limit(5)
            ->get();

        // Chart Data 1: Assets by Status
        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();

        // Categories separated by Master Category
        $itAssetMaster = MasterCategory::where('name', 'like', 'IT Asset%')->first();
        $categories = collect();
        if ($itAssetMaster) {
            $categories = Category::where('master_category_id', $itAssetMaster->id)->orderBy('name')->get();
        }
        if ($categories->isEmpty()) {
            $categories = Category::orderBy('name')->get();
        }

        $consumableMaster = MasterCategory::where('name', 'like', '%Consumable%')->first();
        $consumableCategories = collect();
        if ($consumableMaster) {
            $consumableCategories = Category::where('master_category_id', $consumableMaster->id)->orderBy('name')->get();
        }
        if ($consumableCategories->isEmpty()) {
            $consumableCategories = $categories;
        }

        // Chart Data 1: Status Distribution with Category filter
        $chartDataByCategory = [];
        $chartDataByCategory['all'] = [
            'labels' => $statuses->pluck('name')->toArray(),
            'data' => $statuses->map(fn ($status) => Asset::where('status_id', $status->id)->count())->toArray(),
            'colors' => $statuses->map(function ($status) {
                return match ($status->name) {
                    'Ready to Deploy' => '#12b76a',
                    'Deployed' => '#465fff',
                    'Borrowed' => '#8b5cf6',
                    'Service' => '#f79009',
                    'Draft' => '#06b6d4',
                    'Broken' => '#f04438',
                    'Retired' => '#64748b',
                    default => '#98a2b3'
                };
            })->toArray(),
        ];

        foreach ($categories as $cat) {
            $chartDataByCategory[$cat->id] = [
                'labels' => $statuses->pluck('name')->toArray(),
                'data' => $statuses->map(fn ($status) => Asset::where('status_id', $status->id)->whereHas('product', fn ($q) => $q->where('category_id', $cat->id))->count())->toArray(),
                'colors' => $chartDataByCategory['all']['colors'],
            ];
        }

        $chartData = $chartDataByCategory['all'];

        // Chart Data 2: Department with most assets by Category
        $deptDataByCategory = [];

        $deptOverall = Department::withCount('assets')
            ->orderBy('assets_count', 'desc')
            ->limit(5)
            ->get();

        $deptDataByCategory['all'] = [
            'labels' => $deptOverall->pluck('name')->toArray(),
            'data' => $deptOverall->pluck('assets_count')->toArray(),
        ];

        foreach ($categories as $cat) {
            $deptCat = Department::withCount(['assets' => function ($q) use ($cat) {
                $q->whereHas('product', fn ($pr) => $pr->where('category_id', $cat->id));
            }])
                ->orderBy('assets_count', 'desc')
                ->limit(5)
                ->get();

            $deptDataByCategory[$cat->id] = [
                'labels' => $deptCat->pluck('name')->toArray(),
                'data' => $deptCat->pluck('assets_count')->toArray(),
            ];
        }

        $deptChartData = $deptDataByCategory['all'];

        $productDataByCategory = [];

        // All Categories
        $overallProducts = Product::withCount('assets')
            ->orderBy('assets_count', 'desc')
            ->limit(5)
            ->get();

        $productDataByCategory['all'] = [
            'labels' => $overallProducts->pluck('name')->toArray(),
            'data' => $overallProducts->pluck('assets_count')->toArray(),
        ];

        // Group by category
        foreach ($categories as $cat) {
            $catProducts = Product::where('category_id', $cat->id)
                ->withCount('assets')
                ->orderBy('assets_count', 'desc')
                ->limit(5)
                ->get();

            $productDataByCategory[$cat->id] = [
                'labels' => $catProducts->pluck('name')->toArray(),
                'data' => $catProducts->pluck('assets_count')->toArray(),
            ];
        }

        // Chart Data 4: Monthly Purchases by Release Date / Purchase Date
        $purchaseYears = Asset::where(function ($q) {
            $q->whereNotNull('release_date')->orWhereNotNull('purchase_date');
        })
            ->selectRaw('YEAR(COALESCE(release_date, purchase_date)) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (string) $year)
            ->values();

        $purchaseYear = request('purchase_year');
        if (! $purchaseYear || ! $purchaseYears->contains($purchaseYear)) {
            $purchaseYear = $purchaseYears->first() ?? (string) now()->year;
        }

        $purchaseCategoryId = request('purchase_category_id');

        $baseTimelineQuery = Asset::where(function ($q) {
            $q->whereNotNull('release_date')->orWhereNotNull('purchase_date');
        })
            ->whereRaw('YEAR(COALESCE(release_date, purchase_date)) = ?', [$purchaseYear]);

        if ($purchaseCategoryId && $purchaseCategoryId !== 'all') {
            $baseTimelineQuery->whereHas('product', function ($q) use ($purchaseCategoryId) {
                $q->where('category_id', $purchaseCategoryId);
            });
        }

        $monthlyPurchasesRaw = $baseTimelineQuery
            ->get()
            ->groupBy(function ($asset) {
                $date = $asset->release_date ?? $asset->purchase_date;

                return $date->format('Y-m');
            })
            ->map(function ($group) {
                $date = $group->first()->release_date ?? $group->first()->purchase_date;

                return [
                    'label' => $date->format('M Y'),
                    'count' => $group->count(),
                ];
            })
            ->sortKeys()
            ->values();

        $purchaseChartData = [
            'labels' => $monthlyPurchasesRaw->pluck('label'),
            'data' => $monthlyPurchasesRaw->pluck('count'),
        ];

        // Also pre-build category data per purchase year for instant client-side category switching
        $purchaseDataByCategory = [];
        $allYearAssets = Asset::with('product')
            ->where(function ($q) {
                $q->whereNotNull('release_date')->orWhereNotNull('purchase_date');
            })
            ->whereRaw('YEAR(COALESCE(release_date, purchase_date)) = ?', [$purchaseYear])
            ->get();

        $monthlyAll = $allYearAssets
            ->groupBy(fn ($a) => ($a->release_date ?? $a->purchase_date)->format('Y-m'))
            ->map(fn ($g) => ['label' => ($g->first()->release_date ?? $g->first()->purchase_date)->format('M Y'), 'count' => $g->count()])
            ->sortKeys()->values();

        $purchaseDataByCategory['all'] = [
            'labels' => $monthlyAll->pluck('label')->toArray(),
            'data' => $monthlyAll->pluck('count')->toArray(),
        ];

        foreach ($categories as $cat) {
            $catAssets = $allYearAssets->filter(fn ($a) => $a->product?->category_id == $cat->id);
            $monthlyCat = $catAssets
                ->groupBy(fn ($a) => ($a->release_date ?? $a->purchase_date)->format('Y-m'))
                ->map(fn ($g) => ['label' => ($g->first()->release_date ?? $g->first()->purchase_date)->format('M Y'), 'count' => $g->count()])
                ->sortKeys()->values();

            $purchaseDataByCategory[$cat->id] = [
                'labels' => $monthlyCat->pluck('label')->toArray(),
                'data' => $monthlyCat->pluck('count')->toArray(),
            ];
        }

        $purchaseChartYears = $purchaseYears->toArray();
        $purchaseChartYear = $purchaseYear;

        // Chart Data 5: Consumables most requested (Top 5) with Consumable Category filter
        $consumableDataByCategory = [];

        $requestedConsumables = ConsumableRequestItem::with(['consumable.product'])
            ->select('consumable_id', \DB::raw('sum(quantity_requested) as total_requested'))
            ->groupBy('consumable_id')
            ->orderBy('total_requested', 'desc')
            ->limit(5)
            ->get();

        if ($requestedConsumables->isEmpty() || $requestedConsumables->sum('total_requested') == 0) {
            // Fallback: Query from ConsumableTransaction (type = checkout)
            $requestedConsumables = ConsumableTransaction::with(['consumable.product'])
                ->where('type', 'checkout')
                ->select('consumable_id', \DB::raw('sum(quantity) as total_requested'))
                ->groupBy('consumable_id')
                ->orderBy('total_requested', 'desc')
                ->limit(5)
                ->get();
        }

        $consumableDataByCategory['all'] = [
            'labels' => $requestedConsumables->map(fn ($item) => $item->consumable->product->name ?? 'Unknown Consumable')->toArray(),
            'data' => $requestedConsumables->pluck('total_requested')->map(fn ($val) => (int) $val)->toArray(),
        ];

        foreach ($consumableCategories as $cat) {
            $catConsumables = ConsumableRequestItem::with(['consumable.product'])
                ->whereHas('consumable.product', fn ($q) => $q->where('category_id', $cat->id))
                ->select('consumable_id', \DB::raw('sum(quantity_requested) as total_requested'))
                ->groupBy('consumable_id')
                ->orderBy('total_requested', 'desc')
                ->limit(5)
                ->get();

            if ($catConsumables->isEmpty() || $catConsumables->sum('total_requested') == 0) {
                $catConsumables = ConsumableTransaction::with(['consumable.product'])
                    ->where('type', 'checkout')
                    ->whereHas('consumable.product', fn ($q) => $q->where('category_id', $cat->id))
                    ->select('consumable_id', \DB::raw('sum(quantity) as total_requested'))
                    ->groupBy('consumable_id')
                    ->orderBy('total_requested', 'desc')
                    ->limit(5)
                    ->get();
            }

            $consumableDataByCategory[$cat->id] = [
                'labels' => $catConsumables->map(fn ($item) => $item->consumable->product->name ?? 'Unknown Consumable')->toArray(),
                'data' => $catConsumables->pluck('total_requested')->map(fn ($val) => (int) $val)->toArray(),
            ];
        }

        $consumableChartData = $consumableDataByCategory['all'];

        // Chart Data 6: Consumable Distribution Timeline (Line Chart)
        $consumableTxYears = ConsumableTransaction::whereNotNull('transaction_date')
            ->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($y) => (string) $y)
            ->values();

        $consumableTxYear = request('consumable_year');
        if (! $consumableTxYear || ! $consumableTxYears->contains($consumableTxYear)) {
            $consumableTxYear = $consumableTxYears->first() ?? (string) now()->year;
        }

        $allYearConsumableTx = ConsumableTransaction::with(['consumable.product'])
            ->where('type', 'checkout')
            ->whereNotNull('transaction_date')
            ->whereRaw('YEAR(transaction_date) = ?', [$consumableTxYear])
            ->get();

        $consumableTimelineByCategory = [];

        $monthlyConsumableAll = $allYearConsumableTx
            ->groupBy(fn ($t) => $t->transaction_date ? $t->transaction_date->format('Y-m') : now()->format('Y-m'))
            ->map(fn ($g) => ['label' => ($g->first()->transaction_date ?? now())->format('M Y'), 'count' => (int) $g->sum('quantity')])
            ->sortKeys()->values();

        $consumableTimelineByCategory['all'] = [
            'labels' => $monthlyConsumableAll->pluck('label')->toArray(),
            'data' => $monthlyConsumableAll->pluck('count')->toArray(),
        ];

        foreach ($consumableCategories as $cat) {
            $catTx = $allYearConsumableTx->filter(fn ($t) => $t->consumable?->product?->category_id == $cat->id);
            $monthlyCatTx = $catTx
                ->groupBy(fn ($t) => ($t->transaction_date ?? now())->format('Y-m'))
                ->map(fn ($g) => ['label' => ($g->first()->transaction_date ?? now())->format('M Y'), 'count' => (int) $g->sum('quantity')])
                ->sortKeys()->values();

            $consumableTimelineByCategory[$cat->id] = [
                'labels' => $monthlyCatTx->pluck('label')->toArray(),
                'data' => $monthlyCatTx->pluck('count')->toArray(),
            ];
        }

        $consumableTimelineChartData = $consumableTimelineByCategory['all'];
        $consumableTimelineYears = $consumableTxYears->toArray();
        $consumableTimelineYear = $consumableTxYear;

        $userName = Auth::user() ? Auth::user()->name : 'Guest';
        $greetings = [
            'Welcome back! Hope you have a productive day ahead.',
            'Great to see you again. Let\'s make today count!',
            'Ready to tackle your tasks?',
            'Good to have you back! Your dashboard awaits.',
            'Wishing you a day full of achievements.',
            'What\'s on your agenda today?',
            'Dive into your work.',
            'Here\'s to a successful day.',
            'Your IT assets are ready for management.',
            'Let\'s get things done.',
        ];
        // Store random greeting in session if not already set
        if (! session()->has('dashboard_greeting')) {
            session(['dashboard_greeting' => $greetings[array_rand($greetings)]]);
        }
        $randomGreeting = session('dashboard_greeting');

        $lowStockConsumables = Consumable::with('product')
            ->where(function ($query) {
                $query->where('quantity', '<=', 0)
                    ->orWhereRaw('quantity <= low_threshold');
            })
            ->get();

        $fourYearsAgo = Carbon::now()->subYears(4);
        $assetsOlderThan4Years = Asset::with(['checkedOutToUser.department', 'currentCheckout.checkedOutToUser.department', 'product.brand', 'department', 'status'])
            ->where(function ($query) use ($fourYearsAgo) {
                $query->where('purchase_date', '<=', $fourYearsAgo)
                    ->orWhere(function ($q) use ($fourYearsAgo) {
                        $q->whereNull('purchase_date')
                            ->where('release_date', '<=', $fourYearsAgo);
                    })
                    ->orWhere(function ($q) use ($fourYearsAgo) {
                        $q->whereNull('purchase_date')
                            ->whereNull('release_date')
                            ->where('created_at', '<=', $fourYearsAgo);
                    });
            })
            ->latest()
            ->get();

        $assetsInService = Asset::with(['product.brand', 'department'])
            ->whereHas('status', function ($q) {
                $q->where('name', 'Service');
            })
            ->get();

        // New Metric: Assets by Location
        $assetsByLocation = Asset::select('location_id', \DB::raw('count(*) as total'))
            ->with('location')
            ->groupBy('location_id')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // New Metric: Assets by Category
        $assetsByCategory = Category::query()
            ->select('categories.id', 'categories.name')
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('assets', 'assets.product_id', '=', 'products.id')
            ->selectRaw('count(assets.id) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        // Hardware Lifecycle & Age Distribution Metric
        $allAssets = Asset::all(['id', 'purchase_date', 'release_date', 'created_at']);
        $now = now();
        $ageBuckets = [
            '< 1 Year' => 0,
            '1-2 Years' => 0,
            '2-3 Years' => 0,
            '3-4 Years' => 0,
            '4+ Years' => 0,
        ];

        foreach ($allAssets as $ast) {
            $date = $ast->purchase_date ?? $ast->release_date ?? $ast->created_at;
            if (! $date) {
                continue;
            }
            $years = $date->diffInYears($now);
            if ($years < 1) {
                $ageBuckets['< 1 Year']++;
            } elseif ($years < 2) {
                $ageBuckets['1-2 Years']++;
            } elseif ($years < 3) {
                $ageBuckets['2-3 Years']++;
            } elseif ($years < 4) {
                $ageBuckets['3-4 Years']++;
            } else {
                $ageBuckets['4+ Years']++;
            }
        }

        $assetAgeDistribution = [
            'labels' => array_keys($ageBuckets),
            'data' => array_values($ageBuckets),
        ];

        // Top Hardware Vendors by Volume
        $topVendorsRaw = Brand::withCount('products')
            ->select('brands.id', 'brands.name')
            ->leftJoin('products', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('assets', 'assets.product_id', '=', 'products.id')
            ->selectRaw('count(assets.id) as total_assets')
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('total_assets')
            ->limit(5)
            ->get();

        $vendorChartData = [
            'labels' => $topVendorsRaw->pluck('name')->toArray(),
            'data' => $topVendorsRaw->pluck('total_assets')->map(fn ($v) => (int) $v)->toArray(),
        ];

        // Financial Metrics
        $totalPurchaseCost = Asset::whereNotNull('purchase_cost')->sum('purchase_cost');
        $allAssetsWithDepr = Asset::with('depreciation')->whereNotNull('purchase_cost')->get();
        $totalBookValue = $allAssetsWithDepr->sum(fn ($a) => $a->bookValue ?? $a->purchase_cost);
        $totalDepreciated = max(0, $totalPurchaseCost - $totalBookValue);

        // Status Map for Small-Box Navigation Links
        $statusMap = Status::pluck('id', 'name');
        $readyStatusId = $statusMap['Ready to Deploy'] ?? null;
        $deployedStatusId = $statusMap['Deployed'] ?? null;
        $serviceStatusId = $statusMap['Service'] ?? null;
        $brokenStatusId = $statusMap['Broken'] ?? null;
        $readyForSaleStatusId = $statusMap['Ready for Sale'] ?? ($statusMap['For Sale'] ?? null);

        // Alert Metrics
        $lowStockComponents = Component::whereRaw('qty <= min_qty')->get();
        $expiringLicenses = License::whereNotNull('expiration_date')
            ->where('expiration_date', '<=', now()->addDays(30))
            ->get();

        return view('pages.dashboard.it-assets', compact(
            'totalAssets',
            'totalAssetsTrend',
            'totalConsumables',
            'consumablesBreakdown',
            'readyToDeploy',
            'readyToDeployTrend',
            'deployed',
            'deployedTrend',
            'broken',
            'brokenTrend',
            'brokenDisposal',
            'brokenDisposalTrend',
            'recentCheckouts',
            'recentConsumableCheckouts',
            'chartData',
            'chartDataByCategory',
            'deptChartData',
            'deptDataByCategory',
            'categories',
            'consumableCategories',
            'productDataByCategory',
            'purchaseChartData',
            'purchaseDataByCategory',
            'purchaseChartYears',
            'purchaseChartYear',
            'consumableChartData',
            'consumableDataByCategory',
            'consumableTimelineChartData',
            'consumableTimelineByCategory',
            'consumableTimelineYears',
            'consumableTimelineYear',
            'assetsOlderThan4Years',
            'userName',
            'randomGreeting',
            'lowStockConsumables',
            'assetsInService',
            'assetsByLocation',
            'assetsByCategory',
            'assetAgeDistribution',
            'vendorChartData',
            'service',
            'serviceTrend',
            'readyForSale',
            'readyForSaleTrend',
            'totalPurchaseCost',
            'totalBookValue',
            'totalDepreciated',
            'lowStockComponents',
            'expiringLicenses',
            'readyStatusId',
            'deployedStatusId',
            'serviceStatusId',
            'brokenStatusId',
            'readyForSaleStatusId'
        ));
    }
}
