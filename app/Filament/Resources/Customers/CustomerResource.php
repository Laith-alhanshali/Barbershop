<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

class CustomerResource extends Resource
{

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('admin.customers.fields.phone') => $record->phone,
        ];
    }

    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function getNavigationLabel(): string
    {
        return __('admin.customers.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.customers.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.customers.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.basic');
    }

    protected static ?int $navigationSort = 3;

    /**
     * Helper to silence intelephense and centralize permission checks.
     */
    protected static function userCan(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->can($permission) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.customers.fields.name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label(__('admin.customers.fields.phone'))
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.customers.fields.notes'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.customers.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('admin.customers.fields.phone'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.customers.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.customers.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),

                Actions\RestoreAction::make()
                    ->visible(fn (Customer $record): bool =>
                        $record->trashed() && static::userCan('Restore:Customer')
                    ),

                Actions\ForceDeleteAction::make()
                    ->visible(fn (Customer $record): bool =>
                        $record->trashed() && static::userCan('ForceDelete:Customer')
                    ),
            ])
            ->selectable(fn () => static::userCan('Delete:Customer'))
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->visible(fn () => static::userCan('Delete:Customer')),

                    Actions\RestoreBulkAction::make()
                        ->visible(fn () => static::userCan('Restore:Customer')),

                    Actions\ForceDeleteBulkAction::make()
                        ->visible(fn () => static::userCan('ForceDelete:Customer')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
