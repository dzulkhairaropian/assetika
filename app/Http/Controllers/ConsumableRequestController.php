<?php

namespace App\Http\Controllers;

use App\Enums\ConsumableRequestStatus;
use App\Models\Consumable;
use App\Models\ConsumableRequest;
use App\Models\ConsumableTransaction;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsumableRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsumableRequest::with(['requester', 'department', 'location', 'processedBy', 'items.consumable.product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%$search%")
                    ->orWhereHas('requester', fn ($u) => $u->where('name', 'like', "%$search%"))
                    ->orWhere('notes', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest('requested_at')->paginate(50)->withQueryString();

        $summary = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', ConsumableRequestStatus::PENDING->value)->count(),
            'approved' => (clone $query)->where('status', ConsumableRequestStatus::APPROVED->value)->count(),
            'completed' => (clone $query)->where('status', ConsumableRequestStatus::COMPLETED->value)->count(),
            'rejected' => (clone $query)->where('status', ConsumableRequestStatus::REJECTED->value)->count(),
        ];

        return view('pages.consumable-requests.index', compact('requests', 'summary'));
    }

    public function show(ConsumableRequest $consumable_request)
    {
        $consumable_request->load(['requester', 'department', 'location', 'processedBy', 'items.consumable.product.brand']);

        return view('pages.consumable-requests.show', ['request' => $consumable_request]);
    }

    public function process(Request $request, ConsumableRequest $consumable_request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,complete,reopen',
            'admin_notes' => 'nullable|string',
        ]);

        $action = $request->action;

        if ($action === 'approve') {
            // Enforce the state machine: only pending requests can be approved
            if ($consumable_request->status !== ConsumableRequestStatus::PENDING) {
                return back()->with('error', 'Only pending requests can be approved.');
            }

            $fulfilled = [];
            $skipped = [];

            DB::transaction(function () use ($consumable_request, &$fulfilled, &$skipped) {
                foreach ($consumable_request->items as $item) {
                    // Lock the stock row so concurrent checkouts cannot interleave
                    $consumable = Consumable::whereKey($item->consumable_id)->lockForUpdate()->first();

                    if (! $consumable || $consumable->quantity < $item->quantity_requested) {
                        $skipped[] = $consumable?->product?->name ?? 'Item #'.$item->consumable_id;

                        continue;
                    }

                    $before = $consumable->quantity;
                    $after = $before - $item->quantity_requested;

                    $consumable->update(['quantity' => $after]);
                    $item->update(['quantity_fulfilled' => $item->quantity_requested]);

                    // Record transaction log with full stock snapshot for audit
                    ConsumableTransaction::create([
                        'consumable_id' => $consumable->id,
                        'user_id' => auth()->id(),
                        'checked_out_to_user_id' => $consumable_request->requester_id,
                        'type' => 'checkout',
                        'quantity' => $item->quantity_requested,
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'unit_cost' => $consumable->purchase_cost ?? 0,
                        'total_cost' => $item->quantity_requested * ($consumable->purchase_cost ?? 0),
                        'transaction_date' => now(),
                        'notes' => 'Fulfilled from Consumable Request #'.$consumable_request->request_number,
                    ]);

                    $fulfilled[] = $item->quantity_requested.'x '.($consumable->product?->name ?? 'Item');
                }

                $consumable_request->update([
                    'status' => ConsumableRequestStatus::APPROVED,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                    'admin_notes' => $request->admin_notes,
                ]);
            });

            if ($consumable_request->requester) {
                $message = ! empty($fulfilled)
                    ? 'Your consumable request #'.$consumable_request->request_number.' was approved. Issued: '.implode(', ', $fulfilled).'.'
                    : 'Your consumable request #'.$consumable_request->request_number.' was approved, but no items could be issued due to insufficient stock.';

                if (! empty($skipped)) {
                    $message .= ' Skipped (out of stock): '.implode(', ', $skipped).'.';
                }

                $consumable_request->requester->notify(new SystemNotification(
                    'Consumable Request Approved',
                    $message,
                    route('consumable-requests.index'),
                    'shopping-bag',
                    'Consumable'
                ));
            }

            if (! empty($skipped)) {
                return back()->with('error', 'Request #'.$consumable_request->request_number.' approved, but some items could not be issued (insufficient stock): '.implode(', ', $skipped).'.');
            }

            return back()->with('success', 'Consumable request #'.$consumable_request->request_number.' approved and stock deducted.');
        } elseif ($action === 'reject') {
            if (! in_array($consumable_request->status, [ConsumableRequestStatus::PENDING, ConsumableRequestStatus::APPROVED])) {
                return back()->with('error', 'Only pending or approved requests can be rejected.');
            }

            $consumable_request->update([
                'status' => ConsumableRequestStatus::REJECTED,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);

            if ($consumable_request->requester) {
                $consumable_request->requester->notify(new SystemNotification(
                    'Consumable Request Rejected',
                    'Your consumable request #'.$consumable_request->request_number.' was rejected.'.(! empty($request->admin_notes) ? ' Note: '.$request->admin_notes : ''),
                    route('consumable-requests.index'),
                    'x-circle',
                    'Consumable'
                ));
            }

            return back()->with('success', 'Consumable request #'.$consumable_request->request_number.' rejected.');
        } elseif ($action === 'complete') {
            // Enforce the state machine: only approved requests can be completed
            if ($consumable_request->status !== ConsumableRequestStatus::APPROVED) {
                return back()->with('error', 'Only approved requests can be marked as completed.');
            }

            $consumable_request->update([
                'status' => ConsumableRequestStatus::COMPLETED,
            ]);

            if ($consumable_request->requester) {
                $consumable_request->requester->notify(new SystemNotification(
                    'Consumable Request Completed',
                    'Your consumable request #'.$consumable_request->request_number.' has been completed.',
                    route('consumable-requests.index'),
                    'check-circle',
                    'Consumable'
                ));
            }

            return back()->with('success', 'Consumable request #'.$consumable_request->request_number.' marked as completed.');
        } elseif ($action === 'reopen') {
            $consumable_request->update([
                'status' => ConsumableRequestStatus::PENDING,
                'processed_by' => null,
                'processed_at' => null,
            ]);

            return back()->with('success', 'Consumable request #'.$consumable_request->request_number.' reopened to pending.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function destroy(ConsumableRequest $consumable_request)
    {
        $consumable_request->delete();

        return redirect()->route('consumable-requests.index')->with('success', 'Request deleted successfully.');
    }
}
