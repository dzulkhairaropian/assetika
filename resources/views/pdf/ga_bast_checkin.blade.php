<!DOCTYPE html>
<html>

<head>
    <title>GA Minutes of Return</title>
    <style>
        @page {
            margin: 12mm 14mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11.5px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .logo {
            text-align: left;
            margin-bottom: 2px;
        }

        .company-slogan {
            text-align: right;
            font-size: 9px;
            margin-top: -24px;
        }

        .doc-title {
            text-align: center;
            font-weight: bold;
            margin-top: 8px;
            font-size: 13px;
            text-transform: uppercase;
        }

        .doc-number {
            text-align: center;
            margin-bottom: 10px;
            font-size: 11px;
        }

        table.info-table td {
            padding: 0 !important;
            line-height: 1.2;
        }

        .section-title {
            font-weight: bold;
        }

        .spec-table {
            width: 100%;
            border-collapse: collapse;
        }

        .spec-table td {
            padding: 0 !important;
            line-height: 1.15;
            vertical-align: top;
        }

        .signature-area {
            margin-top: 8px;
            width: 100%;
        }

        .signature-box {
            width: 33.33%;
            text-align: center;
            float: left;
        }

        .signature-name {
            margin-top: 28px;
            font-weight: bold;
            text-decoration: underline;
        }

        p {
            margin: 6px 0;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        footer {
            margin-top: 20px;
            font-size: 9px;
            line-height: 1.3;
        }
    </style>
</head>

<body>

    <div class="logo">
        <img src="{{ public_path('images/diastikalogo.png') }}" height="82px" />
        <div class="company-slogan">
            Your Partner for Best Solution
        </div>
    </div>

    <div class="doc-title">MINUTES OF RETURN (GA ASSET)</div>
    <div class="doc-number">
        No.{{ $sequence_number }}/GA/{{ $company_code }}/RT/{{ $assignment->returned_at->format('m') }}/{{ $assignment->returned_at->format('Y') }}
    </div>

    <p class="no-break">
        On the day <strong>{{ $day }}</strong> date <strong>{{ $date }}</strong> month <strong>{{ $month }}</strong>
        in <strong>{{ $year }}</strong>, the return of <strong>{{ $asset->name ?? 'GA Asset' }}</strong> has been
        carried out between:
    </p>

    <table class="info-table" style="margin-top: -10px">
        <tr>
            <td>Name</td>
            <td>: {{ $user->name }}</td>
        </tr>
        <tr>
            <td>Position</td>
            <td>: {{ $user->position->name ?? '-' }}</td>
        </tr>
    </table>
    <div class="section-title">As a FIRST PARTY (The Returner),</div>

    <div class="section-title">With</div>
    <table class="info-table">
        <tr>
            <td>Name</td>
            <td>: {{ $admin->name }}</td>
        </tr>
        <tr>
            <td>Position</td>
            <td>: {{ $admin->position->name ?? '-' }}</td>
        </tr>
    </table>
    <div class="section-title">As a SECOND PARTY (The Receiver),</div>

    @php
        $specNo = 1;
        $vehicle = $asset->vehicle;
    @endphp
    <div class="section-title" style="margin-top: 8px">Asset Specifications:</div>
    <table class="spec-table no-break">
        <tr>
            <td>{{ $specNo++ }}.</td>
            <td>Asset Name</td>
            <td>: {{ $asset->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $specNo++ }}.</td>
            <td>Quantity</td>
            <td>: {{ $asset->qty ?? 1 }}</td>
        </tr>
        <tr>
            <td>{{ $specNo++ }}.</td>
            <td>Category</td>
            <td>: {{ $asset->category->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $specNo++ }}.</td>
            <td>Brand / Model</td>
            <td>: {{ $asset->brand ?? '-' }} / {{ $asset->model ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $specNo++ }}.</td>
            <td>Asset Code</td>
            <td>: {{ $asset->asset_code ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $specNo++ }}.</td>
            <td>Inventory Number</td>
            <td>: {{ $asset->inventory_number ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $specNo++ }}.</td>
            <td>Serial Number</td>
            <td>: {{ $asset->serial_number ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $specNo++ }}.</td>
            <td>Condition on Return</td>
            <td>: {{ $assignment->condition_on_return ?? '-' }}</td>
        </tr>
        @if($vehicle)
            @if($vehicle->license_plate)
                <tr>
                    <td>{{ $specNo++ }}.</td>
                    <td>License Plate</td>
                    <td>: {{ $vehicle->license_plate }}</td>
                </tr>
            @endif
            @if($vehicle->chassis_number)
                <tr>
                    <td>{{ $specNo++ }}.</td>
                    <td>Chassis Number</td>
                    <td>: {{ $vehicle->chassis_number }}</td>
                </tr>
            @endif
            @if($vehicle->engine_number)
                <tr>
                    <td>{{ $specNo++ }}.</td>
                    <td>Engine Number</td>
                    <td>: {{ $vehicle->engine_number }}</td>
                </tr>
            @endif
            @if($vehicle->bpkb_number)
                <tr>
                    <td>{{ $specNo++ }}.</td>
                    <td>BPKB Number</td>
                    <td>: {{ $vehicle->bpkb_number }}</td>
                </tr>
            @endif
            @if($vehicle->stnk_number)
                <tr>
                    <td>{{ $specNo++ }}.</td>
                    <td>STNK Number</td>
                    <td>: {{ $vehicle->stnk_number }}</td>
                </tr>
            @endif
            @if($vehicle->tax_due_date)
                <tr>
                    <td>{{ $specNo++ }}.</td>
                    <td>Vehicle Tax Due</td>
                    <td>: {{ $vehicle->tax_due_date->format('d M Y') }}</td>
                </tr>
            @endif
        @endif
    </table>

    @if($assignment->checkin_notes)
        <p class="no-break"><strong>Additional Notes:</strong> {{ $assignment->checkin_notes }}</p>
    @endif

    <p>The asset has been returned in the condition described above.</p>

    <div class="signature-area clearfix no-break">
        <div class="signature-box">
            The Returner,<br><br>
            <div class="signature-name">({{ $user->name }})</div>
        </div>
        <div class="signature-box">
            <br><br>
            Knowing
        </div>
        <div class="signature-box">
            The Receiver,<br><br>
            <div class="signature-name">({{ $admin->name }})</div>
        </div>
    </div>

    <footer class="no-break">
        Jalan Rawa Gelam V Blok L, Kav 11-13, Gedung Etana Lantai 3,<br>
        Kelurahan Jatinegara, Kecamatan Cakung, Jakarta Timur,<br>
        DKI Jakarta, 13930<br>
        T: (+62) 21 38865038 - 38865045 - 38865047<br>
        E: customer.service@diastika.co.id &nbsp;&nbsp; www.diastika.co.id
    </footer>

</body>

</html>
