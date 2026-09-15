<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCheckout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class BastService
{
    public static function generateBastPdf(Asset $asset, AssetCheckout $checkout): Response
    {
        $asset->load(['product.brand', 'product.category', 'department.company']);

        if ($checkout->checked_in_at !== null) {
            $dateForBast = $checkout->checked_in_at;
            $bastYear = $dateForBast->year;

            $returnSeq = AssetCheckout::whereNotNull('checked_in_at')
                ->whereYear('checked_in_at', $bastYear)
                ->where('id', '<=', $checkout->id)
                ->count();

            $bastNumber = sprintf('BAST-IT-RETURN/%d/%s', $bastYear, str_pad((string) $returnSeq, 3, '0', STR_PAD_LEFT));

            $pdf = Pdf::loadView('pdf.bast_checkin', [
                'asset' => $asset,
                'checkout' => $checkout,
                'bastNumber' => $bastNumber,
            ]);

            $fileName = 'BAST-IT-RETURN-'.str_replace(' ', '-', $asset->assignedToUser?->name ?? 'User').'-'.$dateForBast->format('Ymd').'.pdf';

            return $pdf->stream($fileName);
        }

        $dateForBast = $checkout->checked_out_at ?? $checkout->created_at;
        $bastYear = $dateForBast->year;

        $checkoutSeq = AssetCheckout::whereYear('checked_out_at', $bastYear)
            ->where('id', '<=', $checkout->id)
            ->count();

        $bastNumber = sprintf('BAST-IT/%d/%s', $bastYear, str_pad((string) $checkoutSeq, 3, '0', STR_PAD_LEFT));

        $pdf = Pdf::loadView('pdf.bast', [
            'asset' => $asset,
            'checkout' => $checkout,
            'bastNumber' => $bastNumber,
        ]);

        $fileName = 'BAST-IT-'.str_replace(' ', '-', $checkout->checkedOutToUser?->name ?? 'User').'-'.$dateForBast->format('Ymd').'.pdf';

        return $pdf->stream($fileName);
    }
}
