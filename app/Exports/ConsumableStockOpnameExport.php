<?php

namespace App\Exports;

use App\Models\Consumable;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsumableStockOpnameExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    protected array $filters;

    protected int $rowCount = 0;

    protected string $opnameDate;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->opnameDate = ! empty($filters['opname_date'])
            ? Carbon::parse($filters['opname_date'])->format('d F Y')
            : now()->format('d F Y');
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function title(): string
    {
        return 'Stock Opname Consumables';
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

        $items = $query->orderBy('id', 'asc')->get();
        $this->rowCount = $items->count();

        return $items;
    }

    public function headings(): array
    {
        return [
            'NO',
            'ITEM MODEL & NAME',
            'CATEGORY',
            'BRAND',
            'SYSTEM QTY',
            'PHYSICAL QTY (ACTUAL)',
            'DIFFERENCE (VARIANCE)',
            'CONDITION / EXPIRY / REMARKS',
            'CHECK STATUS',
        ];
    }

    public function map($consumable): array
    {
        static $index = 0;
        $index++;

        $rowNum = $index + 7; // Heading is row 7, data starts at row 8

        $productName = optional($consumable->product)->name ?? 'Unnamed Consumable';
        $categoryName = optional(optional($consumable->product)->category)->name ?? '-';
        $brandName = optional(optional($consumable->product)->brand)->name ?? '-';
        $systemQty = (int) ($consumable->quantity ?? 0);

        return [
            $index,
            $productName,
            $categoryName,
            $brandName,
            $systemQty,
            '', // Blank for physical count
            "=IF(F{$rowNum}<>\"\", F{$rowNum}-E{$rowNum}, \"\")", // Automatic formula
            $consumable->notes ?? '',
            "=IF(F{$rowNum}=\"\", \"Pending\", IF(G{$rowNum}=0, \"Match\", IF(G{$rowNum}>0, \"Surplus\", \"Shortage\")))",
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header styling on row 7
            7 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 10,
                    'name' => 'Segoe UI',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '465FFF'], // Assetika Enterprise Indigo
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(8, $this->rowCount + 7);

                // --- 1. Aesthetic Document Header (Row 1-5) ---
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'ASSETIKA ENTERPRISE — PHYSICAL STOCK OPNAME FORM');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '1E293B'], // Slate 800
                        'name' => 'Segoe UI',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', 'IT Consumables & Accessories Audit Verification Sheet');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '64748B'], // Slate 500
                        'name' => 'Segoe UI',
                    ],
                ]);

                // Meta Info Box
                $auditorName = auth()->user()?->name ?? 'IT Administrator';
                $sheet->setCellValue('A4', 'Opname Date:');
                $sheet->setCellValue('B4', $this->opnameDate);
                $sheet->setCellValue('D4', 'Audited By:');
                $sheet->setCellValue('E4', $auditorName);
                $sheet->setCellValue('G4', 'Verified By:');
                $sheet->setCellValue('H4', '_____________________ (Sign)');

                $sheet->setCellValue('A5', 'Department:');
                $sheet->setCellValue('B5', 'IT Infrastructure & Operations');
                $sheet->setCellValue('D5', 'Location / Warehouse:');
                $sheet->setCellValue('E5', $this->filters['location'] ?? 'Central IT Storage');
                $sheet->setCellValue('G5', 'Status Target:');
                $sheet->setCellValue('H5', '100% Physical Count Reconciliation');

                $sheet->getStyle('A4:I5')->applyFromArray([
                    'font' => [
                        'size' => 9,
                        'name' => 'Segoe UI',
                    ],
                ]);
                $sheet->getStyle('A4:A5')->getFont()->setBold(true)->getColor()->setRGB('475569');
                $sheet->getStyle('D4:D5')->getFont()->setBold(true)->getColor()->setRGB('475569');
                $sheet->getStyle('G4:G5')->getFont()->setBold(true)->getColor()->setRGB('475569');
                $sheet->getStyle('B4:B5')->getFont()->getColor()->setRGB('0F172A');
                $sheet->getStyle('E4:E5')->getFont()->getColor()->setRGB('0F172A');

                // Row Heights
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);
                $sheet->getRowDimension(5)->setRowHeight(18);
                $sheet->getRowDimension(7)->setRowHeight(28);

                // --- 2. Table Data Styling (Row 8 to $lastRow) ---
                if ($this->rowCount > 0) {
                    for ($row = 8; $row <= $lastRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(20);

                        // Zebra striping
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:I{$row}")->getFill()->applyFromArray([
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8FAFC'], // Light slate tint
                            ]);
                        }

                        // Highlight input column F (Physical QTY) with a subtle soft blue border/background for clear handwriting or spreadsheet entry
                        $sheet->getStyle("F{$row}")->getFill()->applyFromArray([
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'EFF6FF'], // Very soft blue (blue-50)
                        ]);
                    }

                    // Column Alignments
                    $sheet->getStyle("A8:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E8:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("I8:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Borders for Table
                    $sheet->getStyle("A7:I{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E2E8F0'], // Slate 200 border
                            ],
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => 'CBD5E1'],
                            ],
                        ],
                    ]);

                    // Highlight header border bottom
                    $sheet->getStyle('A7:I7')->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '3B82F6'],
                            ],
                        ],
                    ]);
                }

                // --- 3. Footer Sign-off Block ---
                $footerStart = $lastRow + 2;

                $sheet->setCellValue("A{$footerStart}", 'COUNTED BY');
                $sheet->setCellValue("D{$footerStart}", 'CHECKED BY');
                $sheet->setCellValue("G{$footerStart}", 'APPROVED BY (SUPERVISOR / HEAD)');

                $sheet->mergeCells("A{$footerStart}:B{$footerStart}");
                $sheet->mergeCells("D{$footerStart}:E{$footerStart}");
                $sheet->mergeCells("G{$footerStart}:I{$footerStart}");

                $sheet->getStyle("A{$footerStart}:I{$footerStart}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 9,
                        'color' => ['rgb' => '475569'],
                        'name' => 'Segoe UI',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $signRow = $footerStart + 4;
                $sheet->setCellValue("A{$signRow}", '( ________________________ )');
                $sheet->setCellValue("D{$signRow}", '( ________________________ )');
                $sheet->setCellValue("G{$signRow}", '( ________________________ )');

                $sheet->mergeCells("A{$signRow}:B{$signRow}");
                $sheet->mergeCells("D{$signRow}:E{$signRow}");
                $sheet->mergeCells("G{$signRow}:I{$signRow}");

                $sheet->getStyle("A{$signRow}:I{$signRow}")->applyFromArray([
                    'font' => [
                        'size' => 9,
                        'color' => ['rgb' => '64748B'],
                        'name' => 'Segoe UI',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Gridlines enabled for print and display
                $sheet->setShowGridLines(true);
            },
        ];
    }
}
