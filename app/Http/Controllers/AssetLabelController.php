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
            'quantities' => 'nullable|array',
        ]);

        $assetsRaw = Asset::whereIn('id', $request->ids)->with(['product.category', 'department.company'])->get();
        
        $quantities = $request->input('quantities', []);
        $assets = collect();

        foreach ($assetsRaw as $asset) {
            $qty = isset($quantities[$asset->id]) ? (int) $quantities[$asset->id] : 1;
            $qty = max(1, min(50, $qty)); // limit max 50 copies per asset
            for ($i = 0; $i < $qty; $i++) {
                $assets->push($asset);
            }
        }

        return view('pages.labels.print', compact('assets'));
    }

    public function generateSingle(Request $request, Asset $asset)
    {
        $qty = (int) $request->input('copies', 1);
        $qty = max(1, min(50, $qty));

        $assets = collect();
        $asset->load(['product.category', 'department.company']);
        for ($i = 0; $i < $qty; $i++) {
            $assets->push($asset);
        }

        return view('pages.labels.print', compact('assets'));
    }
}
