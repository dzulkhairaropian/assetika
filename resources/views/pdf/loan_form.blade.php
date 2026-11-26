<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Equipment Loan Form</title>
    <style>
        @page {
            margin: 26px 32px 28px 32px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
            padding-bottom: 8px;
        }
        .company-name {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
        }
        .company-slogan {
            font-size: 9px;
            color: #64748b;
            text-align: right;
        }
        .doc-title-box {
            text-align: center;
            margin: 8px 0 12px 0;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #0f172a;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 10px;
            color: #475569;
            margin: 2px 0 4px 0;
        }
        .doc-number {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 10px;
            font-weight: bold;
            color: #334155;
        }
        .section-heading {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #1e293b;
            background: #f8fafc;
            border-left: 3px solid #465fff;
            padding: 4px 8px;
            margin: 10px 0 6px 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .meta-label {
            width: 18%;
            color: #475569;
            font-weight: bold;
        }
        .meta-value {
            width: 32%;
            border-bottom: 1px dotted #cbd5e1;
            color: #0f172a;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .items-table th {
            background: #1e293b;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 6px;
            border: 1px solid #1e293b;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 6px;
            font-size: 10px;
            vertical-align: middle;
            height: 20px;
        }
        .items-table tr:nth-child(even) td {
            background: #f8fafc;
        }
        .text-center { text-align: center; }
        .mono { font-family: "Courier New", Courier, monospace; font-weight: bold; }
        .terms-box {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 4px;
            padding: 7px 10px;
            margin-bottom: 10px;
        }
        .terms-box ul {
            margin: 3px 0 0 0;
            padding-left: 16px;
        }
        .terms-box li {
            font-size: 9.5px;
            color: #334155;
            margin-bottom: 2px;
        }
        .return-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 7px 10px;
            margin-bottom: 14px;
        }
        .return-table {
            width: 100%;
            border-collapse: collapse;
        }
        .return-table td {
            font-size: 10px;
            padding: 3px 4px;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .sig-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 4px 8px;
        }
        .sig-role {
            font-size: 10px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 44px;
        }
        .sig-line {
            border-top: 1px solid #334155;
            padding-top: 3px;
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            width: 82%;
            margin: 0 auto;
        }
        .sig-sub {
            font-size: 9px;
            color: #64748b;
        }
        .footer {
            margin-top: 16px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8.5px;
            color: #64748b;
            text-align: center;
        }
        .checkbox-square {
            display: inline-block;
            width: 8px;
            height: 8px;
            border: 1px solid #475569;
            margin-right: 3px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 45%;">
                @if(file_exists(public_path('images/diastikalogo.png')))
                    <img src="{{ public_path('images/diastikalogo.png') }}" height="42" alt="Company Logo">
                @else
                    <strong style="font-size: 15px;">ASSETIKA</strong>
                @endif
            </td>
            <td style="width: 55%;">
                <div class="company-name">PT. DIASTIKA BIOTEKINDO TBK</div>
                <div class="company-slogan">Your Partner for Best Solution &bull; IT Asset Management</div>
            </td>
        </tr>
    </table>

    <div class="doc-title-box">
        <div class="doc-title">EQUIPMENT LOAN FORM</div>
        <div class="doc-subtitle">IT Asset &amp; Equipment Borrowing Record</div>
        <div class="doc-number">Doc No: {{ $document_number }}</div>
    </div>

    <div class="section-heading">1. Borrower &amp; Loan Schedule</div>
    <table class="meta-table">
        <tr>
            <td class="meta-label">Borrower Name</td>
            <td class="meta-value">{{ $borrower_name ?: '' }}</td>
            <td class="meta-label">Loan Date</td>
            <td class="meta-value">{{ $loan_date ?: '' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Department</td>
            <td class="meta-value">{{ $department ?: '' }}</td>
            <td class="meta-label">Expected Return</td>
            <td class="meta-value">{{ $return_date ?: '' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Email / Ext.</td>
            <td class="meta-value">{{ $borrower_contact ?: '' }}</td>
            <td class="meta-label">Location / Site</td>
            <td class="meta-value">{{ $location ?: '' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Purpose / Notes</td>
            <td class="meta-value" colspan="3">{{ $purpose ?: '' }}</td>
        </tr>
    </table>

    <div class="section-heading">2. Borrowed Equipment Details</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 22%;">Asset Tag / Serial No.</th>
                <th style="width: 28%;">Item Description / Model</th>
                <th style="width: 6%;" class="text-center">Qty</th>
                <th style="width: 15%;">Accessories</th>
                <th style="width: 11%;">Out Cond.</th>
                <th style="width: 13%;">Return Check</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRows = max(5, count($items));
            @endphp
            @for($i = 0; $i < $totalRows; $i++)
                @php $item = $items[$i] ?? null; @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="mono">
                        @if($item)
                            {{ $item['asset_tag'] }}
                            @if(!empty($item['serial_number']))
                                <div style="font-size: 8.5px; font-weight: normal; color: #475569;">S/N: {{ $item['serial_number'] }}</div>
                            @endif
                        @endif
                    </td>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td class="text-center">{{ $item['qty'] ?? '' }}</td>
                    <td>{{ $item['accessories'] ?? '' }}</td>
                    <td>{{ $item['condition'] ?? '' }}</td>
                    <td style="font-size: 9px; white-space: nowrap;">
                        <span class="checkbox-square"></span>Good &nbsp;
                        <span class="checkbox-square"></span>Issue
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="section-heading">3. Loan Terms &amp; Responsibility</div>
    <div class="terms-box">
        <ul>
            <li>The borrower assumes full responsibility for the care, physical security, and proper operational use of all borrowed equipment.</li>
            <li>Equipment is strictly for official company business and must not be transferred to a third party without prior IT approval.</li>
            <li>All borrowed items and included accessories must be returned to IT on or before the Expected Return Date stated above.</li>
            <li>Any damage, malfunction, or loss occurring during the loan period must be reported to IT Support immediately.</li>
        </ul>
    </div>

    <div class="section-heading">4. Return Verification (Filled Upon Return)</div>
    <div class="return-box">
        <table class="return-table">
            <tr>
                <td style="width: 16%; font-weight: bold; color: #475569;">Returned Date</td>
                <td style="width: 34%; border-bottom: 1px dotted #cbd5e1;">{{ $returned_at ?: '' }}</td>
                <td style="width: 16%; font-weight: bold; color: #475569;">Received By</td>
                <td style="width: 34%; border-bottom: 1px dotted #cbd5e1;">{{ $returned_by ?: '' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #475569;">Return Status</td>
                <td colspan="3" style="padding-top: 6px;">
                    <span class="checkbox-square"></span> Complete &amp; Good Condition &nbsp;&nbsp;&nbsp;
                    <span class="checkbox-square"></span> Minor Issue &nbsp;&nbsp;&nbsp;
                    <span class="checkbox-square"></span> Damaged / Missing Parts
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #475569;">Return Notes</td>
                <td colspan="3" style="border-bottom: 1px dotted #cbd5e1; padding-top: 6px;">{{ $return_notes ?: '' }}</td>
            </tr>
        </table>
    </div>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-role">Borrower</div>
                <div class="sig-line">{{ $borrower_name ?: '________________________' }}</div>
                <div class="sig-sub">{{ $department ?: 'User / Recipient' }}</div>
            </td>
            <td>
                <div class="sig-role">Issued By</div>
                <div class="sig-line">{{ $issued_by ?: '________________________' }}</div>
                <div class="sig-sub">IT Support / Administrator</div>
            </td>
            <td>
                <div class="sig-role">Approved / Knowing</div>
                <div class="sig-line">{{ $supervisor_name ?: ($it_manager_name ?? 'Afrizal Rajab') }}</div>
                <div class="sig-sub">{{ $supervisor_position ?: 'IT Manager / Supervisor' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Jalan Rawa Gelam V Blok L, Kav 11-13, Gedung Etana Lantai 3, Jatinegara, Cakung, Jakarta Timur, DKI Jakarta 13930<br>
        T: (+62) 21 38865038 &bull; E: customer.service@diastika.co.id &bull; www.diastika.co.id
    </div>
</body>
</html>
