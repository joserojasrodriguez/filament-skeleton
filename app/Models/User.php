<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\Email\Concerns\InteractsWithEmailAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Joserojasrodriguez\FilamentDeleteGuard\Contracts\HasPreventDeletion;
use Joserojasrodriguez\FilamentDeleteGuard\Exceptions\CannotDeleteModelException;
use Joserojasrodriguez\FilamentDeleteGuard\Traits\InteractsWithPreventDeletion;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'email_verified_at',
    'password',
    'is_active',
    'app_authentication_secret',
    'app_authentication_recovery_codes',
    'has_email_authentication',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasEmailAuthentication, HasPreventDeletion
{
    public const ROLE_SUPER_ADMIN = 'super_admin';

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuids, Notifiable;

    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;
    use InteractsWithEmailAuthentication;
    use InteractsWithPreventDeletion;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isSystemAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active;
    }

    public function customDeletionRules(): void
    {
        if ($this->hasRole(self::ROLE_SUPER_ADMIN)) {
            throw CannotDeleteModelException::because('Super admin users cannot be deleted.');
        }
    }
}
