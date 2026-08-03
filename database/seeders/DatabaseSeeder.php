<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('crm:sync-permissions');
        $this->command->info('Roles & Permissions seeded via config.');
    }
}
