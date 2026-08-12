export function printBulkLabels(assets, copies) {
    if (!assets.length) return;
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Print Asset Labels</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; background: #fff; margin: 0; }');
    printWindow.document.write('.label-grid { display: grid; grid-template-columns: repeat(auto-fill, 330px); gap: 12px; justify-content: start; }');
    printWindow.document.write('.label-card { display: flex; align-items: center; gap: 16px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb; width: 330px !important; max-width: 330px !important; box-sizing: border-box; page-break-inside: avoid; }');
    printWindow.document.write('.h-24 { height: 96px; } .w-24 { width: 96px; }');
    printWindow.document.write('.shrink-0 { flex-shrink: 0; }');
    printWindow.document.write('.bg-white { background-color: white; padding: 6px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; }');
    printWindow.document.write('.space-y-1 > * + * { margin-top: 4px; }');
    printWindow.document.write('.text-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 700; margin: 0; }');
    printWindow.document.write('.text-sm { font-size: 13px; font-weight: 700; color: #1f2937; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }');
    printWindow.document.write('.text-xs { font-size: 11px; font-weight: 600; color: #4b5563; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }');
    printWindow.document.write('.text-xs.font-medium { font-weight: 500; color: #6b7280; }');
    printWindow.document.write('img { max-width: 100%; height: auto; display: block; }');
    printWindow.document.write('@media print { body { padding: 0; } .label-card { page-break-inside: avoid; width: 330px !important; max-width: 330px !important; } }');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write('<div class="label-grid">');

    assets.forEach(asset => {
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(asset.url)}`;
        for (let i = 0; i < copies; i++) {
            printWindow.document.write(`
                <div class="label-card">
                    <div class="bg-white h-24 w-24 shrink-0">
                        <img src="${qrUrl}" alt="QR Code">
                    </div>
                    <div class="space-y-1" style="flex: 1; min-width: 0;">
                        <div>
                            <p class="text-label">Asset Tag</p>
                            <p class="text-sm">${asset.tag}</p>
                        </div>
                        <div>
                            <p class="text-label">Serial Number</p>
                            <p class="text-xs">${asset.sn}</p>
                        </div>
                        <div>
                            <p class="text-label">Company</p>
                            <p class="text-xs font-medium">${asset.company}</p>
                        </div>
                    </div>
                </div>
            `);
        }
    });

    printWindow.document.write('</div></body></html>');
    printWindow.document.close();

    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}

export function printSingleLabel(asset, copies) {
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Print Asset Label</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; background: #fff; margin: 0; }');
    printWindow.document.write('.label-grid { display: grid; grid-template-columns: repeat(auto-fill, 330px); gap: 12px; justify-content: start; }');
    printWindow.document.write('.print-label-card { display: flex; align-items: center; gap: 20px; padding: 20px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb; width: 330px !important; max-width: 330px !important; box-sizing: border-box; page-break-inside: avoid; }');
    printWindow.document.write('.h-28 { height: 112px; } .w-28 { width: 112px; }');
    printWindow.document.write('.shrink-0 { flex-shrink: 0; }');
    printWindow.document.write('.bg-white { background-color: white; padding: 8px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; }');
    printWindow.document.write('.space-y-1\\.5 > * + * { margin-top: 6px; }');
    printWindow.document.write('.text-\\[9px\\] { font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; font-weight: 700; margin-bottom: 2px; }');
    printWindow.document.write('.text-sm { font-size: 14px; font-weight: 700; color: #1f2937; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }');
    printWindow.document.write('.text-xs { font-size: 12px; font-weight: 600; color: #4b5563; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }');
    printWindow.document.write('.text-xs.font-medium { font-weight: 500; color: #6b7280; }');
    printWindow.document.write('img { max-width: 100%; height: auto; display: block; }');
    printWindow.document.write('@media print { body { padding: 0; } .print-label-card { page-break-inside: avoid; width: 330px !important; max-width: 330px !important; } }');
    printWindow.document.write('</style></head><body>');
    printWindow.document.write('<div class="label-grid">');

    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(asset.url)}`;
    for (let i = 0; i < copies; i++) {
        printWindow.document.write(`
            <div class="print-label-card">
                 <div class="bg-white h-28 w-28 shrink-0">
                     <img src="${qrUrl}" alt="QR Code">
                 </div>
                 <div class="space-y-1.5 flex-1 min-w-0">
                     <div>
                         <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Asset Tag</span>
                         <p class="text-sm font-bold text-gray-800 dark:text-white/90 truncate">${asset.tag}</p>
                     </div>
                     <div>
                         <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Serial Number</span>
                         <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate">${asset.sn}</p>
                     </div>
                     <div>
                         <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400 block">Company</span>
                         <p class="text-xs font-medium text-gray-600 dark:text-gray-400 truncate">${asset.company}</p>
                     </div>
                 </div>
            </div>
        `);
    }

    printWindow.document.write('</div></body></html>');
    printWindow.document.close();

    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}

export function exportSelectedAssets(assets, exportUrl) {
    if (!assets.length) return;
    const url = new URL(exportUrl, window.location.origin);
    assets.forEach(asset => url.searchParams.append('ids[]', asset.id));
    window.location.href = url.toString();
}

window.printBulkLabels = printBulkLabels;
window.printSingleLabel = printSingleLabel;
window.exportSelectedAssets = exportSelectedAssets;