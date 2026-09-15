<?php

namespace Tests\Unit;

use App\Http\Controllers\ImportController;
use Tests\TestCase;

class ImportTemplateTest extends TestCase
{
    public function test_assets_import_template_contains_release_date_and_extended_headers()
    {
        $controller = new ImportController;
        $response = $controller->downloadTemplate('assets');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Release Date', $content);
        $this->assertStringContainsString('Windows License', $content);
        $this->assertStringContainsString('Office License', $content);
        $this->assertStringContainsString('Wireless MAC', $content);
        $this->assertStringContainsString('Ethernet MAC', $content);
        $this->assertStringContainsString('Specification', $content);
        $this->assertStringContainsString('Accessories', $content);
    }
}
