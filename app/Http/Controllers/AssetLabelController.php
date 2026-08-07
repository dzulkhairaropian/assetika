<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetLabelController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%$search%")
                    ->orWhere('device_name', 'like', "%$search%")
                    ->orWhere('serial_number', 'like', "%$search%");
            });
        }

        $assets = $query->orderBy('asset_tag')->paginate(50)->withQueryString();

        return view('pages.labels.index', compact('assets'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
        ]);

        $assets = Asset::whereIn('id', $request->ids)->with('product.category')->get();

        return view('pages.labels.print', compact('assets'));
    }

    public function generateSingle(Asset $asset)
    {
        return view('pages.labels.print', ['assets' => collect([$asset->load('product.category')])]);
    }
}
