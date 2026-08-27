<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label - {{ $asset->asset_tag }}</title>
    <style>
        @page { size: 50mm 25mm; margin: 0; }
        body { font-family: sans-serif; margin: 0; padding: 4px; display: flex; align-items: center; justify-content: space-between; width: 48mm; height: 23mm; box-sizing: border-box; }
        .info { font-size: 8px; line-height: 1.1; font-weight: bold; overflow: hidden; }
        .tag { font-size: 9px; margin-bottom: 2px; color: #111; }
        .name { font-size: 7px; color: #555; white-space: nowrap; text-overflow: ellipsis; }
        .qr { width: 20mm; height: 20mm; }
    </style>
</head>
<body onload="window.print()">
    <div class="info">
        <div class="tag">{{ $asset->asset_tag }}</div>
        <div class="name">{{ $asset->device_name ?: $asset->product?->name }}</div>
        <div style="font-size: 6px; color: #777; margin-top: 3px;">S/N: {{ $asset->serial_number ?: '-' }}</div>
    </div>
    <div class="qr">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($asset->asset_tag) }}" alt="QR Code" style="width: 100%; height: 100%;">
    </div>
</body>
</html>
