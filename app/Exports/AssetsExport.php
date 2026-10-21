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
                case 'aged':
                    $now4 = Carbon::now()->subYears(4);
                    $query->where(function ($sub) use ($now4) {
                        $sub->where('release_date', '<=', $now4)
                            ->orWhere(function ($s2) use ($now4) {
                                $s2->whereNull('release_date')
                                    ->where('purchase_date', '<=', $now4);
                            });
                    });
                    break;
            }
        }

        $query->filter($this->filters);

        if (! empty($this->filters['ids'])) {
            $ids = is_array($this->filters['ids']) ? $this->filters['ids'] : explode(',', (string) $this->filters['ids']);
            $query->whereIn('id', array_filter($ids));
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
            $years = is_array($this->filters['year']) ? array_filter($this->filters['year']) : explode(',', (string) $this->filters['year']);
            $years = array_values(array_filter(array_map('intval', $years)));
            if (! empty($years)) {
                $query->where(function ($q) use ($years) {
                    foreach ($years as $idx => $year) {
                        $method = $idx === 0 ? 'where' : 'orWhere';
                        $q->{$method}(function ($sub) use ($year) {
                            $sub->whereYear('release_date', $year)
                                ->orWhere(function ($s2) use ($year) {
                                    $s2->whereNull('release_date')
                                        ->whereYear('purchase_date', $year);
                                })
                                ->orWhere(function ($s2) use ($year) {
                                    $s2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereYear('created_at', $year);
                                });
                        });
                    }
                });
            }
        }

        if (! empty($this->filters['aging'])) {
            $agingList = is_array($this->filters['aging']) ? array_filter($this->filters['aging']) : explode(',', (string) $this->filters['aging']);
            $agingList = array_values(array_filter(array_map('trim', $agingList)));
            if (! empty($agingList)) {
                $now = Carbon::now();
                $query->where(function ($q) use ($agingList, $now) {
                    foreach ($agingList as $idx => $aging) {
                        $method = $idx === 0 ? 'where' : 'orWhere';
                        $q->{$method}(function ($subQuery) use ($aging, $now) {
                            switch ($aging) {
                                case '0-1':
                                case 'new':
                                    $subQuery->where(function ($sub) use ($now) {
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
                                    $subQuery->where(function ($sub) use ($now) {
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
                                    $subQuery->where(function ($sub) use ($now) {
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
                                    $subQuery->where(function ($sub) use ($now) {
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
                                    $subQuery->where(function ($sub) use ($now) {
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
                                    $subQuery->where(function ($sub) use ($now) {
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
                                case 'fully_depreciated':
                                    $subQuery->whereHas('depreciation', function ($sub) use ($now) {
                                        $sub->whereRaw('TIMESTAMPDIFF(MONTH, COALESCE(assets.release_date, assets.purchase_date, assets.created_at), ?) >= depreciations.months', [$now->format('Y-m-d')]);
                                    });
                                    break;
                                case 'no_depreciation':
                                    $subQuery->whereNull('depreciation_id');
                                    break;
                                case 'warranty_expired':
                                    $subQuery->whereNotNull('warranty_expiry_date')
                                        ->where('warranty_expiry_date', '<', $now->format('Y-m-d'));
                                    break;
                                case 'warranty_30d':
                                    $subQuery->whereNotNull('warranty_expiry_date')
                                        ->whereBetween('warranty_expiry_date', [$now->format('Y-m-d'), $now->copy()->addDays(30)->format('Y-m-d')]);
                                    break;
                            }
                        });
                    }
                });
            }
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
