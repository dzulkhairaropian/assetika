<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Component;
use App\Models\Location;
use App\Models\MasterCategory;
use App\Models\Priority;
use App\Models\Product;
use App\Models\SlaStatus;
use App\Models\Source;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\TicketType;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = Component::query()->with(['product.brand', 'product.category', 'category', 'location', 'vendor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('serial', 'like', "%$search%")
                    ->orWhere('model_number', 'like', "%$search%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%$search%"))
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%$search%"));
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $components = $query->latest()->paginate(50)->withQueryString();

        $products = Product::whereHas('masterCategory', fn ($q) => $q->where('name', 'IT Component'))
            ->orderBy('name')
            ->get();
        $categories = Category::whereHas('masterCategory', fn ($q) => $q->where('name', 'IT Component'))
            ->orderBy('name')
            ->get();

        return view('pages.components.index', compact('components', 'products', 'categories'));
    }

    public function create()
    {
        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();

        $products = Product::where('master_category_id', $componentMasterCat?->id)
            ->with(['brand', 'category'])
            ->orderBy('name')
            ->get();

        $categories = Category::where('master_category_id', $componentMasterCat?->id)
            ->orderBy('name')
            ->get();

        $brands = Brand::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();

        return view('pages.components.create', compact('products', 'categories', 'brands', 'locations', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'serial' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:0',
            'min_qty' => 'nullable|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_months' => 'nullable|integer|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        if (! empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                if (empty($validated['category_id'])) {
                    $validated['category_id'] = $product->category_id;
                }
                if (empty($validated['manufacturer']) && $product->brand) {
                    $validated['manufacturer'] = $product->brand->name;
                }
            }
        }

        Component::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('components.create')->with('success', 'Component created successfully. Add another one.');
        }

        return redirect()->route('components.index')->with('success', 'Component created successfully.');
    }

    public function show(Component $component)
    {
        $component->load(['product.brand', 'product.category', 'category', 'location', 'vendor', 'assets.assignedToUser']);
        $available = $component->availableQty();
        $checkouts = $component->assets()
            ->withPivot(['qty', 'checked_out_at'])
            ->orderBy('component_asset.created_at', 'desc')
            ->get();

        $allAssets = Asset::with(['product', 'assignedToUser'])->orderBy('name')->get();

        return view('pages.components.show', compact('component', 'available', 'checkouts', 'allAssets'));
    }

    public function edit(Component $component)
    {
        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();

        $products = Product::where('master_category_id', $componentMasterCat?->id)
            ->with(['brand', 'category'])
            ->orderBy('name')
            ->get();

        $categories = Category::where('master_category_id', $componentMasterCat?->id)
            ->orderBy('name')
            ->get();

        $brands = Brand::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();

        return view('pages.components.edit', compact('component', 'products', 'categories', 'brands', 'locations', 'vendors'));
    }

    public function update(Request $request, Component $component)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'serial' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:0',
            'min_qty' => 'nullable|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_months' => 'nullable|integer|min:0',
            'vendor_id' => 'nullable|exists:vendors,id',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
        ]);

        if (! empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                if (empty($validated['category_id'])) {
                    $validated['category_id'] = $product->category_id;
                }
                if (empty($validated['manufacturer']) && $product->brand) {
                    $validated['manufacturer'] = $product->brand->name;
                }
            }
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
            'qty' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $available = $component->availableQty();
        if ($request->qty > $available) {
            return back()->with('error', "Insufficient stock. Available: {$available}.");
        }

        $asset = Asset::with(['assignedToUser', 'product.category'])->findOrFail($request->asset_id);

        $existing = $component->assets()->where('asset_id', $asset->id)->first();
        if ($existing) {
            $component->assets()->updateExistingPivot($asset->id, [
                'qty' => $existing->pivot->qty + $request->qty,
                'checked_out_at' => now(),
            ]);
        } else {
            $component->assets()->attach($asset->id, [
                'qty' => $request->qty,
                'checked_out_at' => now(),
            ]);
        }

        // Auto Generate Ticket for Component Installation
        $this->createAutomaticTicketForComponent(
            $asset,
            $component,
            $request->qty,
            'installed',
            $request->notes
        );

        return back()->with('success', "{$request->qty}x {$component->name} checked out & installed to {$asset->name} ({$asset->asset_tag}). Maintenance ticket generated.");
    }

    public function checkin(Request $request, Component $component, Asset $asset)
    {
        $checkout = $component->assets()->where('asset_id', $asset->id)->first();

        if (! $checkout) {
            return back()->with('error', 'Asset does not have this component checked out.');
        }

        $request->validate([
            'qty' => 'required|integer|min:1|max:'.$checkout->pivot->qty,
            'notes' => 'nullable|string',
        ]);

        if ($request->qty < $checkout->pivot->qty) {
            $component->assets()->updateExistingPivot($asset->id, [
                'qty' => $checkout->pivot->qty - $request->qty,
            ]);
        } else {
            $component->assets()->detach($asset->id);
        }

        // Auto Generate Ticket for Component Removal
        $this->createAutomaticTicketForComponent(
            $asset,
            $component,
            $request->qty,
            'removed',
            $request->notes
        );

        return back()->with('success', "{$request->qty}x {$component->name} checked in & removed from {$asset->name} ({$asset->asset_tag}). Maintenance ticket generated.");
    }

    private function createAutomaticTicketForComponent(Asset $asset, Component $component, int $qty, string $action, ?string $notes = null): void
    {
        $ticketDate = now();
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

        $desc = "[Auto-Generated] Component {$actionText}: {$qty}x {$component->name} on Asset {$asset->asset_tag} - ".($asset->product->name ?? $asset->name);
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
