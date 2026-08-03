<?php

namespace App\Exports;

use App\Models\AssetCheckout;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetTransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = AssetCheckout::with([
            'asset.product.brand',
            'checkedOutToUser',
            'checkedOutByUser',
            'checkedInByUser',
            'department',
            'location'
        ]);

        if (!empty($this->filters['type'])) {
            if ($this->filters['type'] === 'active') {
                $query->whereNull('checked_in_at');
            } elseif ($this->filters['type'] === 'returned') {
                $query->whereNotNull('checked_in_at');
            }
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->whereHas('asset', function($aq) use ($search) {
                    $aq->where('asset_tag', 'like', "%$search%")
                      ->orWhere('serial_number', 'like', "%$search%")
                      ->orWhereHas('product', function($pq) use ($search) {
                          $pq->where('model', 'like', "%$search%");
                      });
                })
                ->orWhereHas('checkedOutToUser', function($uq) use ($search) {
                    $uq->where('name', 'like', "%$search%");
                });
            });
        }

        return $query->latest('checked_out_at');
    }

    public function headings(): array
    {
        return [
            'Asset Tag',
            'Asset Name / Model',
            'Brand',
            'Serial Number',
            'Assigned To (User)',
            'Department',
            'Location',
            'Checked Out By',
            'Checked Out At',
            'Checkout Notes',
            'Status',
            'Checked In By',
            'Checked In At',
            'Condition Report',
            'Check-in Notes',
        ];
    }

    public function map($transaction): array
    {
        $status = $transaction->checked_in_at ? 'Returned' : 'Active (Checked Out)';

        return [
            optional($transaction->asset)->asset_tag ?? '-',
            optional($transaction->asset)->device_name ?? '-',
            optional(optional(optional($transaction->asset)->product)->brand)->name ?? '-',
            optional($transaction->asset)->serial_number ?? '-',
            optional($transaction->checkedOutToUser)->name ?? '-',
            optional($transaction->department)->name ?? '-',
            optional($transaction->location)->name ?? '-',
            optional($transaction->checkedOutByUser)->name ?? '-',
            $transaction->checked_out_at ? $transaction->checked_out_at->format('Y-m-d H:i:s') : '-',
            $transaction->checkout_notes ?? '',
            $status,
            optional($transaction->checkedInByUser)->name ?? '-',
            $transaction->checked_in_at ? $transaction->checked_in_at->format('Y-m-d H:i:s') : '-',
            $transaction->condition_report ?? '',
            $transaction->checkin_notes ?? '',
        ];
    }
}
