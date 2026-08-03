<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SyncRolesPermissions extends Command
{
    protected $signature = 'crm:sync-permissions
                            {--sync-roles : Also reset role permissions from config (overwrites manual changes)}
                            {--fresh : Reset ALL role permissions from config (first time setup only)}';

    protected $description = 'Sync permissions from config/permissions.php';

    public function handle(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $config = config('permissions');

        if (is_null($config) || !isset($config['modules'], $config['roles'])) {
            $this->error('config/permissions.php not found or invalid.');
            return;
        }

        $modules = $config['modules'];
        $roles   = $config['roles'];

        // ── Step 1: Build full permission list from config ──────────
        $config_permissions = [];
        foreach ($modules as $module => $details) {
            foreach ($details['permissions'] as $action) {
                $config_permissions[] = "{$action} {$module}";
            }
        }

        // ── Step 2: Get existing permissions from DB ────────────────
        $db_permissions = Permission::pluck('name')->toArray();

        // ── Step 3: Add new permissions ─────────────────────────────
        $to_add = array_diff($config_permissions, $db_permissions);

        if (!empty($to_add)) {
            $this->info('Adding new permissions:');
            foreach ($to_add as $name) {
                Permission::create(['name' => $name]);
                $this->line("Added → {$name}");
            }
        } else {
            $this->info('No new permissions to add.');
        }

        // ── Step 4: Remove deleted permissions ──────────────────────
        $to_remove = array_diff($db_permissions, $config_permissions);

        if (!empty($to_remove)) {
            $this->info('Removing deleted permissions:');
            foreach ($to_remove as $name) {
                Permission::where('name', $name)->delete();
                $this->line("  🗑  Removed → {$name}");
            }
        } else {
            $this->info('No permissions to remove.');
        }

        $unchanged = array_intersect($config_permissions, $db_permissions);
        $this->info('Unchanged permissions: ' . count($unchanged));

        // ── Step 5: Ensure all roles exist (create if missing) ──────
        $this->info('');
        $this->info('Checking roles exist in DB...');

        foreach ($roles as $role_name => $role_permissions) {
            $role = Role::firstOrCreate(['name' => $role_name]);
            $this->line("Role exists → {$role_name}");
        }

        // ── Step 6: Only sync role permissions if flag is passed ────
        if ($this->option('sync-roles') || $this->option('fresh')) {
            $this->info('');
            $this->warn('--sync-roles flag detected: Resetting role permissions from config...');
            $this->warn('This will OVERWRITE any manual permission changes made via the panel!');

            if (!$this->option('fresh')) {
                // Ask for confirmation unless --fresh
                if (!$this->confirm('Are you sure you want to overwrite manually set permissions?')) {
                    $this->info('Role sync cancelled. Permissions updated only.');
                    return;
                }
            }

            foreach ($roles as $role_name => $role_permissions) {
                $role = Role::firstOrCreate(['name' => $role_name]);

                if ($role_permissions === '*') {
                    $this->line("{$role_name} → wildcard (Gate bypass)");
                    continue;
                }

                $permissions_to_assign = [];
                foreach ($role_permissions as $module => $actions) {
                    foreach ($actions as $action) {
                        $permissions_to_assign[] = "{$action} {$module}";
                    }
                }

                $valid_permissions = Permission::whereIn('name', $permissions_to_assign)
                    ->pluck('name')
                    ->toArray();

                $role->syncPermissions($valid_permissions);
                $count = count($valid_permissions);
                $this->line("{$role_name} → {$count} permissions assigned");
            }

        } else {
            $this->info('');
            $this->info('Role permissions untouched. (Manual panel changes preserved)');
            $this->info('To reset role permissions from config run:');
            $this->line('  php artisan crm:sync-permissions --sync-roles');
        }

        // ── Done ─────────────────────────────────────────────────────
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('');
        $this->info('Sync complete!');
        $this->table(
            ['Action', 'Count'],
            [
                ['Added',     count($to_add)],
                ['Removed',   count($to_remove)],
                ['Unchanged', count($unchanged)],
            ]
        );
    }
}