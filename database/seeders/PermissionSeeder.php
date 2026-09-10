<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'assets' => ['view', 'create', 'edit', 'delete', 'checkout', 'checkin', 'maintenance'],
            'components' => ['view', 'create', 'edit', 'delete', 'checkout', 'checkin'],
            'licenses' => ['view', 'create', 'edit', 'delete', 'checkout', 'checkin'],
            'kits' => ['view', 'create', 'edit', 'delete'],
            'depreciations' => ['view', 'create', 'edit', 'delete'],
            'eulas' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view'],
            'labels' => ['view'],
            'import' => ['view'],
            'maintenance-types' => ['view', 'create', 'edit', 'delete'],
            'stock-counts' => ['view', 'create', 'edit', 'delete'],
            'asset-requests' => ['view', 'create', 'edit', 'delete'],
            'asset-transfers' => ['view', 'create', 'edit', 'delete', 'approve'],
            'consumables' => ['view', 'create', 'edit', 'delete', 'restock', 'checkout'],
            'groups' => ['view', 'create', 'edit', 'delete'],
            'companies' => ['view', 'create', 'edit', 'delete'],
            'departments' => ['view', 'create', 'edit', 'delete'],
            'locations' => ['view', 'create', 'edit', 'delete'],
            'brands' => ['view', 'create', 'edit', 'delete'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'custom-fields' => ['view', 'create', 'edit', 'delete'],
            'fieldsets' => ['view', 'create', 'edit', 'delete'],
            'vendors' => ['view', 'create', 'edit', 'delete'],
            'products' => ['view', 'create', 'edit', 'delete'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::updateOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
