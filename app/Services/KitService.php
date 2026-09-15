<?php

namespace App\Services;

use App\Models\Kit;
use Illuminate\Support\Facades\DB;

class KitService
{
    public function createKit(array $data, array $items): Kit
    {
        return DB::transaction(function () use ($data, $items) {
            $kit = Kit::create($data);

            if (! empty($items['licenses'])) {
                $syncLicenses = [];
                foreach ($items['licenses'] as $item) {
                    if (! empty($item['id'])) {
                        $syncLicenses[$item['id']] = ['qty' => $item['qty'] ?? 1];
                    }
                }
                $kit->licenses()->sync($syncLicenses);
            }

            if (! empty($items['components'])) {
                $syncComponents = [];
                foreach ($items['components'] as $item) {
                    if (! empty($item['id'])) {
                        $syncComponents[$item['id']] = ['qty' => $item['qty'] ?? 1];
                    }
                }
                $kit->components()->sync($syncComponents);
            }

            if (! empty($items['consumables'])) {
                $syncConsumables = [];
                foreach ($items['consumables'] as $item) {
                    if (! empty($item['id'])) {
                        $syncConsumables[$item['id']] = ['qty' => $item['qty'] ?? 1];
                    }
                }
                $kit->consumables()->sync($syncConsumables);
            }

            return $kit;
        });
    }
}
