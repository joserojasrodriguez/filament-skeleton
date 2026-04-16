<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('prevents inactive users from accessing the admin panel', function (): void {
    $user = User::factory()->create([
        'is_active' => false,
    ]);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();

    actingAs($user);

    get('/admin')->assertForbidden();
});
