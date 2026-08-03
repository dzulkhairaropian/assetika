<?php

namespace App\Http\Controllers;

use App\Models\AssetCheckout;
use Illuminate\Http\Request;

use App\Exports\AssetTransactionsExport;

class AssetTransactionController extends Controller
{
    public function export(Request $request)
    {
        return (new AssetTransactionsExport($request->all()))->download('asset-transactions-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function index(Request $request)
    {
        $query = AssetCheckout::with([
            'asset.product.brand', 
            'checkedOutToUser', 
            'checkedOutByUser', 
            'checkedInByUser'
        ]);

        if ($request->filled('type')) {
            if ($request->type === 'active') {
                $query->whereNull('checked_in_at');
            } elseif ($request->type === 'returned') {
                $query->whereNotNull('checked_in_at');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('asset', function($aq) use ($search) {
                    $aq->where('asset_tag', 'like', "%$search%")
                      ->orWhere('serial_number', 'like', "%$search%")
                      ->orWhereHas('product', function($pq) use ($search) {
                          $pq->where('name', 'like', "%$search%");
                      });
                })
                ->orWhereHas('checkedOutToUser', function($uq) use ($search) {
                    $uq->where('name', 'like', "%$search%");
                });
            });
        }

        $transactions = $query->latest('checked_out_at')->paginate(25)->withQueryString();

        return view('pages.assets.transactions', compact('transactions'));
    }
}
