<?php

namespace App\Filament\Resources\Appointments;

use App\Filament\Resources\Appointments\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;


class AppointmentResource extends Resource
{
    public static function getGloballySearchableAttributes(): array
    {
        return ['customer.name', 'barber.name', 'services.name'];
    }
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->customer->name;
    }
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('admin.appointments.fields.customer') => $record->customer->name,
            __('admin.appointments.fields.barber') => $record->barber->name,
            __('admin.appointments.fields.services') => $record->services->pluck('name')->implode(', ')
        ];
    }

    protected static ?string $model = Appointment::class;

    public static function getNavigationLabel(): string
    {
        return __('admin.appointments.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.appointments.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.appointments.plural');
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

    protected static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasRole('super_admin') ?? false;
    }

    protected static function isFullyLocked(?Appointment $record): bool
    {
        if (! $record) return false;

        return in_array($record->status, ['done', 'cancelled'], true);
    }


    protected static function isPartiallyLocked(?Appointment $record): bool
    {
        if (! $record) return false;

        // confirmed => status فقط مفتوح
        return $record->status === 'confirmed';
    }

    public static function form(Schema $schema): Schema
    {
        $recalcEndAt = function (callable $get, callable $set): void {
            $serviceIds = $get('services') ?? [];
            $startAt    = $get('start_at');

            if (blank($startAt) || empty($serviceIds)) {
                $set('end_at', null);
                return;
            }

            $totalDuration = (int) Service::whereIn('id', $serviceIds)->sum('duration_min');
            $endAt = Carbon::parse($startAt)->addMinutes($totalDuration);

            $set('end_at', $endAt->format('Y-m-d H:i:s'));
        };

        return $schema->components([

            Forms\Components\Select::make('customer_id')
                ->label(__('admin.appointments.fields.customer'))
                ->relationship('customer', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn (?Appointment $record) =>
                    static::isPartiallyLocked($record) || static::isFullyLocked($record)
                ),


            Forms\Components\Select::make('barber_id')
                ->label(__('admin.appointments.fields.barber'))
                ->relationship('barber', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn (?Appointment $record) =>
                    static::isPartiallyLocked($record) || static::isFullyLocked($record)
                ),

            Forms\Components\Select::make('services')
                ->label(__('admin.appointments.fields.services'))
                ->multiple()
                ->preload()
                ->searchable()
                ->options(
                    Service::query()
                        ->where('active', true)
                        ->get()
                        ->mapWithKeys(fn ($s) => [
                            $s->id => "{$s->name} ({$s->duration_min}m - {$s->price})",
                        ])
                        ->toArray()
                )
                ->required()
                ->live()
                ->afterStateHydrated(function (callable $set, $record) {
                    if ($record) {
                        $set('services', $record->services()->pluck('services.id')->toArray());
                    }
                })
                ->afterStateUpdated(fn ($state, callable $get, callable $set) => $recalcEndAt($get, $set))
                ->disabled(fn (?Appointment $record) =>
                    static::isPartiallyLocked($record) || static::isFullyLocked($record)
                ),
                Forms\Components\Placeholder::make('total_after_discount')
                ->label('Total after discount')
                ->content(function (callable $get) {
                    $serviceIds = $get('services') ?? [];
                    $subtotal = !empty($serviceIds)
                        ? (float) \App\Models\Service::whereIn('id', $serviceIds)->sum('price')
                        : 0;

                    $discount = (float) ($get('discount') ?? 0);

                    return number_format(max(0, $subtotal - $discount), 2);
                }),
                


            Forms\Components\DateTimePicker::make('start_at')
                ->label(__('admin.appointments.fields.start_at'))
                ->required()
                ->live()
                ->minDate(function () {
                    /** @var User|null $user */
                    $user = Auth::user();

                    // غير super_admin ممنوع يحجز قبل ساعة من الآن
                    return $user?->hasRole('super_admin') ? null : now()->subHour();
                })
                ->afterStateUpdated(fn ($state, callable $get, callable $set) => $recalcEndAt($get, $set))
                ->disabled(fn (?Appointment $record) =>
                    static::isPartiallyLocked($record) || static::isFullyLocked($record)
                ),

            Forms\Components\DateTimePicker::make('end_at')
                ->label(__('admin.appointments.fields.end_at'))
                ->disabled()
                ->dehydrated(true),

            Forms\Components\Select::make('status')
                ->label(__('admin.appointments.fields.status'))
                ->options([
                    'pending'   => __('admin.appointments.status.pending'),
                    'confirmed' => __('admin.appointments.status.confirmed'),
                    'done'      => __('admin.appointments.status.done'),
                    'cancelled' => __('admin.appointments.status.cancelled'),
                    'no_show'   => __('admin.appointments.status.no_show'),
                ])
                ->default('pending')
                ->required()
                ->disabled(fn (?Appointment $record) =>
                    in_array($record?->status, ['done','cancelled'])
                ),

            Forms\Components\TextInput::make('coupon_code')
    ->label('Coupon Code')
    ->dehydrated(false)
    ->suffixAction(
         Action::make('applyCoupon')
            ->label('Apply')
            ->disabled(fn (?Appointment $record) =>
                static::isPartiallyLocked($record) || static::isFullyLocked($record)
            )
            ->icon('heroicon-o-bolt')
            ->action(function (callable $get, callable $set) {
                $code = trim((string) $get('coupon_code'));

                if ($code === '') {
                    $set('coupon_id', null);
                    $set('discount', 0);
                    return;
                }

                // subtotal من الخدمات المختارة (يفضل تستخدم pivot prices عند edit)
                $serviceIds = $get('services') ?? [];
                $subtotal = !empty($serviceIds)
                    ? (float) \App\Models\Service::whereIn('id', $serviceIds)->sum('price')
                    : 0;

                $coupon = \App\Models\Coupon::query()
                    ->where('code', $code)
                    ->activeNow()
                    ->first();

                if (! $coupon) {
                    $set('coupon_id', null);
                    $set('discount', 0);

                    \Filament\Notifications\Notification::make()
                        ->title('Invalid coupon')
                        ->danger()
                        ->send();
                    return;
                }

                if (!is_null($coupon->min_subtotal) && $subtotal < (float) $coupon->min_subtotal) {
                    $set('coupon_id', null);
                    $set('discount', 0);

                    \Filament\Notifications\Notification::make()
                        ->title('Subtotal is below coupon minimum')
                        ->danger()
                        ->send();
                    return;
                }

                // حساب الخصم (Snapshot)
                $discount = 0;
                if ($coupon->type === 'percent') {
                    $percent = max(0, min(100, (float) $coupon->value));
                    $discount = round($subtotal * ($percent / 100), 2);
                } else {
                    $discount = round(min((float) $coupon->value, $subtotal), 2);
                }

                $set('coupon_id', $coupon->id);
                $set('discount', $discount);

                \Filament\Notifications\Notification::make()
                    ->title('Coupon applied')
                    ->success()
                    ->send();
            })
        ),
            Forms\Components\Hidden::make('coupon_id'),
            Forms\Components\Hidden::make('discount'),

            Forms\Components\Textarea::make('notes')
                ->label(__('admin.appointments.fields.notes'))
                ->columnSpanFull()
                ->disabled(fn (?Appointment $record) =>
                    static::isPartiallyLocked($record) || static::isFullyLocked($record)
                ),

            Placeholder::make('meta_info')
                ->label('')
                ->content(function (?Appointment $record) {
                    if (! $record) return null;

                    $createdBy = $record->creator?->name ?? '-';
                    $createdAt = $record->created_at?->format('Y-m-d h:i A') ?? '-';

                    $createdLine = __("admin.appointments.meta.created", [
                        'by' => $createdBy,
                        'at' => $createdAt,
                    ]);

                    if (! $record->updated_at || $record->updated_at->equalTo($record->created_at)) {
                        return new HtmlString("<div class='text-sm text-gray-500 mt-2'>{$createdLine}</div>");
                    }

                    $updatedBy = $record->updater?->name ?? $createdBy;
                    $updatedAt = $record->updated_at?->format('Y-m-d h:i A') ?? '-';

                    $updatedLine = __("admin.appointments.meta.updated", [
                        'by' => $updatedBy,
                        'at' => $updatedAt,
                    ]);

                    return new HtmlString("
                        <div class='text-sm text-gray-500 mt-2'>{$createdLine}</div>
                        <div class='text-sm text-gray-500 mt-1'>{$updatedLine}</div>
                    ");
                })
                ->visibleOn('edit')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('admin.appointments.fields.customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barber.name')
                    ->label(__('admin.appointments.fields.barber'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_at')
                    ->label(__('admin.appointments.fields.start_at'))
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_at')
                    ->label(__('admin.appointments.fields.end_at'))
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.appointments.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'confirmed',
                        'success' => 'done',
                        'gray'    => 'cancelled',
                        'danger'  => 'no_show',
                    ])
                    ->formatStateUsing(fn ($state) => __("admin.appointments.status.$state")),

                Tables\Columns\TextColumn::make('services_count')
                    ->label(__('admin.appointments.fields.services_count'))
                    ->counts('services'),

                Tables\Columns\TextColumn::make('total_duration_min')
                    ->label(__('admin.appointments.fields.total_duration'))
                    ->state(fn (Appointment $record) =>
                        $record->services->sum('pivot.duration_min_at_booking') . 'm'
                    ),

                Tables\Columns\TextColumn::make('discount')
                ->label('Discount')
                ->state(fn (Appointment $record) => number_format((float) ($record->discount ?? 0), 2))
                ->toggleable(),

            Tables\Columns\TextColumn::make('total_after_discount')
                ->label('Total after discount')
                ->state(function (Appointment $record) {
                    $base = (float) $record->services->sum('pivot.price_at_booking');
                    $discount = (float) ($record->discount ?? 0);

                    return number_format(max(0, $base - $discount), 2);
                })
                ->toggleable(),


                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('admin.appointments.fields.total_price'))
                    ->state(fn (Appointment $record) =>
                        number_format($record->services->sum('pivot.price_at_booking'), 2)
                    ),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('barber_id')
                    ->label(__('admin.appointments.fields.barber'))
                    ->relationship('barber', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.appointments.fields.status'))
                    ->options([
                        'pending'   => __('admin.appointments.status.pending'),
                        'confirmed' => __('admin.appointments.status.confirmed'),
                        'done'      => __('admin.appointments.status.done'),
                        'cancelled' => __('admin.appointments.status.cancelled'),
                        'no_show'   => __('admin.appointments.status.no_show'),
                    ]),

                Tables\Filters\Filter::make('start_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('admin.filters.from')),
                        Forms\Components\DatePicker::make('to')->label(__('admin.filters.to')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('start_at', '>=', $d))
                            ->when($data['to'] ?? null, fn ($q, $d) => $q->whereDate('start_at', '<=', $d));
                    }),
            ])
            ->recordActions([
                Actions\Action::make('invoice')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn (Appointment $record) => $record->invoice()->exists())
                    ->url(fn (Appointment $record) => \App\Filament\Resources\Invoices\InvoiceResource::getUrl('edit', ['record' => $record->invoice])),

                Actions\Action::make('print')
                    ->label(__('admin.appointments.actions.print_invoice'))
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn (Appointment $record) => $record->invoice()->exists())
                    ->url(fn (Appointment $record) => route('invoices.print', $record->invoice))
                    ->openUrlInNewTab(),

                Actions\EditAction::make()->visible(fn (Appointment $record) => in_array($record->status, ['pending'])),
                Actions\DeleteAction::make()->visible(fn (Appointment $record) => in_array($record->status, ['pending'])),
                Actions\RestoreAction::make()
                    ->visible(fn (Appointment $record): bool =>
                        $record->trashed() && static::userCan('Restore:Appointment')
                    ),

            ])
            ->selectable(fn () => static::userCan('Delete:Appointment'))
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->visible(fn () => static::userCan('Delete:Appointment')),

                    Actions\RestoreBulkAction::make()
                        ->visible(fn () => static::userCan('Restore:Appointment')),

                ]),
            ])
            ->defaultSort('start_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit'   => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
