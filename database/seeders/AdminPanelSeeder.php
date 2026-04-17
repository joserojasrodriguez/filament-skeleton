<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AdminPanelSeeder extends Seeder
{
    public function run(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $user = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'is_active' => true,
                'has_email_authentication' => false,
            ],
        );

        $user->forceFill([
            'is_admin' => true,
        ])->save();

        $role = Role::query()->firstOrCreate([
            'name' => User::ROLE_SUPER_ADMIN,
            'guard_name' => Filament::getCurrentPanel()->getAuthGuard(),
        ]);

        $role->syncPermissions(FilamentShield::getEntitiesPermissions());
        $user->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
