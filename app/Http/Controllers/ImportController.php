<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Status;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function index()
    {
        return view('pages.import.index');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'type' => 'required|in:assets,users,consumables',
        ]);

        $path = $request->file('file')->getRealPath();

        if ($request->file('file')->getClientOriginalExtension() === 'csv' || $request->file('file')->getClientOriginalExtension() === 'txt') {
            $rows = array_map('str_getcsv', file($path));
        } else {
            return back()->with('error', 'XLSX not supported yet. Use CSV.');
        }

        $header = array_shift($rows);
        if (!$header) return back()->with('error', 'Empty file.');

        $errors = [];
        $preview = [];
        $maxPreview = 10;

        foreach ($rows as $i => $row) {
            if (count($row) < count($header)) continue;
            $data = array_combine(array_slice($header, 0, count($row)), $row);
            if ($i < $maxPreview) $preview[] = $data;

            if ($request->type === 'assets') {
                $v = Validator::make($data, [
                    'asset_tag' => 'nullable|string',
                    'serial_number' => 'nullable|string',
                    'device_name' => 'nullable|string',
                ]);
                if ($v->fails()) $errors[] = "Row " . ($i + 2) . ": " . implode(', ', $v->errors()->all());
            }
        }

        session()->flash('import_rows', json_encode($rows));
        session()->flash('import_header', json_encode($header));
        session()->flash('import_type', $request->type);

        return view('pages.import.preview', compact('header', 'preview', 'errors', 'maxPreview'));
    }

    public function process(Request $request)
    {
        $header = json_decode(session('import_header'), true);
        $rows = json_decode(session('import_rows'), true);
        $type = session('import_type');

        if (!$rows || !$header) return redirect()->route('import.index')->with('error', 'No data to import.');

        $validRows = [];
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($row) < count($header)) { $skipped++; continue; }
            $data = array_combine(array_slice($header, 0, count($row)), $row);
            $validRows[] = $data;
        }

        $imported = 0;
        if ($type === 'assets') {
            foreach ($validRows as $data) {
                // Skip if no serial
                if (empty($data['serial_number'])) continue;

                // Check duplicate
                if (Asset::where('serial_number', $data['serial_number'])->exists()) {
                    $skipped++;
                    continue;
                }

                // Find or default status
                $statusId = Status::where('name', $data['status_name'] ?? 'Ready')->first()?->id
                    ?? Status::first()?->id;

                Asset::create([
                    'serial_number' => $data['serial_number'],
                    'asset_tag' => $data['asset_tag'] ?? null,
                    'device_name' => $data['device_name'] ?? null,
                    'status_id' => $statusId,
                    'notes' => $data['notes'] ?? null,
                    'specification' => $data['specification'] ?? null,
                ]);
                $imported++;
            }
        }

        session()->forget(['import_header', 'import_rows', 'import_type']);

        return redirect()->route('import.index')->with('success', "Imported {$imported} records. Skipped {$skipped}.");
    }

    public function downloadTemplate($type)
    {
        $headers = match($type) {
            'assets' => ['serial_number', 'asset_tag', 'device_name', 'status_name', 'notes', 'specification'],
            'users' => ['name', 'email', 'department_name', 'position_name'],
            default => ['name'],
        };

        $csv = implode(',', $headers) . "\n";
        if ($type === 'assets') {
            $csv .= 'SN-ABC123,DB/NB/250101/001,Developer Laptop,Ready,,Intel i7 16GB' . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$type}_template.csv",
        ]);
    }
}
