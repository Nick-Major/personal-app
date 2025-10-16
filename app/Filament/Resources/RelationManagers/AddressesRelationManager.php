<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Адреса проекта';

    protected static ?string $label = 'адрес';
    
    protected static ?string $pluralLabel = 'Адреса';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название места')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('full_address')
                    ->label('Полный адрес')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                
                Forms\Components\Textarea::make('description')
                    ->label('Описание')
                    ->rows(2)
                    ->columnSpanFull(),
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
                
                Tables\Columns\TextColumn::make('full_address')
                    ->label('Адрес')
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(30),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить адрес'),
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
