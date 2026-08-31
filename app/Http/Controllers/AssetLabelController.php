<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetLabelController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
            'quantities' => 'nullable|array',
            'copies' => 'nullable|integer|min:1|max:10',
        ]);

        $assetsRaw = Asset::whereIn('id', $request->ids)
            ->with(['product.category', 'product.brand', 'department.company', 'location'])
            ->get();
        
        $quantities = $request->input('quantities', []);
        $multiplier = (int) $request->input('copies', 1);
        $assets = collect();

        foreach ($assetsRaw as $asset) {
            $baseQty = isset($quantities[$asset->id]) ? (int) $quantities[$asset->id] : 1;
            $qty = max(1, min(50, $baseQty * $multiplier));
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
        $asset->load(['product.category', 'product.brand', 'department.company', 'location']);
        for ($i = 0; $i < $qty; $i++) {
            $assets->push($asset);
        }

        return view('pages.labels.print', compact('assets'));
    }
}
