<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labels - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { size: A4; margin: 8mm; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-white p-4">
    <div class="no-print mb-4 flex items-center justify-between">
        <h1 class="text-lg font-bold">{{ count($assets) }} Label(s) Ready</h1>
        <button onclick="window.print()" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Print Labels</button>
    </div>

    @php $colors = ['bg-blue-600','bg-green-600','bg-purple-600','bg-orange-600','bg-pink-600']; @endphp
    @foreach($assets as $idx => $asset)
        <div class="inline-block border border-gray-300 rounded-lg p-3 m-1" style="width: 8cm; height: 4.5cm; vertical-align: top;">
            <div class="flex items-start gap-2 h-full">
                <div class="flex-1 overflow-hidden">
                    <div class="text-xs font-bold text-gray-900 truncate">{{ config('app.name') }}</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1 truncate">{{ $asset->name }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $asset->product?->category?->name ?? '' }}</div>
                    <div class="mt-2 flex items-center gap-2">
                        <div class="bg-gray-100 p-1 rounded">
                            <svg id="qr-{{ $asset->id }}" class="w-16 h-16"></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-mono font-bold text-gray-900 break-all">{{ $asset->asset_tag }}</div>
                            <div class="text-[9px] text-gray-500 truncate">{{ $asset->serial_number ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(($idx + 1) % 8 === 0) <div class="page-break"></div> @endif
    @endforeach

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        @foreach($assets as $asset)
        new QRCode(document.getElementById("qr-{{ $asset->id }}"), {
            text: "{{ route('assets.public-view', $asset) }}",
            width: 64, height: 64,
            colorDark: "#000000", colorLight: "#ffffff",
        });
        @endforeach
    </script>
</body>
</html>
