<!DOCTYPE html>
<html>
<head>
    <title>Berita Acara Serah Terima Aset GA</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; padding: 10px 20px; line-height: 1.5; }
        .logo { text-align: left; margin-bottom: 5px; }
        .company-slogan { text-align: right; font-size: 10px; margin-top: -30px; }
        .doc-title { text-align: center; font-weight: bold; margin-top: 15px; font-size: 16px; text-transform: uppercase; }
        .doc-number { text-align: center; margin-bottom: 20px; font-size: 12px; color: #4b5563; }
        table.info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.info-table td { padding: 4px 0; vertical-align: top; }
        .section-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; font-size: 14px; }
        .spec-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        .spec-table th, .spec-table td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        .spec-table th { background-color: #f3f4f6; font-size: 12px; }
        .signature-area { margin-top: 30px; width: 100%; }
        .signature-box { width: 32%; text-align: center; float: left; }
        .signature-name { margin-top: 55px; font-weight: bold; text-decoration: underline; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>

    <div class="logo">
        <img src="{{ public_path('images/diastikalogo.png') }}" height="40" alt="Logo">
    </div>
    <div class="company-slogan">
        <b>BERITA ACARA SERAH TERIMA ASET GENERAL AFFAIRS</b>
    </div>

    <div class="doc-title">BERITA ACARA SERAH TERIMA</div>
    <div class="doc-number">Nomor: BAST-GA/{{ $assignment->assigned_at->format('Ym') }}/{{ str_pad($assignment->id, 4, '0', STR_PAD_LEFT) }}</div>

    <p>Pada hari ini <b>{{ $assignment->assigned_at->translatedFormat('l, d F Y') }}</b>, kami yang bertanda tangan di bawah ini:</p>

    <table class="info-table">
        <tr>
            <td width="30%"><b>PIHAK PERTAMA (Yang Menyerahkan)</b></td>
            <td width="3%">:</td>
            <td>{{ $assignment->checkedOutByUser->name ?? 'Admin GA' }}</td>
        </tr>
        <tr>
            <td>Jabatan / Unit</td>
            <td>:</td>
            <td>General Affairs & Property Management</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="30%"><b>PIHAK KEDUA (Yang Menerima)</b></td>
            <td width="3%">:</td>
            <td>{{ $assignment->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Email / Dept</td>
            <td>:</td>
            <td>{{ $assignment->user->email ?? '-' }} ({{ $assignment->user->department->name ?? '-' }})</td>
        </tr>
    </table>

    <p>PIHAK PERTAMA menyerahkan barang/aset GA kepada PIHAK KEDUA, dan PIHAK KEDUA menyatakan telah menerima barang/aset tersebut dalam kondisi baik dan berfungsi normal dengan rincian sebagai berikut:</p>

    <table class="spec-table">
        <thead>
            <tr>
                <th>Kode Aset</th>
                <th>Nama Barang / Deskripsi</th>
                <th>Kategori / Subkategori</th>
                <th>Serial / Plat No</th>
                <th>Tgl Penyerahan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>{{ $asset->asset_code }}</b></td>
                <td>{{ $asset->name }} {{ $asset->brand ? '('.$asset->brand.' '.$asset->model.')' : '' }}</td>
                <td>{{ $asset->category->name ?? '-' }} / {{ $asset->subcategory->name ?? '-' }}</td>
                <td>{{ $asset->vehicle->license_plate ?? $asset->serial_number ?? '-' }}</td>
                <td>{{ $assignment->assigned_at->format('d M Y') }}</td>
            </tr>
        </tbody>
    </table>

    @if($assignment->notes)
        <p><b>Catatan Khusus:</b> {{ $assignment->notes }}</p>
    @endif

    <p style="font-size: 11px; color: #6b7280; margin-top: 15px;">
        * PIHAK KEDUA bertanggung jawab penuh atas pemeliharaan dan penggunaan barang/aset GA tersebut sesuai dengan prosedur dan ketentuan yang berlaku di perusahaan.
    </p>

    <div class="signature-area clearfix">
        <div class="signature-box">
            <p>PIHAK PERTAMA,</p>
            <div class="signature-name">{{ $assignment->checkedOutByUser->name ?? 'Admin GA' }}</div>
            <p style="font-size: 10px; color: #6b7280;">General Affairs Team</p>
        </div>
        <div class="signature-box" style="margin-left: 4%;">
            <p>PIHAK KEDUA,</p>
            <div class="signature-name">{{ $assignment->user->name ?? 'Penerima' }}</div>
            <p style="font-size: 10px; color: #6b7280;">Menerima & Menggunakan</p>
        </div>
        <div class="signature-box" style="margin-left: 4%;">
            <p>MENGETAHUI,</p>
            <div class="signature-name">Head of GA / Operations</div>
            <p style="font-size: 10px; color: #6b7280;">Management</p>
        </div>
    </div>

</body>
</html>
