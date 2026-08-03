<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\Consumable;
use App\Models\Status;
use App\Models\Department;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function calculateTrend($current, $previous) {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0; // If previous is 0 and current is >0, assume 100% growth. Otherwise, 0.
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function index()
    {
        $startOfMonth = now()->startOfMonth();

        $totalAssets = Asset::count();
        $totalAssetsPrev = Asset::where('created_at', '<', $startOfMonth)->count();
        $totalAssetsTrend = $this->calculateTrend($totalAssets, $totalAssetsPrev);

        $totalConsumables = Consumable::sum('quantity');
        $consumablesBreakdown = Consumable::with(['product.brand', 'product.category'])
            ->orderBy('quantity', 'desc')
            ->limit(5)
            ->get();

        // Status counts for Widgets
        $readyToDeploy = Asset::whereHas('status', function($q) {
            $q->where('name', 'Ready to Deploy');
        })->count();
        $readyToDeployPrev = Asset::whereHas('status', function($q) {
            $q->where('name', 'Ready to Deploy');
        })->where('created_at', '<', $startOfMonth)->count();
        $readyToDeployTrend = $this->calculateTrend($readyToDeploy, $readyToDeployPrev);

        $deployed = Asset::whereHas('status', function($q) {
            $q->where('name', 'Deployed');
        })->count();
        $deployedPrev = Asset::whereHas('status', function($q) {
            $q->where('name', 'Deployed');
        })->where('created_at', '<', $startOfMonth)->count();
        $deployedTrend = $this->calculateTrend($deployed, $deployedPrev);

        $broken = Asset::whereHas('status', function($q) {
            $q->where('name', 'Broken');
        })->count();
        $brokenPrev = Asset::whereHas('status', function($q) {
            $q->where('name', 'Broken');
        })->where('created_at', '<', $startOfMonth)->count();
        $brokenTrend = $this->calculateTrend($broken, $brokenPrev);


        $brokenDisposal = Asset::whereHas('status', function($q) {
            $q->whereIn('name', ['Broken', 'Retired']);
        })->count();
        $brokenDisposalPrev = Asset::whereHas('status', function($q) {
            $q->whereIn('name', ['Broken', 'Retired']);
        })->where('created_at', '<', $startOfMonth)->count();
        $brokenDisposalTrend = $this->calculateTrend($brokenDisposal, $brokenDisposalPrev);

        // Reintroduce Service metric
        $service = Asset::whereHas('status', function($q) {
            $q->where('name', 'Service');
        })->count();
        $servicePrev = Asset::whereHas('status', function($q) {
            $q->where('name', 'Service');
        })->where('created_at', '<', $startOfMonth)->count();
        $serviceTrend = $this->calculateTrend($service, $servicePrev);

        $recentCheckouts = AssetCheckout::with(['asset.product.brand', 'asset.department', 'checkedOutToUser'])
            ->latest('checked_out_at')
            ->limit(5)
            ->get();

        // Recent Consumable Checkouts
        $recentConsumableCheckouts = \App\Models\ConsumableTransaction::with(['consumable.product', 'checkedOutToUser'])
            ->where('type', 'checkout')
            ->latest('transaction_date')
            ->limit(5)
            ->get();

        // Chart Data 1: Assets by Status
        $statuses = Status::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Asset');
        })->get();
        
        $chartData = [
            'labels' => $statuses->pluck('name'),
            'data' => $statuses->map(function($status) {
                return Asset::where('status_id', $status->id)->count();
            }),
            'colors' => $statuses->map(function($status) {
                return match($status->name) {
                    'Ready to Deploy', 'In Stock', 'Fulfilled', 'Available', 'Completed' => '#10B981', // Success (Emerald)
                    'Deployed', 'Borrowed', 'Assigned', 'In Progress', 'Reserved' => '#3B82F6', // Info (Blue)
                    'Service', 'Low Stock', 'Pending', 'Draft', 'Maintenance' => '#F59E0B', // Warning (Amber)
                    'Broken', 'Out of Stock', 'Declined', 'Retired', 'Lost', 'Rejected' => '#EF4444', // Danger (Red)
                    default => '#98A2B3' // Default Grey
                };
            })
        ];

        // Chart Data 2: Department with most assets
        $departments = Department::withCount('assets')
            ->orderBy('assets_count', 'desc')
            ->limit(5)
            ->get();
            
        $deptChartData = [
            'labels' => $departments->pluck('name'),
            'data' => $departments->pluck('assets_count'),
        ];

        // Chart Data 3: Product most used with Category Selection
        $itAssetMaster = \App\Models\MasterCategory::where('name', 'like', 'IT Asset%')->first();
        $categories = collect();
        if ($itAssetMaster) {
            $categories = Category::where('master_category_id', $itAssetMaster->id)->get();
        }
        if ($categories->isEmpty()) {
            $categories = Category::all();
        }

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

        // Chart Data 4: Monthly Purchases by Release Date
        $purchaseYears = Asset::whereNotNull('release_date')
            ->selectRaw('YEAR(release_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (string) $year)
            ->values();

        $purchaseYear = request('purchase_year');
        if (! $purchaseYear || ! $purchaseYears->contains($purchaseYear)) {
            $purchaseYear = $purchaseYears->first() ?? (string) now()->year;
        }

        $purchaseTimelineQuery = Asset::whereNotNull('release_date')
            ->whereYear('release_date', $purchaseYear);

        $monthlyPurchasesRaw = $purchaseTimelineQuery
            ->get()
            ->groupBy(function($asset) {
                return $asset->release_date->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'label' => $group->first()->release_date->format('M Y'),
                    'count' => $group->count()
                ];
            })
            ->sortKeys()
            ->values();

        $purchaseChartData = [
            'labels' => $monthlyPurchasesRaw->pluck('label'),
            'data' => $monthlyPurchasesRaw->pluck('count'),
        ];

        $purchaseChartYears = $purchaseYears->toArray();
        $purchaseChartYear = $purchaseYear;

        // Chart Data 5: Consumables most requested (Top 5)
        $requestedConsumables = \App\Models\ConsumableRequestItem::with(['consumable.product'])
            ->select('consumable_id', \DB::raw('sum(quantity_requested) as total_requested'))
            ->groupBy('consumable_id')
            ->orderBy('total_requested', 'desc')
            ->limit(5)
            ->get();
            
        if ($requestedConsumables->isEmpty() || $requestedConsumables->sum('total_requested') == 0) {
            // Fallback: Query from ConsumableTransaction (type = checkout)
            $requestedConsumables = \App\Models\ConsumableTransaction::with(['consumable.product'])
                ->where('type', 'checkout')
                ->select('consumable_id', \DB::raw('sum(quantity) as total_requested'))
                ->groupBy('consumable_id')
                ->orderBy('total_requested', 'desc')
                ->limit(5)
                ->get();
        }

        $consumableChartData = [
            'labels' => $requestedConsumables->map(function($item) {
                return $item->consumable->product->name ?? 'Unknown Consumable';
            })->toArray(),
            'data' => $requestedConsumables->pluck('total_requested')->map(function($val) {
                return (int) $val;
            })->toArray(),
        ];

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
            'Let\'s get things done.'
        ];
        // Store random greeting in session if not already set
        if (! session()->has('dashboard_greeting')) {
            session(['dashboard_greeting' => $greetings[array_rand($greetings)]]);
        }
        $randomGreeting = session('dashboard_greeting');

        $lowStockConsumables = Consumable::with('product')
            ->where(function($query) {
                $query->where('quantity', '<=', 0)
                      ->orWhereRaw('quantity <= low_threshold');
            })
            ->get();

        $fourYearsAgo = \Carbon\Carbon::now()->subYears(4);
        $assetsOlderThan4Years = Asset::with(['checkedOutToUser', 'product.brand', 'department'])
            ->where(function($query) use ($fourYearsAgo) {
                $query->where('purchase_date', '<=', $fourYearsAgo)
                      ->orWhere(function($q) use ($fourYearsAgo) {
                          $q->whereNull('purchase_date')
                            ->where('release_date', '<=', $fourYearsAgo);
                      })
                      ->orWhere(function($q) use ($fourYearsAgo) {
                          $q->whereNull('purchase_date')
                            ->whereNull('release_date')
                            ->where('created_at', '<=', $fourYearsAgo);
                      });
            })
            ->latest()
            ->get();

        $assetsInService = Asset::with(['product.brand', 'department'])
            ->whereHas('status', function($q) {
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
            'deptChartData',
            'categories',
            'productDataByCategory',
            'purchaseChartData',
            'purchaseChartYears',
            'purchaseChartYear',
            'consumableChartData',
            'assetsOlderThan4Years',
            'userName',
            'randomGreeting',
            'lowStockConsumables',
            'assetsInService',
            'assetsByLocation',
            'assetsByCategory',
            'service',
            'serviceTrend'
        ));
    }
}
