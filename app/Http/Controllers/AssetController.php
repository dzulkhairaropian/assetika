<?php

namespace App\Http\Controllers;

use App\Exports\AssetsExport;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\Depreciation;
use App\Models\Location;
use App\Models\OperatingSystem;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use App\Models\Vendor;
use App\Services\AssetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    protected AssetService $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function export(Request $request)
    {
        return (new AssetsExport($request->all()))->download('assets-'.now()->format('Y-m-d').'.xlsx');
    }

    public function index(Request $request)
    {
        $baseQuery = Asset::with([
            'product.category', 'product.brand',
            'status', 'assignedToUser', 'department.company',
            'location', 'vendor', 'depreciation',
            'currentCheckout.checkedOutToUser',
            'lastCheckout.checkedOutToUser',
        ])->filter($request->except('tab'));

        $now4 = Carbon::now()->subYears(4);
        $agedScope = function ($q) use ($now4) {
            $q->where(function ($sub) use ($now4) {
                $sub->where('assets.release_date', '<=', $now4)
                    ->orWhere(function ($s2) use ($now4) {
                        $s2->whereNull('assets.release_date')
                            ->where('assets.purchase_date', '<=', $now4);
                    });
            });
        };

        $inventorySummary = [
            'total' => (clone $baseQuery)->count(),
            'today' => (clone $baseQuery)->whereDate('assets.created_at', Carbon::today())->count(),
            'draft' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->whereIn('name', ['Draft']))->count(),
            'ready' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->whereIn('name', ['Ready to Deploy']))->count(),
            'deployed' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->whereIn('name', ['Deployed', 'Borrowed']))->count(),
            'in_service' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->whereIn('name', ['Service', 'Maintenance', 'In Repair']))->count(),
            'broken' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->whereIn('name', ['Broken']))->count(),
            'retired' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->whereIn('name', ['Retired', 'Disposal']))->count(),
            'sold' => (clone $baseQuery)->whereHas('status', fn ($q) => $q->whereIn('name', ['Sold', 'For Sale']))->count(),
            'aged' => (clone $baseQuery)->where($agedScope)->count(),
        ];

        $query = (clone $baseQuery);
        if ($request->filled('tab')) {
            $query->filter(['tab' => $request->input('tab')]);
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $sortBy = $request->input('sort_by');
        $sortDir = strtolower($request->input('sort_dir')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'asset_tag') {
            $query->orderBy('asset_tag', $sortDir);
        } elseif ($sortBy === 'name' || $sortBy === 'device_name') {
            $query->orderBy('device_name', $sortDir);
        } elseif ($sortBy === 'serial_number' || $sortBy === 'serial') {
            $query->orderBy('serial_number', $sortDir);
        } elseif ($sortBy === 'purchase_cost' || $sortBy === 'cost') {
            $query->orderBy('purchase_cost', $sortDir);
        } elseif ($sortBy === 'purchase_date') {
            $query->orderBy('purchase_date', $sortDir);
        } elseif ($sortBy === 'warranty_expiry_date' || $sortBy === 'warranty') {
            $query->orderBy('warranty_expiry_date', $sortDir);
        } else {
            $query->latest();
        }

        $assets = $query->paginate($perPage)->withQueryString();
        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $departments = Department::all();
        $locations = Location::all();
        $products = Product::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $categories = Category::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $depreciations = Depreciation::all();

        // Get distinct years from release_date, purchase_date, and created_at
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearFunc = $isSqlite ? "strftime('%%Y', %s)" : 'YEAR(%s)';

        $yearsFromRelease = Asset::whereNotNull('release_date')
            ->selectRaw(sprintf($yearFunc, 'release_date').' as yr')
            ->distinct()
            ->pluck('yr');
        $yearsFromPurchase = Asset::whereNotNull('purchase_date')
            ->selectRaw(sprintf($yearFunc, 'purchase_date').' as yr')
            ->distinct()
            ->pluck('yr');
        $availableYears = $yearsFromRelease->merge($yearsFromPurchase)
            ->push(now()->year)
            ->map(fn ($y) => (int) $y)
            ->unique()
            ->filter()
            ->sortDesc()
            ->values();

        $companies = Company::orderBy('name')->get();

        return view('pages.assets.index', compact('assets', 'statuses', 'departments', 'companies', 'locations', 'products', 'categories', 'depreciations', 'inventorySummary', 'availableYears'));
    }

    public function clone(Asset $asset)
    {
        return redirect()->route('assets.create', ['clone_from' => $asset->id]);
    }

    public function create(Request $request)
    {
        $clonedAsset = null;
        if ($request->filled('clone_from')) {
            $clonedAsset = Asset::with(['product.brand', 'product.category', 'department.company'])->find($request->clone_from);
        }

        $products = Product::with(['category', 'brand'])
            ->whereHas('masterCategory', function ($q) {
                $q->where('name', 'IT Asset');
            })->get();
        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::with('company')->get();
        $locations = Location::all();
        $depreciations = Depreciation::all();

        $productOpts = $products->map(fn ($p) => ['id' => $p->id, 'text' => ($p->brand->name ?? '').' - '.$p->model.' ('.($p->category->name ?? '').')'])->toArray();
        $statusOpts = $statuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->toArray();
        $vendorOpts = $vendors->map(fn ($v) => ['id' => $v->id, 'text' => $v->name])->toArray();
        $deptOpts = $departments->map(fn ($d) => ['id' => $d->id, 'text' => $d->name.($d->company ? ' - '.$d->company->name : '')])->toArray();
        $locationOpts = $locations->map(fn ($l) => ['id' => $l->id, 'text' => $l->name])->toArray();
        $osOpts = $operatingSystems->map(fn ($o) => ['id' => $o->id, 'text' => $o->name])->toArray();

        return view('pages.assets.create', compact(
            'products', 'statuses', 'vendors', 'operatingSystems', 'departments', 'locations', 'depreciations',
            'productOpts', 'statusOpts', 'vendorOpts', 'deptOpts', 'locationOpts', 'osOpts', 'clonedAsset'
        ));
    }

    public function store(Request $request)
    {
        $isDraft = $request->input('is_draft') == '1';

        if ($isDraft) {
            $validated = $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'status_id' => 'nullable|exists:statuses,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'operating_system_id' => 'nullable|exists:operating_systems,id',
                'department_id' => 'nullable|exists:departments,id',
                'location_id' => 'nullable|exists:locations,id',
                'serial_number' => 'nullable|string|unique:assets,serial_number',
                'product_number' => 'nullable|string',
                'user_name' => 'nullable|string',
                'license_windows' => 'nullable|string',
                'license_office' => 'nullable|string',
                'mac_address_wireless' => 'nullable|string',
                'mac_address_ethernet' => 'nullable|string',
                'specification' => 'nullable|string',
                'accessories' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'release_date' => 'nullable|date',
                'warranty_expiry_date' => 'nullable|date',
                'purchase_cost' => 'nullable|numeric',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
                'asset_tag' => 'nullable|string|unique:assets,asset_tag',
                'device_name' => 'nullable|string',
                'depreciation_id' => 'nullable|exists:depreciations,id',
                'sale_price' => 'nullable|numeric',
                'buyer_name' => 'nullable|string',
                'sale_date' => 'nullable|date',
                'is_components_stripped' => 'nullable|boolean',
            ]);

            if (empty($validated['status_id']) || $isDraft) {
                $draftStatus = Status::firstOrCreate(
                    ['name' => 'Draft'],
                    ['color' => 'gray', 'icon' => 'file-text']
                );
                $validated['status_id'] = $draftStatus->id;
            }
        } else {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'status_id' => 'required|exists:statuses,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'operating_system_id' => 'nullable|exists:operating_systems,id',
                'department_id' => 'required|exists:departments,id',
                'location_id' => 'nullable|exists:locations,id',
                'serial_number' => 'required|string|unique:assets,serial_number',
                'product_number' => 'nullable|string',
                'user_name' => 'nullable|string',
                'license_windows' => 'nullable|string',
                'license_office' => 'nullable|string',
                'mac_address_wireless' => 'nullable|string',
                'mac_address_ethernet' => 'nullable|string',
                'specification' => 'nullable|string',
                'accessories' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'release_date' => 'required|date',
                'warranty_expiry_date' => 'nullable|date',
                'purchase_cost' => 'nullable|numeric',
                'depreciation_id' => 'nullable|exists:depreciations,id',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
                'sale_price' => 'nullable|numeric',
                'buyer_name' => 'nullable|string',
                'sale_date' => 'nullable|date',
                'is_components_stripped' => 'nullable|boolean',
            ]);
        }

        $asset = Asset::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('assets.create')->with('success', 'Asset created successfully. Add another one.');
        }

        return redirect()->route('assets.index')->with('success', $isDraft ? 'Asset saved as draft successfully.' : 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'product.category', 'product.brand', 'status', 'vendor', 'operatingSystem', 'department.company', 'location',
            'currentCheckout.checkedOutToUser.department', 'currentCheckout.location',
            'checkoutHistory.checkedOutToUser', 'checkoutHistory.department', 'maintenances.maintenanceType',
            'tickets.status', 'tickets.priority', 'tickets.assignedTo',
            'activityLogs.user', 'installedComponents.product', 'installedComponents.vendor',
        ]);

        return view('pages.assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $products = Product::with(['category', 'brand'])
            ->whereHas('masterCategory', function ($q) {
                $q->where('name', 'IT Asset');
            })->get();
        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::with('company')->get();
        $locations = Location::all();
        $depreciations = Depreciation::all();

        $productOpts = $products->map(fn ($p) => ['id' => $p->id, 'text' => ($p->brand->name ?? '').' - '.$p->model.' ('.($p->category->name ?? '').')'])->toArray();
        $statusOpts = $statuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->toArray();
        $vendorOpts = $vendors->map(fn ($v) => ['id' => $v->id, 'text' => $v->name])->toArray();
        $deptOpts = $departments->map(fn ($d) => ['id' => $d->id, 'text' => $d->name.($d->company ? ' - '.$d->company->name : '')])->toArray();
        $locationOpts = $locations->map(fn ($l) => ['id' => $l->id, 'text' => $l->name])->toArray();
        $osOpts = $operatingSystems->map(fn ($o) => ['id' => $o->id, 'text' => $o->name])->toArray();

        return view('pages.assets.edit', compact(
            'asset', 'products', 'statuses', 'vendors', 'operatingSystems', 'departments', 'locations', 'depreciations',
            'productOpts', 'statusOpts', 'vendorOpts', 'deptOpts', 'locationOpts', 'osOpts'
        ));
    }

    public function update(Request $request, Asset $asset)
    {
        $isDraft = $request->input('is_draft') == '1';

        if ($isDraft) {
            $validated = $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'status_id' => 'nullable|exists:statuses,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'operating_system_id' => 'nullable|exists:operating_systems,id',
                'department_id' => 'nullable|exists:departments,id',
                'location_id' => 'nullable|exists:locations,id',
                'serial_number' => 'nullable|string|unique:assets,serial_number,'.$asset->id,
                'product_number' => 'nullable|string',
                'user_name' => 'nullable|string',
                'license_windows' => 'nullable|string',
                'license_office' => 'nullable|string',
                'mac_address_wireless' => 'nullable|string',
                'mac_address_ethernet' => 'nullable|string',
                'specification' => 'nullable|string',
                'accessories' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'release_date' => 'nullable|date',
                'warranty_expiry_date' => 'nullable|date',
                'purchase_cost' => 'nullable|numeric',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
                'asset_tag' => 'nullable|string|unique:assets,asset_tag,'.$asset->id,
                'device_name' => 'nullable|string',
                'depreciation_id' => 'nullable|exists:depreciations,id',
                'sale_price' => 'nullable|numeric',
                'buyer_name' => 'nullable|string',
                'sale_date' => 'nullable|date',
                'is_components_stripped' => 'nullable|boolean',
            ]);

            $draftStatus = Status::firstOrCreate(
                ['name' => 'Draft'],
                ['color' => 'gray', 'icon' => 'file-text']
            );
            $validated['status_id'] = $draftStatus->id;
        } else {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'status_id' => 'required|exists:statuses,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'operating_system_id' => 'nullable|exists:operating_systems,id',
                'department_id' => 'required|exists:departments,id',
                'location_id' => 'nullable|exists:locations,id',
                'serial_number' => 'required|string|unique:assets,serial_number,'.$asset->id,
                'product_number' => 'nullable|string',
                'user_name' => 'nullable|string',
                'license_windows' => 'nullable|string',
                'license_office' => 'nullable|string',
                'mac_address_wireless' => 'nullable|string',
                'mac_address_ethernet' => 'nullable|string',
                'specification' => 'nullable|string',
                'accessories' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'release_date' => 'required|date',
                'warranty_expiry_date' => 'nullable|date',
                'purchase_cost' => 'nullable|numeric',
                'depreciation_id' => 'nullable|exists:depreciations,id',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
                'sale_price' => 'nullable|numeric',
                'buyer_name' => 'nullable|string',
                'sale_date' => 'nullable|date',
                'is_components_stripped' => 'nullable|boolean',
            ]);
        }

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', $isDraft ? 'Asset saved as draft successfully.' : 'Asset updated successfully.');
    }

    public function printLabel(Asset $asset)
    {
        $asset->load(['product.category', 'product.brand', 'department.company', 'location']);
        $assets = collect([$asset]);

        return view('pages.labels.print', compact('assets'));
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
        ]);

        $assets = Asset::whereIn('id', $request->ids)->get();
        foreach ($assets as $asset) {
            $asset->delete();
        }

        return redirect()->route('assets.index')->with('success', 'Selected assets deleted successfully.');
    }

    public function bulkEdit(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
            'status_id' => 'nullable|exists:statuses,id',
            'department_id' => 'nullable|exists:departments,id',
            'location_id' => 'nullable|exists:locations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        $data = array_filter($request->only(['status_id', 'department_id', 'location_id', 'vendor_id']));

        if (empty($data)) {
            return back()->with('error', 'No changes selected.');
        }

        $assets = Asset::whereIn('id', $request->ids)->get();
        foreach ($assets as $asset) {
            $asset->update($data);
        }

        return redirect()->route('assets.index')->with('success', count($assets).' assets updated successfully.');
    }

    public function bulkCheckoutForm(Request $request)
    {
        $rawIds = $request->input('ids');
        if (empty($rawIds)) {
            return redirect()->route('assets.index')->with('error', 'Please select at least one available asset to check out using the checkboxes.');
        }

        $ids = is_array($rawIds) ? $rawIds : array_filter(explode(',', (string) $rawIds));
        $assets = Asset::whereIn('id', $ids)
            ->whereNull('current_checkout_id')
            ->whereHas('status', fn ($q) => $q->where('name', 'Ready to Deploy'))
            ->get();

        if ($assets->isEmpty()) {
            return redirect()->route('assets.index')->with('error', 'None of the selected assets are currently available for checkout.');
        }

        $users = User::orderBy('name')->get();
        $departments = Department::all();
        $locations = Location::all();

        return view('pages.assets.bulk-checkout', compact('assets', 'users', 'departments', 'locations'));
    }

    public function bulkCheckout(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
            'checked_out_to_user_id' => 'required|exists:users,id',
            'location_id' => 'nullable|exists:locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'checked_out_at' => 'nullable|date',
            'expected_return_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $toUser = User::findOrFail($request->checked_out_to_user_id);
        $assets = Asset::whereIn('id', $request->ids)
            ->whereNull('current_checkout_id')
            ->whereHas('status', fn ($q) => $q->where('name', 'Ready to Deploy'))
            ->get();

        $count = 0;
        foreach ($assets as $asset) {
            $this->assetService->processCheckout($asset, $toUser, [
                'location_id' => $request->location_id,
                'department_id' => $request->department_id,
                'supervisor_user_id' => $request->supervisor_user_id,
                'checkout_date' => $request->checked_out_at ?? now(),
                'expected_return_date' => $request->expected_return_date,
                'notes' => $request->notes,
            ]);
            $count++;
        }

        $skipped = count($request->ids) - $count;

        return redirect()->route('assets.index')->with('success', "{$count} assets checked out successfully.".($skipped > 0 ? " {$skipped} skipped (already checked out or not ready)." : ''));
    }

    public function publicScan($assetId)
    {
        $asset = Asset::withoutGlobalScopes()->findOrFail($assetId);
        $asset->load([
            'product.brand',
            'product.category',
            'operatingSystem',
            'status',
            'location',
            'department.company',
            'maintenances' => function ($q) {
                $q->orderBy('start_date', 'desc');
            },
        ]);

        $title = ($asset->asset_tag ?? 'Asset').' | '.config('app.name');

        return view('pages.assets.public-scan', compact('asset', 'title'));
    }

    public function myAssets(Request $request)
    {
        $userId = auth()->id();
        $query = Asset::with([
            'product.category',
            'product.brand',
            'status',
            'location',
            'department',
            'currentCheckout',
        ])
            ->where(function ($q) use ($userId) {
                $q->where('assigned_to_user_id', $userId)
                    ->orWhereHas('currentCheckout', function ($cq) use ($userId) {
                        $cq->where('checked_out_to_user_id', $userId)
                            ->whereNull('checked_in_at');
                    });
            });

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('device_name', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('brand', function ($bq) use ($search) {
                                $bq->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $allMyAssets = (clone $query)->get();

        $summary = [
            'total' => $allMyAssets->count(),
            'laptops' => $allMyAssets->filter(fn ($a) => in_array(strtolower(optional(optional($a->product)->category)->name), ['notebook', 'laptop', 'desktop', 'pc', 'workstation']))->count(),
            'other' => $allMyAssets->filter(fn ($a) => ! in_array(strtolower(optional(optional($a->product)->category)->name), ['notebook', 'laptop', 'desktop', 'pc', 'workstation']))->count(),
            'bast_count' => $allMyAssets->filter(fn ($a) => $a->currentCheckout !== null)->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $assets = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.assets.my-assets', compact('assets', 'summary'));
    }
}
