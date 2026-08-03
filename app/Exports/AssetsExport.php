<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Asset::with([
            'product.brand',
            'product.category',
            'status',
            'assignedToUser',
            'operatingSystem'
        ]);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%$search%")
                  ->orWhere('serial_number', 'like', "%$search%")
                  ->orWhere('device_name', 'like', "%$search%");
            });
        }

        if (!empty($this->filters['status_id'])) {
            $query->where('status_id', $this->filters['status_id']);
        }

        if (!empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }

        if (!empty($this->filters['location_id'])) {
            $query->where('location_id', $this->filters['location_id']);
        }

        if (!empty($this->filters['product_id'])) {
            $query->where('product_id', $this->filters['product_id']);
        }

        if (!empty($this->filters['category_id'])) {
            $query->whereHas('product', function($q) {
                $q->where('category_id', $this->filters['category_id']);
            });
        }

        if (!empty($this->filters['ids'])) {
            $query->whereIn('id', $this->filters['ids']);
        }

        if (!empty($this->filters['aging'])) {
            $aging = $this->filters['aging'];
            $now = \Carbon\Carbon::now();
            $query->where(function($q) use ($aging, $now) {
                switch($aging) {
                    case '0-1':
                        $q->where(function($sub) use ($now) {
                            $sub->where('purchase_date', '>', $now->copy()->subYear())
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->where('release_date', '>', $now->copy()->subYear());
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->where('created_at', '>', $now->copy()->subYear());
                                });
                        });
                        break;
                    case '1-2':
                        $q->where(function($sub) use ($now) {
                            $sub->whereBetween('purchase_date', [$now->copy()->subYears(2), $now->copy()->subYear()])
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereBetween('release_date', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->whereBetween('created_at', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                });
                        });
                        break;
                    case '2-3':
                        $q->where(function($sub) use ($now) {
                            $sub->whereBetween('purchase_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)])
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereBetween('release_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->whereBetween('created_at', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                });
                        });
                        break;
                    case '3-4':
                        $q->where(function($sub) use ($now) {
                            $sub->whereBetween('purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)])
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereBetween('release_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->whereBetween('created_at', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                });
                        });
                        break;
                    case '4+':
                        $q->where(function($sub) use ($now) {
                            $sub->where('purchase_date', '<=', $now->copy()->subYears(4))
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->where('release_date', '<=', $now->copy()->subYears(4));
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->where('created_at', '<=', $now->copy()->subYears(4));
                                });
                        });
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
            'Product Model',
            'Brand',
            'Category',
            'Serial Number',
            'Status',
            'Holder',
            'Specification',
            'Operating System',
            'Aging',
        ];
    }

    public function map($asset): array
    {
        $date = $asset->release_date;
        $aging = '-';
        if ($date) {
            $years = (int) $date->diffInYears(now());
            $months = (int) $date->diffInMonths(now()) % 12;
            if ($years >= 1) {
                $aging = $years . ' Year' . ($years > 1 ? 's' : '') . ($months >= 1 ? ' ' . $months . ' Month' . ($months > 1 ? 's' : '') : '') . ' old';
            } else {
                $monthsTotal = (int) $date->diffInMonths(now());
                if ($monthsTotal >= 1) {
                    $aging = $monthsTotal . ' Month' . ($monthsTotal > 1 ? 's' : '') . ' old';
                } else {
                    $days = (int) $date->diffInDays(now());
                    $aging = $days . ' Day' . ($days > 1 ? 's' : '') . ' old';
                }
            }
        }

        return [
            $asset->asset_tag,
            $asset->device_name ?? '-',
            optional($asset->product)->model ?? '-',
            optional(optional($asset->product)->brand)->name ?? '-',
            optional(optional($asset->product)->category)->name ?? '-',
            $asset->serial_number ?? '-',
            optional($asset->status)->name ?? '-',
            optional($asset->assignedToUser)->name ?? '-',
            $asset->specification ?? '-',
            optional($asset->operatingSystem)->name ?? '-',
            $aging,
        ];
    }
}
