<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminPanelSeeder;
use Database\Seeders\ShieldPermissionsSeeder;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->seed([
        ShieldPermissionsSeeder::class,
        AdminPanelSeeder::class,
    ]);
});

it('lists users in the filament resource table', function (): void {
    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    $users = User::factory()->count(3)->create();

    actingAs($admin);

    Livewire::test(ListUsers::class)
        ->assertSuccessful()
        ->loadTable()
        ->assertCanSeeTableRecords($users);
});

it('creates users from the filament resource form', function (): void {
    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    $editorRole = Role::factory()->create(['name' => 'editor']);

    actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'UUID User',
            'email' => 'uuid-user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_active' => true,
            'roles' => [$editorRole->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $user = User::query()->where('email', 'uuid-user@example.com')->firstOrFail();

    expect($user->id)->toMatch('/^[0-9a-f-]{36}$/i')
        ->and($user->hasRole($editorRole))->toBeTrue()
        ->and($user->is_admin)->toBeFalse()
        ->and($user->has_email_authentication)->toBe((bool) config('filament.mfa.email'));
});

it('validates the required fields in the user form', function (): void {
    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

    actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => null,
            'email' => null,
            'password' => null,
            'is_active' => null,
            'roles' => [],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'is_active' => 'required',
            'roles' => 'required',
        ]);
});

it('edits users by uuid from the filament resource', function (): void {
    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    $editorRole = Role::factory()->create(['name' => 'editor']);
    $reviewerRole = Role::factory()->create(['name' => 'reviewer']);
    $user = User::factory()->create([
        'name' => 'Before',
    ]);
    $user->assignRole($editorRole);

    actingAs($admin);

    get(route('filament.admin.resources.users.edit', ['record' => $user]))
        ->assertOk();

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'After',
            'email' => $user->email,
            'is_active' => $user->is_active,
            'roles' => [$reviewerRole->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'After',
    ]);

    expect($user->fresh()->hasRole($reviewerRole))->toBeTrue()
        ->and($user->fresh()->hasRole($editorRole))->toBeFalse();
});

it('can search and sort users in the filament resource table', function (): void {
    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    $firstUser = User::factory()->create([
        'name' => 'Alpha Search',
        'email' => 'alpha@example.com',
        'created_at' => now()->subDays(2),
    ]);
    $secondUser = User::factory()->create([
        'name' => 'Beta Search',
        'email' => 'beta@example.com',
        'created_at' => now()->subDay(),
    ]);
    $thirdUser = User::factory()->create([
        'name' => 'Gamma Search',
        'email' => 'gamma@example.com',
        'created_at' => now(),
    ]);

    actingAs($admin);

    Livewire::test(ListUsers::class)
        ->loadTable()
        ->searchTable('beta@example.com')
        ->assertCanSeeTableRecords([$secondUser])
        ->assertCanNotSeeTableRecords([$firstUser, $thirdUser])
        ->searchTable(null)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords([$thirdUser, $secondUser, $firstUser], true);
});

it('can filter users by role in the filament resource table', function (): void {
    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    $editorRole = Role::factory()->create(['name' => 'editor']);
    $reviewerRole = Role::factory()->create(['name' => 'reviewer']);
    $editorUser = User::factory()->create(['name' => 'Editor User']);
    $reviewerUser = User::factory()->create(['name' => 'Reviewer User']);

    $editorUser->assignRole($editorRole);
    $reviewerUser->assignRole($reviewerRole);

    actingAs($admin);

    Livewire::test(ListUsers::class)
        ->loadTable()
        ->assertTableColumnExists('roles.name')
        ->filterTable('roles', [$editorRole->id])
        ->assertCanSeeTableRecords([$editorUser])
        ->assertCanNotSeeTableRecords([$reviewerUser]);
});
