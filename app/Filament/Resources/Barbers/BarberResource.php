<?php

namespace App\Filament\Resources\Barbers;

use App\Filament\Resources\Barbers\BarberResource\Pages;
use App\Models\Barber;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class BarberResource extends Resource
{
    protected static ?string $model = Barber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScissors;

    public static function getNavigationLabel(): string
    {
        return __('admin.barbers.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.barbers.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.barbers.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.basic');
    }
    protected static ?int $navigationSort = 2;

public static function form(Schema $schema): Schema
{
    return $schema
        ->components([
            Forms\Components\Select::make('user_id')
                ->label(__('admin.barbers.fields.user_account'))
                ->relationship('user', 'name')
                ->searchable()
                ->nullable()
                ->helperText(__('admin.barbers.fields.user_account_helper')),

            Forms\Components\TextInput::make('name')
                ->label(__('admin.barbers.fields.name'))
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('phone')
                ->label(__('admin.barbers.fields.phone'))
                ->tel()
                ->maxLength(255),

            Forms\Components\Textarea::make('bio')
                ->label(__('admin.barbers.fields.bio'))
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Toggle::make('active')
                ->label(__('admin.barbers.fields.active'))
                ->default(true),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.barbers.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('admin.barbers.fields.phone'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.barbers.fields.user_account'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.barbers.fields.active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.barbers.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('admin.barbers.fields.active'))
                    ->placeholder(__('admin.barbers.fields.all'))
                    ->trueLabel(__('admin.barbers.fields.active'))
                    ->falseLabel(__('admin.barbers.fields.inactive')),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->selectable() // مهم لتفعيل اختيار الصفوف
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

        if ($user && ! $user->can('ViewAny:Barber') && $user->can('View:Barber')) {
            return $query->where('user_id', $user->id);
        }

        return $query;
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
            'index' => Pages\ListBarbers::route('/'),
            'create' => Pages\CreateBarber::route('/create'),
            'edit' => Pages\EditBarber::route('/{record}/edit'),
        ];
    }
}
