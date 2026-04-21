<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('User details')
                    ->columns(2)
                    ->schema([
                        ToggleButtons::make('is_active')
                            ->label('Active')
                            ->grouped()
                            ->inline()
                            ->boolean()
                            ->required()
                            ->columnSpan(1),
                        Hidden::make('has_email_authentication')
                            ->label(__('filament/admin/user_resource.has_email_authentication'))
                            ->default((bool) config('filament.mfa.email'))
                            ->formatStateUsing(fn ($state): bool => (bool) ($state ?? config('filament.mfa.email'))),
                        Select::make('roles')
                            ->label(__('filament/admin/user_resource.roles'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->minItems(1)
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label(__('filament/admin/user_resource.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('filament/admin/user_resource.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label(__('filament/admin/user_resource.password'))
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->autocomplete('new-password')
                            ->rule(Password::default())
                            ->live(debounce: 500)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->confirmed()
                            ->columnSpan(1),
                        TextInput::make('password_confirmation')
                            ->label(__('filament/admin/user_resource.password_confirmation'))
                            ->password()
                            ->autocomplete('new-password')
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required(fn (Get $get): bool => filled($get('password')))
                            ->visible(fn (Get $get): bool => filled($get('password')))
                            ->dehydrated(false)
                            ->columnSpan(1),
                        TextInput::make('current_password')
                            ->label(__('filament/admin/user_resource.current_password'))
                            ->password()
                            ->autocomplete('current-password')
                            ->currentPassword(guard: Filament::getAuthGuard())
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required(fn (string $operation, Get $get): bool => $operation === 'edit' && filled($get('password')))
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'edit' && filled($get('password')))
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
