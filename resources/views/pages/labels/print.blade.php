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
            justify-start: start;
            align-content: start;
        }

        /* Fixed Dimensions Label Card (68mm x 38mm) to fit all text cleanly */
        .label-card {
            width: 100%;
            height: 38mm;
            border: 1px dashed #94a3b8; /* Cutting line guide */
            box-sizing: border-box;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            page-break-inside: avoid;
            border-radius: 4px;
        }

        .label-header {
            background-color: #465fff; /* TailAdmin Brand Blue */
            color: #ffffff;
            padding: 3px 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            line-height: 1.1;
            flex-shrink: 0;
            min-height: 24px;
        }

        .label-header-logo {
            height: 18px;
            max-width: 50px;
            object-fit: contain;
            filter: brightness(0) invert(1);
            flex-shrink: 0;
        }

        .label-header-text {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
            min-width: 0;
            flex: 1;
        }

        .label-header-sub {
            font-size: 5pt;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            opacity: 0.95;
            white-space: nowrap;
        }

        .label-header-title {
            font-size: 5.5pt;
            font-weight: 800;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            line-height: 1.1;
            max-width: 100%;
            white-space: normal;
            word-break: break-word;
        }

        .label-body {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 6px;
            background: #ffffff;
            overflow: hidden;
        }

        .qr-container {
            width: 22mm;
            height: 22mm;
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
            gap: 2px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6pt;
            line-height: 1.25;
        }

        .info-table td {
            padding: 0.5px 0;
            vertical-align: top;
        }

        .info-table td.lbl {
            font-size: 5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            width: 26px;
            white-space: nowrap;
        }

        .info-table td.val {
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
            white-space: normal;
        }

        .label-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 2px 6px;
            font-size: 4.5pt;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
            line-height: 1;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-gray-100 p-4">

    <!-- Top Action Bar (No Print) -->
    <div class="no-print mb-4 flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div>
            <h1 class="text-base font-bold text-gray-800">Asset Label Print Preview</h1>
            <p class="text-xs text-gray-500">Layout Grid 3 Columns • Format A4 • Total {{ count($assets) }} item(s)</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.close()" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Close</button>
            <button onclick="window.print()" class="rounded-lg bg-brand-500 hover:bg-brand-600 px-5 py-2 text-xs font-semibold text-white shadow-xs">
                Print Labels
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
                <!-- Header 2 Baris + Multi-Company Dynamic Logo -->
                <div class="label-header">
                    @php
                        $company = optional($asset->department)->company;
                        $companyLogo = $company?->logo ? Storage::url($company->logo) : '/images/diastikalogo.png';
                    @endphp
                    <img src="{{ $companyLogo }}" alt="Logo" class="label-header-logo" onerror="this.style.display='none'">
                    <div class="label-header-text">
                        <span class="label-header-sub">PROPERTY OF</span>
                        <span class="label-header-title" title="{{ $companyName }}">{{ $companyName }}</span>
                    </div>
                </div>

                <!-- Body Area: QR Code + Clean Table Metadata -->
                <div class="label-body">
                    <!-- High-Res Smartphone Scannable QR Code -->
                    <div class="qr-container" id="qr-box-{{ $idx }}" data-url="{{ $scanUrl }}"></div>

                    <!-- Clean Structured Information Table -->
                    <div class="info-container">
                        <!-- Asset Tag Header -->
                        <div class="font-extrabold text-[10pt] leading-tight text-slate-900 mb-1">
                            {{ $asset->asset_tag }}
                        </div>

                        <table class="info-table">
                            <tr>
                                <td class="lbl">Name</td>
                                <td class="val font-bold text-slate-900">{{ $deviceName }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">S/N</td>
                                <td class="val font-mono text-[5.5pt]">{{ $serialNum }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Cat</td>
                                <td class="val text-slate-600">{{ $categoryName }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Dept</td>
                                <td class="val text-slate-600">{{ $deptName }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Footer: Clean Copyright Text -->
                <div class="label-footer">
                    <span>© {{ date('Y') }} {{ config('app.name', 'Assetika') }} | ITSM System</span>
                    <span>Do Not Remove</span>
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
