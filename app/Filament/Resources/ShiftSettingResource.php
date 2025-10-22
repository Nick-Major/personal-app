<?php
// app/Filament/Resources/ShiftSettingResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftSettingResource\Pages;
use App\Models\ShiftSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftSettingResource extends Resource
{
    protected static ?string $model = ShiftSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Настройки смен';
    protected static ?string $modelLabel = 'настройка смен';
    protected static ?string $pluralModelLabel = 'Настройки смен';
    protected static ?string $navigationGroup = 'Настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Настройки расчетов')
                    ->schema([
                        Forms\Components\TextInput::make('transport_fee')
                            ->label('Транспортная надбавка (руб)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Фиксированная сумма за трансфер'),
                            
                        Forms\Components\TextInput::make('no_lunch_bonus_hours')
                            ->label('Доп. часы за отсутствие обеда')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(8)
                            ->default(1)
                            ->required()
                            ->helperText('Количество дополнительных часов при работе без обеда'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transport_fee')
                    ->label('Транспортная надбавка')
                    ->money('RUB')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('no_lunch_bonus_hours')
                    ->label('Доп. часы за обед')
                    ->suffix(' час(а)')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('Редактировать'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Настройки не найдены')
            ->emptyStateDescription('Создайте настройки по умолчанию.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Создать настройки'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShiftSettings::route('/'),
            'edit' => Pages\EditShiftSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return ShiftSetting::count() === 0; // Только одна запись
    }
}
