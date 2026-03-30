<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Actions;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\User;



class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    public static function getNavigationLabel(): string
    {
        return __('admin.services.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.services.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.services.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.basic');
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.services.fields.name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('duration_min')
                    ->label(__('admin.services.fields.duration_min'))
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->suffix('دقيقة'),

                Forms\Components\TextInput::make('price')
                    ->label(__('admin.services.fields.price'))
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('ر.س'),

                Forms\Components\Toggle::make('active')
                    ->label(__('admin.services.fields.active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.services.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_min')
                    ->label(__('admin.services.fields.duration_min'))
                    ->suffix(' دقيقة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('admin.services.fields.price'))
                    ->money('SAR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.services.fields.active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.services.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('admin.services.fields.active'))
                    ->placeholder(__('admin.filters.all'))
                    ->trueLabel(__('admin.services.fields.active'))
                    ->falseLabel(__('admin.services.fields.inactive')),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->selectable(function () {
               /** @var User|null $user */
                $user = Auth::user();

                return $user?->can('Delete:' . class_basename(static::$model)) ?? false;
            })
            // مهم لتفعيل اختيار الصفوف
            ->toolbarActions([
                    Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
