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

    public static function generateLoanFormPdf(?AssetCheckout $checkout = null, array $options = []): Response
    {
        $items = [];
        $borrowerName = trim((string) ($options['borrower_name'] ?? ''));
        $borrowerContact = trim((string) ($options['borrower_contact'] ?? ''));
        $department = trim((string) ($options['department'] ?? ''));
        $location = trim((string) ($options['location'] ?? ''));
        $loanDate = trim((string) ($options['loan_date'] ?? ''));
        $returnDate = trim((string) ($options['return_date'] ?? ''));
        $purpose = trim((string) ($options['purpose'] ?? ''));
        $issuedBy = trim((string) ($options['issued_by'] ?? (auth()->user()?->name ?? '')));
        $supervisorName = '';
        $supervisorPosition = '';
        $returnedAt = '';
        $returnedBy = '';
        $returnNotes = '';

        if ($checkout) {
            $checkout->loadMissing([
                'asset.product.brand',
                'asset.product.category',
                'checkedOutToUser.department',
                'checkedOutByUser',
                'checkedInByUser',
                'supervisor.position',
                'department',
                'location',
            ]);

            $asset = $checkout->asset;
            $toUser = $checkout->checkedOutToUser;

            $borrowerName = $toUser?->name ?? '';
            $borrowerContact = $toUser?->email ?? '';
            $department = $checkout->department?->name ?? $toUser?->department?->name ?? '';
            $location = $checkout->location?->name ?? '';
            $loanDate = $checkout->checked_out_at?->format('d M Y') ?? '';
            $returnDate = $checkout->expected_return_date?->format('d M Y') ?? '';
            $purpose = $checkout->checkout_notes ?? '';
            $issuedBy = $checkout->checkedOutByUser?->name ?? $issuedBy;
            $supervisorName = $checkout->supervisor?->name ?? '';
            $supervisorPosition = $checkout->supervisor?->position?->name ?? '';
            $returnedAt = $checkout->checked_in_at?->format('d M Y') ?? '';
            $returnedBy = $checkout->checkedInByUser?->name ?? '';
            $returnNotes = trim(($checkout->condition_report ? $checkout->condition_report.' ' : '').($checkout->checkin_notes ?? ''));

            if ($asset) {
                $items[] = [
                    'asset_tag' => $asset->asset_tag,
                    'serial_number' => $asset->serial_number,
                    'description' => trim(($asset->product?->brand?->name ?? '').' '.($asset->product?->model ?? $asset->product?->name ?? $asset->device_name ?? 'IT Asset')),
                    'qty' => 1,
                    'accessories' => $asset->accessories ?: '-',
                    'condition' => 'Good',
                ];
            }

            $docDate = $checkout->checked_out_at ?? now();
            $docNumber = sprintf('LOAN-IT/%s/%04d', $docDate->format('Y/m'), $checkout->id);
            $safeName = preg_replace('/[^A-Za-z0-9\-]/', '-', $borrowerName ?: 'User');
            $fileName = 'Equipment-Loan-Form-'.$safeName.'-'.$docDate->format('Ymd').'.pdf';
        } else {
            $assetIds = array_values(array_filter(array_map('intval', (array) ($options['asset_ids'] ?? []))));
            if (! empty($assetIds)) {
                $selectedAssets = Asset::with(['product.brand', 'product.category', 'status'])
                    ->whereIn('id', $assetIds)
                    ->get();

                foreach ($selectedAssets as $asset) {
                    $items[] = [
                        'asset_tag' => $asset->asset_tag,
                        'serial_number' => $asset->serial_number,
                        'description' => trim(($asset->product?->brand?->name ?? '').' '.($asset->product?->model ?? $asset->product?->name ?? $asset->device_name ?? 'IT Asset')),
                        'qty' => 1,
                        'accessories' => $asset->accessories ?: '-',
                        'condition' => $asset->status?->name ?? 'Good',
                    ];
                }
            }

            $formattedLoanDate = $loanDate ? Carbon::parse($loanDate)->format('d M Y') : '';
            $formattedReturnDate = $returnDate ? Carbon::parse($returnDate)->format('d M Y') : '';
            $loanDate = $formattedLoanDate;
            $returnDate = $formattedReturnDate;

            $docNumber = sprintf('LOAN-IT/%s/_____', now()->format('Y/m'));
            $fileName = 'Equipment-Loan-Form-'.now()->format('Y-m-d').'.pdf';
        }

        $pdf = Pdf::loadView('pdf.loan_form', [
            'document_number' => $docNumber,
            'borrower_name' => $borrowerName,
            'borrower_contact' => $borrowerContact,
            'department' => $department,
            'location' => $location,
            'loan_date' => $loanDate,
            'return_date' => $returnDate,
            'purpose' => $purpose,
            'issued_by' => $issuedBy,
            'supervisor_name' => $supervisorName,
            'supervisor_position' => $supervisorPosition,
            'it_manager_name' => 'Afrizal Rajab',
            'returned_at' => $returnedAt,
            'returned_by' => $returnedBy,
            'return_notes' => $returnNotes,
            'items' => $items,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    }
}
