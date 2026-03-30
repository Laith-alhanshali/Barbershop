<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\ViewField;




class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    // نخلي النصوص من الترجمة
    public static function getNavigationLabel(): string
    {
        return __('filament-panels::admin.users.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('filament-panels::admin.users.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-panels::admin.users.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament-panels::admin.groups.employees');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament-panels::admin.users.fields.name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label(__('filament-panels::admin.users.fields.email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('phone')
                        ->label(__('filament-panels::admin.users.fields.phone'))
                        ->tel()
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\Select::make('locale')
                        ->label(__('filament-panels::admin.users.fields.locale'))
                        ->options([
                            'en' => 'English',
                            'ar' => 'العربية',
                        ])
                        ->required()
                        ->default('en'),

                    // roles من Spatie
                   Forms\Components\Select::make('roles')
                        ->label(__('filament-panels::admin.users.fields.roles'))
                        ->multiple()
                        ->relationship('roles', 'name')  // Filament يجيب الخيارات ويخزّن IDs صح
                        ->preload()
                        ->searchable()
                        ->helperText(__('filament-panels::admin.users.helpers.roles')),

                    Forms\Components\Toggle::make('active')
                        ->label(__('filament-panels::admin.users.fields.active'))
                        ->default(true),
                ]),

            Section::make('Sessions')
                ->schema([
                    ViewField::make('browser_sessions')
                        ->view('filament.users.browser-sessions-wrapper'),
                ])
                ->visible(function (): bool {
                    /** @var User|null $user */
                    $user = Auth::user();

                    return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
                })
                ->visibleOn('edit'),
            Section::make(__('filament-panels::admin.users.sections.password'))
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label(__('filament-panels::admin.users.fields.password'))
                        ->password()
                        ->revealable()
                        ->rule(Password::defaults())
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation) => $operation === 'create'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-panels::admin.users.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament-panels::admin.users.fields.email'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('filament-panels::admin.users.fields.phone'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('filament-panels::admin.users.fields.roles'))
                    ->badge()
                    ->separator(', ')
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament-panels::admin.users.fields.active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('locale')
                    ->label(__('filament-panels::admin.users.fields.locale'))
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament-panels::admin.users.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label(__('filament-panels::admin.users.filters.role'))
                    ->options(fn () => Role::pluck('name', 'name')->toArray())
                    ->query(function ($query, array $data) {
                        if (filled($data['value'])) {
                            $query->role($data['value']); // scope من Spatie
                        }
                    }),
            ])
            ->recordActions([  
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->selectable()
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    /** @var User|null $user */
    $user = Auth::user();

    if (! $user) return $query;

    // معه ViewAny أو أدمن => يشوف الكل
    if ($user->can('ViewAny:User') || $user->hasAnyRole(['admin', 'super_admin'])) {
        return $query;
    }

    // معه View فقط => يشوف نفسه
    return $query->where('id', $user->id);
}


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}