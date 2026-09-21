<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

class BastService
{
    public static function generateBastPdf(Asset $asset, AssetCheckout $checkout): Response
    {
        $asset->load(['product.brand', 'product.category', 'department.company', 'assignedToUser.position']);
        $checkout->load(['checkedOutByUser.position', 'checkedOutToUser.position', 'checkedInByUser.position', 'supervisor.position']);

        $companyCode = optional(optional($asset->department)->company)->code ?? 'DB';

        if ($checkout->checked_in_at !== null) {
            $dateForBast = Carbon::parse($checkout->checked_in_at);
            $bastYear = $dateForBast->year;

            $returnSeq = AssetCheckout::whereNotNull('checked_in_at')
                ->whereYear('checked_in_at', $bastYear)
                ->where('id', '<=', $checkout->id)
                ->count();

            $seqStr = str_pad((string) $returnSeq, 3, '0', STR_PAD_LEFT);
            $bastNumber = sprintf('BAST-IT-RETURN/%d/%s', $bastYear, $seqStr);

            $user = $checkout->checkedOutToUser ?? $asset->assignedToUser ?? new User(['name' => 'User']);
            $admin = $checkout->checkedInByUser ?? auth()->user() ?? new User(['name' => 'IT Admin']);

            $knowingUser = $checkout->supervisor;
            $supervisorName = $knowingUser?->name;
            $supervisorPosition = $knowingUser?->position?->name ?? 'Supervisor';

            $pdf = Pdf::loadView('pdf.bast_checkin', [
                'asset' => $asset,
                'checkout' => $checkout,
                'bastNumber' => $bastNumber,
                'sequence_number' => $seqStr,
                'company_code' => $companyCode,
                'day' => $dateForBast->format('l'),
                'date' => $dateForBast->format('d'),
                'month' => $dateForBast->format('F'),
                'year' => $dateForBast->format('Y'),
                'user' => $user,
                'user_position' => $user->position?->name ?? 'Staff',
                'admin' => $admin,
                'admin_position' => $admin->position?->name ?? 'IT Staff',
                'it_manager_name' => 'Afrizal Rajab',
                'it_manager_position' => 'IT Manager',
                'supervisor_name' => $supervisorName,
                'supervisor_position' => $supervisorPosition,
            ]);

            $fileName = 'BAST-IT-RETURN-'.str_replace(' ', '-', $user->name ?? 'User').'-'.$dateForBast->format('Ymd').'.pdf';

            return $pdf->stream($fileName);
        }

        $dateForBast = Carbon::parse($checkout->checked_out_at ?? $checkout->created_at);
        $bastYear = $dateForBast->year;

        $checkoutSeq = AssetCheckout::whereYear('checked_out_at', $bastYear)
            ->where('id', '<=', $checkout->id)
            ->count();

        $seqStr = str_pad((string) $checkoutSeq, 3, '0', STR_PAD_LEFT);
        $bastNumber = sprintf('BAST-IT/%d/%s', $bastYear, $seqStr);

        $giver = $checkout->checkedOutByUser ?? auth()->user() ?? new User(['name' => 'IT Admin']);
        $receiver = $checkout->checkedOutToUser ?? $asset->assignedToUser ?? new User(['name' => 'User']);
        $knowingUser = $checkout->supervisor;
        $supervisorName = $knowingUser?->name;
        $supervisorPosition = $knowingUser?->position?->name ?? 'Supervisor';

        $pdf = Pdf::loadView('pdf.bast', [
            'asset' => $asset,
            'checkout' => $checkout,
            'bastNumber' => $bastNumber,
            'sequence_number' => $seqStr,
            'company_code' => $companyCode,
            'day' => $dateForBast->format('l'),
            'date' => $dateForBast->format('d'),
            'month' => $dateForBast->format('F'),
            'year' => $dateForBast->format('Y'),
            'giver' => $giver,
            'giver_position' => $giver->position?->name ?? 'IT Staff',
            'receiver' => $receiver,
            'receiver_position' => $receiver->position?->name ?? 'Staff',
            'it_manager_name' => 'Afrizal Rajab',
            'it_manager_position' => 'IT Manager',
            'supervisor_name' => $supervisorName,
            'supervisor_position' => $supervisorPosition,
        ]);

        $fileName = 'BAST-IT-'.str_replace(' ', '-', $receiver->name ?? 'User').'-'.$dateForBast->format('Ymd').'.pdf';

        return $pdf->stream($fileName);
    }
}
