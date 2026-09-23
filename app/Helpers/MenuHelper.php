<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMenuGroups()
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        // Super Admin & Group Admin: full menu
        $isSuperAdmin = $user->hasRole('super_admin');
        $isGroupAdmin = $user->hasRole('group_admin');
        $isItAdmin = $user->hasRole('it_admin');
        $isGaAdmin = $user->hasRole('ga_admin');

        // Regular User Menu
        if (! $isSuperAdmin && ! $isGroupAdmin && ! $isItAdmin && ! $isGaAdmin) {
            return [
                [
                    'title' => 'Personal Workspace',
                    'items' => [
                        [
                            'name' => 'My Assets',
                            'icon' => 'user-assets',
                            'path' => '/my-assets',
                        ],
                        [
                            'name' => 'Ticketing System',
                            'icon' => 'ticket',
                            'path' => '/tickets',
                        ],
                        [
                            'name' => 'Asset Requests',
                            'icon' => 'ticket',
                            'subItems' => [
                                ['name' => 'My Requests', 'path' => '/asset-requests'],
                                ['name' => 'Request Catalog', 'path' => '/requestable-assets'],
                            ],
                        ],
                    ],
                ],
            ];
        }

        // Build Dynamic Menu Groups per Role
        $groups = [];

        // 1. Overview
        $dashboards = [];
        if ($isSuperAdmin || $isGroupAdmin || $isItAdmin) {
            $dashboards[] = ['name' => 'IT Dashboard', 'path' => '/'];
            $dashboards[] = ['name' => 'Ticket Dashboard', 'path' => '/ticket-dashboard'];
            $dashboards[] = ['name' => 'Financial Dashboard', 'path' => '/finance-dashboard'];
        }
        if ($isSuperAdmin || $isGroupAdmin || $isGaAdmin) {
            $dashboards[] = ['name' => 'General Affair Dashboard', 'path' => '/ga-dashboard'];
        }
        if (! empty($dashboards)) {
            $groups[] = [
                'title' => 'Overview',
                'items' => [
                    [
                        'name' => 'Dashboards',
                        'icon' => 'dashboard',
                        'subItems' => $dashboards,
                    ],
                ],
            ];
        }

        // 2. Assets & Inventory
        $assetItems = [];

        if ($isSuperAdmin || $isGroupAdmin || $isItAdmin) {
            $assetItems[] = [
                'name' => 'IT Assets',
                'icon' => 'assets',
                'subItems' => [
                    ['name' => 'Inventory', 'path' => '/assets'],
                    ['name' => 'Transactions', 'path' => '/asset-transactions'],
                    ['name' => 'Transfers', 'path' => '/asset-transfers'],
                ],
            ];
        }

        $assetItems[] = [
            'name' => 'My Assets',
            'icon' => 'user-assets',
            'path' => '/my-assets',
        ];

        if ($isSuperAdmin || $isGroupAdmin || $isGaAdmin) {
            $assetItems[] = [
                'name' => 'GA Assets',
                'icon' => 'ga-assets',
                'subItems' => [
                    ['name' => 'GA Inventory', 'path' => '/ga-assets'],
                    ['name' => 'GA Categories', 'path' => '/ga-categories'],
                    ['name' => 'GA Subcategories', 'path' => '/ga-subcategories'],
                ],
            ];
        }

        if ($isSuperAdmin || $isGroupAdmin || $isItAdmin) {
            $assetItems[] = [
                'name' => 'Consumables',
                'icon' => 'consumables',
                'subItems' => [
                    ['name' => 'Inventory', 'path' => '/consumables'],
                    ['name' => 'Transactions', 'path' => '/consumable-transactions'],
                ],
            ];
            $assetItems[] = ['name' => 'Software Licenses', 'icon' => 'license', 'path' => '/licenses'];
            $assetItems[] = ['name' => 'Components', 'icon' => 'components', 'path' => '/components'];
            $assetItems[] = ['name' => 'Asset Kits', 'icon' => 'kit', 'path' => '/kits'];
            $assetItems[] = ['name' => 'Depreciations', 'icon' => 'catalog', 'path' => '/depreciations'];
            $assetItems[] = ['name' => 'Stock Audit', 'icon' => 'support', 'path' => '/stock-counts'];
        }

        $groups[] = [
            'title' => 'Assets & Inventory',
            'items' => $assetItems,
        ];

        // 3. Service Desk
        $serviceDeskItems = [];
        if ($isSuperAdmin || $isGroupAdmin || $isItAdmin) {
            $serviceDeskItems[] = ['name' => 'IT Projects', 'icon' => 'dashboard', 'path' => '/projects'];
        }
        $serviceDeskItems[] = ['name' => 'Ticketing System', 'icon' => 'ticket', 'path' => '/tickets'];

        $requestSubs = [['name' => 'My Requests', 'path' => '/asset-requests']];
        if ($isSuperAdmin || $isGroupAdmin || $isItAdmin) {
            $requestSubs[] = ['name' => 'All Requests', 'path' => '/asset-requests'];
        }
        $requestSubs[] = ['name' => 'Request Catalog', 'path' => '/requestable-assets'];

        $serviceDeskItems[] = [
            'name' => 'Asset Requests',
            'icon' => 'ticket',
            'subItems' => $requestSubs,
        ];
        $serviceDeskItems[] = ['name' => 'Knowledge Base', 'icon' => 'catalog', 'path' => '/kb'];

        $groups[] = [
            'title' => 'Service Desk',
            'items' => $serviceDeskItems,
        ];

        // 4. Master Data
        $masterItems = [];
        $orgSubs = [];
        if ($isSuperAdmin) {
            $orgSubs[] = ['name' => 'Holdings / Groups', 'path' => '/groups'];
        }
        if ($isSuperAdmin || $isGroupAdmin || $isGaAdmin) {
            $orgSubs[] = ['name' => 'Companies', 'path' => '/companies'];
            $orgSubs[] = ['name' => 'Departments', 'path' => '/departments'];
            $orgSubs[] = ['name' => 'Positions', 'path' => '/positions'];
            $orgSubs[] = ['name' => 'Locations', 'path' => '/locations'];
        }

        if (! empty($orgSubs)) {
            $masterItems[] = [
                'name' => 'Organization',
                'icon' => 'org',
                'subItems' => $orgSubs,
            ];
        }

        if ($isSuperAdmin || $isGroupAdmin || $isItAdmin) {
            $masterItems[] = [
                'name' => 'Product Catalog',
                'icon' => 'catalog',
                'subItems' => [
                    ['name' => 'Products', 'path' => '/products'],
                    ['name' => 'Master Categories', 'path' => '/master-categories'],
                    ['name' => 'Categories', 'path' => '/categories'],
                    ['name' => 'Brands', 'path' => '/brands'],
                    ['name' => 'Operating Systems', 'path' => '/operating-systems'],
                ],
            ];
            $masterItems[] = [
                'name' => 'Configurations',
                'icon' => 'support',
                'subItems' => [
                    ['name' => 'Asset Statuses', 'path' => '/statuses'],
                    ['name' => 'Vendors', 'path' => '/vendors'],
                    ['name' => 'Maintenance Types', 'path' => '/maintenance-types'],
                    ['name' => 'Ticket Statuses', 'path' => '/admin/ticket-statuses'],
                    ['name' => 'Ticket Types', 'path' => '/admin/ticket-types'],
                    ['name' => 'Ticket Categories', 'path' => '/admin/ticket-categories'],
                    ['name' => 'Priorities', 'path' => '/admin/priorities'],
                    ['name' => 'SLA Statuses', 'path' => '/admin/sla-statuses'],
                    ['name' => 'SLA Policies', 'path' => '/sla-policies'],
                    ['name' => 'Sources', 'path' => '/admin/sources'],
                    ['name' => 'Ticket Labels', 'path' => '/admin/labels'],
                ],
            ];
        }

        if (! empty($masterItems)) {
            $groups[] = [
                'title' => 'Master Data',
                'items' => $masterItems,
            ];
        }

        // 5. System Administration (Super Admin / Group Admin Only)
        if ($isSuperAdmin || $isGroupAdmin) {
            $adminItems = [
                [
                    'name' => 'Users & Roles',
                    'icon' => 'access',
                    'subItems' => [
                        ['name' => 'Users', 'path' => '/users'],
                        ['name' => 'Roles', 'path' => '/roles'],
                    ],
                ],
                ['name' => 'Import Data', 'icon' => 'upload', 'path' => '/import'],
                ['name' => 'Backups', 'icon' => 'backup', 'path' => '/backups'],
                ['name' => 'Renewals Expiry', 'icon' => 'support', 'path' => '/renewals'],
                ['name' => 'System Settings', 'icon' => 'support', 'path' => '/settings'],
            ];

            $groups[] = [
                'title' => 'System Administration',
                'items' => $adminItems,
            ];
        }

        return $groups;
    }

    public static function getIconSvg($icon)
    {
        return match ($icon) {
            'dashboard' => '<i data-lucide="layout" class="w-6 h-6"></i>',
            'ga-dashboard' => '<i data-lucide="home" class="w-6 h-6"></i>',
            'ga-assets' => '<i data-lucide="briefcase" class="w-6 h-6"></i>',
            'user-assets' => '<i data-lucide="monitor" class="w-6 h-6"></i>',
            'assets' => '<i data-lucide="hard-drive" class="w-6 h-6"></i>',
            'consumables' => '<i data-lucide="package" class="w-6 h-6"></i>',
            'org' => '<i data-lucide="columns" class="w-6 h-6"></i>',
            'catalog' => '<i data-lucide="book-open" class="w-6 h-6"></i>',
            'support' => '<i data-lucide="settings" class="w-6 h-6"></i>',
            'access' => '<i data-lucide="shield" class="w-6 h-6"></i>',
            'backup' => '<i data-lucide="database" class="w-6 h-6"></i>',
            'ticket' => '<i data-lucide="file-text" class="w-6 h-6"></i>',
            'components' => '<i data-lucide="cpu" class="w-6 h-6"></i>',
            'license' => '<i data-lucide="key" class="w-6 h-6"></i>',
            'kit' => '<i data-lucide="box" class="w-6 h-6"></i>',
            'ticket-config' => '<i data-lucide="sliders" class="w-6 h-6"></i>',
            'report' => '<i data-lucide="chart-bar" class="w-6 h-6"></i>',
            'label' => '<i data-lucide="tag" class="w-6 h-6"></i>',
            'upload' => '<i data-lucide="upload" class="w-6 h-6"></i>',
            default => '<i data-lucide="circle" class="w-6 h-6"></i>',
        };
    }
}
