<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMenuGroups()
    {
        return [
            [
                'title' => 'Dashboard',
                'items' => [
                    [
                        'name' => 'IT Dashboard',
                        'icon' => 'dashboard',
                        'path' => '/',
                    ],
                    [
                        'name' => 'Financial Dashboard',
                        'icon' => 'catalog',
                        'path' => '/finance-dashboard',
                    ],
                    [
                        'name' => 'Ticket Dashboard',
                        'icon' => 'ticket',
                        'path' => '/ticket-dashboard',
                    ],
                    [
                        'name' => 'General Affair Dashboard',
                        'icon' => 'ga-dashboard',
                        'path' => '/ga-dashboard',
                    ],
                ],
            ],
            [
                'title' => 'Help Desk & Requests',
                'items' => [
                    [
                        'name' => 'All Tickets',
                        'icon' => 'ticket',
                        'path' => '/tickets',
                    ],
                    [
                        'name' => 'Item Requests',
                        'icon' => 'ticket',
                        'path' => '/asset-requests',
                    ],
                    [
                        'name' => 'Request Catalog',
                        'icon' => 'kit',
                        'path' => '/requestable-assets',
                    ],
                    [
                        'name' => 'Knowledge Base',
                        'icon' => 'catalog',
                        'path' => '/kb',
                    ],
                ],
            ],
            [
                'title' => 'IT Assets & Inventory',
                'items' => [
                    [
                        'name' => 'IT Assets',
                        'icon' => 'assets',
                        'subItems' => [
                            ['name' => 'Inventory', 'path' => '/assets'],
                            ['name' => 'Transactions', 'path' => '/asset-transactions'],
                        ],
                    ],
                    [
                        'name' => 'My Assets',
                        'icon' => 'user-assets',
                        'path' => '/my-assets',
                    ],
                    [
                        'name' => 'Consumables',
                        'icon' => 'consumables',
                        'subItems' => [
                            ['name' => 'Inventory', 'path' => '/consumables'],
                            ['name' => 'Transactions', 'path' => '/consumable-transactions'],
                        ],
                    ],
                    [
                        'name' => 'Components',
                        'icon' => 'components',
                        'path' => '/components',
                    ],
                    [
                        'name' => 'Licenses',
                        'icon' => 'license',
                        'path' => '/licenses',
                    ],
                    [
                        'name' => 'Kits',
                        'icon' => 'kit',
                        'path' => '/kits',
                    ],
                    [
                        'name' => 'Depreciations',
                        'icon' => 'catalog',
                        'path' => '/depreciations',
                    ],
                    [
                        'name' => 'Stock Audit',
                        'icon' => 'support',
                        'path' => '/stock-counts',
                    ],
                ],
            ],
            [
                'title' => 'General Affairs (GA)',
                'items' => [
                    [
                        'name' => 'GA Assets',
                        'icon' => 'ga-assets',
                        'path' => '/ga-assets',
                    ],
                    [
                        'name' => 'GA Categories',
                        'icon' => 'catalog',
                        'path' => '/ga-categories',
                    ],
                ],
            ],
            [
                'title' => 'Master Data',
                'items' => [
                    [
                        'name' => 'Organization',
                        'icon' => 'org',
                        'subItems' => [
                            ['name' => 'Companies', 'path' => '/companies'],
                            ['name' => 'Departments', 'path' => '/departments'],
                            ['name' => 'Positions', 'path' => '/positions'],
                            ['name' => 'Locations', 'path' => '/locations'],
                        ],
                    ],
                    [
                        'name' => 'Product Catalog',
                        'icon' => 'catalog',
                        'subItems' => [
                            ['name' => 'Products', 'path' => '/products'],
                            ['name' => 'Master Categories', 'path' => '/master-categories'],
                            ['name' => 'Categories', 'path' => '/categories'],
                            ['name' => 'Brands', 'path' => '/brands'],
                            ['name' => 'Operating Systems', 'path' => '/operating-systems'],
                        ],
                    ],
                    [
                        'name' => 'Support Settings',
                        'icon' => 'support',
                        'subItems' => [
                            ['name' => 'Priorities', 'path' => '/admin/priorities'],
                            ['name' => 'Ticket Statuses', 'path' => '/admin/ticket-statuses'],
                            ['name' => 'Ticket Types', 'path' => '/admin/ticket-types'],
                            ['name' => 'SLA Statuses', 'path' => '/admin/sla-statuses'],
                            ['name' => 'SLA Policies', 'path' => '/sla-policies'],
                            ['name' => 'Sources', 'path' => '/admin/sources'],
                            ['name' => 'Ticket Labels', 'path' => '/admin/labels'],
                            ['name' => 'Asset Statuses', 'path' => '/statuses'],
                            ['name' => 'Vendors', 'path' => '/vendors'],
                            ['name' => 'Maintenance Types', 'path' => '/maintenance-types'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'System & Reports',
                'items' => [
                    [
                        'name' => 'Access Control',
                        'icon' => 'access',
                        'subItems' => [
                            ['name' => 'Users', 'path' => '/users'],
                            ['name' => 'Roles', 'path' => '/roles'],
                        ],
                    ],
                    [
                        'name' => 'Import Data',
                        'icon' => 'upload',
                        'path' => '/import',
                    ],
                    [
                        'name' => 'Backups',
                        'icon' => 'backup',
                        'path' => '/backups',
                    ],
                    [
                        'name' => 'Expiry & Renewals',
                        'icon' => 'support',
                        'path' => '/renewals',
                    ],
                ],
            ],
        ];
    }

    public static function getIconSvg($icon)
    {
        return match($icon) {
            'dashboard' => '<i data-feather="layout" class="w-6 h-6"></i>',
            'ga-dashboard' => '<i data-feather="home" class="w-6 h-6"></i>',
            'ga-assets' => '<i data-feather="briefcase" class="w-6 h-6"></i>',
            'user-assets' => '<i data-feather="monitor" class="w-6 h-6"></i>',
            'assets' => '<i data-feather="hard-drive" class="w-6 h-6"></i>',
            'consumables' => '<i data-feather="package" class="w-6 h-6"></i>',
            'org' => '<i data-feather="columns" class="w-6 h-6"></i>',
            'catalog' => '<i data-feather="book-open" class="w-6 h-6"></i>',
            'support' => '<i data-feather="help-circle" class="w-6 h-6"></i>',
            'access' => '<i data-feather="shield" class="w-6 h-6"></i>',
            'backup' => '<i data-feather="database" class="w-6 h-6"></i>',
            'ticket' => '<i data-feather="file-text" class="w-6 h-6"></i>',
            'components' => '<i data-feather="cpu" class="w-6 h-6"></i>',
            'license' => '<i data-feather="key" class="w-6 h-6"></i>',
            'kit' => '<i data-feather="box" class="w-6 h-6"></i>',
            'ticket-config' => '<i data-feather="settings" class="w-6 h-6"></i>',
            'report' => '<i data-feather="bar-chart-2" class="w-6 h-6"></i>',
            'label' => '<i data-feather="tag" class="w-6 h-6"></i>',
            'upload' => '<i data-feather="upload" class="w-6 h-6"></i>',
            default => '<i data-feather="circle" class="w-6 h-6"></i>',
        };
    }
}
