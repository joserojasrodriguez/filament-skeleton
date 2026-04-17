<?php

declare(strict_types=1);

use App\Filament\Resources\Users\UserResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Database\Seeders\AdminPanelSeeder;
use Database\Seeders\ShieldPermissionsSeeder;

use function Pest\Laravel\assertDatabaseHas;

it('uses uuid primary keys across users roles permissions and pivots', function (): void {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();

    $user->assignRole($role);
    $user->givePermissionTo($permission);
    $role->givePermissionTo($permission);

    expect($user->id)->toMatch('/^[0-9a-f-]{36}$/i')
        ->and($role->id)->toMatch('/^[0-9a-f-]{36}$/i')
        ->and($permission->id)->toMatch('/^[0-9a-f-]{36}$/i');

    assertDatabaseHas('model_has_roles', [
        'role_id' => $role->id,
        'model_type' => User::class,
        'model_uuid' => $user->id,
    ]);

    assertDatabaseHas('model_has_permissions', [
        'permission_id' => $permission->id,
        'model_type' => User::class,
        'model_uuid' => $user->id,
    ]);

    assertDatabaseHas('role_has_permissions', [
        'permission_id' => $permission->id,
        'role_id' => $role->id,
    ]);
});

it('seeds shield permissions and super admin access for the admin panel', function (): void {
    $this->seed([
        ShieldPermissionsSeeder::class,
        AdminPanelSeeder::class,
    ]);

    $user = User::query()->where('email', 'admin@example.com')->firstOrFail();
    $permissions = FilamentShield::getResourcePermissions(UserResource::class);

    expect($user->id)->toMatch('/^[0-9a-f-]{36}$/i')
        ->and($user->hasRole(User::ROLE_SUPER_ADMIN))->toBeTrue()
        ->and($user->is_admin)->toBeTrue();

    foreach ($permissions as $permission) {
        expect($user->can($permission))->toBeTrue();
    }
});
