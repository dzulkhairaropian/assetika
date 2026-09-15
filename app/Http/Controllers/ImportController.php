<?php

namespace App\Http\Controllers;

use App\Services\ImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(protected ImportService $importService) {}

    public function index(): View
    {
        return view('pages.import.index');
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'type' => 'required|in:assets,users,consumables,companies,departments,locations,brands,categories,positions,vendors,products,components,licenses',
        ]);

        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();

        if ($ext !== 'csv' && $ext !== 'txt') {
            return back()->with('error', 'XLSX not supported yet. Use CSV.');
        }

        $parsed = $this->importService->parseCsvFile($file);
        $header = $parsed['header'];
        $rows = $parsed['rows'];

        if (empty($header) && empty($rows)) {
            return back()->with('error', 'Empty file.');
        }

        if (isset($header[0])) {
            $header[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $header[0]);
        }

        $preview = array_slice($rows, 0, 100);
        $totalRows = count($rows);

        session()->flash('import_rows', json_encode($rows));
        session()->flash('import_header', json_encode($header));
        session()->flash('import_type', $request->type);

        return view('pages.import.preview', [
            'header' => $header,
            'preview' => $preview,
            'errors' => [],
            'maxPreview' => 100,
            'totalRows' => $totalRows,
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $header = json_decode(session('import_header'), true);
        $rows = json_decode(session('import_rows'), true);
        $type = session('import_type');

        if (! $rows || ! $header) {
            return redirect()->route('import.index')->with('error', 'No data to import.');
        }

        $formattedData = [];
        $headerCount = count($header);

        foreach ($rows as $row) {
            if (empty($row) || (count($row) === 1 && $row[0] === null)) {
                continue;
            }

            $minCount = min($headerCount, count($row));
            if ($minCount === 0) {
                continue;
            }

            $keys = array_slice($header, 0, $minCount);
            $values = array_slice($row, 0, $minCount);
            $formattedData[] = array_combine($keys, $values);
        }

        $imported = $this->importService->processImport($type, $formattedData);

        session()->forget(['import_header', 'import_rows', 'import_type']);

        return redirect()->route('import.index')->with('success', "Imported {$imported} records successfully.");
    }

    private function formatAssetTagStandard(?string $rawTag, string $categoryCode): string
    {
        $catCode = strtoupper(trim($categoryCode));
        if (empty($rawTag)) {
            return "DB/{$catCode}/220101/001";
        }

        $cleanTag = trim($rawTag);

        // Pattern 1: DB/NB/210801/014 or DB/NB/210801/14
        if (preg_match('/^([A-Z0-9]+)\/([A-Z0-9]+)\/(\d{6})\/(\d+)$/i', $cleanTag, $matches)) {
            $comp = strtoupper($matches[1]);
            $cat = in_array(strtoupper($matches[2]), ['NO', 'NOT']) ? $catCode : strtoupper($matches[2]);
            $dateCode = $matches[3];
            $seq = str_pad((string) ((int) $matches[4]), 3, '0', STR_PAD_LEFT);

            return "{$comp}/{$cat}/{$dateCode}/{$seq}";
        }

        // Pattern 2: DB/NB/181200046, DB/NB/21080114, DB/NB/201201006, DB/NB/23030017
        if (preg_match('/^([A-Z0-9]+)\/([A-Z0-9]+)\/(\d{2})(\d{2})(\d{2}|\d{3})(\d{1,5})$/i', $cleanTag, $matches)) {
            $comp = strtoupper($matches[1]);
            $cat = in_array(strtoupper($matches[2]), ['NO', 'NOT']) ? $catCode : strtoupper($matches[2]);
            $year = $matches[3];
            $month = $matches[4];
            $seq = str_pad((string) ((int) $matches[6]), 3, '0', STR_PAD_LEFT);

            return "{$comp}/{$cat}/{$year}{$month}01/{$seq}";
        }

        // Pattern 3: DB/15050029 -> DB/NB/150501/029
        if (preg_match('/^([A-Z0-9]+)\/(\d{2})(\d{2})(\d{4,5})$/i', $cleanTag, $matches)) {
            $comp = strtoupper($matches[1]);
            $cat = $catCode;
            $year = $matches[2];
            $month = $matches[3];
            $seq = str_pad((string) ((int) $matches[4]), 3, '0', STR_PAD_LEFT);

            return "{$comp}/{$cat}/{$year}{$month}01/{$seq}";
        }

        return $cleanTag;
    }

    private function formatDeviceNameStandard(?string $rawDeviceName, ?int $companyId, ?int $categoryId, ?int $departmentId, string $assetTag): string
    {
        if (! empty($rawDeviceName) && strlen(trim($rawDeviceName)) > 4 && strpos($rawDeviceName, '-') !== false) {
            return trim($rawDeviceName);
        }

        $companyCode = $companyId ? (Company::find($companyId)?->code ?? 'DB') : 'DB';
        $categoryCode = $categoryId ? (Category::find($categoryId)?->code ?? 'NB') : 'NB';
        $deptCode = $departmentId ? (Department::find($departmentId)?->code ?? 'IT') : 'IT';

        // Extract sequence digits from asset tag
        $parts = explode('/', $assetTag);
        $seq = end($parts);
        if (! is_numeric($seq)) {
            $seq = '001';
        } else {
            $seq = str_pad((string) ((int) $seq), 3, '0', STR_PAD_LEFT);
        }

        return "{$companyCode}{$categoryCode}{$deptCode}-{$seq}";
    }

    private function normalizeRowData(array $rawRow): array
    {
        $aliasMap = [
            'brand' => 'brand_name',
            'category' => 'category_name',
            'model' => 'model_name',
            'product_model' => 'model_name',
            'status' => 'status_name',
            'company' => 'company_name',
            'department' => 'department_name',
            'position' => 'position_name',
            'location' => 'location_name',
            'vendor' => 'vendor_name',
            'holder' => 'assigned_user_email',
            'assigned_user' => 'assigned_user_email',
            'release_date' => 'release_date',
            'release' => 'release_date',
            'date_released' => 'release_date',
            'warranty_expiry' => 'warranty_expiry_date',
            'master_category' => 'master_category_name',
            'manufacturer' => 'manufacturer_name',
            'license_key' => 'license_key',
            'serial' => 'serial_number',
            'serial_number' => 'serial_number',
            'quantity' => 'quantity',
            'qty' => 'quantity',
        ];

        $normalized = [];
        foreach ($rawRow as $key => $value) {
            $trimmedKey = trim($key);
            $clean = strtolower(str_replace([' ', '-'], '_', preg_replace('/[^A-Za-z0-9 _-]/', '', $trimmedKey)));
            $targetKey = $aliasMap[$clean] ?? $clean;
            $normalized[$targetKey] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    public function downloadTemplate($type)
    {
        switch ($type) {
            case 'assets':
                $headers = [
                    'Asset Tag',
                    'Device Name',
                    'Brand',
                    'Product Model',
                    'Category',
                    'Serial Number',
                    'Product Number',
                    'Status',
                    'Company',
                    'Department',
                    'Location',
                    'Holder',
                    'Vendor',
                    'Operating System',
                    'Release Date',
                    'Purchase Date',
                    'Warranty Expiry',
                    'Purchase Cost',
                    'Invoice Number',
                    'Windows License',
                    'Office License',
                    'Wireless MAC',
                    'Ethernet MAC',
                    'Specification',
                    'Accessories',
                    'Notes',
                ];
                $rows = [
                    [
                        'AST-IT-2026-001',
                        'MacBook Pro M3 Max',
                        'Apple',
                        'MacBook Pro 16 Inch M3 Max',
                        'Notebook',
                        'SN-MAC2026-001',
                        'PN-MBP16-01',
                        'Ready to Deploy',
                        'PT Diastika Biotekindo',
                        'Information Technology',
                        'Head Office Jakarta',
                        'Dzul Khair Aropian',
                        'Bhinneka Mentari Dimensi',
                        'macOS Sequoia',
                        '2026-01-01',
                        '2026-01-15',
                        '2028-01-15',
                        '45000000',
                        'INV-2026-001',
                        '',
                        '',
                        'AA:BB:CC:DD:EE:FF',
                        '11:22:33:44:55:66',
                        'Apple M3 Max 36GB RAM 1TB SSD',
                        'Charger 140W, Sleeve',
                        'Primary Developer Laptop',
                    ],
                    [
                        'AST-IT-2026-002',
                        'Dell XPS 15 Workstation',
                        'Dell',
                        'Dell XPS 15 9530',
                        'Notebook',
                        'SN-DELL2026-002',
                        'PN-XPS15-9530',
                        'Deployed',
                        'PT Diastika Biotekindo',
                        'Finance, Accounting & Tax',
                        'Head Office Jakarta',
                        'Dian',
                        'Global IT Solution',
                        'Windows 11 Pro',
                        '2026-01-10',
                        '2026-02-01',
                        '2029-02-01',
                        '32000000',
                        'INV-2026-088',
                        'WIN-11-PRO-KEY',
                        'MSO-365-BUS',
                        'AA:BB:CC:11:22:33',
                        '44:55:66:77:88:99',
                        'Intel Core i7 32GB RAM 1TB SSD RTX 4060',
                        'Type-C Adapter',
                        'Finance Lead Laptop',
                    ],
                ];
                break;

            case 'consumables':
                $headers = [
                    'Consumable Name',
                    'Category',
                    'Brand',
                    'Company',
                    'Location',
                    'Quantity',
                    'Min Qty Alert',
                    'Purchase Cost',
                    'Status',
                    'Notes',
                ];
                $rows = [
                    [
                        'Logitech MX Master 3S Mouse',
                        'Peripherals',
                        'Logitech',
                        'PT Diastika Biotekindo',
                        'Head Office Jakarta',
                        '50',
                        '5',
                        '1490000',
                        'In Stock',
                        'Wireless Ergonomic Mouse for Staff',
                    ],
                    [
                        'HDMI Cable 2m 4K',
                        'Cables & Adapters',
                        'Vention',
                        'PT Diastika Biotekindo',
                        'Head Office Jakarta',
                        '100',
                        '10',
                        '85000',
                        'In Stock',
                        'High Speed HDMI Cable for Meeting Rooms',
                    ],
                ];
                break;

            case 'users':
                $headers = [
                    'Full Name',
                    'Email Address',
                    'Phone Number',
                    'Company',
                    'Department',
                    'Position',
                    'Location',
                    'Is Active',
                ];
                $rows = [
                    [
                        'Budi Pratama',
                        'budi.pratama@diastika.co.id',
                        '081234567890',
                        'PT Diastika Biotekindo',
                        'Information Technology',
                        'Senior Developer',
                        'Head Office Jakarta',
                        'Active',
                    ],
                    [
                        'Siti Aminah',
                        'siti.aminah@diastika.co.id',
                        '081234567891',
                        'PT Diastika Biotekindo',
                        'Finance & Accounting',
                        'Financial Analyst',
                        'Head Office Jakarta',
                        'Active',
                    ],
                ];
                break;

            case 'companies':
                $headers = ['Company Name', 'Company Code', 'Email', 'Phone', 'Website', 'Address', 'Notes'];
                $rows = [
                    ['PT Diastika Biotekindo', 'DB', 'info@diastika.co.id', '021-1234567', 'https://diastika.co.id', 'Kabayoran Lama, Jakarta', 'Head Company'],
                    ['PT UBC Indonesia', 'UBC', 'contact@ubcindonesia.com', '021-7654321', 'https://ubcindonesia.com', 'Jakarta', 'Subsidiary Company'],
                ];
                break;

            case 'departments':
                $headers = ['Department Name', 'Department Code', 'Company', 'Notes'];
                $rows = [
                    ['Information Technology', 'IT', 'PT Diastika Biotekindo', 'IT Department'],
                    ['Finance & Accounting', 'FIN', 'PT Diastika Biotekindo', 'Finance & Tax Department'],
                    ['Human Resources', 'HR', 'PT Diastika Biotekindo', 'HR & GA Department'],
                ];
                break;

            case 'locations':
                $headers = ['Location Name', 'Location Code', 'Company', 'Address', 'City', 'State', 'Zip Code', 'Country', 'Notes'];
                $rows = [
                    ['Head Office Jakarta', 'HO-JKT', 'PT Diastika Biotekindo', 'Jl. Kebayoran Lama No. 12', 'Jakarta Selatan', 'DKI Jakarta', '12210', 'Indonesia', 'Main Headquarter'],
                    ['Branch Surabaya', 'BR-SBY', 'PT Diastika Biotekindo', 'Jl. Raya Darmo', 'Surabaya', 'Jawa Timur', '60241', 'Indonesia', 'Surabaya Office'],
                ];
                break;

            case 'brands':
                $headers = ['Brand Name', 'Website', 'Support Phone', 'Support Email', 'Notes'];
                $rows = [
                    ['Apple', 'https://apple.com', '1-800-MY-APPLE', 'support@apple.com', 'Apple Hardware & Devices'],
                    ['Dell', 'https://dell.com', '1-800-DELL-CARE', 'support@dell.com', 'Dell Workstations & Servers'],
                    ['Logitech', 'https://logitech.com', '1-800-LOGITECH', 'support@logitech.com', 'Computer Peripherals'],
                ];
                break;

            case 'categories':
                $headers = ['Category Name', 'Master Category', 'Category Code', 'EULA Name', 'Notes'];
                $rows = [
                    ['Notebook', 'IT Asset', 'NB', 'Standard Hardware EULA', 'Laptop Computers'],
                    ['Printer', 'IT Asset', 'PR', 'Standard Hardware EULA', 'Desktop Printers'],
                    ['Peripherals', 'IT Consumable', 'PERIPH', '', 'Mice, Keyboards, Audio'],
                ];
                break;

            case 'positions':
                $headers = ['Position Name', 'Position Code', 'Department', 'Notes'];
                $rows = [
                    ['Senior Developer', 'SR-DEV', 'Information Technology', 'Lead Developer Position'],
                    ['IT Support Specialist', 'IT-SUPP', 'Information Technology', 'Helpdesk & Support'],
                    ['Financial Analyst', 'FIN-ANL', 'Finance & Accounting', 'Finance Analyst'],
                ];
                break;

            case 'vendors':
                $headers = ['Vendor Name', 'Contact Person', 'Email', 'Phone', 'Website', 'Address', 'NPWP Number', 'NPWP Name', 'NIB', 'Bank Name', 'Bank Account Number', 'Bank Account Name', 'Notes'];
                $rows = [
                    ['PT Jasanet Mitra Network', 'Erna', 'admin@jasanet.co.id', '085894711377', 'https://jasanet.co.id', 'Jakarta', '12.345.678.9-012.000', 'PT Jasanet', '1234567890', 'BCA', '1234567890', 'PT Jasanet Mitra Network', 'Network Vendor'],
                    ['Bhinneka Mentari Dimensi', 'Sales Team', 'sales@bhinneka.com', '021-29292828', 'https://bhinneka.com', 'Jakarta', '', '', '', 'BCA', '0987654321', 'Bhinneka Mentari Dimensi', 'IT Hardware Vendor'],
                ];
                break;

            case 'products':
                $headers = ['Product Name', 'Brand', 'Category', 'Master Category', 'Product Type', 'Description'];
                $rows = [
                    ['MacBook Pro 16 M3 Max', 'Apple', 'Notebook', 'IT Asset', 'Hardware', '16-inch Apple Laptop'],
                    ['Dell XPS 15 9530', 'Dell', 'Notebook', 'IT Asset', 'Hardware', '15-inch Dell Laptop'],
                ];
                break;

            case 'components':
                $headers = ['Component Name', 'Category', 'Brand', 'Serial Number', 'Quantity', 'Min Qty Alert', 'Purchase Cost', 'Location', 'Notes'];
                $rows = [
                    ['DDR5 32GB RAM SODIMM', 'Memory RAM', 'Corsair', 'SN-RAM-001', '20', '5', '1850000', 'Head Office Jakarta', 'DDR5 Laptop Memory'],
                    ['Samsung 990 PRO 2TB SSD', 'Storage SSD', 'Samsung', 'SN-SSD-002', '15', '3', '2450000', 'Head Office Jakarta', 'NVMe M.2 SSD'],
                ];
                break;

            case 'licenses':
                $headers = ['License Name', 'License Key', 'Category', 'Manufacturer', 'Seats', 'Purchase Cost', 'Purchase Date', 'Expiration Date', 'Notes'];
                $rows = [
                    ['Microsoft 365 Business Premium', 'MS365-KEY-2026-XYZ', 'Software License', 'Microsoft', '100', '25000000', '2026-01-01', '2027-01-01', 'Annual License'],
                    ['Adobe Creative Cloud All Apps', 'ADOBE-CC-2026-ABC', 'Software License', 'Adobe', '15', '18000000', '2026-02-01', '2027-02-01', 'Design Team Licenses'],
                ];
                break;

            default:
                $headers = ['Name', 'Email'];
                $rows = [];
                break;
        }

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $headers);
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$type}_template.csv",
        ]);
    }
}
