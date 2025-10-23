<?php
// app/Filament/Resources/ShiftExpenseResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftExpenseResource\Pages;
use App\Models\ShiftExpense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftExpenseResource extends Resource
{
    protected static ?string $model = ShiftExpense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Операционные расходы';
    protected static ?string $modelLabel = 'расход';
    protected static ?string $pluralModelLabel = 'Операционные расходы';
    protected static ?string $navigationGroup = 'Финансы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о расходе')
                    ->schema([
                        Forms\Components\Select::make('shift_id')
                            ->label('Смена')
                            ->relationship('shift', 'id')
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\Select::make('type')
                            ->label('Тип расхода')
                            ->options([
                                'taxi' => 'Такси',
                                'other' => 'Прочие расходы',
                            ])
                            ->required()
                            ->native(false),
                            
                        Forms\Components\TextInput::make('amount')
                            ->label('Сумма (руб)')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->prefix('₽'),
                            
                        Forms\Components\FileUpload::make('receipt_photo')
                            ->label('Фото чека')
                            ->image()
                            ->directory('receipts')
                            ->maxSize(5120)
                            ->helperText('Максимальный размер: 5MB'),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(2)
                            ->maxLength(65535)
                            ->placeholder('Описание расхода...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shift.id')
                    ->label('ID смены')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'taxi' => 'warning',
                        'other' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'taxi' => 'Такси',
                        'other' => 'Прочие',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(30)
                    ->searchable(),
                    
                Tables\Columns\IconColumn::make('receipt_photo')
                    ->label('Чек')
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип расхода')
                    ->options([
                        'taxi' => 'Такси',
                        'other' => 'Прочие',
                    ]),
                    
                Tables\Filters\Filter::make('has_receipt')
                    ->label('С чеком')
                    ->query(fn ($query) => $query->whereNotNull('receipt_photo')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShiftExpenses::route('/'),
            'create' => Pages\CreateShiftExpense::route('/create'),
            'edit' => Pages\EditShiftExpense::route('/{record}/edit'),
        ];
    }
}
