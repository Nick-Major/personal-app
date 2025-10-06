<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrigadierAssignmentResource\Pages;
use App\Filament\Resources\BrigadierAssignmentResource\RelationManagers;
use App\Models\BrigadierAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrigadierAssignmentResource extends Resource
{
    protected static ?string $model = BrigadierAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('brigadier_id')
                    ->relationship('brigadier', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Бригадир'),
                    
                Forms\Components\Select::make('initiator_id')
                    ->relationship('initiator', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Инициатор'),
                    
                Forms\Components\DatePicker::make('assignment_date')
                    ->required()
                    ->label('Дата назначения'),
                    
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Ожидает подтверждения',
                        'confirmed' => 'Подтверждено',
                        'rejected' => 'Отклонено',
                    ])
                    ->required()
                    ->default('pending')
                    ->label('Статус'),
                    
                Forms\Components\DateTimePicker::make('confirmed_at')
                    ->label('Дата подтверждения'),
                    
                Forms\Components\DateTimePicker::make('rejected_at')
                    ->label('Дата отклонения'),
                    
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Причина отклонения')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brigadier.name')
                    ->searchable()
                    ->sortable()
                    ->label('Бригадир'),
                    
                Tables\Columns\TextColumn::make('initiator.name')
                    ->searchable()
                    ->sortable()
                    ->label('Инициатор'),
                    
                Tables\Columns\TextColumn::make('assignment_date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->label('Дата назначения'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Ожидает',
                        'confirmed' => 'Подтверждено',
                        'rejected' => 'Отклонено',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                    })
                    ->label('Статус'),
                    
                Tables\Columns\TextColumn::make('confirmed_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Подтверждено'),
                    
                Tables\Columns\TextColumn::make('rejected_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Отклонено'),
                    
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Причина отклонения'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Создано'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Ожидает',
                        'confirmed' => 'Подтверждено', 
                        'rejected' => 'Отклонено',
                    ])
                    ->label('Статус'),
                    
                Tables\Filters\SelectFilter::make('brigadier_id')
                    ->relationship('brigadier', 'name')
                    ->label('Бригадир')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('assignment_date')
                    ->form([
                        Forms\Components\DatePicker::make('assignment_from')
                            ->label('С даты'),
                        Forms\Components\DatePicker::make('assignment_to')
                            ->label('По дату'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['assignment_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('assignment_date', '>=', $date),
                            )
                            ->when(
                                $data['assignment_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('assignment_date', '<=', $date),
                            );
                    })
                    ->label('Дата назначения'),
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
            ->defaultSort('assignment_date', 'desc');
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
            'index' => Pages\ListBrigadierAssignments::route('/'),
            'create' => Pages\CreateBrigadierAssignment::route('/create'),
            'edit' => Pages\EditBrigadierAssignment::route('/{record}/edit'),
        ];
    }
}
