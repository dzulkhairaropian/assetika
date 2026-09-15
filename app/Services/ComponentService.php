<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Component;
use App\Models\MasterCategory;
use App\Models\Product;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComponentService
{
    public function createBatch(array $validated): int
    {
        $product = Product::with(['brand', 'category'])->findOrFail($validated['product_id']);
        $validated['name'] = $product->name;
        $validated['category_id'] = $product->category_id;
        $validated['manufacturer'] = $product->brand?->name;
        if (empty($validated['model_number'])) {
            $validated['model_number'] = $product->product_type ?? $product->name;
        }

        $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();
        if (empty($validated['status_id'])) {
            $defaultStatus = Status::where('master_category_id', $componentMasterCat?->id)->where('name', 'Ready')->first()
                ?? Status::where('master_category_id', $componentMasterCat?->id)->first();
            $validated['status_id'] = $defaultStatus?->id;
        }

        $statusModel = Status::find($validated['status_id']);
        $validated['status'] = $statusModel?->name ?? 'Ready';

        $totalQty = (int) $validated['qty'];
        $singleCost = $validated['purchase_cost'] ? ($validated['purchase_cost'] / max(1, $totalQty)) : null;

        DB::transaction(function () use ($validated, $totalQty, $singleCost) {
            for ($i = 0; $i < $totalQty; $i++) {
                $unitData = $validated;
                $unitData['qty'] = 1;
                $unitData['purchase_cost'] = $singleCost;
                if ($i > 0) {
                    unset($unitData['component_tag']);
                    if (! empty($unitData['serial'])) {
                        $unitData['serial'] = $unitData['serial'].'-'.($i + 1);
                    }
                }
                Component::create($unitData);
            }
        });

        return $totalQty;
    }

    public function updateComponent(Component $component, array $validated): Component
    {
        if (! empty($validated['status_id'])) {
            $statusModel = Status::find($validated['status_id']);
            $validated['status'] = $statusModel?->name ?? 'Ready';
        }

        $product = Product::with(['brand', 'category'])->findOrFail($validated['product_id']);
        $validated['name'] = $product->name;
        $validated['category_id'] = $product->category_id;
        $validated['manufacturer'] = $product->brand?->name;
        if (empty($validated['model_number'])) {
            $validated['model_number'] = $product->product_type ?? $product->name;
        }

        $component->update($validated);

        return $component;
    }

    public function checkout(Component $component, Asset $asset, ?string $releaseDate = null, ?string $notes = null): Component
    {
        return DB::transaction(function () use ($component, $asset, $releaseDate, $notes) {
            $checkoutDate = $releaseDate ? Carbon::parse($releaseDate) : now();

            $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();
            $installedStatus = Status::where('master_category_id', $componentMasterCat?->id)->where('name', 'Installed')->first();

            $component->update([
                'assigned_asset_id' => $asset->id,
                'status_id' => $installedStatus?->id ?? $component->status_id,
                'status' => 'Installed',
                'release_date' => $checkoutDate,
            ]);

            AssetTicketService::createCheckoutTicket($asset, $asset->assignedToUser, "Installed component {$component->component_tag} ({$component->name}). Notes: {$notes}");

            return $component;
        });
    }

    public function checkin(Component $component, ?string $notes = null): Component
    {
        return DB::transaction(function () use ($component, $notes) {
            $asset = $component->assignedAsset;

            $componentMasterCat = MasterCategory::where('name', 'IT Component')->first();
            $readyStatus = Status::where('master_category_id', $componentMasterCat?->id)->where('name', 'Ready')->first();

            $component->update([
                'assigned_asset_id' => null,
                'status_id' => $readyStatus?->id ?? $component->status_id,
                'status' => 'Ready',
            ]);

            if ($asset) {
                AssetTicketService::createCheckinTicket($asset, $asset->assignedToUser, "Detached component {$component->component_tag} ({$component->name}). Notes: {$notes}");
            }

            return $component;
        });
    }
}
