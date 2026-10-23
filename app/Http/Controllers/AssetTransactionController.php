<?php

namespace App\Http\Controllers;

use App\Exports\AssetTransactionsExport;
use App\Models\AssetCheckout;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AssetTransactionController extends Controller
{
    public function export(Request $request)
    {
        return (new AssetTransactionsExport($request->all()))->download('asset-transactions-'.now()->format('Y-m-d').'.xlsx');
    }

    public function index(Request $request)
    {
        $query = AssetCheckout::with([
            'asset.product.brand',
            'department',
            'checkedOutToUser.department',
            'checkedOutByUser',
            'checkedInByUser',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('asset', function ($aq) use ($search) {
                    $aq->where('asset_tag', 'like', "%$search%")
                        ->orWhere('serial_number', 'like', "%$search%")
                        ->orWhere('device_name', 'like', "%$search%")
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%$search%");
                        });
                })
                    ->orWhereHas('checkedOutToUser', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    });
            });
        }

        $summary = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->whereNull('checked_in_at')->count(),
            'overdue' => (clone $query)->whereNull('checked_in_at')->whereNotNull('expected_return_date')->whereDate('expected_return_date', '<', Carbon::today())->count(),
            'returned' => (clone $query)->whereNotNull('checked_in_at')->count(),
            'today' => (clone $query)->where(function ($q) {
                $q->whereDate('checked_out_at', Carbon::today())
                    ->orWhereDate('checked_in_at', Carbon::today());
            })->count(),
        ];

        $tab = $request->input('tab');
        if ($tab === 'active') {
            $query->whereNull('checked_in_at');
        } elseif ($tab === 'returned') {
            $query->whereNotNull('checked_in_at');
        } elseif ($tab === 'overdue') {
            $query->whereNull('checked_in_at')
                ->whereNotNull('expected_return_date')
                ->whereDate('expected_return_date', '<', Carbon::today());
        } elseif ($tab === 'today') {
            $query->where(function ($q) {
                $q->whereDate('checked_out_at', Carbon::today())
                    ->orWhereDate('checked_in_at', Carbon::today());
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $transactions = $query->latest('checked_out_at')->paginate($perPage)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('pages.assets.transactions', compact('transactions', 'summary', 'departments'));
    }
}
