<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Asset Labels - {{ config('app.name', 'Assetika') }}</title>
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script>
        if (typeof QRCode === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>');
        }
    </script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 16px;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Action Bar (No Print) */
        .top-bar {
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: #ffffff;
            padding: 14px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .top-bar-info h1 {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .top-bar-info p {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1;
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            line-height: 1;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
        }

        .btn-secondary {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .btn-primary {
            border: 1px solid transparent;
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin: 0;
                padding: 0;
                background: #fff;
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
            grid-template-columns: repeat(3, 68mm);
            gap: 2.5mm 3mm;
            justify-content: start;
            align-content: start;
        }

        /* Fixed Dimensions Label Card (68mm x 38mm) */
        .label-card {
            width: 68mm;
            max-width: 68mm;
            height: 38mm;
            border: 1px dashed #cbd5e1;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            page-break-inside: avoid;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        @media print {
            .label-card {
                box-shadow: none;
                border: 1px dashed #cbd5e1;
            }
        }

        .label-header {
            background-color: #2563eb;
            color: #ffffff;
            padding: 2.5px 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            height: 7mm;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        .label-header-logo {
            height: 5.2mm;
            max-width: 25mm;
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
            line-height: 1.1;
        }

        .label-header-sub {
            font-size: 5pt;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .label-header-title {
            font-size: 6pt;
            font-weight: 800;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .label-body {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 2.5mm;
            padding: 2mm 2.5mm;
            background: #ffffff;
            box-sizing: border-box;
            overflow: hidden;
        }

        .qr-container {
            width: 20mm;
            height: 20mm;
            flex-shrink: 0;
            background: #ffffff;
            padding: 0.5mm;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .qr-container img, .qr-container canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
            object-fit: contain;
        }

        .info-container {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1.5px;
        }

        .asset-tag-heading {
            font-size: 8.5pt;
            font-weight: 800;
            line-height: 1.15;
            color: #0f172a;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            letter-spacing: -0.01em;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6pt;
            line-height: 1.3;
        }

        .info-table tr {
            border: none;
        }

        .info-table td {
            padding: 0.5px 0;
            vertical-align: top;
            border: none;
        }

        .info-table td.lbl {
            font-size: 5.5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            width: 28px;
            white-space: nowrap;
        }

        .info-table td.val {
            font-weight: 600;
            color: #1e293b;
            word-break: break-word;
            white-space: normal;
        }

        .info-table td.val-sn {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 5.5pt;
            font-weight: 700;
            color: #0f172a;
        }

        .label-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 1.5mm 2.5mm;
            font-size: 4.5pt;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            line-height: 1;
            flex-shrink: 0;
            height: 4.5mm;
            box-sizing: border-box;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (No Print) -->
    <div class="no-print top-bar">
        <div class="top-bar-info">
            <h1>Asset Label Print Preview</h1>
            <p>Layout Grid 3 Columns • Format A4 • Total {{ count($assets) }} item(s)</p>
        </div>
        <div class="top-bar-actions">
            <button type="button" onclick="window.close()" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Close</span>
            </button>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Labels</span>
            </button>
        </div>
    </div>

    <!-- Printable Grid Area -->
    <div class="label-grid">
        @foreach($assets as $idx => $asset)
            @php
                $scanUrl = route('assets.public-view', $asset);
                $companyName = optional(optional($asset->department)->company)->name ?? config('app.name', 'Assetika');
                $deviceName = $asset->device_name ?: (optional($asset->product)->name ?: optional($asset->product)->model);
                $serialNum = $asset->serial_number ?? '-';
                $categoryName = optional(optional($asset->product)->category)->name ?? '-';
                $deptName = optional($asset->department)->name ?? '-';
            @endphp
            <div class="label-card">
                <!-- Header 2 Baris + Multi-Company Dynamic Logo -->
                <div class="label-header">
                    @php
                        $company = optional($asset->department)->company;
                        $companyLogo = $company?->logo ? Storage::url($company->logo) : asset('images/diastikalogo.png');
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
                        <div class="asset-tag-heading">
                            {{ $asset->asset_tag }}
                        </div>

                        <table class="info-table">
                            <tr>
                                <td class="lbl">NAME</td>
                                <td class="val">{{ $deviceName }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">S/N</td>
                                <td class="val val-sn">{{ $serialNum }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">CAT</td>
                                <td class="val">{{ $categoryName }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">DEPT</td>
                                <td class="val">{{ $deptName }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Footer: Clean Hardcoded Copyright Text -->
                <div class="label-footer">
                    <span>© {{ date('Y') }} Assetika | ITSM</span>
                    <span>Do Not Remove</span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof QRCode === 'undefined') {
                console.error("QRCode library failed to load");
                return;
            }
            const qrBoxes = document.querySelectorAll('.qr-container');
            qrBoxes.forEach(function(box) {
                const targetUrl = box.getAttribute('data-url');
                if (!targetUrl) return;
                box.innerHTML = '';
                new QRCode(box, {
                    text: targetUrl,
                    width: 90,
                    height: 90,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            });
        });
    </script>

</body>
</html>
