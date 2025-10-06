<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractorResource\Pages;
use App\Filament\Resources\ContractorResource\RelationManagers;
use App\Models\Contractor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractorResource extends Resource
{
    protected static ?string $model = Contractor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Название компании'),
                    
                Forms\Components\TextInput::make('contact_person')
                    ->required()
                    ->maxLength(255)
                    ->label('Контактное лицо'),
                    
                Forms\Components\TextInput::make('phone')
                    ->required()
                    ->maxLength(255)
                    ->label('Телефон'),
                    
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->label('Email'),
                    
                Forms\Components\Select::make('specializations')
                    ->multiple()
                    ->options([
                        'администраторы' => 'Администраторы',
                        'декораторы' => 'Декораторы',
                        'помощник садовника' => 'Помощник садовника',
                        'садовники' => 'Садовники',
                        'садовники (хим. обработка)' => 'Садовники (хим. обработка)',
                        'специалисты по озеленению' => 'Специалисты по озеленению',
                        'старшие администраторы' => 'Старшие администраторы',
                        'старшие декораторы' => 'Старшие декораторы',
                        'старшие садовники' => 'Старшие садовники',
                        'установщик деревьев' => 'Установщик деревьев',
                        'штатные специалисты' => 'Штатные специалисты',
                    ])
                    ->required()
                    ->label('Специализации'),
                    
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true)
                    ->label('Активен'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Название компании'),
                    
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->sortable()
                    ->label('Контактное лицо'),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->label('Телефон'),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),
                    
                Tables\Columns\TextColumn::make('specializations')
                    ->label('Специализации')
                    ->formatStateUsing(function ($state) {
                        return is_array($state) ? implode(', ', $state) : $state;
                    })
                    ->wrap(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Активен')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Создан'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Обновлен'),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Только активные')
                    ->query(fn (Builder $query) => $query->where('is_active', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListContractors::route('/'),
            'create' => Pages\CreateContractor::route('/create'),
            'edit' => Pages\EditContractor::route('/{record}/edit'),
        ];
    }
}
