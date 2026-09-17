<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Component;
use App\Models\Location;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\Vendor;
use App\Services\ComponentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ComponentController extends Controller
{
    protected ComponentService $componentService;

    public function __construct(ComponentService $componentService)
    {
        $this->componentService = $componentService;
    }

    public function index(Request $request)
    {
        $query = Component::query()->with(['product.brand', 'product.category', 'category', 'location', 'vendor', 'assignedAsset.assignedToUser', 'status']);

        // Tab filter handling
        $tab = $request->input('tab');
        if ($tab === 'today') {
            $query->whereDate('components.created_at', Carbon::today());
        } elseif ($tab === 'ready') {
            $query->whereNull('assigned_asset_id')
                ->where(function ($q) {
                    $q->whereNull('status_id')
                        ->orWhereHas('status', fn ($s) => $s->whereIn('name', ['Ready', 'Ready to Deploy', 'Available', 'In Stock']));
                });
        } elseif ($tab === 'installed') {
            $query->where(function ($q) {
                $q->whereNotNull('assigned_asset_id')
                    ->orWhereHas('status', fn ($s) => $s->whereIn('name', ['Installed', 'Deployed']));
            });
        } elseif ($tab === 'service') {
            $query->whereHas('status', fn ($s) => $s->whereIn('name', ['In Repair', 'Service', 'Maintenance']));
        } elseif ($tab === 'scrapped') {
            $query->whereHas('status', fn ($s) => $s->whereIn('name', ['Scrapped', 'Broken', 'Retired']));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('component_tag', 'like', "%$search%")
                    ->orWhere('serial', 'like', "%$search%")
                    ->orWhere('model_number', 'like', "%$search%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%$search%"))
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%$search%"))
                    ->orWhereHas('assignedAsset', fn ($a) => $a->where('asset_tag', 'like', "%$search%")->orWhere('device_name', 'like', "%$search%"));
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('created_from')) {
            $query->whereDate('components.created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('components.created_at', '<=', $request->created_to);
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $components = $query->latest()->paginate($perPage)->withQueryString();

        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();

        $products = Product::where('master_category_id', $componentMasterCat?->id)
            ->orderBy('name')
            ->get();
        $categories = Category::where('master_category_id', $componentMasterCat?->id)
            ->orderBy('name')
            ->get();
        $statuses = Status::where('master_category_id', $componentMasterCat?->id)
            ->orderBy('name')
            ->get();
        $locations = Location::orderBy('name')->get();

        $allAssets = Asset::with(['product', 'assignedToUser'])->orderBy('asset_tag')->get();

        $assetOpts = $allAssets->map(fn ($a) => [
            'id' => $a->id,
            'text' => $a->asset_tag.' - '.($a->product?->name ?? $a->device_name).($a->assignedToUser ? ' ('.$a->assignedToUser->name.')' : ''),
        ])->values()->all();

        // Summary counts for Tab Bar
        $summary = [
            'total' => Component::count(),
            'today' => Component::whereDate('created_at', Carbon::today())->count(),
            'ready' => Component::whereNull('assigned_asset_id')
                ->where(function ($q) {
                    $q->whereNull('status_id')
                        ->orWhereHas('status', fn ($s) => $s->whereIn('name', ['Ready', 'Ready to Deploy', 'Available', 'In Stock']));
                })->count(),
            'installed' => Component::where(function ($q) {
                $q->whereNotNull('assigned_asset_id')
                    ->orWhereHas('status', fn ($s) => $s->whereIn('name', ['Installed', 'Deployed']));
            })->count(),
            'service' => Component::whereHas('status', fn ($s) => $s->whereIn('name', ['In Repair', 'Service', 'Maintenance']))->count(),
            'scrapped' => Component::whereHas('status', fn ($s) => $s->whereIn('name', ['Scrapped', 'Broken', 'Retired']))->count(),
        ];

        return view('pages.components.index', compact('components', 'products', 'categories', 'statuses', 'locations', 'assetOpts', 'summary'));
    }

    public function create()
    {
        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();

        $products = Product::where('master_category_id', $componentMasterCat?->id)
            ->with(['brand', 'category'])
            ->orderBy('name')
            ->get();

        $productOpts = $products->map(fn ($p) => [
            'id' => $p->id,
            'text' => $p->name.($p->brand ? ' ('.$p->brand->name.')' : '').($p->category ? ' - '.$p->category->name : ''),
        ])->values()->all();

        $statuses = Status::where('master_category_id', $componentMasterCat?->id)->orderBy('name')->get();
        $statusOpts = $statuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->values()->all();
        $readyStatus = $statuses->firstWhere('name', 'Ready') ?? $statuses->first();

        $locations = Location::orderBy('name')->get();
        $locationOpts = $locations->map(fn ($l) => ['id' => $l->id, 'text' => $l->name])->values()->all();

        $vendors = Vendor::orderBy('name')->get();
        $vendorOpts = $vendors->map(fn ($v) => ['id' => $v->id, 'text' => $v->name])->values()->all();

        return view('pages.components.create', compact('productOpts', 'statusOpts', 'readyStatus', 'locationOpts', 'vendorOpts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'component_tag' => 'nullable|string|max:255|unique:components,component_tag',
            'product_id' => 'required|exists:products,id',
            'status_id' => 'nullable|exists:statuses,id',
            'model_number' => 'nullable|string|max:255',
            'serial' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:1',
            'min_qty' => 'nullable|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'release_date' => 'nullable|date',
            'warranty_months' => 'nullable|integer|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        $totalQty = $this->componentService->createBatch($validated);

        if ($request->has('create_another')) {
            return redirect()->route('components.create')->with('success', "{$totalQty} component unit(s) created successfully. Add another batch.");
        }

        return redirect()->route('components.index')->with('success', "{$totalQty} component unit(s) created successfully.");
    }

    public function show(Component $component)
    {
        $component->load(['product.brand', 'product.category', 'category', 'location', 'vendor', 'assignedAsset.assignedToUser']);
        $allAssets = Asset::with(['product', 'assignedToUser'])->orderBy('asset_tag')->get();

        $assetOpts = $allAssets->map(fn ($a) => [
            'id' => $a->id,
            'text' => $a->asset_tag.' - '.($a->product?->name ?? $a->device_name).($a->assignedToUser ? ' ('.$a->assignedToUser->name.')' : ''),
        ])->values()->all();

        $tickets = Ticket::with(['status', 'priority', 'ticketCategory', 'assignedTo'])
            ->where(function ($q) use ($component) {
                $q->where('problem_description', 'like', "%{$component->name}%")
                    ->orWhere('problem_description', 'like', "%{$component->component_tag}%");
                if ($component->assigned_asset_id) {
                    $q->orWhere('asset_id', $component->assigned_asset_id);
                }
            })
            ->latest()
            ->take(15)
            ->get();

        return view('pages.components.show', compact('component', 'assetOpts', 'tickets'));
    }

    public function edit(Component $component)
    {
        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();

        $products = Product::where('master_category_id', $componentMasterCat?->id)
            ->with(['brand', 'category'])
            ->orderBy('name')
            ->get();

        $productOpts = $products->map(fn ($p) => [
            'id' => $p->id,
            'text' => $p->name.($p->brand ? ' ('.$p->brand->name.')' : '').($p->category ? ' - '.$p->category->name : ''),
        ])->values()->all();

        $statuses = Status::where('master_category_id', $componentMasterCat?->id)->orderBy('name')->get();
        $statusOpts = $statuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->values()->all();

        $locations = Location::orderBy('name')->get();
        $locationOpts = $locations->map(fn ($l) => ['id' => $l->id, 'text' => $l->name])->values()->all();

        $vendors = Vendor::orderBy('name')->get();
        $vendorOpts = $vendors->map(fn ($v) => ['id' => $v->id, 'text' => $v->name])->values()->all();

        return view('pages.components.edit', compact('component', 'productOpts', 'statusOpts', 'locationOpts', 'vendorOpts'));
    }

    public function update(Request $request, Component $component)
    {
        $validated = $request->validate([
            'component_tag' => 'nullable|string|max:255|unique:components,component_tag,'.$component->id,
            'product_id' => 'required|exists:products,id',
            'status_id' => 'nullable|exists:statuses,id',
            'model_number' => 'nullable|string|max:255',
            'serial' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:0',
            'min_qty' => 'nullable|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'release_date' => 'nullable|date',
            'warranty_months' => 'nullable|integer|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        $this->componentService->updateComponent($component, $validated);

        return redirect()->route('components.index')->with('success', 'Component updated successfully.');
    }

    public function destroy(Component $component)
    {
        $component->delete();

        return redirect()->route('components.index')->with('success', 'Component deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:components,id',
        ]);

        Component::whereIn('id', $request->ids)->delete();

        return redirect()->route('components.index')->with('success', 'Selected components deleted successfully.');
    }

    public function checkout(Request $request, Component $component)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'release_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($component->status === 'Installed' && $component->assigned_asset_id) {
            return back()->with('error', 'This component unit is already installed on another asset.');
        }

        $asset = Asset::with(['assignedToUser', 'product.category'])->findOrFail($request->asset_id);
        $this->componentService->checkout($component, $asset, $request->release_date, $request->notes);

        $assetName = $asset->product?->name ?? $asset->device_name ?? $asset->asset_tag;

        return back()->with('success', "Component {$component->component_tag} ({$component->name}) checked out & installed to {$assetName} ({$asset->asset_tag}). Maintenance ticket generated.");
    }

    public function checkin(Request $request, Component $component)
    {
        if (! $component->assigned_asset_id) {
            return back()->with('error', 'Component unit is not currently installed on any asset.');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $asset = $component->assignedAsset;
        $this->componentService->checkin($component, $request->notes);

        $assetName = $asset?->product?->name ?? $asset?->device_name ?? $asset?->asset_tag ?? 'Asset';

        return back()->with('success', "Component {$component->component_tag} ({$component->name}) checked in & removed from {$assetName}. Maintenance ticket generated.");
    }
}
