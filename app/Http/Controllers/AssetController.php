<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Product;
use App\Models\Status;
use App\Models\Vendor;
use App\Models\OperatingSystem;
use App\Models\Department;
use App\Models\Location;
use App\Models\Category;
use Illuminate\Http\Request;

use App\Exports\AssetsExport;

class AssetController extends Controller
{
    public function export(Request $request)
    {
        return (new AssetsExport($request->all()))->download('assets-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function index(Request $request)
    {
        $query = Asset::with(['product.category', 'status', 'assignedToUser', 'department']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%$search%")
                  ->orWhere('serial_number', 'like', "%$search%")
                  ->orWhere('device_name', 'like', "%$search%");
            });
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('aging')) {
            $aging = $request->aging;
            $now = \Carbon\Carbon::now();
            $query->where(function($q) use ($aging, $now) {
                switch($aging) {
                    case '0-1':
                        $q->where(function($sub) use ($now) {
                            $sub->where('purchase_date', '>', $now->copy()->subYear())
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->where('release_date', '>', $now->copy()->subYear());
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->where('created_at', '>', $now->copy()->subYear());
                                });
                        });
                        break;
                    case '1-2':
                        $q->where(function($sub) use ($now) {
                            $sub->whereBetween('purchase_date', [$now->copy()->subYears(2), $now->copy()->subYear()])
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereBetween('release_date', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->whereBetween('created_at', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                });
                        });
                        break;
                    case '2-3':
                        $q->where(function($sub) use ($now) {
                            $sub->whereBetween('purchase_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)])
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereBetween('release_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->whereBetween('created_at', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                });
                        });
                        break;
                    case '3-4':
                        $q->where(function($sub) use ($now) {
                            $sub->whereBetween('purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)])
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereBetween('release_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->whereBetween('created_at', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                });
                        });
                        break;
                    case '4+':
                        $q->where(function($sub) use ($now) {
                            $sub->where('purchase_date', '<=', $now->copy()->subYears(4))
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->where('release_date', '<=', $now->copy()->subYears(4));
                                })
                                ->orWhere(function($sub2) use ($now) {
                                    $sub2->whereNull('purchase_date')
                                         ->whereNull('release_date')
                                         ->where('created_at', '<=', $now->copy()->subYears(4));
                                });
                        });
                        break;
                }
            });
        }

        $inventorySummary = [
            'total' => (clone $query)->count(),
            'ready' => (clone $query)->whereHas('status', function($q) {
                $q->whereIn('name', ['Ready', 'Ready to Deploy', 'Available']);
            })->count(),
            'deployed' => (clone $query)->whereHas('status', function($q) {
                $q->whereIn('name', ['Deployed', 'In Use', 'Borrowed']);
            })->count(),
            'broken' => (clone $query)->whereHas('status', function($q) {
                $q->where('name', 'Broken');
            })->count(),
            'in_service' => (clone $query)->whereHas('status', function($q) {
                $q->where('name', 'Service');
            })->count(),
        ];

        $assets = $query->latest()->paginate(50)->withQueryString();
        $statuses = Status::all();
        $departments = Department::all();
        $locations = Location::all();
        $products = Product::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $categories = Category::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Asset');
        })->get();

        return view('pages.assets.index', compact('assets', 'statuses', 'departments', 'locations', 'products', 'categories', 'inventorySummary'));
    }

    public function create()
    {
        $products = Product::with('category')
            ->whereHas('masterCategory', function($q) {
                $q->where('name', 'IT Asset');
            })->get();
        $statuses = Status::all();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::all();
        $locations = Location::all();
        
        return view('pages.assets.create', compact('products', 'statuses', 'vendors', 'operatingSystems', 'departments', 'locations'));
    }
    public function store(Request $request)
    {
        $isDraft = $request->input('is_draft') == '1';

        if ($isDraft) {
            $validated = $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'status_id' => 'nullable|exists:statuses,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'operating_system_id' => 'nullable|exists:operating_systems,id',
                'department_id' => 'nullable|exists:departments,id',
                'location_id' => 'nullable|exists:locations,id',
                'serial_number' => 'nullable|string|unique:assets,serial_number',
                'product_number' => 'nullable|string',
                'user_name' => 'nullable|string',
                'license_windows' => 'nullable|string',
                'license_office' => 'nullable|string',
                'mac_address_wireless' => 'nullable|string',
                'mac_address_ethernet' => 'nullable|string',
                'specification' => 'nullable|string',
                'accessories' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'release_date' => 'nullable|date',
                'warranty_expiry_date' => 'nullable|date',
                'purchase_cost' => 'nullable|numeric',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
                'asset_tag' => 'nullable|string|unique:assets,asset_tag',
                'device_name' => 'nullable|string',
            ]);

            if (empty($validated['status_id']) || $isDraft) {
                $draftStatus = Status::firstOrCreate(
                    ['name' => 'Draft'],
                    ['color' => 'gray', 'icon' => 'file-text']
                );
                $validated['status_id'] = $draftStatus->id;
            }
        } else {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'status_id' => 'required|exists:statuses,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'operating_system_id' => 'nullable|exists:operating_systems,id',
                'department_id' => 'required|exists:departments,id',
                'location_id' => 'nullable|exists:locations,id',
                'serial_number' => 'required|string|unique:assets,serial_number',
                'product_number' => 'nullable|string',
                'user_name' => 'nullable|string',
                'license_windows' => 'nullable|string',
                'license_office' => 'nullable|string',
                'mac_address_wireless' => 'nullable|string',
                'mac_address_ethernet' => 'nullable|string',
                'specification' => 'nullable|string',
                'accessories' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'release_date' => 'required|date',
                'warranty_expiry_date' => 'nullable|date',
                'purchase_cost' => 'nullable|numeric',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
        }

        Asset::create($validated);

        if ($request->has('create_another')) {
            return redirect()->route('assets.create')->with('success', 'Asset created successfully. Add another one.');
        }

        return redirect()->route('assets.index')->with('success', $isDraft ? 'Asset saved as draft successfully.' : 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $asset->load(['product.category', 'status', 'vendor', 'operatingSystem', 'department.company', 'location', 'checkoutHistory.checkedOutToUser']);
        return view('pages.assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $products = Product::with('category')
            ->whereHas('masterCategory', function($q) {
                $q->where('name', 'IT Asset');
            })->get();
        $statuses = Status::all();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::all();
        $locations = Location::all();

        return view('pages.assets.edit', compact('asset', 'products', 'statuses', 'vendors', 'operatingSystems', 'departments', 'locations'));
    }

    public function update(Request $request, Asset $asset)
    {
        $isDraft = $request->input('is_draft') == '1';

        if ($isDraft) {
            $validated = $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'status_id' => 'nullable|exists:statuses,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'operating_system_id' => 'nullable|exists:operating_systems,id',
                'department_id' => 'nullable|exists:departments,id',
                'location_id' => 'nullable|exists:locations,id',
                'serial_number' => 'nullable|string|unique:assets,serial_number,' . $asset->id,
                'product_number' => 'nullable|string',
                'user_name' => 'nullable|string',
                'license_windows' => 'nullable|string',
                'license_office' => 'nullable|string',
                'mac_address_wireless' => 'nullable|string',
                'mac_address_ethernet' => 'nullable|string',
                'specification' => 'nullable|string',
                'accessories' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'release_date' => 'nullable|date',
                'warranty_expiry_date' => 'nullable|date',
                'purchase_cost' => 'nullable|numeric',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
                'asset_tag' => 'nullable|string|unique:assets,asset_tag,' . $asset->id,
                'device_name' => 'nullable|string',
            ]);

            $draftStatus = Status::firstOrCreate(
                ['name' => 'Draft'],
                ['color' => 'gray', 'icon' => 'file-text']
            );
            $validated['status_id'] = $draftStatus->id;
        } else {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'status_id' => 'required|exists:statuses,id',
                'vendor_id' => 'nullable|exists:vendors,id',
                'operating_system_id' => 'nullable|exists:operating_systems,id',
                'department_id' => 'required|exists:departments,id',
                'location_id' => 'nullable|exists:locations,id',
                'serial_number' => 'required|string|unique:assets,serial_number,' . $asset->id,
                'product_number' => 'nullable|string',
                'user_name' => 'nullable|string',
                'license_windows' => 'nullable|string',
                'license_office' => 'nullable|string',
                'mac_address_wireless' => 'nullable|string',
                'mac_address_ethernet' => 'nullable|string',
                'specification' => 'nullable|string',
                'accessories' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'release_date' => 'required|date',
                'warranty_expiry_date' => 'nullable|date',
                'purchase_cost' => 'nullable|numeric',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
        }

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', $isDraft ? 'Asset saved as draft successfully.' : 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
        ]);

        Asset::whereIn('id', $request->ids)->delete();

        return redirect()->route('assets.index')->with('success', 'Selected assets deleted successfully.');
    }

    public function publicScan(Asset $asset)
    {
        $asset->load([
            'product.brand',
            'product.category',
            'operatingSystem',
            'status',
            'location',
            'department.company',
            'maintenances' => function($q) {
                $q->orderBy('start_date', 'desc');
            }
        ]);

        $title = ($asset->asset_tag ?? 'Asset') . ' | ' . config('app.name');

        return view('pages.assets.public-scan', compact('asset', 'title'));
    }

    public function myAssets(Request $request)
    {
        $userId = auth()->id();
        $assets = Asset::with(['product.category', 'product.brand', 'status', 'location'])
            ->where('assigned_to_user_id', $userId)
            ->orWhereHas('currentCheckout', function($q) use ($userId) {
                $q->where('checked_out_to_user_id', $userId)
                  ->whereNull('checked_in_at');
            })
            ->latest()
            ->get();

        return view('pages.assets.my-assets', compact('assets'));
    }
}
