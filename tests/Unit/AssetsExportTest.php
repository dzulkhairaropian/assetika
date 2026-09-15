<?php

namespace Tests\Unit;

use App\Exports\AssetsExport;
use App\Models\Asset;
use Carbon\Carbon;
use Tests\TestCase;

class AssetsExportTest extends TestCase
{
    public function test_assets_export_headings_and_mapping()
    {
        $export = new AssetsExport;
        $headings = $export->headings();

        $this->assertContains('Release Date', $headings);
        $this->assertContains('Invoice Number', $headings);
        $this->assertContains('Windows License', $headings);
        $this->assertContains('Office License', $headings);
        $this->assertContains('Wireless MAC', $headings);
        $this->assertContains('Ethernet MAC', $headings);
        $this->assertContains('Specification', $headings);
        $this->assertContains('Notes', $headings);

        $asset = new Asset([
            'asset_tag' => 'DB/NB/250101/001',
            'device_name' => 'DBNBIT-001',
            'serial_number' => 'SN12345',
            'product_number' => 'PN12345',
            'release_date' => Carbon::parse('2023-05-15'),
            'purchase_date' => Carbon::parse('2023-06-01'),
            'warranty_expiry_date' => Carbon::parse('2026-06-01'),
            'purchase_cost' => 20000000,
            'invoice_number' => 'INV-123',
            'license_windows' => 'WIN-KEY-1',
            'license_office' => 'OFF-KEY-1',
            'mac_address_wireless' => 'AA:BB:CC:DD:EE:FF',
            'mac_address_ethernet' => '11:22:33:44:55:66',
            'specification' => 'M2 Max',
            'notes' => 'Test note',
        ]);

        $mapped = $export->map($asset);

        $releaseDateIndex = array_search('Release Date', $headings);
        $invoiceIndex = array_search('Invoice Number', $headings);
        $winIndex = array_search('Windows License', $headings);

        $this->assertEquals('2023-05-15', $mapped[$releaseDateIndex]);
        $this->assertEquals('INV-123', $mapped[$invoiceIndex]);
        $this->assertEquals('WIN-KEY-1', $mapped[$winIndex]);
    }
}
