<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCheckout;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('pages.reports.index');
    }

    public function activity(Request $request)
    {
        $query = AssetCheckout::with(['asset', 'checkedOutToUser', 'checkedOutByUser']);

        if ($request->filled('from')) {
            $query->whereDate('checked_out_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('checked_out_at', '<=', $request->to);
        }
        if ($request->filled('user_id')) {
            $query->where('checked_out_to_user_id', $request->user_id);
        }

        $activities = $query->orderBy('checked_out_at', 'desc')->paginate(50)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();

        return view('pages.reports.activity', compact('activities', 'users'));
    }

    public function assets(Request $request)
    {
        $query = Asset::with(['product.category', 'status', 'department.company', 'location']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($b) use ($q) {
                $b->where('asset_tag', 'like', "%$q%")
                    ->orWhere('device_name', 'like', "%$q%")
                    ->orWhere('serial_number', 'like', "%$q%");
            });
        }
        if ($request->filled('status_id')) $query->where('status_id', $request->status_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);

        $assets = $query->latest()->paginate(50)->withQueryString();
        $statuses = \App\Models\Status::all();
        $departments = \App\Models\Department::all();

        return view('pages.reports.assets', compact('assets', 'statuses', 'departments'));
    }

    public function depreciation(Request $request)
    {
        $query = Asset::with(['depreciation', 'product.category'])
            ->whereNotNull('purchase_cost')
            ->whereNotNull('depreciation_id')
            ->whereNotNull('purchase_date');

        $assets = $query->paginate(50);

        $totalPurchase = (clone $query)->sum('purchase_cost');
        $totalBook = 0;
        foreach ($query->get() as $a) { $totalBook += $a->bookValue; }

        return view('pages.reports.depreciation', compact('assets', 'totalPurchase', 'totalBook'));
    }

    public function custom()
    {
        return view('pages.reports.custom');
    }
}
