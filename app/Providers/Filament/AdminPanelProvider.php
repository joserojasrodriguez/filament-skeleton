<?php

namespace App\Providers\Filament;

use App\Models\User;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->passwordReset()
            ->profile()
            ->emailVerification(fn (): bool => config('filament.has_email_verification', false))
            ->emailChangeVerification()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local') && config('filament.developer_login_enabled'))
                    ->users([
                        'Admin' => 'admin@example.com',
                    ]),
            ])
            ->navigationItems([
                NavigationItem::make('Log Viewer')
                    ->group('Super Admin')
                    ->url(fn (): string => route('log-viewer.index'))
                    ->label('Logs')
                    ->icon(Heroicon::OutlinedNumberedList)
                    ->visible(fn (): bool => auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)),
                NavigationItem::make('Horizon')
                    ->group('Super Admin')
                    ->url(fn (): string => route('horizon.index'))
                    ->label('Horizon')
                    ->icon(Heroicon::CpuChip)
                    ->visible(fn (): bool => auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if (! config('filament.mfa.enabled')) {
            return $panel;
        }

        $providers = [];

        if (config('filament.mfa.email')) {
            $providers[] = EmailAuthentication::make();
        }

        if (config('filament.mfa.app')) {
            $providers[] = AppAuthentication::make()
                ->recoverable()
                ->recoveryCodeCount(10);
        }

        return $panel->multiFactorAuthentication(
            $providers,
            isRequired: config('filament.mfa.required')
        );
    }
}
