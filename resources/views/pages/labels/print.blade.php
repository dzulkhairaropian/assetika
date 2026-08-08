<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Asset Labels - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin: 0;
                padding: 0;
                background: #fff;
                font-family: 'Outfit', sans-serif;
            }
            @page {
                size: A4 portrait;
                margin: 4mm;
            }
            .no-print {
                display: none !important;
            }
        }

        /* Grid layout for A4 Sheet: 3 Columns x N Rows */
        .label-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5mm 2mm;
            justify-content: start;
            align-content: start;
        }

        /* Industrial Asset Label Sticker Dimensions (68mm x 34mm) */
        .label-card {
            width: 100%;
            height: 34mm;
            border: 1px dashed #94a3b8; /* Panduan pemotongan cutter */
            box-sizing: border-box;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow: hidden;
            page-break-inside: avoid;
            border-radius: 4px;
        }

        .label-header {
            background-color: #047857; /* Emerald Dark Solid Green */
            color: #ffffff;
            text-align: center;
            padding: 2px 3px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            line-height: 1.1;
            flex-shrink: 0;
        }

        .label-header-sub {
            font-size: 5.5pt;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .label-header-title {
            font-size: 6.5pt;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 98%;
        }

        .label-body {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 2px 5px;
            background: #ffffff;
            overflow: hidden;
        }

        .qr-container {
            width: 21mm;
            height: 21mm;
            flex-shrink: 0;
            background: #ffffff;
            padding: 1px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-container img, .qr-container canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain;
        }

        .info-container {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1px;
            overflow: hidden;
        }

        .info-row {
            display: flex;
            align-items: baseline;
            gap: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-label {
            font-size: 5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            flex-shrink: 0;
            width: 22px;
        }

        .info-val {
            font-size: 6.5pt;
            font-weight: 600;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-gray-100 p-4">

    <!-- Top Action Bar (No Print) -->
    <div class="no-print mb-4 flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div>
            <h1 class="text-base font-bold text-gray-800">Sticker Label Print Preview ({{ count($assets) }} Units)</h1>
            <p class="text-xs text-gray-500">Format Grid 3 Kolom A4 • Font Official Assetika (Outfit) • Presisi & Rapih • QR Code Scannable</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.close()" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Close</button>
            <button onclick="window.print()" class="rounded-lg bg-emerald-600 px-5 py-2 text-xs font-semibold text-white hover:bg-emerald-700 shadow-xs">
                🖨️ Print Now (Epson L3250 / A4)
            </button>
        </div>
    </div>

    <!-- Printable Grid Area -->
    <div class="label-grid">
        @foreach($assets as $idx => $asset)
            @php
                $scanUrl = route('assets.public-view', $asset);
                $companyName = optional(optional($asset->department)->company)->name ?? config('app.name');
                $deviceName = $asset->device_name ?? optional($asset->product)->name ?? $asset->name;
                $serialNum = $asset->serial_number ?? '-';
                $categoryName = optional(optional($asset->product)->category)->name ?? '-';
                $deptName = optional($asset->department)->name ?? '-';
            @endphp
            <div class="label-card">
                <!-- Header 2 Baris: "PROPERTY OF" lalu "NAMA PERUSAHAAN" -->
                <div class="label-header">
                    <span class="label-header-sub">PROPERTY OF</span>
                    <span class="label-header-title" title="{{ $companyName }}">{{ $companyName }}</span>
                </div>

                <!-- Body Area: QR Code + Structured Metadata -->
                <div class="label-body">
                    <!-- High-Res Smartphone Scannable QR Code -->
                    <div class="qr-container" id="qr-box-{{ $idx }}" data-url="{{ $scanUrl }}"></div>

                    <!-- Clean Structured Information -->
                    <div class="info-container">
                        <!-- Asset Tag -->
                        <div class="font-extrabold text-[10pt] leading-none text-slate-900 truncate mb-0.5" title="{{ $asset->asset_tag }}">
                            {{ $asset->asset_tag }}
                        </div>

                        <!-- Device Name -->
                        <div class="info-row">
                            <span class="info-label">Name</span>
                            <span class="info-val font-bold text-slate-900" title="{{ $deviceName }}">{{ $deviceName }}</span>
                        </div>

                        <!-- Serial Number -->
                        <div class="info-row">
                            <span class="info-label">S/N</span>
                            <span class="info-val text-[6pt]">{{ $serialNum }}</span>
                        </div>

                        <!-- Category -->
                        <div class="info-row">
                            <span class="info-label">Cat</span>
                            <span class="info-val text-[6pt] text-slate-600">{{ $categoryName }}</span>
                        </div>

                        <!-- Department -->
                        <div class="info-row">
                            <span class="info-label">Dept</span>
                            <span class="info-val text-[6pt] text-slate-600">{{ $deptName }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const qrBoxes = document.querySelectorAll('.qr-container');
            qrBoxes.forEach(function(box) {
                const targetUrl = box.getAttribute('data-url');
                new QRCode(box, {
                    text: targetUrl,
                    width: 100,
                    height: 100,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H // High Error Correction level for instant mobile scan
                });
            });
        });
    </script>

</body>
</html>
