<?php

namespace App\Console\Commands;

use App\Services\LdapService;
use Illuminate\Console\Command;

class LdapSyncDevicesCommand extends Command
{
    protected $signature = 'ldap:sync-devices';

    protected $description = 'Synchronize computer devices (objectClass=computer) from Active Directory / LDAP server';

    public function handle(LdapService $ldapService): int
    {
        $this->info('Starting Active Directory Computer Objects Device Sync...');

        $result = $ldapService->syncComputerDevices();

        $this->info($result['message']);

        return Command::SUCCESS;
    }
}
