<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftsRelationManager extends RelationManager
{
    protected static string $relationship = 'shifts';

    protected static ?string $title = 'Смены';

    protected static ?string $label = 'смена';

    protected static ?string $pluralLabel = 'Смены';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('workRequest.request_number')
                    ->label('Заявка')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('work_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Начало')
                    ->time('H:i'),
                    
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Окончание')
                    ->time('H:i'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'warning' => 'planned',
                        'success' => 'active',
                        'gray' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                    
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Роль')
                    ->formatStateUsing(fn ($state) => $state === 'brigadier' ? 'Бригадир' : 'Исполнитель')
                    ->colors([
                        'success' => 'brigadier',
                        'info' => 'executor',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'planned' => 'Запланирована',
                        'active' => 'Активна',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\ShiftResource::getUrl('edit', [$record->id])),
            ])
            ->bulkActions([]);
    }
}
