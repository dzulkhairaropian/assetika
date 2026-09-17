<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\Product;
use App\Models\User;
use App\Services\ConsumableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsumableController extends Controller
{
    protected ConsumableService $consumableService;

    public function __construct(ConsumableService $consumableService)
    {
        $this->consumableService = $consumableService;
    }

    public function index(Request $request)
    {
        $query = Consumable::with(['product.brand', 'product.category', 'status']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('product_type', 'like', "%$search%");
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('brand_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        $consumableSummary = [
            'total' => (clone $query)->count(),
            'in_stock' => (clone $query)->where('quantity', '>', DB::raw('low_threshold'))->count(),
            'low_stock' => (clone $query)->where('quantity', '<=', DB::raw('low_threshold'))->where('quantity', '>', 0)->count(),
            'out_of_stock' => (clone $query)->where('quantity', '<=', 0)->count(),
        ];

        $activeTab = $request->input('tab') ?: $request->input('status');
        if ($activeTab === 'in_stock') {
            $query->where('quantity', '>', DB::raw('low_threshold'));
        } elseif ($activeTab === 'low_stock') {
            $query->where('quantity', '<=', DB::raw('low_threshold'))->where('quantity', '>', 0);
        } elseif ($activeTab === 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $consumables = $query->latest()->paginate($perPage)->withQueryString();

        $products = Product::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Consumable');
        })->get();

        $categories = Category::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Consumable');
        })->orderBy('name')->get();

        $brands = Brand::orderBy('name')->get();

        return view('pages.consumables.index', compact('consumables', 'products', 'categories', 'brands', 'consumableSummary'));
    }

    public function create()
    {
        // Consumables are products with master categories that are consumables
        $products = Product::with(['brand', 'category'])
            ->whereHas('masterCategory', function ($q) {
                $q->where('name', 'IT Consumable');
            })->get();

        $productOpts = $products->map(fn ($p) => [
            'id' => $p->id,
            'text' => ($p->brand->name ?? '').' - '.$p->name.' ('.($p->category->name ?? '').')',
        ])->values()->all();

        return view('pages.consumables.create', compact('productOpts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id|unique:consumables,product_id',
            'quantity' => 'required|integer|min:0',
            'low_threshold' => 'required|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        Consumable::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('consumables.create')->with('success', 'Consumable created successfully. Add another one.');
        }

        return redirect()->route('consumables.index')->with('success', 'Consumable created successfully.');
    }

    public function edit(Consumable $consumable)
    {
        $products = Product::with(['brand', 'category'])
            ->whereHas('masterCategory', function ($q) {
                $q->where('name', 'IT Consumable');
            })->get();

        $productOpts = $products->map(fn ($p) => [
            'id' => $p->id,
            'text' => ($p->brand->name ?? '').' - '.$p->name.' ('.($p->category->name ?? '').')',
        ])->values()->all();

        return view('pages.consumables.edit', compact('consumable', 'productOpts'));
    }

    public function update(Request $request, Consumable $consumable)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id|unique:consumables,product_id,'.$consumable->id,
            'quantity' => 'required|integer|min:0',
            'low_threshold' => 'required|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $consumable->update($validated);

        return redirect()->route('consumables.index')->with('success', 'Consumable updated successfully.');
    }

    public function destroy(Consumable $consumable)
    {
        $consumable->delete();

        return redirect()->route('consumables.index')->with('success', 'Consumable deleted successfully.');
    }

    public function restock(Consumable $consumable)
    {
        return view('pages.consumables.restock', compact('consumable'));
    }

    public function processRestock(Request $request, Consumable $consumable)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'transaction_date' => 'required|date',
        ]);

        $this->consumableService->processRestock($consumable, (int) $request->quantity, $request->all(), $request->file('evidence'));

        return redirect()->route('consumables.index')->with('success', 'Consumable restocked successfully.');
    }

    public function checkout(Consumable $consumable)
    {
        if ($consumable->quantity <= 0) {
            return redirect()->route('consumables.index')->with('error', 'Consumable is out of stock.');
        }

        $users = User::orderBy('name')->get();
        $userOpts = $users->map(fn ($u) => ['id' => $u->id, 'text' => $u->name.' ('.$u->email.')'])->values()->all();

        return view('pages.consumables.checkout', compact('consumable', 'userOpts'));
    }

    public function processCheckout(Request $request, Consumable $consumable)
    {
        $request->validate([
            'checked_out_to_user_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1|max:'.$consumable->quantity,
            'notes' => 'nullable|string',
            'evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'transaction_date' => 'required|date',
        ]);

        $toUser = User::findOrFail($request->checked_out_to_user_id);
        $this->consumableService->processCheckout($consumable, $toUser, (int) $request->quantity, $request->all(), $request->file('evidence'));

        return redirect()->route('consumables.index')->with('success', 'Consumable checked out successfully.');
    }

    public function bulkCheckoutForm(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            return redirect()->route('consumables.index')->with('error', 'No consumables selected for checkout.');
        }

        $consumables = Consumable::whereIn('id', $ids)->get();

        // Check if any are out of stock
        foreach ($consumables as $consumable) {
            if ($consumable->quantity <= 0) {
                return redirect()->route('consumables.index')->with('error', 'Some selected consumables are out of stock.');
            }
        }

        $users = User::orderBy('name')->get();
        $userOpts = $users->map(fn ($u) => ['id' => $u->id, 'text' => $u->name.' ('.$u->email.')'])->values()->all();

        return view('pages.consumables.bulk-checkout', compact('consumables', 'userOpts'));
    }

    public function processBulkCheckout(Request $request)
    {
        $request->validate([
            'checked_out_to_user_id' => 'required|exists:users,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $quantities = $request->input('quantities', []);
        $consumableIds = array_keys($quantities);
        $consumables = Consumable::whereIn('id', $consumableIds)->get();

        foreach ($consumables as $consumable) {
            $qty = (int) ($quantities[$consumable->id] ?? 0);
            if ($qty > $consumable->quantity) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Quantity for '.($consumable->product?->name ?? 'Consumable')." cannot exceed available stock ({$consumable->quantity}).");
            }
        }

        $toUser = User::findOrFail($request->checked_out_to_user_id);
        $this->consumableService->processBulkCheckout($quantities, $toUser, $request->all(), $request->file('evidence'));

        return redirect()->route('consumables.index')->with('success', 'Bulk checkout completed successfully.');
    }
}
