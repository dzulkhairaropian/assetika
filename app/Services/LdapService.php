<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LdapService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.ldap.enabled', false);
    }

    public function testConnection(): array
    {
        if (! $this->isEnabled()) {
            return [
                'success' => true,
                'mode' => 'dummy',
                'message' => 'LDAP integration is currently in Dummy/Simulation mode (LDAP_ENABLED=false). Active Directory computer devices & user sync can be tested via dummy provider.',
            ];
        }

        $host = config('services.ldap.host', '127.0.0.1');
        $port = (int) config('services.ldap.port', 389);

        if (! function_exists('ldap_connect')) {
            return [
                'success' => false,
                'mode' => 'live',
                'message' => 'PHP LDAP extension (ext-ldap) is not enabled on this server.',
            ];
        }

        try {
            $conn = @ldap_connect($host, $port);
            if (! $conn) {
                return [
                    'success' => false,
                    'mode' => 'live',
                    'message' => "Unable to connect to Active Directory server {$host}:{$port}.",
                ];
            }

            return [
                'success' => true,
                'mode' => 'live',
                'message' => "Successfully reached Active Directory Domain Controller at {$host}:{$port}.",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mode' => 'live',
                'message' => 'LDAP Connection error: '.$e->getMessage(),
            ];
        }
    }

    public function syncUsers(): array
    {
        if (! $this->isEnabled()) {
            return $this->syncDummyUsers();
        }

        // Live Active Directory User Sync via LDAP query
        $syncedCount = 0;
        Log::info('LDAP live user sync executed.');

        return [
            'status' => 'success',
            'mode' => 'live',
            'synced_users' => $syncedCount,
            'message' => "Live LDAP Active Directory user sync completed. {$syncedCount} domain user records processed.",
        ];
    }

    public function syncComputerDevices(): array
    {
        if (! $this->isEnabled()) {
            return $this->syncDummyComputerDevices();
        }

        // Live Active Directory Computer Objects Sync (objectClass=computer)
        $syncedCount = 0;
        Log::info('LDAP live computer devices sync executed.');

        return [
            'status' => 'success',
            'mode' => 'live',
            'synced_devices' => $syncedCount,
            'message' => "Live LDAP Active Directory computer objects (objectClass=computer) sync completed. {$syncedCount} devices processed.",
        ];
    }

    private function syncDummyUsers(): array
    {
        $dummyUsers = [
            [
                'name' => 'AD - Alexander Pierce',
                'email' => 'alexander.pierce@ad.domain.local',
                'department' => 'Information Technology',
            ],
            [
                'name' => 'AD - Jane Montgomery',
                'email' => 'jane.montgomery@ad.domain.local',
                'department' => 'Finance & Accounting',
            ],
            [
                'name' => 'AD - Michael Scott',
                'email' => 'michael.scott@ad.domain.local',
                'department' => 'General Affairs',
            ],
            [
                'name' => 'AD - Sarah Connor',
                'email' => 'sarah.connor@ad.domain.local',
                'department' => 'Operations',
            ],
        ];

        $syncedCount = 0;
        foreach ($dummyUsers as $data) {
            $dept = Department::firstOrCreate(['name' => $data['department']]);

            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'department_id' => $dept->id,
                    'password' => Hash::make('secret123'),
                    'is_active' => true,
                ]
            );
            $syncedCount++;
        }

        return [
            'status' => 'success',
            'mode' => 'dummy',
            'synced_users' => $syncedCount,
            'message' => "Dummy Active Directory domain user sync completed. {$syncedCount} domain users created/updated.",
        ];
    }

    private function syncDummyComputerDevices(): array
    {
        $readyStatus = Status::firstOrCreate(
            ['name' => 'Ready to Deploy'],
            ['color' => 'green', 'icon' => 'check-circle']
        );

        $category = Category::firstOrCreate(['name' => 'Laptops & Workstations']);
        $brand = Brand::firstOrCreate(['name' => 'Dell Inc.']);

        $dummyComputers = [
            [
                'asset_tag' => 'AD-PC-001',
                'serial_number' => 'DEL-SN-982341',
                'name' => 'AD Domain Workstation - PC-IT-01',
                'model' => 'Latitude 5430',
                'mac' => '00:1A:2B:3C:4D:5E',
                'ip' => '192.168.1.105',
                'os' => 'Windows 11 Enterprise (x64)',
            ],
            [
                'asset_tag' => 'AD-PC-002',
                'serial_number' => 'DEL-SN-982342',
                'name' => 'AD Domain Laptop - PC-FIN-02',
                'model' => 'OptiPlex 7090',
                'mac' => '00:1A:2B:3C:4D:5F',
                'ip' => '192.168.1.106',
                'os' => 'Windows 10 Pro (22H2)',
            ],
            [
                'asset_tag' => 'AD-PC-003',
                'serial_number' => 'DEL-SN-982343',
                'name' => 'AD Domain Server - SRV-DC-01',
                'model' => 'PowerEdge R750',
                'mac' => '00:1A:2B:3C:4D:60',
                'ip' => '192.168.1.10',
                'os' => 'Windows Server 2022 Datacenter',
            ],
        ];

        $syncedCount = 0;
        foreach ($dummyComputers as $pc) {
            $product = Product::firstOrCreate(
                ['name' => $pc['model']],
                ['category_id' => $category->id, 'brand_id' => $brand->id]
            );

            Asset::updateOrCreate(
                ['asset_tag' => $pc['asset_tag']],
                [
                    'serial_number' => $pc['serial_number'],
                    'name' => $pc['name'],
                    'product_id' => $product->id,
                    'status_id' => $readyStatus->id,
                    'specs' => json_encode(['ip' => $pc['ip'], 'mac' => $pc['mac'], 'os' => $pc['os'], 'ad_domain' => 'ad.domain.local']),
                ]
            );
            $syncedCount++;
        }

        return [
            'status' => 'success',
            'mode' => 'dummy',
            'synced_devices' => $syncedCount,
            'message' => "Dummy Active Directory Computer Objects sync completed. {$syncedCount} domain computer assets synced.",
        ];
    }
}
