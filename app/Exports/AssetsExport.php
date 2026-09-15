<?php

namespace App\Exports;

use App\Models\Asset;
use Carbon\Carbon;
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
        ]);

        if (! empty($this->filters['tab'])) {
            switch ($this->filters['tab']) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'draft':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Draft']));
                    break;
                case 'ready':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Ready to Deploy']));
                    break;
                case 'deployed':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Deployed', 'Borrowed']));
                    break;
                case 'service':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Service', 'Maintenance', 'In Repair']));
                    break;
                case 'broken':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Broken']));
                    break;
                case 'retired':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Retired', 'Disposal']));
                    break;
                case 'sold':
                    $query->whereHas('status', fn ($q) => $q->whereIn('name', ['Sold', 'For Sale']));
                    break;
            }
        }

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%$search%")
                    ->orWhere('serial_number', 'like', "%$search%")
                    ->orWhere('device_name', 'like', "%$search%")
                    ->orWhere('product_number', 'like', "%$search%");
            });
        }

        if (! empty($this->filters['status_id'])) {
            $query->where('status_id', $this->filters['status_id']);
        }
        if (! empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }
        if (! empty($this->filters['location_id'])) {
            $query->where('location_id', $this->filters['location_id']);
        }
        if (! empty($this->filters['product_id'])) {
            $query->where('product_id', $this->filters['product_id']);
        }
        if (! empty($this->filters['vendor_id'])) {
            $query->where('vendor_id', $this->filters['vendor_id']);
        }

        if (! empty($this->filters['category_id'])) {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', $this->filters['category_id']);
            });
        }

        if (! empty($this->filters['brand_id'])) {
            $query->whereHas('product', function ($q) {
                $q->where('brand_id', $this->filters['brand_id']);
            });
        }

        if (! empty($this->filters['ids'])) {
            $query->whereIn('id', $this->filters['ids']);
        }

        if (! empty($this->filters['created_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['created_from']);
        }
        if (! empty($this->filters['created_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['created_to']);
        }

        if (! empty($this->filters['purchase_from'])) {
            $query->whereDate('purchase_date', '>=', $this->filters['purchase_from']);
        }
        if (! empty($this->filters['purchase_to'])) {
            $query->whereDate('purchase_date', '<=', $this->filters['purchase_to']);
        }

        if (! empty($this->filters['cost_min'])) {
            $query->where('purchase_cost', '>=', $this->filters['cost_min']);
        }
        if (! empty($this->filters['cost_max'])) {
            $query->where('purchase_cost', '<=', $this->filters['cost_max']);
        }

        if (! empty($this->filters['year'])) {
            $year = (int) $this->filters['year'];
            $query->where(function ($q) use ($year) {
                $q->whereYear('release_date', $year)
                    ->orWhere(function ($sub) use ($year) {
                        $sub->whereNull('release_date')
                            ->whereYear('purchase_date', $year);
                    })
                    ->orWhere(function ($sub) use ($year) {
                        $sub->whereNull('release_date')
                            ->whereNull('purchase_date')
                            ->whereYear('created_at', $year);
                    });
            });
        }

        if (! empty($this->filters['aging'])) {
            $aging = $this->filters['aging'];
            $now = Carbon::now();
            $query->where(function ($q) use ($aging, $now) {
                switch ($aging) {
                    case '0-1':
                    case 'new':
                        $q->where(function ($sub) use ($now) {
                            $sub->where('release_date', '>', $now->copy()->subYear())
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->where('purchase_date', '>', $now->copy()->subYear());
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->where('created_at', '>', $now->copy()->subYear());
                                });
                        });
                        break;
                    case '1-2':
                        $q->where(function ($sub) use ($now) {
                            $sub->whereBetween('release_date', [$now->copy()->subYears(2), $now->copy()->subYear()])
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereBetween('purchase_date', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereBetween('created_at', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                });
                        });
                        break;
                    case '2-3':
                        $q->where(function ($sub) use ($now) {
                            $sub->whereBetween('release_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)])
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereBetween('purchase_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereBetween('created_at', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                });
                        });
                        break;
                    case '2years':
                        $q->where(function ($sub) use ($now) {
                            $sub->whereBetween('release_date', [$now->copy()->subYears(4), $now->copy()->subYears(2)])
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereBetween('purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(2)]);
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereBetween('created_at', [$now->copy()->subYears(4), $now->copy()->subYears(2)]);
                                });
                        });
                        break;
                    case '3-4':
                        $q->where(function ($sub) use ($now) {
                            $sub->whereBetween('release_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)])
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereBetween('purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereBetween('created_at', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                });
                        });
                        break;
                    case '4+':
                    case '4years':
                        $q->where(function ($sub) use ($now) {
                            $sub->where('release_date', '<=', $now->copy()->subYears(4))
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->where('purchase_date', '<=', $now->copy()->subYears(4));
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->where('created_at', '<=', $now->copy()->subYears(4));
                                });
                        });
                        break;
                    case 'warranty_expired':
                        $q->where('warranty_expiry_date', '<', $now);
                        break;
                    case 'warranty_30d':
                        $q->whereBetween('warranty_expiry_date', [$now, $now->copy()->addDays(30)]);
                        break;
                }
            });
        }

        return $query->latest();
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

        return [
            $asset->asset_tag,
            $asset->device_name ?? '-',
            $asset->product?->brand?->name ?? '-',
            $asset->product?->model ?? '-',
            $asset->product?->category?->name ?? '-',
            $asset->serial_number ?? '-',
            $asset->product_number ?? '-',
            $asset->status?->name ?? '-',
            trim(($asset->department?->company?->code ?? '').' '.($asset->department?->name ?? '-')),
            $asset->location?->name ?? '-',
            $asset->assignedToUser?->name ?? '-',
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
        ];
    }
}
