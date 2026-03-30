<?php

namespace App\Filament\Resources\Customers\CustomerResource\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;


class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // ✅ لازم نضيف withTrashed عشان TrashedFilter يشتغل
        return $query->withTrashed();
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
