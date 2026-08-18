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
use App\Models\User;
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
        $query = Asset::with([
            'product.category', 'product.brand',
            'status', 'assignedToUser', 'department.company',
            'location', 'vendor', 'depreciation',
            'currentCheckout.checkedOutToUser',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like, $search) {
                $q->where('asset_tag', 'like', $like)
                  ->orWhere('serial_number', 'like', $like)
                  ->orWhere('product_number', 'like', $like)
                  ->orWhere('device_name', 'like', $like)
                  ->orWhere('user_name', 'like', $like)
                  ->orWhere('invoice_number', 'like', $like)
                  ->orWhere('notes', 'like', $like)
                  ->orWhere('specification', 'like', $like)
                  ->orWhereHas('product', function($p) use ($like) {
                      $p->where('name', 'like', $like)
                        ->orWhereHas('brand', function($b) use ($like) {
                            $b->where('name', 'like', $like);
                        })
                        ->orWhereHas('category', function($c) use ($like) {
                            $c->where('name', 'like', $like);
                        });
                  })
                  ->orWhereHas('status', function($s) use ($like) {
                      $s->where('name', 'like', $like);
                  })
                  ->orWhereHas('department', function($d) use ($like) {
                      $d->where('name', 'like', $like)
                        ->orWhereHas('company', function($cmp) use ($like) {
                            $cmp->where('name', 'like', $like);
                        });
                  })
                  ->orWhereHas('location', function($l) use ($like) {
                      $l->where('name', 'like', $like);
                  })
                  ->orWhereHas('vendor', function($v) use ($like) {
                      $v->where('name', 'like', $like);
                  })
                  ->orWhereHas('depreciation', function($d) use ($like) {
                      $d->where('name', 'like', $like);
                  })
                  ->orWhereHas('assignedToUser', function($u) use ($like) {
                      $u->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                  })
                  ->orWhereHas('currentCheckout.checkedOutToUser', function($u) use ($like) {
                      $u->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                  });
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

        if ($request->filled('depreciation_id')) {
            $query->where('depreciation_id', $request->depreciation_id);
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
                    case 'fully_depreciated':
                        $q->whereHas('depreciation', function($sub) use ($now) {
                            $sub->whereRaw('TIMESTAMPDIFF(MONTH, assets.release_date, ?) >= depreciations.months', [$now->format('Y-m-d')]);
                        });
                        break;
                    case 'no_depreciation':
                        $q->whereNull('depreciation_id');
                        break;
                    case 'warranty_expired':
                        $q->whereNotNull('warranty_expiry_date')
                          ->where('warranty_expiry_date', '<', $now->format('Y-m-d'));
                        break;
                    case 'warranty_30d':
                        $q->whereNotNull('warranty_expiry_date')
                          ->whereBetween('warranty_expiry_date', [$now->format('Y-m-d'), $now->copy()->addDays(30)->format('Y-m-d')]);
                        break;
                }
            });
        }

        $now = \Carbon\Carbon::now();
        $now4 = $now->copy()->subYears(4);
        // Matches the pink-row logic in index.blade.php: startDate = release_date ?? purchase_date
        $agedScope = function($q) use ($now4) {
            $q->where(function($sub) use ($now4) {
                $sub->where('release_date', '<=', $now4)
                    ->orWhere(function($s2) use ($now4) {
                        $s2->whereNull('release_date')
                           ->where('purchase_date', '<=', $now4);
                    });
            });
        };

        $inventorySummary = [
            'total' => (clone $query)->count(),
            'ready' => (clone $query)->whereHas('status', function($q) {
                $q->whereIn('name', ['Ready to Deploy']);
            })->count(),
            'deployed' => (clone $query)->whereHas('status', function($q) {
                $q->whereIn('name', ['Deployed', 'Borrowed']);
            })->count(),
            'in_service' => (clone $query)->whereHas('status', function($q) {
                $q->whereIn('name', ['Service', 'Maintenance', 'In Repair']);
            })->count(),
            'broken' => (clone $query)->whereHas('status', function($q) {
                $q->whereIn('name', ['Broken']);
            })->count(),
            'aged' => (clone $query)->where($agedScope)->count(),
        ];

        // Tab quick filter
        switch ($request->input('tab')) {
            case 'ready':
                $query->whereHas('status', function($q) {
                    $q->whereIn('name', ['Ready to Deploy']);
                });
                break;
            case 'deployed':
                $query->whereHas('status', function($q) {
                    $q->whereIn('name', ['Deployed', 'Borrowed']);
                });
                break;
            case 'service':
                $query->whereHas('status', function($q) {
                    $q->whereIn('name', ['Service', 'Maintenance', 'In Repair']);
                });
                break;
            case 'broken':
                $query->whereHas('status', function($q) {
                    $q->whereIn('name', ['Broken']);
                });
                break;
            case 'aged':
                $query->where($agedScope);
                break;
        }

        $assets = $query->latest()->paginate(50)->withQueryString();
        $statuses = Status::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $departments = Department::all();
        $locations = Location::all();
        $products = Product::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $categories = Category::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $depreciations = \App\Models\Depreciation::all();

        return view('pages.assets.index', compact('assets', 'statuses', 'departments', 'locations', 'products', 'categories', 'depreciations', 'inventorySummary'));
    }

    public function create()
    {
        $products = Product::with('category')
            ->whereHas('masterCategory', function($q) {
                $q->where('name', 'IT Asset');
            })->get();
        $statuses = Status::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::all();
        $locations = Location::all();
        $depreciations = \App\Models\Depreciation::all();

        // Pre-load fieldsets for JS-driven dynamic rendering
        $fieldsets = \App\Models\Fieldset::with('customFields')->get()->keyBy('id');
        $categoryFieldsets = \App\Models\Category::with('fieldsets.customFields')->get()
            ->mapWithKeys(fn($c) => [$c->id => $c->fieldsets]);

        return view('pages.assets.create', compact(
            'products', 'statuses', 'vendors', 'operatingSystems', 'departments', 'locations', 'depreciations',
            'fieldsets', 'categoryFieldsets'
        ));
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
                'depreciation_id' => 'nullable|exists:depreciations,id',
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
                'depreciation_id' => 'nullable|exists:depreciations,id',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
        }

        $asset = Asset::create($validated);

        // Sync custom field values
        if ($request->has('custom_fields')) {
            $asset->syncCustomFields($request->custom_fields);
        }

        if ($request->has('create_another')) {
            return redirect()->route('assets.create')->with('success', 'Asset created successfully. Add another one.');
        }

        return redirect()->route('assets.index')->with('success', $isDraft ? 'Asset saved as draft successfully.' : 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'product.category', 'status', 'vendor', 'operatingSystem', 'department.company', 'location', 
            'checkoutHistory.checkedOutToUser', 'checkoutHistory.department', 'maintenances.maintenanceType', 
            'tickets.status', 'tickets.priority', 'tickets.assignedTo'
        ]);
        return view('pages.assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $products = Product::with('category')
            ->whereHas('masterCategory', function($q) {
                $q->where('name', 'IT Asset');
            })->get();
        $statuses = Status::whereHas('masterCategory', function($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::all();
        $locations = Location::all();
        $depreciations = \App\Models\Depreciation::all();

        $asset->load('customFieldValues');

        $fieldsets = \App\Models\Fieldset::with('customFields')->get()->keyBy('id');
        $categoryFieldsets = \App\Models\Category::with('fieldsets.customFields')->get()
            ->mapWithKeys(fn($c) => [$c->id => $c->fieldsets]);

        return view('pages.assets.edit', compact(
            'asset', 'products', 'statuses', 'vendors', 'operatingSystems', 'departments', 'locations', 'depreciations',
            'fieldsets', 'categoryFieldsets'
        ));
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
                'depreciation_id' => 'nullable|exists:depreciations,id',
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
                'depreciation_id' => 'nullable|exists:depreciations,id',
                'invoice_number' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
        }

        $asset->update($validated);

        // Sync custom field values
        if ($request->has('custom_fields')) {
            $asset->syncCustomFields($request->custom_fields);
        }

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

    public function bulkEdit(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
            'status_id' => 'nullable|exists:statuses,id',
            'department_id' => 'nullable|exists:departments,id',
            'location_id' => 'nullable|exists:locations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
        ]);

        $data = array_filter($request->only(['status_id', 'department_id', 'location_id', 'vendor_id']));

        if (empty($data)) {
            return back()->with('error', 'No changes selected.');
        }

        Asset::whereIn('id', $request->ids)->update($data);

        return redirect()->route('assets.index')->with('success', count($request->ids) . ' assets updated successfully.');
    }

    public function bulkCheckoutForm(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $assets = Asset::whereIn('id', $request->ids)->get();
        $users = User::orderBy('name')->get();
        $departments = Department::all();
        $locations = Location::all();

        return view('pages.assets.bulk-checkout', compact('assets', 'users', 'departments', 'locations'));
    }

    public function bulkCheckout(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id',
            'checked_out_to_user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $assets = Asset::whereIn('id', $request->ids)
            ->whereNull('current_checkout_id')
            ->get();

        $count = 0;
        foreach ($assets as $asset) {
            $checkout = $asset->checkouts()->create([
                'checked_out_to_user_id' => $request->checked_out_to_user_id,
                'checked_out_by_user_id' => auth()->id(),
                'checked_out_at' => now(),
                'checkout_notes' => $request->notes,
            ]);
            $asset->update([
                'assigned_to_user_id' => $request->checked_out_to_user_id,
                'current_checkout_id' => $checkout->id,
            ]);
            $count++;
        }

        return redirect()->route('assets.index')->with('success', "{$count} assets checked out. " . ($assets->count() < count($request->ids) ? 'Some were already checked out.' : ''));
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
