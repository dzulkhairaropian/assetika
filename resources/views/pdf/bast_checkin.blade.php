<!DOCTYPE html>
<html>

<head>
    <title>Minutes of Return</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            padding: 5px 20px;
            line-height: 1.6;
        }

        .logo {
            text-align: left;
            margin-bottom: 5px;
        }

        .company-slogan {
            text-align: right;
            font-size: 10px;
            margin-top: -30px;
        }

        .doc-title {
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            font-size: 15px;
            text-transform: uppercase;
        }

        .doc-number {
            text-align: center;
            margin-bottom: 25px;
        }

        table.info-table td {
            padding: 0px 0 !important;
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
            padding: 0px !important;
            line-height: 1.15;
        }

        .signature-area {
            margin-top: 10px;
            width: 100%;
        }

        .signature-box {
            width: 32%;
            text-align: center;
            float: left;
        }

        .signature-name {
            margin-top: 45px;
            font-weight: bold;
            text-decoration: underline;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        footer {
            margin-top: 80px;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <div class="logo">
        <img src="{{ public_path('images/diastikalogo.png') }}" height="100px" />
        <div class="company-slogan">
            Your Partner for Best Solution
        </div>
    </div>

    <div class="doc-title">MINUTES OF RETURN</div>
    <div class="doc-number">
        No.{{ $sequence_number }}/IT/{{ $company_code }}/RT/{{ $checkout->checked_in_at->format('m') }}/{{ $checkout->checked_in_at->format('Y') }}
    </div>

    <p>On the day <strong>{{ $day }}</strong> date <strong>{{ $date }}</strong> months
        <strong>{{ $month }}</strong>
        in
        <strong>{{ $year }}</strong> the return of <strong>{{ $asset->product->name ?? 'Asset' }}</strong> has
        been carried
        out
        well between:
    </p>

    <table class="info-table" style="margin-top: -10px">
        <tr>
            <td>Name</td>
            <td>: {{ $user->name }}</td>
        </tr>
        <tr>
            <td>Position</td>
            <td>:
                @if ($user->position)
                    {{ $user->position->name }}
                @else
                    {{ $user_position ?? 'N/A' }}
                @endif
            </td>
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
            <td>:
                @if ($admin->position)
                    {{ $admin->position->name }}
                @else
                    {{ $admin_position ?? 'N/A' }}
                @endif
            </td>
        </tr>
    </table>
    <div class="section-title">As a SECOND PARTY (The Receiver),</div>

    <div class="section-title" style="margin-top: 10px">Asset Specifications:</div>
    <table class="spec-table">
        <tr>
            <td>1.</td>
            <td>Brand</td>
            <td>: {{ $asset->product->brand->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Type</td>
            <td>: {{ $asset->product->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Asset Tag</td>
            <td>: {{ $asset->asset_tag }}</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Specifications</td>
            <td>: {{ $asset->specification ?? '-' }}</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Serial Number</td>
            <td>: {{ $asset->serial_number }}</td>
        </tr>
        <tr>
            <td>6.</td>
            <td>Completeness</td>
            <td>: {{ $asset->accessories ?? '-' }}</td>
        </tr>
        <tr>
            <td>7.</td>
            <td>Condition</td>
            <td>: {{ $checkout->condition_report ?? 'Returned' }}</td>
        </tr>
    </table>

    <p>The asset has been returned in the condition described above.</p>

    @if ($checkout->checkin_notes)
        <p><strong>Additional Notes:</strong> {{ $checkout->checkin_notes }}</p>
    @endif

    <div class="signature-area clearfix">
        <div class="signature-box">
            The Returner,<br><br><br><br><br>
            <div class="signature-name">({{ $user->name }})</div>
            <div style="font-size:11px; color:#4b5563;">{{ $user->position?->name ?? $user_position ?? '' }}</div>
        </div>
        <div class="signature-box">
            Knowing,<br><br><br><br><br>
            <div class="signature-name">({{ $knowing_name ?? 'Afrizal Rajab' }})</div>
            <div style="font-size:11px; color:#4b5563;">{{ $knowing_position ?? 'IT Manager' }}</div>
        </div>
        <div class="signature-box">
            The Receiver,<br><br><br><br><br>
            <div class="signature-name">({{ $admin->name }})</div>
            <div style="font-size:11px; color:#4b5563;">{{ $admin->position?->name ?? $admin_position ?? '' }}</div>
        </div>
    </div>

    <footer>
        Jalan Rawa Gelam V Blok L, Kav 11-13, Gedung Etana Lantai 3,<br>
        Kelurahan Jatinegara, Kecamatan Cakung, Jakarta Timur,<br>
        DKI Jakarta, 13930<br>
        T: (+62) 21 38865038 - 38865045 - 38865047<br>
        E: customer.service@diastika.co.id &nbsp;&nbsp; www.diastika.co.id
    </footer>

</body>

</html>
