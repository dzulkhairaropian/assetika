<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetRequest;
use App\Models\Category;
use App\Models\Consumable;
use Illuminate\Http\Request;

class AssetRequestController extends Controller
{
    // Admin list all requests
    public function index(Request $request)
    {
        $query = AssetRequest::with(['user', 'requestable', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(50)->withQueryString();

        return view('pages.requests.index', compact('requests'));
    }

    // User catalog to request items
    public function catalog()
    {
        $categories = Category::whereHas('masterCategory', fn($q) => $q->where('name', 'IT Asset'))->orderBy('name')->get();
        $consumables = Consumable::with('product')->get();
        $myRequests = AssetRequest::where('user_id', auth()->id())->with('requestable')->latest()->take(10)->get();

        return view('pages.requests.catalog', compact('categories', 'consumables', 'myRequests'));
    }

    // User store request
    public function store(Request $request)
    {
        $request->validate([
            'requestable_type' => 'required|in:category,consumable',
            'requestable_id' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $type = $request->requestable_type === 'category' ? Category::class : Consumable::class;

        AssetRequest::create([
            'user_id' => auth()->id(),
            'requestable_type' => $type,
            'requestable_id' => $request->requestable_id,
            'qty' => 1,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Asset request submitted successfully.');
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
