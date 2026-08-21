<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Status;

class AssetService
{
    public function createAsset(array $data, bool $isDraft = false): Asset
    {
        if (empty($data['status_id']) || $isDraft) {
            $draftStatus = Status::firstOrCreate(
                ['name' => 'Draft'],
                ['color' => 'gray', 'icon' => 'file-text']
            );
            $data['status_id'] = $draftStatus->id;
        }

        $asset = Asset::create($data);

        if (isset($data['custom_fields'])) {
            $asset->syncCustomFields($data['custom_fields']);
        }

        return $asset;
    }

    public function updateAsset(Asset $asset, array $data, bool $isDraft = false): Asset
    {
        if ($isDraft) {
            $draftStatus = Status::firstOrCreate(
                ['name' => 'Draft'],
                ['color' => 'gray', 'icon' => 'file-text']
            );
            $data['status_id'] = $draftStatus->id;
        }

        $asset->update($data);

        if (isset($data['custom_fields'])) {
            $asset->syncCustomFields($data['custom_fields']);
        }

        return $asset;
    }
}
