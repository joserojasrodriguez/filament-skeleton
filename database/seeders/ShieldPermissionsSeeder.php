<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ShieldPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        foreach (FilamentShield::getEntitiesPermissions() as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => Filament::getCurrentPanel()->getAuthGuard(),
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
