<?php

namespace App\Console\Commands;

use App\Services\LdapService;
use Illuminate\Console\Command;

class LdapSyncUsersCommand extends Command
{
    protected $signature = 'ldap:sync-users';

    protected $description = 'Synchronize domain users from Active Directory / LDAP server';

    public function handle(LdapService $ldapService): int
    {
        $this->info('Starting Active Directory / LDAP Users Sync...');

        $result = $ldapService->syncUsers();

        $this->info($result['message']);

        return Command::SUCCESS;
    }
}
