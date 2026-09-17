<?php

namespace App\Http\Controllers;

use App\Exports\ConsumableTransactionsExport;
use App\Models\ConsumableTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ConsumableTransactionController extends Controller
{
    public function export(Request $request)
    {
        return (new ConsumableTransactionsExport($request->all()))->download('consumable-transactions-'.now()->format('Y-m-d').'.xlsx');
    }

    public function index(Request $request)
    {
        $query = ConsumableTransaction::with(['consumable.product.brand', 'user', 'checkedOutToUser']);

        $tab = $request->input('tab');
        if ($tab === 'restock') {
            $query->where('type', 'restock');
        } elseif ($tab === 'checkout') {
            $query->where('type', 'checkout');
        } elseif ($tab === 'today') {
            $query->whereDate('transaction_date', Carbon::today());
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $startDate = $request->input('start_date', $request->input('from_date', $request->input('created_from')));
        $endDate = $request->input('end_date', $request->input('to_date', $request->input('created_to')));

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('consumable.product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%$search%");
                })
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('checkedOutToUser', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%$search%");
                    });
            });
        }

        $summary = [
            'total' => (clone $query)->count(),
            'restock' => (clone $query)->where('type', 'restock')->count(),
            'checkout' => (clone $query)->where('type', 'checkout')->count(),
            'today' => (clone $query)->whereDate('transaction_date', Carbon::today())->count(),
        ];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $transactions = $query->latest('transaction_date')->paginate($perPage)->withQueryString();

        return view('pages.consumables.transactions', compact('transactions', 'summary'));
    }
}
