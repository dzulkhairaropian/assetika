<?php

namespace App\Http\Controllers;

use App\Exports\ConsumableTransactionsExport;
use App\Models\ConsumableTransaction;
use Illuminate\Http\Request;

class ConsumableTransactionController extends Controller
{
    public function export(Request $request)
    {
        return (new ConsumableTransactionsExport($request->all()))->download('consumable-transactions-'.now()->format('Y-m-d').'.xlsx');
    }

    public function index(Request $request)
    {
        $query = ConsumableTransaction::with(['consumable.product.brand', 'user', 'checkedOutToUser']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('consumable.product', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        }

        $transactions = $query->latest()->paginate(50)->withQueryString();

        return view('pages.consumables.transactions', compact('transactions'));
    }
}
