@extends('layouts.fullscreen-layout')

@section('content')
<div class="bg-white min-h-screen p-8 sm:p-12 text-gray-900">
    <!-- Print Header / Navigation -->
    <div class="no-print mb-8 flex items-center justify-between border-b pb-4">
        <a href="{{ route('assets.show', $asset) }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Back to Asset
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-opacity-90 transition-all">
            <i data-lucide="printer" class="h-4 w-4"></i>
            Print Document
        </button>
    </div>

    <!-- BAST Document -->
    <div class="max-w-4xl mx-auto border p-12 shadow-sm print:shadow-none print:border-none">
        <!-- Header -->
        <div class="text-center mb-12 border-b-2 border-gray-900 pb-6">
            <h1 class="text-2xl font-bold uppercase tracking-widest">Berita Acara Serah Terima Aset IT</h1>
            <p class="text-sm mt-2">Nomor: BAST/IT/{{ date('Y/m') }}/{{ str_pad($checkout->id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        <p class="mb-8 leading-relaxed">
            Pada hari ini, <strong>{{ $checkout->checked_out_at?->translatedFormat('l') ?? '..........' }}</strong>, 
            tanggal <strong>{{ $checkout->checked_out_at?->format('d') ?? '.....' }}</strong> 
            bulan <strong>{{ $checkout->checked_out_at?->translatedFormat('F') ?? '..........' }}</strong> 
            tahun <strong>{{ $checkout->checked_out_at?->format('Y') ?? '..........' }}</strong>, 
            telah dilakukan penyerahan aset IT dengan rincian sebagai berikut:
        </p>

        <!-- Section 1: Parties -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div class="space-y-3">
                <h3 class="font-bold border-b pb-1">PIHAK I (PENYERAH)</h3>
                <table class="text-sm w-full">
                    <tr>
                        <td class="w-24 py-1">Nama</td>
                        <td class="py-1">: {{ auth()->user()->name }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">Jabatan</td>
                        <td class="py-1">: IT Administrator</td>
                    </tr>
                    <tr>
                        <td class="py-1">Dept</td>
                        <td class="py-1">: IT Department</td>
                    </tr>
                </table>
            </div>
            <div class="space-y-3">
                <h3 class="font-bold border-b pb-1">PIHAK II (PENERIMA)</h3>
                <table class="text-sm w-full">
                    <tr>
                        <td class="w-24 py-1">Nama</td>
                        <td class="py-1">: {{ $checkout->checkedOutToUser->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">Jabatan</td>
                        <td class="py-1">: {{ $checkout->checkedOutToUser->position->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">Dept</td>
                        <td class="py-1">: {{ $checkout->department->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Section 2: Asset Details -->
        <div class="mb-10">
            <h3 class="font-bold border-b mb-3 pb-1 uppercase">Rincian Aset</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 p-2 text-left">Deskripsi / Model</th>
                            <th class="border border-gray-300 p-2 text-left">Serial Number</th>
                            <th class="border border-gray-300 p-2 text-left">Asset Tag</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-gray-300 p-2">
                                {{ optional($asset->product->brand)->name }} {{ optional($asset->product)->model }}
                            </td>
                            <td class="border border-gray-300 p-2">{{ $asset->serial_number ?? '-' }}</td>
                            <td class="border border-gray-300 p-2 font-mono font-bold">{{ $asset->asset_tag }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if($asset->specification)
                <div class="mt-4 p-3 bg-gray-50 border rounded-lg italic text-xs">
                    <strong>Spesifikasi:</strong><br>
                    {{ $asset->specification }}
                </div>
            @endif
        </div>

        <!-- Section 3: Terms -->
        <div class="mb-12">
            <h3 class="font-bold border-b mb-3 pb-1 uppercase">Ketentuan & Syarat</h3>
            <ul class="list-decimal ml-5 text-sm space-y-2 leading-relaxed">
                <li>Penerima bertanggung jawab penuh atas keamanan dan pemeliharaan aset tersebut di atas.</li>
                <li>Aset hanya digunakan untuk kepentingan operasional perusahaan.</li>
                <li>Apabila terjadi kerusakan akibat kelalaian atau kehilangan, maka penerima bersedia mengikuti prosedur perusahaan yang berlaku.</li>
                <li>Aset harus dikembalikan dalam kondisi baik apabila masa kerja berakhir atau terdapat penggantian aset baru.</li>
            </ul>
        </div>

        <!-- Section 4: Signature Blocks -->
        <div class="grid grid-cols-2 text-center mt-20">
            <div>
                <p class="mb-20 font-medium">PIHAK I (PENYERAH)</p>
                <div class="inline-block border-b-2 border-gray-900 px-8 pb-1">
                    <strong>{{ auth()->user()->name }}</strong>
                </div>
                <p class="text-xs text-gray-500 mt-1">IT Administrator</p>
            </div>
            <div>
                <p class="mb-20 font-medium">PIHAK II (PENERIMA)</p>
                <div class="inline-block border-b-2 border-gray-900 px-8 pb-1">
                    <strong>{{ $checkout->checkedOutToUser->name ?? '..........' }}</strong>
                </div>
                <p class="text-xs text-gray-500 mt-1">User / Karyawan</p>
            </div>
            
            <div class="col-span-2 mt-12 flex justify-center">
                <div class="w-64">
                    <p class="mb-20 font-medium uppercase">Mengetahui (SUPERVISOR)</p>
                    <div class="inline-block border-b-2 border-gray-900 px-8 pb-1 w-full">
                        <strong>{{ $checkout->supervisor->name ?? '..........' }}</strong>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Manager / Supervisor</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-20 pt-8 border-t text-[10px] text-gray-400 text-center uppercase">
            Sistem Manajemen Aset IT - {{ config('app.name') }} - Dicetak pada {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    body {
        background-color: white !important;
    }
    .min-h-screen {
        min-height: auto !important;
        padding: 0 !important;
    }
    .shadow-sm {
        box-shadow: none !important;
    }
    .border {
        border: none !important;
    }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.createIcons && window.lucideIcons) {
            createIcons({ icons: lucideIcons });
        }
    });
</script>
@endsection
