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
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('category')
                    ->label('Категория')
                    ->options([
                        'construction' => 'Застройка',
                        'installation' => 'Монтаж/Демонтаж',
                        'maintenance' => 'Уход',
                        'administrative' => 'Административные',
                        'other' => 'Прочие',
                    ])
                    ->required(),
                
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
                
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Категория')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'construction' => 'Застройка',
                        'installation' => 'Монтаж/Демонтаж',
                        'maintenance' => 'Уход',
                        'administrative' => 'Административные',
                        'other' => 'Прочие',
                    })
                    ->colors([
                        'warning' => 'construction',
                        'primary' => 'installation', 
                        'success' => 'maintenance',
                        'gray' => 'administrative',
                        'info' => 'other',
                    ]),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean(),
            ])
            ->filters([
                //
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
