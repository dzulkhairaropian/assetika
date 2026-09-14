<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Component;
use App\Models\Location;
use App\Models\MasterCategory;
use App\Models\Priority;
use App\Models\Product;
use App\Models\SlaStatus;
use App\Models\Source;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\TicketType;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = Component::query()->with(['product.brand', 'product.category', 'category', 'location', 'vendor', 'assignedAsset.assignedToUser', 'status']);

        // Tab filter handling
        $tab = $request->input('tab');
        if ($tab === 'ready') {
            $query->where(function ($q) {
                $q->whereNull('assigned_asset_id')
                    ->whereHas('status', fn ($s) => $s->where('name', 'Ready'));
            });
        } elseif ($tab === 'installed') {
            $query->where(function ($q) {
                $q->whereNotNull('assigned_asset_id')
                    ->orWhereHas('status', fn ($s) => $s->where('name', 'Installed'));
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

        $components = $query->latest()->paginate(50)->withQueryString();

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

        // Summary counts for Tab Bar
        $summary = [
            'total' => Component::count(),
            'ready' => Component::whereNull('assigned_asset_id')->whereHas('status', fn ($s) => $s->where('name', 'Ready'))->count(),
            'installed' => Component::whereNotNull('assigned_asset_id')->orWhereHas('status', fn ($s) => $s->where('name', 'Installed'))->count(),
            'service' => Component::whereHas('status', fn ($s) => $s->whereIn('name', ['In Repair', 'Service', 'Maintenance']))->count(),
            'scrapped' => Component::whereHas('status', fn ($s) => $s->whereIn('name', ['Scrapped', 'Broken', 'Retired']))->count(),
        ];

        return view('pages.components.index', compact('components', 'products', 'categories', 'statuses', 'summary'));
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

        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();
        if (empty($validated['status_id'])) {
            $defaultStatus = Status::where('master_category_id', $componentMasterCat?->id)->where('name', 'Ready')->first() ?? Status::where('master_category_id', $componentMasterCat?->id)->first();
            $validated['status_id'] = $defaultStatus?->id;
        }

        $statusModel = Status::find($validated['status_id']);
        $validated['status'] = $statusModel?->name ?? 'Ready';

        $product = Product::with(['brand', 'category'])->findOrFail($validated['product_id']);
        $validated['name'] = $product->name;
        $validated['category_id'] = $product->category_id;
        $validated['manufacturer'] = $product->brand?->name;
        if (empty($validated['model_number'])) {
            $validated['model_number'] = $product->product_type ?? $product->name;
        }

        $totalQty = (int) $validated['qty'];
        $singleCost = $validated['purchase_cost'] ? ($validated['purchase_cost'] / max(1, $totalQty)) : null;

        // Batch creation for N individual component units
        for ($i = 0; $i < $totalQty; $i++) {
            $unitData = $validated;
            $unitData['qty'] = 1;
            $unitData['purchase_cost'] = $singleCost;
            if ($i > 0) {
                // Let booted hook generate unique component_tag per item
                unset($unitData['component_tag']);
                if (! empty($unitData['serial'])) {
                    $unitData['serial'] = $unitData['serial'].'-'.($i + 1);
                }
            }
            Component::create($unitData);
        }

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

        if (! empty($validated['status_id'])) {
            $statusModel = Status::find($validated['status_id']);
            $validated['status'] = $statusModel?->name ?? 'Ready';
        }

        $product = Product::with(['brand', 'category'])->findOrFail($validated['product_id']);
        $validated['name'] = $product->name;
        $validated['category_id'] = $product->category_id;
        $validated['manufacturer'] = $product->brand?->name;
        if (empty($validated['model_number'])) {
            $validated['model_number'] = $product->product_type ?? $product->name;
        }

        $component->update($validated);

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
        $checkoutDate = $request->filled('release_date') ? Carbon::parse($request->release_date) : now();

        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();
        $installedStatus = Status::where('master_category_id', $componentMasterCat?->id)->where('name', 'Installed')->first();

        $component->update([
            'assigned_asset_id' => $asset->id,
            'status_id' => $installedStatus?->id ?? $component->status_id,
            'status' => 'Installed',
            'release_date' => $checkoutDate,
        ]);

        // Auto Generate Ticket for Component Installation
        $this->createAutomaticTicketForComponent(
            $asset,
            $component,
            1,
            'installed',
            $request->notes,
            $checkoutDate
        );

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

        $asset = Asset::with(['assignedToUser', 'product.category'])->findOrFail($component->assigned_asset_id);

        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();
        $readyStatus = Status::where('master_category_id', $componentMasterCat?->id)->where('name', 'Ready')->first();

        $component->update([
            'assigned_asset_id' => null,
            'status_id' => $readyStatus?->id ?? $component->status_id,
            'status' => 'Ready',
        ]);

        // Auto Generate Ticket for Component Removal
        $this->createAutomaticTicketForComponent(
            $asset,
            $component,
            1,
            'removed',
            $request->notes
        );

        $assetName = $asset->product?->name ?? $asset->device_name ?? $asset->asset_tag;

        return back()->with('success', "Component {$component->component_tag} ({$component->name}) checked in & removed from {$assetName} ({$asset->asset_tag}). Maintenance ticket generated.");
    }

    private function createAutomaticTicketForComponent(Asset $asset, Component $component, int $qty, string $action, ?string $notes = null, ?Carbon $customDate = null): void
    {
        $ticketDate = $customDate ?? now();
        $prefix = 'TCK-'.date('Ym').'-';
        $latestTicket = Ticket::where('request_id', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestTicket) {
            $parts = explode('-', $latestTicket->request_id);
            $nextSeq = ((int) end($parts)) + 1;
        }

        $completedStatus = TicketStatus::where('name', 'Completed')->first() ?? TicketStatus::where('is_closed', true)->first();
        $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->first();
        $mediumPriority = Priority::where('name', 'Medium')->first();
        $portalSource = Source::first();
        $maintType = TicketType::where('name', 'like', '%Maintenance%')->first() ?? TicketType::first();

        // Resolve category
        $hardwareCat = TicketCategory::where('name', 'Hardware')->orWhere('code', 'HARDWARE')->first();
        $catId = $hardwareCat?->id ?? TicketCategory::first()?->id;
        $subCatId = null;
        if ($hardwareCat) {
            $sub = TicketSubcategory::where('ticket_category_id', $hardwareCat->id)
                ->where('name', 'like', '%Peripherals%')
                ->first();
            $subCatId = $sub?->id;
        }

        $toUser = $asset->assignedToUser;
        $actionText = $action === 'installed' ? 'Installed/Attached' : 'Removed/Detached';

        $assetName = $asset->product?->name ?? $asset->device_name ?? $asset->asset_tag;
        $desc = "[Auto-Generated] Component {$actionText}: {$qty}x {$component->name} on Asset {$asset->asset_tag} - ".$assetName;
        if ($notes) {
            $desc .= "\nNotes: ".$notes;
        }

        Ticket::create([
            'request_id' => $prefix.str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT),
            'ticket_type_id' => $maintType?->id,
            'ticket_category_id' => $catId,
            'ticket_subcategory_id' => $subCatId,
            'requester_user_id' => $toUser?->id ?? auth()->id(),
            'requester_name' => $toUser?->name ?? auth()->user()?->name,
            'requester_email' => $toUser?->email ?? auth()->user()?->email,
            'requester_company_id' => $toUser?->company_id ?? auth()->user()?->company_id,
            'requester_department_id' => $toUser?->department_id,
            'assigned_to_id' => auth()->id(),
            'created_by_id' => auth()->id(),
            'asset_id' => $asset->id,
            'priority_id' => $mediumPriority?->id,
            'source_id' => $portalSource?->id,
            'status_id' => $completedStatus?->id,
            'sla_status_id' => $fulfilledSla?->id,
            'problem_description' => $desc,
            'resolution_notes' => "Component {$actionText} successfully by ".(auth()->user()?->name ?? 'System').'.',
            'request_date' => $ticketDate->toDateString(),
            'request_time' => $ticketDate,
            'completed_date' => $ticketDate->toDateString(),
            'completed_time' => $ticketDate,
            'created_at' => $ticketDate,
        ]);
    }
}
