<?php

namespace App\Exports;

use App\Models\ConsumableTransaction;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ConsumableTransactionsExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
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
        $query = ConsumableTransaction::with([
            'consumable.product.brand',
            'user',
            'checkedOutToUser',
        ]);

        if (! empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('consumable.product', function ($q) use ($search) {
                $q->where('model', 'like', "%$search%");
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'Consumable Item',
            'Brand',
            'Transaction Type',
            'Quantity',
            'Quantity Before',
            'Quantity After',
            'Unit Cost',
            'Total Cost',
            'Assigned To (User)',
            'Processed By',
            'Transaction Date',
            'Notes',
        ];
    }

    public function map($transaction): array
    {
        return [
            optional(optional($transaction->consumable)->product)->model ?? '-',
            optional(optional(optional($transaction->consumable)->product)->brand)->name ?? '-',
            ucfirst($transaction->type),
            $transaction->quantity,
            $transaction->quantity_before ?? '-',
            $transaction->quantity_after ?? '-',
            $transaction->unit_cost ? number_format($transaction->unit_cost, 2, '.', '') : '0.00',
            $transaction->total_cost ? number_format($transaction->total_cost, 2, '.', '') : '0.00',
            optional($transaction->checkedOutToUser)->name ?? '-',
            optional($transaction->user)->name ?? '-',
            $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d H:i:s') : ($transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : '-'),
            $transaction->notes ?? '',
        ];
    }
}
