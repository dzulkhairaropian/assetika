<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use App\Models\ConsumableTransaction;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsumableController extends Controller
{
    public function index(Request $request)
    {
        $query = Consumable::with(['product.brand', 'product.category', 'status']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('product_type', 'like', "%$search%");
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('brand_id')) {
            $query->whereHas('product', function($q) use ($request) {
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

        $consumables = $query->latest()->paginate(50)->withQueryString();
        
        $products = Product::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Consumable');
        })->get();

        $categories = Category::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Consumable');
        })->orderBy('name')->get();

        $brands = Brand::orderBy('name')->get();

        return view('pages.consumables.index', compact('consumables', 'products', 'categories', 'brands', 'consumableSummary'));
    }

    public function create()
    {
        // Consumables are products with master categories that are consumables
        $products = Product::with(['brand', 'category'])
            ->whereHas('masterCategory', function($q) {
                $q->where('name', 'IT Consumable');
            })->get();
        return view('pages.consumables.create', compact('products'));
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
            ->whereHas('masterCategory', function($q) {
                $q->where('name', 'IT Consumable');
            })->get();
        return view('pages.consumables.edit', compact('consumable', 'products'));
    }

    public function update(Request $request, Consumable $consumable)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id|unique:consumables,product_id,' . $consumable->id,
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

        DB::transaction(function () use ($request, $consumable) {
            $qty = $request->quantity;
            $unitCost = $request->unit_cost ?? $consumable->purchase_cost ?? 0;
            $totalCost = $qty * $unitCost;

            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')->store('consumables/transactions', 'public');
            }

            $before = $consumable->quantity;
            $after = $before + $qty;

            ConsumableTransaction::create([
                'consumable_id' => $consumable->id,
                'user_id' => auth()->id(),
                'type' => 'restock',
                'quantity' => $qty,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'evidence_file_path' => $evidencePath,
                'notes' => $request->notes,
                'transaction_date' => $request->transaction_date,
            ]);

            $consumable->update([
                'quantity' => $after,
                'purchase_cost' => $unitCost // Update default purchase cost to latest restock cost
            ]);
        });

        $auth = auth()->user();
        if ($auth) {
            $auth->notify(new \App\Notifications\SystemNotification(
                'Consumable Restocked',
                "{$request->quantity} units of '" . ($consumable->product->name ?? 'Consumable') . "' have been restocked.",
                route('consumables.index'),
                'plus-circle',
                'Consumable'
            ));
        }

        return redirect()->route('consumables.index')->with('success', 'Consumable restocked successfully.');
    }

    public function checkout(Consumable $consumable)
    {
        if ($consumable->quantity <= 0) {
            return redirect()->route('consumables.index')->with('error', 'Consumable is out of stock.');
        }

        $users = User::all();
        return view('pages.consumables.checkout', compact('consumable', 'users'));
    }

    public function processCheckout(Request $request, Consumable $consumable)
    {
        $request->validate([
            'checked_out_to_user_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1|max:' . $consumable->quantity,
            'notes' => 'nullable|string',
            'evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'transaction_date' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $consumable) {
            $qty = $request->quantity;
            $unitCost = $consumable->purchase_cost ?? 0;
            $totalCost = $qty * $unitCost;

            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')->store('consumables/transactions', 'public');
            }

            $before = $consumable->quantity;
            $after = $before - $qty;

            ConsumableTransaction::create([
                'consumable_id' => $consumable->id,
                'user_id' => auth()->id(),
                'checked_out_to_user_id' => $request->checked_out_to_user_id,
                'type' => 'checkout',
                'quantity' => $qty,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'evidence_file_path' => $evidencePath,
                'notes' => $request->notes,
                'transaction_date' => $request->transaction_date,
            ]);

            $consumable->update([
                'quantity' => $after,
            ]);
        });

        $toUser = User::find($request->checked_out_to_user_id);
        if ($toUser) {
            $toUser->notify(new \App\Notifications\SystemNotification(
                'Consumable Checked Out',
                "{$request->quantity} units of '" . ($consumable->product->name ?? 'Consumable') . "' have been checked out to you.",
                route('consumables.index'),
                'shopping-bag',
                'Consumable'
            ));
        }

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

        $users = User::all();
        return view('pages.consumables.bulk-checkout', compact('consumables', 'users'));
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

        $quantities = $request->input('quantities');
        $consumableIds = array_keys($quantities);

        $consumables = Consumable::whereIn('id', $consumableIds)->get();

        // Validate quantities against stock
        foreach ($consumables as $consumable) {
            $qty = $quantities[$consumable->id] ?? 0;
            if ($qty > $consumable->quantity) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Quantity for " . ($consumable->product->name ?? 'Consumable') . " cannot exceed available stock ({$consumable->quantity}).");
            }
        }

        DB::transaction(function () use ($request, $consumables, $quantities) {
            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')->store('consumables/transactions', 'public');
            }

            foreach ($consumables as $consumable) {
                $qty = $quantities[$consumable->id];
                $unitCost = $consumable->purchase_cost ?? 0;
                $totalCost = $qty * $unitCost;
                $before = $consumable->quantity;
                $after = $before - $qty;

                ConsumableTransaction::create([
                    'consumable_id' => $consumable->id,
                    'user_id' => auth()->id(),
                    'checked_out_to_user_id' => $request->checked_out_to_user_id,
                    'type' => 'checkout',
                    'quantity' => $qty,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'evidence_file_path' => $evidencePath,
                    'notes' => $request->notes,
                    'transaction_date' => now(),
                ]);

                $consumable->update([
                    'quantity' => $after,
                ]);
            }
        });

        $toUser = User::find($request->checked_out_to_user_id);
        if ($toUser) {
            $details = [];
            foreach ($consumables as $consumable) {
                $qty = $quantities[$consumable->id];
                $details[] = "{$qty}x " . ($consumable->product->name ?? 'Consumable');
            }
            $message = "Consumables checked out to you: " . implode(', ', $details);

            $toUser->notify(new \App\Notifications\SystemNotification(
                'Consumables Checked Out',
                $message,
                route('consumables.index'),
                'shopping-bag',
                'Consumable'
            ));
        }

        return redirect()->route('consumables.index')->with('success', 'Selected consumables checked out successfully.');
    }
}
