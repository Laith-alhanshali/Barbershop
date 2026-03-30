<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\InvoiceResource\Pages;
use App\Filament\Resources\Invoices\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Section;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    public static function getNavigationLabel(): string
    {
        return __('admin.invoices.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.invoices.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.invoices.plural');
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.bookings');
    }

    /**
     * Helper to silence intelephense + centralize permission checks.
     */
    protected static function userCan(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->can($permission) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.invoices.sections.details'))
                ->schema([
                    Forms\Components\TextInput::make('number')
                        ->label(__('admin.invoices.fields.number'))
                        ->disabled(),

                    Forms\Components\Select::make('appointment_id')
                        ->label(__('admin.invoices.fields.appointment'))
                        ->relationship('appointment', 'id')
                        ->disabled()
                        ->formatStateUsing(fn ($state, $record) => $record?->appointment 
                            ? "#{$record->appointment->id} - " . $record->appointment->start_at?->format('Y-m-d H:i')
                            : $state),

                    Forms\Components\Select::make('customer_id')
                        ->label(__('admin.invoices.fields.customer'))
                        ->relationship('customer', 'name')
                        ->disabled(),

                    Forms\Components\Select::make('barber_id')
                        ->label(__('admin.invoices.fields.barber'))
                        ->relationship('barber', 'name')
                        ->disabled(),

                    Forms\Components\Select::make('status')
                        ->label(__('admin.invoices.fields.status'))
                        ->options([
                            'unpaid' => __('admin.invoices.status.unpaid'),
                            'paid' => __('admin.invoices.status.paid'),
                            'void' => __('admin.invoices.status.void'),
                        ])
                        ->disabled(),
                ])
                ->columns(2),

            Section::make(__('admin.invoices.sections.totals'))
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->label(__('admin.invoices.fields.subtotal'))
                        ->disabled()
                        ->numeric(),

                    Forms\Components\TextInput::make('discount')
                        ->label(__('admin.invoices.fields.discount'))
                        ->disabled()
                        ->numeric(),

                    Forms\Components\TextInput::make('tax')
                        ->label(__('admin.invoices.fields.tax'))
                        ->disabled()
                        ->numeric(),

                    Forms\Components\TextInput::make('total')
                        ->label(__('admin.invoices.fields.total'))
                        ->disabled()
                        ->numeric(),
                ])
                ->columns(4),

            Section::make(__('admin.invoices.sections.payment'))
                ->schema([
                    Forms\Components\TextInput::make('payment_method')
                        ->label(__('admin.invoices.fields.payment_method'))
                        ->disabled(),

                    Forms\Components\DateTimePicker::make('paid_at')
                        ->label(__('admin.invoices.fields.paid_at'))
                        ->disabled(),
                ])
                ->columns(2),

            Section::make(__('admin.invoices.sections.notes'))
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label(__('admin.invoices.fields.notes'))
                        ->columnSpanFull(),
                ]),

            Placeholder::make('meta_info')
                ->label('')
                ->content(function (?Invoice $record) {
                    if (! $record) return null;

                    $createdBy = $record->creator?->name ?? '-';
                    $createdAt = $record->created_at?->format('Y-m-d h:i A') ?? '-';

                    return new HtmlString(
                        "<div class='text-sm text-gray-500 mt-2'>" . 
                        __('admin.invoices.meta.created', ['by' => $createdBy, 'at' => $createdAt]) . 
                        "</div>"
                    );
                })
                ->visibleOn('edit')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('admin.invoices.fields.number'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('admin.invoices.fields.customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barber.name')
                    ->label(__('admin.invoices.fields.barber'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.invoices.fields.status'))
                    ->colors([
                        'warning' => 'unpaid',
                        'success' => 'paid',
                        'gray' => 'void',
                    ])
                    ->formatStateUsing(fn ($state) => __("admin.invoices.status.$state")),

                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.invoices.fields.total'))
                    ->money('SAR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('admin.invoices.fields.paid_at'))
                    ->dateTime('Y-m-d h:i A')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.invoices.fields.created_at'))
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.invoices.fields.status'))
                    ->options([
                        'unpaid' => __('admin.invoices.status.unpaid'),
                        'paid' => __('admin.invoices.status.paid'),
                        'void' => __('admin.invoices.status.void'),
                    ]),
            ])
            ->recordActions([
                Actions\Action::make('print')
                    ->label(__('admin.invoices.actions.print'))
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (Invoice $record) => route('invoices.print', $record))
                    ->openUrlInNewTab(),

                Actions\RestoreAction::make()
                    ->visible(fn (Invoice $record): bool =>
                        $record->trashed() && static::userCan('Restore:Invoice')
                    ),

            ])
            ->selectable(fn () => static::userCan('Delete:Invoice'))
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoiceItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
