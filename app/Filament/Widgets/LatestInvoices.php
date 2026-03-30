<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestInvoices extends BaseWidget
{
    protected static ?string $heading = 'Latest Invoices';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->with(['customer', 'barber'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('barber.name')
                    ->label('Barber')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'unpaid',
                        'success' => 'paid',
                        'gray'    => 'void',
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('SAR'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('Y-m-d h:i A')
                    ->placeholder('-'),
            ])
            // ✅ الصف كله يصير "Open" بدون الحاجة لـ Tables\Actions\Action
            ->recordUrl(fn (Invoice $record) => InvoiceResource::getUrl('edit', ['record' => $record]))
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25]);
    }
}
