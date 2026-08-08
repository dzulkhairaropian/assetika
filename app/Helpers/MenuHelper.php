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
                        'name' => 'Dashboard',
                        'icon' => 'dashboard',
                        'path' => '/',
                    ],
                    [
                        'name' => 'GA Dashboard',
                        'icon' => 'ga-dashboard',
                        'path' => '/ga-dashboard',
                    ],
                    [
                        'name' => 'My Assets',
                        'icon' => 'user-assets',
                        'path' => '/my-assets',
                    ],
                    [
                        'name' => 'Request Item',
                        'icon' => 'kit',
                        'path' => '/requestable-assets',
                    ],
                ],
            ],
            [
                'title' => 'Support Ticketing',
                'items' => [
                    [
                        'name' => 'Tickets',
                        'icon' => 'ticket',
                        'path' => '/ticket-dashboard',
                    ],
                    [
                        'name' => 'Ticketing Data',
                        'icon' => 'ticket-config',
                        'subItems' => [
                            ['name' => 'Priorities', 'path' => '/admin/priorities'],
                            ['name' => 'Ticket Statuses', 'path' => '/admin/ticket-statuses'],
                            ['name' => 'Ticket Types', 'path' => '/admin/ticket-types'],
                            ['name' => 'SLA Statuses', 'path' => '/admin/sla-statuses'],
                            ['name' => 'Labels', 'path' => '/admin/labels'],
                            ['name' => 'Sources', 'path' => '/admin/sources'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'IT Asset Management',
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
                        'name' => 'Consumables',
                        'icon' => 'consumables',
                        'subItems' => [
                            ['name' => 'Inventory', 'path' => '/consumables'],
                            ['name' => 'Transactions', 'path' => '/consumable-transactions'],
                        ],
                    ],
                    [
                        'name' => 'Depreciations',
                        'icon' => 'catalog',
                        'path' => '/depreciations',
                    ],
                    [
                        'name' => 'Audit / Opname',
                        'icon' => 'support',
                        'path' => '/stock-counts',
                    ],
                    [
                        'name' => 'Asset Requests',
                        'icon' => 'ticket',
                        'path' => '/asset-requests',
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
                        'name' => 'Catalog',
                        'icon' => 'catalog',
                        'subItems' => [
                            ['name' => 'Products', 'path' => '/products'],
                            ['name' => 'Master Categories', 'path' => '/master-categories'],
                            ['name' => 'Categories', 'path' => '/categories'],
                            ['name' => 'Custom Fields', 'path' => '/custom-fields'],
                            ['name' => 'Fieldsets', 'path' => '/fieldsets'],
                            ['name' => 'EULA Templates', 'path' => '/eulas'],
                            ['name' => 'Brands', 'path' => '/brands'],
                            ['name' => 'Operating Systems', 'path' => '/operating-systems'],
                        ],
                    ],
                    [
                        'name' => 'Support Data',
                        'icon' => 'support',
                        'subItems' => [
                            ['name' => 'Statuses', 'path' => '/statuses'],
                            ['name' => 'Vendors', 'path' => '/vendors'],
                            ['name' => 'Maintenance Types', 'path' => '/maintenance-types'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Reports & Tools',
                'items' => [
                    [
                        'name' => 'Reports',
                        'icon' => 'report',
                        'path' => '/reports',
                    ],
                    [
                        'name' => 'Label Generator',
                        'icon' => 'label',
                        'path' => '/labels',
                    ],
                    [
                        'name' => 'Import Data',
                        'icon' => 'upload',
                        'path' => '/import',
                    ],
                ],
            ],
            [
                'title' => 'System',
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
                        'name' => 'Backups',
                        'icon' => 'backup',
                        'path' => '/backups',
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
