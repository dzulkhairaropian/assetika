<?php

namespace App\Http\Controllers;

use App\Enums\ConsumableRequestStatus;
use App\Models\ConsumableRequest;
use App\Models\ConsumableTransaction;
use Illuminate\Http\Request;

class ConsumableRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsumableRequest::with(['requester', 'department', 'location', 'processedBy', 'items.consumable.product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'like', "%$search%")
                  ->orWhereHas('requester', fn($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhere('notes', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest('requested_at')->paginate(50)->withQueryString();

        $summary = [
            'total'     => (clone $query)->count(),
            'pending'   => (clone $query)->where('status', ConsumableRequestStatus::PENDING->value)->count(),
            'approved'  => (clone $query)->where('status', ConsumableRequestStatus::APPROVED->value)->count(),
            'completed' => (clone $query)->where('status', ConsumableRequestStatus::COMPLETED->value)->count(),
            'rejected'  => (clone $query)->where('status', ConsumableRequestStatus::REJECTED->value)->count(),
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
            'action' => 'required|in:approve,reject,complete',
            'admin_notes' => 'nullable|string',
        ]);

        $action = $request->action;

        if ($action === 'approve') {
            $consumable_request->update([
                'status' => ConsumableRequestStatus::APPROVED,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);

            // Process inventory reduction for each item
            foreach ($consumable_request->items as $item) {
                $consumable = $item->consumable;
                if ($consumable && $consumable->quantity >= $item->quantity_requested) {
                    $consumable->decrement('quantity', $item->quantity_requested);
                    $item->update(['quantity_fulfilled' => $item->quantity_requested]);

                    // Record transaction log
                    ConsumableTransaction::create([
                        'consumable_id' => $consumable->id,
                        'checked_out_to_user_id' => $consumable_request->requester_id,
                        'checked_out_by_user_id' => auth()->id(),
                        'type' => 'checkout',
                        'quantity' => $item->quantity_requested,
                        'transaction_date' => now(),
                        'checkout_notes' => 'Fulfilled from Consumable Request #' . $consumable_request->request_number,
                    ]);
                }
            }

            return back()->with('success', 'Consumable request #' . $consumable_request->request_number . ' approved and stock deducted.');
        } elseif ($action === 'reject') {
            $consumable_request->update([
                'status' => ConsumableRequestStatus::REJECTED,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);

            return back()->with('success', 'Consumable request #' . $consumable_request->request_number . ' rejected.');
        } elseif ($action === 'complete') {
            $consumable_request->update([
                'status' => ConsumableRequestStatus::COMPLETED,
            ]);

            return back()->with('success', 'Consumable request #' . $consumable_request->request_number . ' marked as completed.');
        } elseif ($action === 'reopen') {
            $consumable_request->update([
                'status' => ConsumableRequestStatus::PENDING,
                'processed_by' => null,
                'processed_at' => null,
            ]);

            return back()->with('success', 'Consumable request #' . $consumable_request->request_number . ' reopened to pending.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function destroy(ConsumableRequest $consumable_request)
    {
        $consumable_request->delete();
        return redirect()->route('consumable-requests.index')->with('success', 'Request deleted successfully.');
    }
}
