<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Filament\Resources\ShiftResource\RelationManagers;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('request_id')
                    ->relationship('workRequest', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Заявка'),
                    
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Исполнитель (наш персонал)')
                    ->visible(fn ($get) => !$get('contractor_id')),
                    
                Forms\Components\Select::make('contractor_id')
                    ->relationship('contractor', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Подрядчик')
                    ->visible(fn ($get) => !$get('user_id')),
                    
                Forms\Components\TextInput::make('contractor_worker_name')
                    ->maxLength(255)
                    ->label('ФИО работника подрядчика')
                    ->visible(fn ($get) => $get('contractor_id')),
                    
                Forms\Components\DatePicker::make('work_date')
                    ->required()
                    ->label('Дата работы'),
                    
                Forms\Components\TimePicker::make('start_time')
                    ->label('Время начала'),
                    
                Forms\Components\TimePicker::make('end_time')
                    ->label('Время окончания'),
                    
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Запланирована',
                        'started' => 'Начата',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                        'no_show' => 'Неявка',
                    ])
                    ->required()
                    ->default('scheduled')
                    ->label('Статус смены'),
                    
                Forms\Components\DateTimePicker::make('shift_started_at')
                    ->label('Фактическое время начала'),
                    
                Forms\Components\DateTimePicker::make('shift_ended_at')
                    ->label('Фактическое время окончания'),
                    
                Forms\Components\Textarea::make('notes')
                    ->label('Примечания')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('workRequest.request_number')
                    ->searchable()
                    ->sortable()
                    ->label('Номер заявки'),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Исполнитель')
                    ->visible(fn ($record) => $record?->user_id), // Исправлено: добавлен ?
                    
                Tables\Columns\TextColumn::make('contractor.name')
                    ->searchable()
                    ->sortable()
                    ->label('Подрядчик')
                    ->visible(fn ($record) => $record?->contractor_id), // Исправлено: добавлен ?
                    
                Tables\Columns\TextColumn::make('contractor_worker_name')
                    ->searchable()
                    ->sortable()
                    ->label('Работник подрядчика')
                    ->visible(fn ($record) => $record?->contractor_id), // Исправлено: добавлен ?
                    
                Tables\Columns\TextColumn::make('work_date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->label('Дата работы'),
                    
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i')
                    ->sortable()
                    ->label('Начало'),
                    
                Tables\Columns\TextColumn::make('end_time')
                    ->time('H:i')
                    ->sortable()
                    ->label('Окончание'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Запланирована',
                        'started' => 'Начата',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                        'no_show' => 'Неявка',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'started' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'danger',
                    })
                    ->label('Статус'),
                    
                Tables\Columns\TextColumn::make('shift_started_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Факт. начало'),
                    
                Tables\Columns\TextColumn::make('shift_ended_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Факт. окончание'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Создана'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Запланирована',
                        'started' => 'Начата',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                        'no_show' => 'Неявка',
                    ])
                    ->label('Статус'),
                    
                Tables\Filters\SelectFilter::make('workRequest.initiator_id')
                    ->relationship('workRequest.initiator', 'name')
                    ->label('Инициатор заявки')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('work_date')
                    ->form([
                        Forms\Components\DatePicker::make('work_date_from')
                            ->label('С даты'),
                        Forms\Components\DatePicker::make('work_date_to')
                            ->label('По дату'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['work_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('work_date', '>=', $date),
                            )
                            ->when(
                                $data['work_date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('work_date', '<=', $date),
                            );
                    })
                    ->label('Дата работы'),
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
            ->defaultSort('work_date', 'desc');
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
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
