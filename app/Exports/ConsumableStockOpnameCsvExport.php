<?php

namespace App\Exports;

use App\Models\Consumable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ConsumableStockOpnameCsvExport implements FromCollection, WithCustomCsvSettings, WithHeadings, WithMapping
{
    use Exportable;

    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => "\r\n",
            'use_bom' => true,
            'include_separator_line' => false,
            'excel_compatibility' => true,
        ];
    }

    public function collection()
    {
        $query = Consumable::with(['product.brand', 'product.category', 'status']);

        if (! empty($this->filters['category_id'])) {
            $ids = is_array($this->filters['category_id']) ? $this->filters['category_id'] : explode(',', $this->filters['category_id']);
            $query->whereHas('product', fn ($q) => $q->whereIn('category_id', array_filter($ids)));
        }

        if (! empty($this->filters['brand_id'])) {
            $ids = is_array($this->filters['brand_id']) ? $this->filters['brand_id'] : explode(',', $this->filters['brand_id']);
            $query->whereHas('product', fn ($q) => $q->whereIn('brand_id', array_filter($ids)));
        }

        if (! empty($this->filters['status'])) {
            $status = $this->filters['status'];
            if ($status === 'in_stock') {
                $query->whereColumn('quantity', '>', 'low_threshold');
            } elseif ($status === 'low_stock') {
                $query->whereColumn('quantity', '<=', 'low_threshold')->where('quantity', '>', 0);
            } elseif ($status === 'out_of_stock') {
                $query->where('quantity', '<=', 0);
            }
        }

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_type', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Item Model & Name',
            'Category',
            'Brand',
            'System Qty',
            'Physical Qty',
            'Variance',
            'Remarks / Notes',
            'Opname Date',
        ];
    }

    public function map($consumable): array
    {
        static $index = 0;
        $index++;

        $productName = optional($consumable->product)->name ?? 'Unnamed Consumable';
        $categoryName = optional(optional($consumable->product)->category)->name ?? '-';
        $brandName = optional(optional($consumable->product)->brand)->name ?? '-';
        $systemQty = (int) ($consumable->quantity ?? 0);
        $date = ! empty($this->filters['opname_date']) ? $this->filters['opname_date'] : now()->format('Y-m-d');

        return [
            $index,
            $productName,
            $categoryName,
            $brandName,
            $systemQty,
            '', // Blank for manual input
            '', // Variance calculation
            $consumable->notes ?? '',
            $date,
        ];
    }
}
