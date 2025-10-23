<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractorRateResource\Pages;
use App\Models\ContractorRate;
use App\Models\Contractor;
use App\Models\Specialty;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContractorRateResource extends Resource
{
    protected static ?string $model = ContractorRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-ruble';
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Ставки подрядчиков';
    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'ставка подрядчика';
    protected static ?string $pluralModelLabel = 'Ставки подрядчиков';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('contractor_id')
                            ->label('Подрядчик')
                            ->relationship('contractor', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                            
                        Forms\Components\Select::make('specialty_id')
                            ->label('Специальность')
                            ->relationship('specialty', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Специальность должна быть в той же категории, что и у подрядчика'),
                            
                        Forms\Components\TextInput::make('hourly_rate')
                            ->label('Ставка (руб/час)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->placeholder('0')
                            ->helperText('Почасовая ставка для этой специальности'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Дополнительные настройки')
                    ->schema([
                        Forms\Components\Toggle::make('is_anonymous')
                            ->label('Обезличенный персонал')
                            ->default(false)
                            ->helperText('Если включено - ставка применяется для обезличенного персонала'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активная ставка')
                            ->default(true)
                            ->helperText('Неактивные ставки не будут использоваться в расчетах'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contractor.name')
                    ->label('Подрядчик')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                    
                Tables\Columns\TextColumn::make('specialty.name')
                    ->label('Специальность')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('specialty.category.name')
                    ->label('Категория')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('hourly_rate')
                    ->label('Ставка')
                    ->money('RUB')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' ₽/час'),
                    
                Tables\Columns\IconColumn::make('is_anonymous')
                    ->label('Обезличенный')
                    ->boolean()
                    ->trueIcon('heroicon-o-user-group')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('success'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contractor')
                    ->label('Подрядчик')
                    ->relationship('contractor', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('specialty')
                    ->label('Специальность')
                    ->relationship('specialty', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('is_anonymous')
                    ->label('Тип персонала')
                    ->placeholder('Все')
                    ->trueLabel('Только обезличенные')
                    ->falseLabel('Только персонализированные'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные ставки')
                    ->placeholder('Все ставки')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->emptyStateHeading('Нет ставок подрядчиков')
            ->emptyStateDescription('Создайте первую ставку для подрядчика.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Создать ставку'),
            ])
            ->defaultSort('contractor_id', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContractorRates::route('/'),
            'create' => Pages\CreateContractorRate::route('/create'),
            'edit' => Pages\EditContractorRate::route('/{record}/edit'),
        ];
    }
}
