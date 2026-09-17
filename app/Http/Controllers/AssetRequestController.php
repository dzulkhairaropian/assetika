<?php

namespace App\Http\Controllers;

use App\Models\AssetRequest;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AssetRequestController extends Controller
{
    // Admin list all requests
    public function index(Request $request)
    {
        $query = AssetRequest::with(['user', 'requestable', 'processedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%$search%"))
                    ->orWhere('notes', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requestSummary = [
            'total' => (clone $query)->count(),
            'today' => (clone $query)->whereDate('created_at', Carbon::today())->count(),
            'hardware' => (clone $query)->where('requestable_type', Category::class)->count(),
            'consumable' => (clone $query)->where('requestable_type', Consumable::class)->count(),
            'license' => (clone $query)->where('requestable_type', License::class)->count(),
        ];

        if ($tab = $request->input('tab')) {
            match ($tab) {
                'today' => $query->whereDate('created_at', Carbon::today()),
                'hardware' => $query->where('requestable_type', Category::class),
                'consumable' => $query->where('requestable_type', Consumable::class),
                'license' => $query->where('requestable_type', License::class),
                default => null,
            };
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $requests = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.requests.index', compact('requests', 'requestSummary'));
    }

    // User catalog to request items
    public function catalog()
    {
        $categories = Category::whereHas('masterCategory', fn ($q) => $q->where('name', 'IT Asset'))->orderBy('name')->get();
        $consumables = Consumable::with('product')->get();
        $licenses = License::orderBy('name')->get();
        $myRequests = AssetRequest::where('user_id', auth()->id())->with('requestable')->latest()->take(10)->get();

        return view('pages.requests.catalog', compact('categories', 'consumables', 'licenses', 'myRequests'));
    }

    // User store request
    public function store(Request $request)
    {
        $request->validate([
            'requestable_type' => 'required|in:category,consumable,license',
            'requestable_id' => 'required|integer',
            'qty' => 'nullable|integer|min:1|max:50',
            'notes' => 'nullable|string',
        ]);

        $type = match ($request->requestable_type) {
            'category' => Category::class,
            'consumable' => Consumable::class,
            'license' => License::class,
        };

        AssetRequest::create([
            'user_id' => auth()->id(),
            'requestable_type' => $type,
            'requestable_id' => $request->requestable_id,
            'qty' => (int) $request->input('qty', 1),
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Request submitted successfully.');
    }

    // Admin approve/reject request
    public function process(Request $request, AssetRequest $assetRequest)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string',
        ]);

        $status = $request->action === 'approve' ? 'approved' : 'rejected';

        $assetRequest->update([
            'status' => $status,
            'admin_notes' => $request->admin_notes,
            'processed_by_user_id' => auth()->id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', "Request {$status} successfully.");
    }
}
