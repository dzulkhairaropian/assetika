<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetsExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function query()
    {
        $query = Asset::with([
            'product.brand',
            'product.category',
            'status',
            'assignedToUser',
            'operatingSystem',
            'department.company',
            'location',
            'vendor',
            'depreciation',
            'currentCheckout.checkedOutToUser.department',
        ])->filter($this->filters);

        $sortBy = $this->filters['sort_by'] ?? null;
        $sortDir = strtolower($this->filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'asset_tag') {
            $query->orderBy('assets.asset_tag', $sortDir);
        } elseif ($sortBy === 'name' || $sortBy === 'device_name') {
            $query->orderBy('assets.device_name', $sortDir);
        } elseif ($sortBy === 'serial_number' || $sortBy === 'serial') {
            $query->orderBy('assets.serial_number', $sortDir);
        } elseif ($sortBy === 'purchase_cost' || $sortBy === 'cost') {
            $query->orderBy('assets.purchase_cost', $sortDir);
        } elseif ($sortBy === 'purchase_date') {
            $query->orderBy('assets.purchase_date', $sortDir);
        } elseif ($sortBy === 'warranty_expiry_date' || $sortBy === 'warranty') {
            $query->orderBy('assets.warranty_expiry_date', $sortDir);
        } else {
            $query->latest('assets.created_at');
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Asset Tag',
            'Device Name',
            'Brand',
            'Product Model',
            'Category',
            'Serial Number',
            'Product Number',
            'Status',
            'Department',
            'Location',
            'Holder',
            'Vendor',
            'Operating System',
            'Release Date',
            'Purchase Date',
            'Warranty Expiry',
            'Purchase Cost',
            'Book Value',
            'Invoice Number',
            'Windows License',
            'Office License',
            'Wireless MAC',
            'Ethernet MAC',
            'Specification',
            'Notes',
            'Aging',
            'Replacement Recommendation',
        ];
    }

    public function map($asset): array
    {
        $date = $asset->release_date ?? $asset->purchase_date;
        $aging = '-';
        if ($date) {
            $years = (int) $date->diffInYears(now());
            $months = (int) $date->diffInMonths(now()) % 12;
            if ($years >= 1) {
                $aging = $years.' Year'.($years > 1 ? 's' : '').($months >= 1 ? ' '.$months.' Month'.($months > 1 ? 's' : '') : '').' old';
            } else {
                $monthsTotal = (int) $date->diffInMonths(now());
                if ($monthsTotal >= 1) {
                    $aging = $monthsTotal.' Month'.($monthsTotal > 1 ? 's' : '').' old';
                } else {
                    $days = (int) $date->diffInDays(now());
                    $aging = $days.' Day'.($days > 1 ? 's' : '').' old';
                }
            }
        }

        $cost = $asset->purchase_cost ? (float) $asset->purchase_cost : null;
        $bookValue = $asset->bookValue ? (float) $asset->bookValue : null;

        $holder = $asset->currentCheckout?->checkedOutToUser?->name
            ?? $asset->assignedToUser?->name
            ?? '-';

        $replacement = 'Optimal Lifecycle';
        if ($asset->isDueForReplacement()) {
            $replacement = 'DUE FOR REPLACEMENT ('.$asset->replacement_priority.' Priority)';
        } elseif (($asset->age_years ?? 0) >= 3.0) {
            $replacement = 'Approaching Lifecycle (≥3y)';
        }

        return [
            $asset->asset_tag,
            $asset->device_name ?? '-',
            $asset->product?->brand?->name ?? '-',
            $asset->product?->model ?? '-',
            $asset->product?->category?->name ?? '-',
            $asset->serial_number ?? '-',
            $asset->product_number ?? '-',
            $asset->status?->name ?? '-',
            trim(($asset->department?->company?->code ?? $asset->department?->company?->name ?? '').' '.($asset->department?->name ?? '-')),
            $asset->location?->name ?? '-',
            $holder,
            $asset->vendor?->name ?? '-',
            $asset->operatingSystem?->name ?? '-',
            $asset->release_date ? $asset->release_date->format('Y-m-d') : '-',
            $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-',
            $asset->warranty_expiry_date ? $asset->warranty_expiry_date->format('Y-m-d') : '-',
            $cost ? 'Rp '.number_format($cost, 0, ',', '.') : '-',
            $bookValue ? 'Rp '.number_format($bookValue, 0, ',', '.') : '-',
            $asset->invoice_number ?? '-',
            $asset->license_windows ?? '-',
            $asset->license_office ?? '-',
            $asset->mac_address_wireless ?? '-',
            $asset->mac_address_ethernet ?? '-',
            $asset->specification ?? '-',
            $asset->notes ?? '-',
            $aging,
            $replacement,
        ];
    }
}
