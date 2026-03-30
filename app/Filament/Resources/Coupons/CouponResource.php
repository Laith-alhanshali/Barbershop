<?php

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Coupons\CouponResource\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\CouponResource\Pages\EditCoupon;
use App\Filament\Resources\Coupons\CouponResource\Pages\ListCoupons;
use App\Models\Coupon;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    public static function getNavigationGroup(): string
    {
        return __('admin.groups.basic');
    }
    protected static ?string $modelLabel = 'Coupon';
    protected static ?string $pluralModelLabel = 'Coupons';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Coupon Info')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->maxLength(50)
                        ->unique(
                            table: Coupon::class,
                            column: 'code',
                            ignorable: fn ($record) => $record
                        )
                        ->helperText('Example: NEWYEAR15'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),

                    Forms\Components\Select::make('type')
                        ->label('Type')
                        ->options([
                            'fixed' => 'Fixed',
                            'percent' => 'Percent',
                        ])
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('value')
                        ->label('Value')
                        ->required()
                        ->numeric()
                        ->step(0.01)
                        ->minValue(fn (callable $get) => $get('type') === 'percent' ? 0 : 0.01)
                        ->maxValue(fn (callable $get) => $get('type') === 'percent' ? 100 : null)
                        ->suffix(fn (callable $get) => $get('type') === 'percent' ? '%' : null)
                        ->helperText(fn (callable $get) => $get('type') === 'percent'
                            ? '0 - 100'
                            : 'Fixed discount amount'
                        ),
                ])
                ->columns(2),

            Section::make('Validity & Limits')
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Starts at')
                        ->seconds(false),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Expires at')
                        ->seconds(false)
                        ->rule(function (callable $get) {
                            $startsAt = $get('starts_at');
                            if (!$startsAt) return null;

                            // expires_at must be after starts_at
                            return 'after:' . $startsAt;
                        }),

                    Forms\Components\TextInput::make('max_uses')
                        ->label('Max uses (optional)')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->helperText('Leave empty for unlimited uses.'),

                    Forms\Components\TextInput::make('used_count')
                        ->label('Used count')
                        ->numeric()
                        ->integer()
                        ->disabled()
                        ->dehydrated(false)
                        ->default(0),

                    Forms\Components\TextInput::make('min_subtotal')
                        ->label('Min invoice subtotal (optional)')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0),
                ])
                ->columns(2),

            Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('note')
                        ->label('Internal note')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'info' => 'percent',
                        'success' => 'fixed',
                    ]),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(function ($state, Coupon $record) {
                        return $record->type === 'percent'
                            ? rtrim(rtrim(number_format((float) $state, 2), '0'), '.') . '%'
                            : number_format((float) $state, 2);
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status_badge')
                    ->label('Status')
                    ->getStateUsing(function (Coupon $record) {
                        if (! $record->is_active) return 'inactive';

                        $now = now();

                        if ($record->starts_at && $record->starts_at->gt($now)) return 'scheduled';
                        if ($record->expires_at && $record->expires_at->lt($now)) return 'expired';

                        if (! is_null($record->max_uses) && $record->used_count >= $record->max_uses) {
                            return 'limit_reached';
                        }

                        return 'active';
                    })
                    ->colors([
                        'success' => 'active',
                        'warning' => 'scheduled',
                        'danger'  => 'expired',
                        'gray'    => 'inactive',
                        'danger'  => 'limit_reached',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Active',
                        'scheduled' => 'Scheduled',
                        'expired' => 'Expired',
                        'inactive' => 'Inactive',
                        'limit_reached' => 'Limit reached',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Used')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_uses')
                    ->label('Max')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => is_null($state) ? '∞' : (string) $state),

                Tables\Columns\TextColumn::make('min_subtotal')
                    ->label('Min subtotal')
                    ->formatStateUsing(fn ($state) => is_null($state) ? '-' : number_format((float) $state, 2))
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Active?')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),

                Tables\Filters\Filter::make('valid_now')
                    ->label('Valid now')
                    ->query(fn (Builder $query) => $query->activeNow()),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(function (Builder $query) {
                        return $query->whereNotNull('expires_at')->where('expires_at', '<', now());
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => pages\ListCoupons::route('/'),
            'create' => pages\CreateCoupon::route('/create'),
            'edit' => pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
