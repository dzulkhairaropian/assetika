<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssetikaViewController extends Controller
{
    private array $tableMeta = [
        'companies' => ['label' => 'Company', 'plural' => 'Companies', 'cols' => ['name', 'code', 'email'], 'userCount' => 'company_id'],
        'departments' => ['label' => 'Department', 'plural' => 'Departments', 'cols' => ['name', 'code'], 'userCount' => 'department_id'],
        'positions' => ['label' => 'Position', 'plural' => 'Positions', 'cols' => ['name', 'code'], 'userCount' => 'position_id'],
        'locations' => ['label' => 'Location', 'plural' => 'Locations', 'cols' => ['name']],
        'categories' => ['label' => 'Category', 'plural' => 'Categories', 'cols' => ['name', 'code']],
        'statuses' => ['label' => 'Status', 'plural' => 'Statuses', 'cols' => ['name']],
        'brands' => ['label' => 'Brand', 'plural' => 'Brands', 'cols' => ['name', 'code']],
        'operating_systems' => ['label' => 'Operating System', 'plural' => 'Operating Systems', 'cols' => ['name']],
        'vendors' => ['label' => 'Vendor', 'plural' => 'Vendors', 'cols' => ['name', 'email']],
        'products' => ['label' => 'Product', 'plural' => 'Products', 'cols' => ['name']],
        'users' => ['label' => 'User', 'plural' => 'Users', 'cols' => ['name', 'email', 'role', 'phone']],
        'assets' => ['label' => 'Asset', 'plural' => 'Assets', 'cols' => ['asset_tag', 'device_name', 'serial_number', 'user_name']],
    ];

    private function meta(string $table): array
    {
        if (! array_key_exists($table, $this->tableMeta)) {
            abort(404);
        }

        return $this->tableMeta[$table];
    }

    public function index(Request $request, string $table): View
    {
        $meta = $this->meta($table);
        $hasUserCount = isset($meta['userCount']);

        $query = DB::table($table);

        if ($table === 'assets') {
            $query->select('assets.*', 'products.name as product_name', 'statuses.name as status_name', 'assigned.name as holder_name')
                ->leftJoin('products', 'assets.product_id', '=', 'products.id')
                ->leftJoin('statuses', 'assets.status_id', '=', 'statuses.id')
                ->leftJoin('users as assigned', 'assets.assigned_to_user_id', '=', 'assigned.id');
        } elseif ($hasUserCount) {
            $query->select($table.'.*');
            $userCountQuery = DB::table('users')
                ->whereColumn('users.'.$meta['userCount'], $table.'.id')
                ->whereNull('users.deleted_at')
                ->selectRaw('count(*)');
            $query->selectSub($userCountQuery, 'user_count');
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($query) use ($search, $table): void {
                $query->where('name', 'like', "%{$search}%");
                if (DB::getSchemaBuilder()->hasColumn($table, 'code')) {
                    $query->orWhere('code', 'like', "%{$search}%");
                }
                if (DB::getSchemaBuilder()->hasColumn($table, 'email')) {
                    $query->orWhere('email', 'like', "%{$search}%");
                }
                if ($table === 'assets') {
                    $query->orWhere('assets.asset_tag', 'like', "%{$search}%")
                        ->orWhere('assets.serial_number', 'like', "%{$search}%")
                        ->orWhere('assets.device_name', 'like', "%{$search}%");
                }
            });
        }

        return view('admin.sync.index', [
            'meta' => $meta,
            'table' => $table,
            'items' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
            'hasUserCount' => $hasUserCount,
        ]);
    }

    public function show(string $table, int $id): View
    {
        $meta = $this->meta($table);

        $item = DB::table($table)->where('id', $id)->first();

        if (! $item) {
            abort(404);
        }

        return view('admin.sync.show', [
            'meta' => $meta,
            'table' => $table,
            'item' => $item,
        ]);
    }
}
