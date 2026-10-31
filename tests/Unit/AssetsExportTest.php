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
        $this->assertContains('Windows License', $headings);
        $this->assertContains('Office License', $headings);
        $this->assertContains('Specification', $headings);
        $this->assertContains('Notes', $headings);
        $this->assertNotContains('Product Number', $headings);
        $this->assertNotContains('Invoice Number', $headings);
        $this->assertNotContains('Wireless MAC', $headings);
        $this->assertNotContains('Ethernet MAC', $headings);
        $this->assertNotContains('Book Value', $headings);

        $asset = new Asset([
            'asset_tag' => 'DB/NB/250101/001',
            'device_name' => 'DBNBIT-001',
            'serial_number' => 'SN12345',
            'release_date' => Carbon::parse('2023-05-15'),
            'purchase_date' => Carbon::parse('2023-06-01'),
            'warranty_expiry_date' => Carbon::parse('2026-06-01'),
            'purchase_cost' => 20000000,
            'license_windows' => 'WIN-KEY-1',
            'license_office' => 'OFF-KEY-1',
            'specification' => 'M2 Max',
            'notes' => 'Test note',
        ]);

        $mapped = $export->map($asset);

        $releaseDateIndex = array_search('Release Date', $headings);
        $costIndex = array_search('Purchase Cost', $headings);
        $winIndex = array_search('Windows License', $headings);

        $this->assertEquals('2023-05-15', $mapped[$releaseDateIndex]);
        $this->assertEquals('Rp 20.000.000', $mapped[$costIndex]);
        $this->assertEquals('WIN-KEY-1', $mapped[$winIndex]);
    }
}
