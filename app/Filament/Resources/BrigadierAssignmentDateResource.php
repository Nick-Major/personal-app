<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrigadierAssignmentDateResource\Pages;
use App\Models\BrigadierAssignmentDate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrigadierAssignmentDateResource extends Resource
{
    protected static ?string $model = BrigadierAssignmentDate::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Управление персоналом';
    protected static ?string $navigationLabel = 'Даты назначений бригадиров';
    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'дата назначения';
    protected static ?string $pluralModelLabel = 'Даты назначений бригадиров';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('assignment_id')
                    ->label('Назначение')
                    ->relationship('assignment', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->brigadier->full_name} → {$record->initiator->full_name}")
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\DatePicker::make('assignment_date')
                    ->label('Дата назначения')
                    ->required()
                    ->native(false),
                    
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'confirmed' => 'Подтверждено',
                        'rejected' => 'Отклонено',
                    ])
                    ->required()
                    ->default('pending'),
                    
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Причина отказа')
                    ->maxLength(65535)
                    ->rows(3)
                    ->visible(fn (callable $get) => $get('status') === 'rejected'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assignment.brigadier.full_name')
                    ->label('Бригадир')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('assignment.initiator.full_name')
                    ->label('Инициатор')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('assignment_date')
                    ->label('Дата назначения')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                    
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('Причина отказа')
                    ->limit(30)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'confirmed' => 'Подтверждено',
                        'rejected' => 'Отклонено',
                    ]),
                    
                Tables\Filters\SelectFilter::make('assignment_id')
                    ->label('Назначение')
                    ->relationship('assignment', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->brigadier->full_name} → {$record->initiator->full_name}")
                    ->searchable()
                    ->preload(),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrigadierAssignmentDates::route('/'),
            'create' => Pages\CreateBrigadierAssignmentDate::route('/create'),
            'edit' => Pages\EditBrigadierAssignmentDate::route('/{record}/edit'),
        ];
    }
}
