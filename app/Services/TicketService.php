<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\SlaStatus;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function createTicket(array $data, ?UploadedFile $attachment = null): Ticket
    {
        return DB::transaction(function () use ($data, $attachment) {
            $ticketDate = ! empty($data['created_at']) ? Carbon::parse($data['created_at']) : now();
            $ticketType = ! empty($data['ticket_type_id']) ? TicketType::find($data['ticket_type_id']) : null;
            $requestCode = strtoupper($ticketType?->code ?: 'REQ');
            $prefix = $requestCode.'-'.$ticketDate->format('ymd').'-';

            $latestTicket = Ticket::where('request_id', 'like', $prefix.'%')
                ->orderBy('id', 'desc')
                ->first();

            $nextSeq = 1;
            if ($latestTicket) {
                $parts = explode('-', $latestTicket->request_id);
                $nextSeq = ((int) end($parts)) + 1;
            }

            $dailySequence = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);
            $data['request_id'] = $requestCode.'-'.$ticketDate->format('ymd').'-'.$dailySequence;
            $data['request_date'] = $ticketDate->toDateString();
            $data['request_time'] = $ticketDate;
            $data['created_at'] = $ticketDate;

            // Auto-map category and subcategory from asset if asset_id is provided
            if (! empty($data['asset_id']) && empty($data['ticket_category_id'])) {
                $assetObj = Asset::with('product.category')->find($data['asset_id']);
                if ($assetObj) {
                    $mapped = AssetTicketService::resolveTicketCategoryForAsset($assetObj);
                    $data['ticket_category_id'] = $mapped['category_id'];
                    $data['ticket_subcategory_id'] = $mapped['subcategory_id'];
                }
            }

            $requester = User::find($data['requester_user_id'] ?? null);
            if ($requester) {
                $data['requester_name'] = $requester->name;
                $data['requester_email'] = $requester->email;
                $data['requester_company_id'] = $requester->company_id;
                $data['requester_department_id'] = $requester->department_id;
            }

            $data['created_by_id'] = auth()->id();

            if (! empty($data['completed_date'])) {
                $currentStatus = ! empty($data['status_id']) ? TicketStatus::find($data['status_id']) : null;
                if (! $currentStatus || ! $currentStatus->is_closed) {
                    $completedStatus = TicketStatus::where('name', 'Completed')->first()
                        ?? TicketStatus::where('is_closed', true)->first();
                    if ($completedStatus) {
                        $data['status_id'] = $completedStatus->id;
                    }
                }
                if (empty($data['completed_time'])) {
                    $data['completed_time'] = now();
                }
            }

            if (empty($data['sla_status_id'])) {
                $fulfilledSla = SlaStatus::where('code', 'FULFILLED')->orWhere('name', 'Fulfilled')->first();
                if ($fulfilledSla) {
                    $data['sla_status_id'] = $fulfilledSla->id;
                }
            }

            $ticket = new Ticket;
            $ticket->fill($data);
            $ticket->created_at = $ticketDate;
            $ticket->save();

            if ($attachment) {
                $path = $attachment->store('attachments/tickets', 'public');
                $ticket->attachments()->create([
                    'user_id' => auth()->id(),
                    'filename' => $attachment->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $attachment->getClientMimeType(),
                    'size' => $attachment->getSize(),
                ]);
            }

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'action' => 'Created Ticket',
                'description' => 'Ticket #'.$ticket->request_id.' created.',
            ]);

            if ($ticket->assignedTo()->exists()) {
                $ticket->assignedTo->notify(new SystemNotification(
                    'New Ticket Assigned',
                    'Ticket #'.$ticket->request_id.' has been assigned to you.',
                    route('tickets.show', $ticket),
                    'ticket',
                    'ITSM'
                ));
            }

            return $ticket;
        });
    }

    public function updateTicket(Ticket $ticket, array $data, ?UploadedFile $attachment = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $data, $attachment) {
            $oldStatusId = $ticket->status_id;

            if (! empty($data['requester_user_id'])) {
                $requester = User::find($data['requester_user_id']);
                if ($requester) {
                    $data['requester_name'] = $requester->name;
                    $data['requester_email'] = $requester->email;
                    $data['requester_company_id'] = $requester->company_id;
                    $data['requester_department_id'] = $requester->department_id;
                }
            }

            if (! empty($data['completed_date'])) {
                $currentStatus = ! empty($data['status_id']) ? TicketStatus::find($data['status_id']) : null;
                if (! $currentStatus || ! $currentStatus->is_closed) {
                    $completedStatus = TicketStatus::where('name', 'Completed')->first()
                        ?? TicketStatus::where('is_closed', true)->first();
                    if ($completedStatus) {
                        $data['status_id'] = $completedStatus->id;
                    }
                }
                if (empty($data['completed_time'])) {
                    $data['completed_time'] = now();
                }
            }

            $ticket->update($data);

            if ($attachment) {
                $path = $attachment->store('attachments/tickets', 'public');
                $ticket->attachments()->create([
                    'user_id' => auth()->id(),
                    'filename' => $attachment->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $attachment->getClientMimeType(),
                    'size' => $attachment->getSize(),
                ]);
            }

            if (isset($data['status_id']) && $data['status_id'] != $oldStatusId) {
                $newStatus = TicketStatus::find($data['status_id']);
                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'action' => 'Status Updated',
                    'description' => 'Status changed to '.($newStatus?->name ?? 'Unknown').'.',
                ]);
            }

            return $ticket;
        });
    }
}
