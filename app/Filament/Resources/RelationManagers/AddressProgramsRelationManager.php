<?php

namespace App\Filament\Resources\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressProgramsRelationManager extends RelationManager
{
    protected static string $relationship = 'addressPrograms';

    protected static ?string $title = 'Адресные программы';

    protected static ?string $label = 'адресную программу';
    
    protected static ?string $pluralLabel = 'Адресные программы';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('address_id')
                    ->label('Адрес')
                    ->relationship('address', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\TextInput::make('order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Активно')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('address.name')
            ->columns([
                Tables\Columns\TextColumn::make('address.name')
                    ->label('Адрес')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('address.address')
                    ->label('Полный адрес')
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('order')
                    ->label('Порядок')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить адресную программу'),
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
