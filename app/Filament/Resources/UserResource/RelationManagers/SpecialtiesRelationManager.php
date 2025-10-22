<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SpecialtiesRelationManager extends RelationManager
{
    protected static string $relationship = 'specialties';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('base_hourly_rate')
                    ->label('Базовая ставка')
                    ->numeric()
                    ->minValue(0)
                    ->step(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Специальность')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('pivot.base_hourly_rate')
                    ->label('Индивидуальная ставка')
                    ->money('RUB')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('base_hourly_rate')
                    ->label('Базовая ставка')
                    ->money('RUB')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Добавить специальность')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Специальность')
                            ->required(),
                            
                        Forms\Components\TextInput::make('base_hourly_rate')
                            ->label('Индивидуальная ставка (руб/час)')
                            ->numeric()
                            ->minValue(0)
                            ->step(1),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Удалить'),
                    
                Tables\Actions\EditAction::make()
                    ->label('Изменить ставку')
                    ->form([
                        Forms\Components\TextInput::make('pivot.base_hourly_rate')
                            ->label('Индивидуальная ставка (руб/час)')
                            ->numeric()
                            ->minValue(0)
                            ->step(1),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ]);
    }
}
