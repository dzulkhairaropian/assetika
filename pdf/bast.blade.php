<!DOCTYPE html>
<html>

<head>
    <title>Minutes of Handover</title>
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

    <div class="doc-title">MINUTES OF HANDOVER</div>
    <div class="doc-number">
        No.{{ $sequence_number }}/IT/{{ $company_code }}/HD/{{ $checkout->checked_out_at->format('m') }}/{{ $checkout->checked_out_at->format('Y') }}
    </div>

    <p>On the day <strong>{{ $day }}</strong> date <strong>{{ $date }}</strong> months
        <strong>{{ $month }}</strong>
        in
        <strong>{{ $year }}</strong> the handover of <strong>{{ $asset->product->name ?? 'Asset' }}</strong> has
        been carried
        out
        well between:
    </p>

    <table class="info-table" style="margin-top: -10px">
        <tr>
            <td>Name</td>
            <td>: {{ $giver->name }}</td>
        </tr>
        <tr>
            <td>Position</td>
            <td>:
                @if ($giver->position)
                    {{ $giver->position->name }}
                @else
                    {{ $giver_position }}
                @endif
            </td>
        </tr>
    </table>
    <div class="section-title">As a FIRST PARTY,</div>

    <div class="section-title">With</div>
    <table class="info-table">
        <tr>
            <td>Name</td>
            <td>: {{ $receiver->name }}</td>
        </tr>
        <tr>
            <td>Position</td>
            <td>:
                @if ($receiver->position)
                    {{ $receiver->position->name }}
                @else
                    {{ $receiver_position }}
                @endif
            </td>
        </tr>
    </table>
    <div class="section-title">As a SECOND PARTY,</div>

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
            <td>: {{ $asset->condition_report ?? 'Good' }}</td>
        </tr>
    </table>

    <p>Thus, the Handover Minutes are made and used as they should.</p>

    <div class="signature-area clearfix">
        <div class="signature-box">
            The Submitted,<br><br><br>
            <div class="signature-name">({{ $giver->name }})</div><br><br><br><br>
            <!-- Placeholder for additional signature if needed -->
            <div style="margin-top:12px; font-weight:bold;">(Afrizal Rajab)</div>
        </div>
        <div class="signature-box">
            <br><br><br><br><br><br><br>
            Knowing
        </div>
        <div class="signature-box">
            The Receiver,<br><br><br>
            <div class="signature-name">({{ $receiver->name }})</div><br><br><br>
            <!-- Placeholder for additional signature if needed -->
            @php
                $supervisorPosition = '';
                if ($receiver_supervisor->position) {
                    $supervisorPosition = $receiver_supervisor->position->name;
                }
            @endphp
            <div style="margin-top:30px; font-weight:bold;">({{ $receiver_supervisor->name }} @if ($supervisorPosition)
                    - {{ $supervisorPosition }}
                @endif)</div>
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
