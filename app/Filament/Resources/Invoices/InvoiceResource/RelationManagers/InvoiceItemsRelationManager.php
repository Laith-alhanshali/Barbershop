<?php

namespace App\Filament\Resources\Invoices\InvoiceResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('admin.invoices.items.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.invoices.items.fields.name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('qty')
                    ->label(__('admin.invoices.items.fields.qty'))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('duration_min')
                    ->label(__('admin.invoices.items.fields.duration_min'))
                    ->suffix('m')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label(__('admin.invoices.items.fields.unit_price'))
                    ->money('SAR'),

                Tables\Columns\TextColumn::make('line_total')
                    ->label(__('admin.invoices.items.fields.line_total'))
                    ->money('SAR'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->paginated(false);
    }
}
