<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\Status;
use App\Models\Department;
use App\Models\Category;
use App\Models\Product;
use App\Models\Location;
use App\Models\Brand;
use App\Models\Vendor;
use App\Exports\AssetsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('pages.reports.index');
    }

    public function activity(Request $request)
    {
        $query = AssetCheckout::with(['asset', 'checkedOutToUser', 'checkedOutByUser']);

        if ($request->filled('from')) {
            $query->whereDate('checked_out_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('checked_out_at', '<=', $request->to);
        }
        if ($request->filled('user_id')) {
            $query->where('checked_out_to_user_id', $request->user_id);
        }

        $activities = $query->orderBy('checked_out_at', 'desc')->paginate(50)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();

        return view('pages.reports.activity', compact('activities', 'users'));
    }

    public function assets(Request $request)
    {
        $query = Asset::with([
            'product.brand',
            'product.category',
            'status',
            'department.company',
            'location',
            'operatingSystem',
            'vendor',
            'assignedToUser',
            'currentCheckout.checkedOutToUser',
            'currentCheckout.department',
            'currentCheckout.location',
            'depreciation'
        ]);

        $query = $this->applyAssetFilters($query, $request->all());

        // Calculate summaries before pagination
        $summaryQuery = clone $query;
        $totalCount = $summaryQuery->count();
        $totalPurchaseCost = $summaryQuery->sum('purchase_cost');
        
        // Book value needs iteration because it's an accessor, we can't sum it in DB easily
        // We will do a rough estimation or chunk it if needed, but for performance let's 
        // calculate on the paginated set for book value/depreciated, or skip total book value
        // if dataset is huge. We'll skip total book value here to avoid OOM.
        
        $assets = $query->latest()->paginate(50)->withQueryString();
        
        // Compute average age of displayed assets for performance
        $totalAge = 0;
        $agedCount = 0;
        foreach ($assets as $asset) {
            $date = $asset->release_date ?? $asset->purchase_date;
            if ($date) {
                $totalAge += $date->diffInYears(now());
                $agedCount++;
            }
        }
        $averageAgeYears = $agedCount > 0 ? round($totalAge / $agedCount, 1) : 0;

        $statuses = Status::all();
        $departments = Department::all();
        $categories = Category::all();
        $products = Product::with('brand')->get();
        $locations = Location::all();
        $brands = Brand::all();
        $vendors = Vendor::all();

        return view('pages.reports.assets', compact(
            'assets', 'statuses', 'departments', 'categories', 'products', 
            'locations', 'brands', 'vendors', 'totalCount', 'totalPurchaseCost', 'averageAgeYears'
        ));
    }

    public function exportAssets(Request $request)
    {
        return Excel::download(new AssetsExport($request->all()), 'assets_report.xlsx');
    }

    private function applyAssetFilters($query, $filters)
    {
        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function ($b) use ($q) {
                $b->where('asset_tag', 'like', "%$q%")
                    ->orWhere('device_name', 'like', "%$q%")
                    ->orWhere('serial_number', 'like', "%$q%")
                    ->orWhere('product_number', 'like', "%$q%");
            });
        }

        if (!empty($filters['status_id'])) $query->where('status_id', $filters['status_id']);
        if (!empty($filters['department_id'])) $query->where('department_id', $filters['department_id']);
        if (!empty($filters['location_id'])) $query->where('location_id', $filters['location_id']);
        if (!empty($filters['product_id'])) $query->where('product_id', $filters['product_id']);
        if (!empty($filters['vendor_id'])) $query->where('vendor_id', $filters['vendor_id']);
        
        if (!empty($filters['category_id'])) {
            $query->whereHas('product', function($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['brand_id'])) {
            $query->whereHas('product', function($q) use ($filters) {
                $q->where('brand_id', $filters['brand_id']);
            });
        }

        if (!empty($filters['purchase_from'])) $query->whereDate('purchase_date', '>=', $filters['purchase_from']);
        if (!empty($filters['purchase_to'])) $query->whereDate('purchase_date', '<=', $filters['purchase_to']);

        if (!empty($filters['cost_min'])) $query->where('purchase_cost', '>=', $filters['cost_min']);
        if (!empty($filters['cost_max'])) $query->where('purchase_cost', '<=', $filters['cost_max']);

        if (!empty($filters['aging'])) {
            $aging = $filters['aging'];
            $now = Carbon::now();
            $query->where(function($q) use ($aging, $now) {
                switch($aging) {
                    case '0-1':
                        $q->where('purchase_date', '>', $now->copy()->subYear())
                          ->orWhere('release_date', '>', $now->copy()->subYear());
                        break;
                    case '1-2':
                        $q->whereBetween('purchase_date', [$now->copy()->subYears(2), $now->copy()->subYear()])
                          ->orWhereBetween('release_date', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                        break;
                    case '2-3':
                        $q->whereBetween('purchase_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)])
                          ->orWhereBetween('release_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                        break;
                    case '3-4':
                        $q->whereBetween('purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)])
                          ->orWhereBetween('release_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                        break;
                    case '4+':
                        $q->where('purchase_date', '<=', $now->copy()->subYears(4))
                          ->orWhere('release_date', '<=', $now->copy()->subYears(4));
                        break;
                    case 'warranty_expired':
                        $q->where('warranty_expiry_date', '<', $now);
                        break;
                    case 'warranty_30d':
                        $q->whereBetween('warranty_expiry_date', [$now, $now->copy()->addDays(30)]);
                        break;
                }
            });
        }

        return $query;
    }

    public function depreciation(Request $request)
    {
        $query = Asset::with(['depreciation', 'product.category'])
            ->whereNotNull('purchase_cost')
            ->whereNotNull('depreciation_id')
            ->whereNotNull('purchase_date');

        $assets = $query->paginate(50);

        $totalPurchase = (clone $query)->sum('purchase_cost');
        $totalBook = 0;
        foreach ($query->get() as $a) { $totalBook += $a->bookValue; }

        return view('pages.reports.depreciation', compact('assets', 'totalPurchase', 'totalBook'));
    }

    public function custom()
    {
        return view('pages.reports.custom');
    }
}
