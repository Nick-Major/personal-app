<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InitiatorGrantResource\Pages;
use App\Filament\Resources\InitiatorGrantResource\RelationManagers;
use App\Models\InitiatorGrant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InitiatorGrantResource extends Resource
{
    protected static ?string $model = InitiatorGrant::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('initiator_id')
                    ->relationship('initiator', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Инициатор (кто выдал права)'),
                    
                Forms\Components\Select::make('brigadier_id')
                    ->relationship('brigadier', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Бригадир (кому выданы права)'),
                    
                Forms\Components\Toggle::make('is_temporary')
                    ->required()
                    ->default(false)
                    ->label('Временные права')
                    ->reactive(),
                    
                Forms\Components\DatePicker::make('expires_at')
                    ->label('Срок действия')
                    ->visible(fn ($get) => $get('is_temporary')),
                    
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true)
                    ->label('Активно'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('initiator.name')
                    ->searchable()
                    ->sortable()
                    ->label('Инициатор (кто выдал)'),
                    
                Tables\Columns\TextColumn::make('brigadier.name')
                    ->searchable()
                    ->sortable()
                    ->label('Бригадир (кому выдано)'),
                    
                Tables\Columns\IconColumn::make('is_temporary')
                    ->boolean()
                    ->label('Временные')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('expires_at')
                    ->date('d.m.Y')
                    ->sortable()
                    ->label('Срок действия')
                    ->placeholder('Бессрочно'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Активно')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Создано'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Обновлено'),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('Только активные')
                    ->query(fn (Builder $query) => $query->where('is_active', true)),
                    
                Tables\Filters\Filter::make('is_temporary')
                    ->label('Только временные')
                    ->query(fn (Builder $query) => $query->where('is_temporary', true)),
                    
                Tables\Filters\SelectFilter::make('initiator_id')
                    ->relationship('initiator', 'name')
                    ->label('Инициатор')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('brigadier_id')
                    ->relationship('brigadier', 'name')
                    ->label('Бригадир')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListInitiatorGrants::route('/'),
            'create' => Pages\CreateInitiatorGrant::route('/create'),
            'edit' => Pages\EditInitiatorGrant::route('/{record}/edit'),
        ];
    }
}
