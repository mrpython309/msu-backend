<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SeedItems extends Command
{
    protected $signature = 'seed:items';

    protected $description = 'Seed asset master and item master permissions';

    public function handle()
    {
        foreach ([
            'item.view', 'item.create', 'item.edit', 'item.delete',
            'asset-master.view', 'asset-master.create', 'asset-master.edit', 'asset-master.delete',
            'item-master:view', 'item-master:create', 'item-master:edit', 'item-master:delete',
        ] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        
        $permissions = [
            'item.view', 'item.create', 'item.edit', 'item.delete',
            'asset-master.view', 'asset-master.create', 'asset-master.edit', 'asset-master.delete',
            'item-master:view', 'item-master:create', 'item-master:edit', 'item-master:delete',
        ];

        foreach (['Super Admin', 'superadmin', 'Admin', 'admin'] as $roleName) {
            if ($role = Role::where('name', $roleName)->where('guard_name', 'web')->first()) {
                $role->givePermissionTo($permissions);
            }
        }

        $this->info('Asset Master and Item Master permissions seeded successfully.');
    }
}
