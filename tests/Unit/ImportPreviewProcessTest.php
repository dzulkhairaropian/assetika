<?php

namespace Tests\Unit;

use App\Http\Controllers\ImportController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Tests\TestCase;

class ImportPreviewProcessTest extends TestCase
{
    public function test_import_handles_semicolon_delimited_csv_and_mismatched_column_counts()
    {
        $csvContent = "Company Name;Company Code;Email\n".
                      "PT Diastika Biotekindo;DB;info@diastika.co.id;Extra1;Extra2\n".
                      "PT UBC Medical;UBC\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        $request = Request::create('/import/preview', 'POST', [
            'type' => 'companies',
        ], [], [
            'file' => $file,
        ]);

        $controller = new ImportController;
        $response = $controller->preview($request);

        $this->assertInstanceOf(View::class, $response);
        $this->assertNotNull(session('import_header'));
        $this->assertNotNull(session('import_rows'));
    }
}
