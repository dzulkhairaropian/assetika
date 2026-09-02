<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get initial data for relationships
        $company = Company::where('code', 'DB')->first();
        $department = Department::where('code', 'IT')->first();
        $position = Position::where('code', 'MGR')->first();
        $location = Location::first();

        // Create Roles (Spatie)
        $superAdminRole = Role::updateOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $groupAdminRole = Role::updateOrCreate(['name' => 'group_admin', 'guard_name' => 'web']);
        $itAdminRole = Role::updateOrCreate(['name' => 'it_admin', 'guard_name' => 'web']);
        $gaAdminRole = Role::updateOrCreate(['name' => 'ga_admin', 'guard_name' => 'web']);
        $userRole = Role::updateOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Give all permissions to super_admin
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // Give Group Admin permissions across company resources
        $groupAdminPermissions = Permission::whereIn('name', [
            'groups.view', 'groups.edit',
            'companies.view', 'companies.create', 'companies.edit',
            'departments.view', 'departments.create', 'departments.edit',
            'users.view', 'users.create', 'users.edit',
            'assets.view', 'assets.create', 'assets.edit', 'assets.checkout', 'assets.checkin', 'assets.maintenance',
            'reports.view',
        ])->get();
        $groupAdminRole->syncPermissions($groupAdminPermissions);

        // Give IT Admin permissions (Asset, Brand, Category, Consumable, MasterCategory, OperatingSystem, Product, Status, Vendor, ConsumableRequest)
        $itPermissions = Permission::where(function($q) {
            $q->where('name', 'like', '%:Asset')
              ->orWhere('name', 'like', '%:Brand')
              ->orWhere('name', 'like', '%:Category')
              ->orWhere('name', 'like', '%:Consumable')
              ->orWhere('name', 'like', '%:MasterCategory')
              ->orWhere('name', 'like', '%:OperatingSystem')
              ->orWhere('name', 'like', '%:Product')
              ->orWhere('name', 'like', '%:Status')
              ->orWhere('name', 'like', '%:Vendor')
              ->orWhere('name', 'like', '%:ConsumableRequest');
        })->orWhereIn('name', [
            'View:MyAssets',
            'View:AssetStatsWidget',
            'View:ConsumableStatsWidget',
            'View:AssetStatusChart',
            'View:LowStockAlertsWidget',
            'View:WarrantyExpiringWidget',
            'View:RecentAssetActivitiesWidget',
            'View:RecentConsumableTransactionsWidget',
            'View:AssetUserWidget',
            'View:RequestConsumable',
            'View:Dashboard'
        ])->get();
        $itAdminRole->syncPermissions($itPermissions);

        // Give GA Admin permissions (GaAsset, GaAssetCategory, GaAssetSubcategory, Company, Department, Location, Position)
        $gaPermissions = Permission::where(function($q) {
            $q->where('name', 'like', '%:GaAsset')
              ->orWhere('name', 'like', '%:GaAssetCategory')
              ->orWhere('name', 'like', '%:GaAssetSubcategory')
              ->orWhere('name', 'like', '%:Company')
              ->orWhere('name', 'like', '%:Department')
              ->orWhere('name', 'like', '%:Location')
              ->orWhere('name', 'like', '%:Position');
        })->orWhereIn('name', [
            'View:GaDashboard',
            'View:GaAssetsByCategoryChart',
            'View:GaProcurementSpendChart',
            'View:GaStatsOverviewWidget',
            'View:MyAssets'
        ])->get();
        $gaAdminRole->syncPermissions($gaPermissions);

        // Give basic permissions to user
        $userPermissions = Permission::whereIn('name', [
            'View:MyAssets',
            'View:RequestConsumable',
            'Create:ConsumableRequest',
            'ViewAny:ConsumableRequest',
            'View:ConsumableRequest'
        ])->get();
        $userRole->syncPermissions($userPermissions);

        // Create Super Admin User (admin@assetika.com)
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@assetika.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '08123456789',
                'company_id' => $company?->id,
                'department_id' => $department?->id,
                'position_id' => $position?->id,
                'location_id' => $location?->id,
            ]
        );
        $superAdmin->syncRoles([$superAdminRole]);

        // Create IT Admin User
        $itAdmin = User::updateOrCreate(
            ['email' => 'itadmin@assetika.com'],
            [
                'name' => 'IT Admin User',
                'password' => Hash::make('password'),
                'phone' => '08122334455',
                'company_id' => $company?->id,
                'department_id' => $department?->id,
                'position_id' => Position::where('code', 'STF')->first()?->id,
                'location_id' => $location?->id,
            ]
        );
        $itAdmin->syncRoles([$itAdminRole]);

        // Create GA Admin User
        $gaAdmin = User::updateOrCreate(
            ['email' => 'gaadmin@assetika.com'],
            [
                'name' => 'GA Admin User',
                'password' => Hash::make('password'),
                'phone' => '08556677889',
                'company_id' => $company?->id,
                'department_id' => Department::where('code', 'HR')->first()?->id ?? $department?->id,
                'position_id' => Position::where('code', 'STF')->first()?->id,
                'location_id' => $location?->id,
            ]
        );
        $gaAdmin->syncRoles([$gaAdminRole]);

        // Create Regular User
        $regularUser = User::updateOrCreate(
            ['email' => 'user@assetika.com'],
            [
                'name' => 'Regular User Account',
                'password' => Hash::make('password'),
                'phone' => '08987654321',
                'company_id' => $company?->id,
                'department_id' => $department?->id,
                'position_id' => Position::where('code', 'STF')->first()?->id,
                'location_id' => $location?->id,
            ]
        );
        $regularUser->syncRoles([$userRole]);

        // Seed notifications
        $superAdmin->notify(new \App\Notifications\SystemNotification(
            'Welcome to Assetika!',
            'Get started by checking out assets, restocking consumables, and reviewing the master categories.',
            route('dashboard'),
            'sparkles',
            'System'
        ));
    }
}
