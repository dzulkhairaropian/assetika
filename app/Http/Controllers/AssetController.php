<?php

namespace App\Http\Controllers;

use App\Exports\AssetsExport;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Department;
use App\Models\Depreciation;
use App\Models\Location;
use App\Models\OperatingSystem;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use App\Models\Vendor;
use App\Services\AssetService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    protected AssetService $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function export(Request $request)
    {
        return (new AssetsExport($request->all()))->download('assets-'.now()->format('Y-m-d').'.xlsx');
    }

    public function index(Request $request)
    {
        $query = Asset::with([
            'product.category', 'product.brand',
            'status', 'assignedToUser', 'department.company',
            'location', 'vendor', 'depreciation',
            'currentCheckout.checkedOutToUser',
            'lastCheckout.checkedOutToUser',
        ])->filter($request->all());

        if ($request->filled('depreciation_id')) {
            $query->where('depreciation_id', $request->depreciation_id);
        }

        if ($request->filled('year')) {
            $year = (int) $request->year;
            $query->where(function ($q) use ($year) {
                $q->whereYear('release_date', $year)
                    ->orWhere(function ($sub) use ($year) {
                        $sub->whereNull('release_date')
                            ->whereYear('purchase_date', $year);
                    })
                    ->orWhere(function ($sub) use ($year) {
                        $sub->whereNull('release_date')
                            ->whereNull('purchase_date')
                            ->whereYear('created_at', $year);
                    });
            });
        }

        if ($request->filled('aging')) {
            $aging = $request->aging;
            $now = Carbon::now();
            $query->where(function ($q) use ($aging, $now) {
                switch ($aging) {
                    case '0-1':
                    case 'new':
                        $q->where(function ($sub) use ($now) {
                            $sub->where('release_date', '>', $now->copy()->subYear())
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->where('purchase_date', '>', $now->copy()->subYear());
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->where('created_at', '>', $now->copy()->subYear());
                                });
                        });
                        break;
                    case '1-2':
                        $q->where(function ($sub) use ($now) {
                            $sub->whereBetween('release_date', [$now->copy()->subYears(2), $now->copy()->subYear()])
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereBetween('purchase_date', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereBetween('created_at', [$now->copy()->subYears(2), $now->copy()->subYear()]);
                                });
                        });
                        break;
                    case '2-3':
                        $q->where(function ($sub) use ($now) {
                            $sub->whereBetween('release_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)])
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereBetween('purchase_date', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereBetween('created_at', [$now->copy()->subYears(3), $now->copy()->subYears(2)]);
                                });
                        });
                        break;
                    case '2years':
                        $q->where(function ($sub) use ($now) {
                            $sub->whereBetween('release_date', [$now->copy()->subYears(4), $now->copy()->subYears(2)])
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereBetween('purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(2)]);
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereBetween('created_at', [$now->copy()->subYears(4), $now->copy()->subYears(2)]);
                                });
                        });
                        break;
                    case '3-4':
                        $q->where(function ($sub) use ($now) {
                            $sub->whereBetween('release_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)])
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereBetween('purchase_date', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->whereBetween('created_at', [$now->copy()->subYears(4), $now->copy()->subYears(3)]);
                                });
                        });
                        break;
                    case '4+':
                    case '4years':
                        $q->where(function ($sub) use ($now) {
                            $sub->where('release_date', '<=', $now->copy()->subYears(4))
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->where('purchase_date', '<=', $now->copy()->subYears(4));
                                })
                                ->orWhere(function ($sub2) use ($now) {
                                    $sub2->whereNull('release_date')
                                        ->whereNull('purchase_date')
                                        ->where('created_at', '<=', $now->copy()->subYears(4));
                                });
                        });
                        break;
                    case 'fully_depreciated':
                        $q->whereHas('depreciation', function ($sub) use ($now) {
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

        $now = Carbon::now();
        $now4 = $now->copy()->subYears(4);
        // Matches the pink-row logic in index.blade.php: startDate = release_date ?? purchase_date
        $agedScope = function ($q) use ($now4) {
            $q->where(function ($sub) use ($now4) {
                $sub->where('release_date', '<=', $now4)
                    ->orWhere(function ($s2) use ($now4) {
                        $s2->whereNull('release_date')
                            ->where('purchase_date', '<=', $now4);
                    });
            });
        };

        $inventorySummary = [
            'total' => (clone $query)->count(),
            'today' => (clone $query)->whereDate('assets.created_at', Carbon::today())->count(),
            'draft' => (clone $query)->whereHas('status', function ($q) {
                $q->whereIn('name', ['Draft']);
            })->count(),
            'ready' => (clone $query)->whereHas('status', function ($q) {
                $q->whereIn('name', ['Ready to Deploy']);
            })->count(),
            'deployed' => (clone $query)->whereHas('status', function ($q) {
                $q->whereIn('name', ['Deployed', 'Borrowed']);
            })->count(),
            'in_service' => (clone $query)->whereHas('status', function ($q) {
                $q->whereIn('name', ['Service', 'Maintenance', 'In Repair']);
            })->count(),
            'broken' => (clone $query)->whereHas('status', function ($q) {
                $q->whereIn('name', ['Broken']);
            })->count(),
            'retired' => (clone $query)->whereHas('status', function ($q) {
                $q->whereIn('name', ['Retired', 'Disposal']);
            })->count(),
            'sold' => (clone $query)->whereHas('status', function ($q) {
                $q->whereIn('name', ['Sold', 'For Sale']);
            })->count(),
            'aged' => (clone $query)->where($agedScope)->count(),
        ];

        // Tab quick filter
        switch ($request->input('tab')) {
            case 'today':
                $query->whereDate('assets.created_at', Carbon::today());
                break;
            case 'draft':
                $query->whereHas('status', function ($q) {
                    $q->whereIn('name', ['Draft']);
                });
                break;
            case 'ready':
                $query->whereHas('status', function ($q) {
                    $q->whereIn('name', ['Ready to Deploy']);
                });
                break;
            case 'deployed':
                $query->whereHas('status', function ($q) {
                    $q->whereIn('name', ['Deployed', 'Borrowed']);
                });
                break;
            case 'service':
                $query->whereHas('status', function ($q) {
                    $q->whereIn('name', ['Service', 'Maintenance', 'In Repair']);
                });
                break;
            case 'broken':
                $query->whereHas('status', function ($q) {
                    $q->whereIn('name', ['Broken']);
                });
                break;
            case 'retired':
                $query->whereHas('status', function ($q) {
                    $q->whereIn('name', ['Retired', 'Disposal']);
                });
                break;
            case 'sold':
                $query->whereHas('status', function ($q) {
                    $q->whereIn('name', ['Sold', 'For Sale']);
                });
                break;
            case 'aged':
                $query->where($agedScope);
                break;
        }

        $assets = $query->latest()->paginate(50)->withQueryString();
        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $departments = Department::all();
        $locations = Location::all();
        $products = Product::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $categories = Category::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $depreciations = Depreciation::all();

        // Get distinct years from release_date, purchase_date, and created_at
        $yearsFromRelease = Asset::whereNotNull('release_date')->selectRaw('YEAR(release_date) as yr')->distinct()->pluck('yr');
        $yearsFromPurchase = Asset::whereNotNull('purchase_date')->selectRaw('YEAR(purchase_date) as yr')->distinct()->pluck('yr');
        $availableYears = $yearsFromRelease->merge($yearsFromPurchase)
            ->push(now()->year)
            ->unique()
            ->filter()
            ->sortDesc()
            ->values();

        return view('pages.assets.index', compact('assets', 'statuses', 'departments', 'locations', 'products', 'categories', 'depreciations', 'inventorySummary', 'availableYears'));
    }

    public function create()
    {
        $products = Product::with(['category', 'brand'])
            ->whereHas('masterCategory', function ($q) {
                $q->where('name', 'IT Asset');
            })->get();
        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::with('company')->get();
        $locations = Location::all();
        $depreciations = Depreciation::all();

        $productOpts = $products->map(fn ($p) => ['id' => $p->id, 'text' => ($p->brand->name ?? '').' - '.$p->model.' ('.($p->category->name ?? '').')'])->toArray();
        $statusOpts = $statuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->toArray();
        $vendorOpts = $vendors->map(fn ($v) => ['id' => $v->id, 'text' => $v->name])->toArray();
        $deptOpts = $departments->map(fn ($d) => ['id' => $d->id, 'text' => $d->name.($d->company ? ' - '.$d->company->name : '')])->toArray();
        $locationOpts = $locations->map(fn ($l) => ['id' => $l->id, 'text' => $l->name])->toArray();
        $osOpts = $operatingSystems->map(fn ($o) => ['id' => $o->id, 'text' => $o->name])->toArray();

        return view('pages.assets.create', compact(
            'products', 'statuses', 'vendors', 'operatingSystems', 'departments', 'locations', 'depreciations',
            'productOpts', 'statusOpts', 'vendorOpts', 'deptOpts', 'locationOpts', 'osOpts'
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
                'sale_price' => 'nullable|numeric',
                'buyer_name' => 'nullable|string',
                'sale_date' => 'nullable|date',
                'is_components_stripped' => 'nullable|boolean',
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
                'sale_price' => 'nullable|numeric',
                'buyer_name' => 'nullable|string',
                'sale_date' => 'nullable|date',
                'is_components_stripped' => 'nullable|boolean',
            ]);
        }

        $asset = Asset::create($validated);

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
            'tickets.status', 'tickets.priority', 'tickets.assignedTo',
            'activityLogs.user',
        ]);

        return view('pages.assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $products = Product::with(['category', 'brand'])
            ->whereHas('masterCategory', function ($q) {
                $q->where('name', 'IT Asset');
            })->get();
        $statuses = Status::whereHas('masterCategory', function ($q) {
            $q->where('name', 'IT Asset');
        })->get();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::with('company')->get();
        $locations = Location::all();
        $depreciations = Depreciation::all();

        $productOpts = $products->map(fn ($p) => ['id' => $p->id, 'text' => ($p->brand->name ?? '').' - '.$p->model.' ('.($p->category->name ?? '').')'])->toArray();
        $statusOpts = $statuses->map(fn ($s) => ['id' => $s->id, 'text' => $s->name])->toArray();
        $vendorOpts = $vendors->map(fn ($v) => ['id' => $v->id, 'text' => $v->name])->toArray();
        $deptOpts = $departments->map(fn ($d) => ['id' => $d->id, 'text' => $d->name.($d->company ? ' - '.$d->company->name : '')])->toArray();
        $locationOpts = $locations->map(fn ($l) => ['id' => $l->id, 'text' => $l->name])->toArray();
        $osOpts = $operatingSystems->map(fn ($o) => ['id' => $o->id, 'text' => $o->name])->toArray();

        return view('pages.assets.edit', compact(
            'asset', 'products', 'statuses', 'vendors', 'operatingSystems', 'departments', 'locations', 'depreciations',
            'productOpts', 'statusOpts', 'vendorOpts', 'deptOpts', 'locationOpts', 'osOpts'
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
                'serial_number' => 'nullable|string|unique:assets,serial_number,'.$asset->id,
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
                'asset_tag' => 'nullable|string|unique:assets,asset_tag,'.$asset->id,
                'device_name' => 'nullable|string',
                'depreciation_id' => 'nullable|exists:depreciations,id',
                'sale_price' => 'nullable|numeric',
                'buyer_name' => 'nullable|string',
                'sale_date' => 'nullable|date',
                'is_components_stripped' => 'nullable|boolean',
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
                'serial_number' => 'required|string|unique:assets,serial_number,'.$asset->id,
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
                'sale_price' => 'nullable|numeric',
                'buyer_name' => 'nullable|string',
                'sale_date' => 'nullable|date',
                'is_components_stripped' => 'nullable|boolean',
            ]);
        }

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', $isDraft ? 'Asset saved as draft successfully.' : 'Asset updated successfully.');
    }

    public function printLabel(Asset $asset)
    {
        $asset->load(['product.category', 'product.brand', 'department.company', 'location']);
        $assets = collect([$asset]);

        return view('pages.labels.print', compact('assets'));
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

        return redirect()->route('assets.index')->with('success', count($request->ids).' assets updated successfully.');
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

        return redirect()->route('assets.index')->with('success', "{$count} assets checked out. ".($assets->count() < count($request->ids) ? 'Some were already checked out.' : ''));
    }

    public function publicScan($assetId)
    {
        $asset = Asset::withoutGlobalScopes()->findOrFail($assetId);
        $asset->load([
            'product.brand',
            'product.category',
            'operatingSystem',
            'status',
            'location',
            'department.company',
            'maintenances' => function ($q) {
                $q->orderBy('start_date', 'desc');
            },
        ]);

        $title = ($asset->asset_tag ?? 'Asset').' | '.config('app.name');

        return view('pages.assets.public-scan', compact('asset', 'title'));
    }

    public function myAssets(Request $request)
    {
        $userId = auth()->id();
        $assets = Asset::with(['product.category', 'product.brand', 'status', 'location'])
            ->where('assigned_to_user_id', $userId)
            ->orWhereHas('currentCheckout', function ($q) use ($userId) {
                $q->where('checked_out_to_user_id', $userId)
                    ->whereNull('checked_in_at');
            })
            ->latest()
            ->get();

        return view('pages.assets.my-assets', compact('assets'));
    }
}
