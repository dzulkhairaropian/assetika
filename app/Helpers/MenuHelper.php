<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMenuGroups()
    {
        return [
            [
                'title' => 'Menu',
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
                ],
            ],
            [
                'title' => 'Assets Management',
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
                        'name' => 'GA Assets',
                        'icon' => 'ga-assets',
                        'path' => '/ga-assets',
                    ],
                    [
                        'name' => 'GA Categories',
                        'icon' => 'catalog',
                        'path' => '/ga-categories',
                    ],
                    [
                        'name' => 'Consumables',
                        'icon' => 'consumables',
                        'subItems' => [
                            ['name' => 'Inventory', 'path' => '/consumables'],
                            ['name' => 'Transactions', 'path' => '/consumable-transactions'],
                        ],
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
                        ],
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
            'dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
            'ga-dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
            'ga-assets' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>',
            'user-assets' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 16h10"></path><path d="M10 12h7"></path><path d="M10 8h7"></path><circle cx="6.5" cy="10" r="1.5"></circle></svg>',
            'assets' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>',
            'consumables' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
            'org' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>',
            'catalog' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>',
            'support' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
            'access' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>',
            'backup' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path></svg>',
            default => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle></svg>'
        };
    }
}
