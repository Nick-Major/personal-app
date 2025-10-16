<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PurposesRelationManager extends RelationManager
{
    protected static string $relationship = 'purposes';

    protected static ?string $title = 'Назначения проекта';

    protected static ?string $label = 'назначение';
    
    protected static ?string $pluralLabel = 'Назначения';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название назначения')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Forms\Components\Toggle::make('has_custom_payer_selection')
                    ->label('Ручной выбор плательщика')
                    ->helperText('Если включено, можно будет выбирать компанию при создании заявки')
                    ->default(false),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Активно')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),
                
                Tables\Columns\IconColumn::make('has_custom_payer_selection')
                    ->label('Ручной выбор')
                    ->boolean()
                    ->trueIcon('heroicon-o-hand-raised')
                    ->falseIcon('heroicon-o-cog')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                // Фильтр по ручному выбору плательщика
                Tables\Filters\TernaryFilter::make('has_custom_payer_selection')
                    ->label('Ручной выбор плательщика')
                    ->placeholder('Все')
                    ->trueLabel('С ручным выбором')
                    ->falseLabel('Без ручного выбора'),
                
                // Фильтр по активности
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
                
                // Фильтр по поиску в названии
                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Поиск по названию')
                            ->placeholder('Введите название...')
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['name'],
                            fn ($query, $name) => $query->where('name', 'like', "%{$name}%")
                        );
                    })
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить назначение'),
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
}
