<?php

namespace App\Filament\Resources\Users\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Session;
use Filament\Tables\Actions\Action;


class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessionsFake'; // وهمي (مش Relation حقيقي)

    protected static ?string $title = 'Sessions';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Session::query()
                    ->where('user_id', $this->getOwnerRecord()->id)
                    ->orderByDesc('last_activity')
            )
            ->columns([
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->copyable()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Device')
                    ->limit(45)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_activity')
                    ->label('Last Activity')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::createFromTimestamp((int) $state)->diffForHumans())
                    ->sortable(),
            ])
            ->striped()
            ->paginated([5, 10, 25]);
    }
}
