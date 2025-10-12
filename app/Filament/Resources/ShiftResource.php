<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('request_id')
                            ->label('Заявка')
                            ->relationship('workRequest', 'request_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\Select::make('user_id')
                            ->label('Исполнитель')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\Select::make('role')
                            ->label('Роль в смене')
                            ->options([
                                'executor' => 'Исполнитель',
                                'brigadier' => 'Бригадир',
                            ])
                            ->required()
                            ->default('executor'),
                            
                        Forms\Components\Select::make('specialty_id')
                            ->label('Специальность')
                            ->relationship('specialty', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\Select::make('work_type_id')
                            ->label('Вид работ')
                            ->relationship('workType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Дата и время')
                    ->schema([
                        Forms\Components\DatePicker::make('work_date')
                            ->label('Дата работы')
                            ->required()
                            ->native(false),
                            
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Время начала')
                            ->seconds(false)
                            ->required(),
                            
                        Forms\Components\TimePicker::make('end_time')
                            ->label('Время окончания')
                            ->seconds(false)
                            ->required(),
                            
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'planned' => 'Запланирована',
                                'active' => 'Активна',
                                'completed' => 'Завершена',
                                'cancelled' => 'Отменена',
                            ])
                            ->required()
                            ->default('planned'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Подрядчик (если применимо)')
                    ->schema([
                        Forms\Components\Select::make('contractor_id')
                            ->label('Подрядчик')
                            ->relationship('contractor', 'name')
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\TextInput::make('contractor_worker_name')
                            ->label('Имя рабочего от подрядчика')
                            ->maxLength(255),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Учет времени и расходов')
                    ->schema([
                        Forms\Components\TextInput::make('worked_minutes')
                            ->label('Отработано минут')
                            ->numeric()
                            ->minValue(0),
                            
                        Forms\Components\TextInput::make('lunch_minutes')
                            ->label('Обеденный перерыв (минуты)')
                            ->numeric()
                            ->minValue(0),
                            
                        Forms\Components\TextInput::make('travel_expense_amount')
                            ->label('Сумма дорожных расходов')
                            ->numeric()
                            ->minValue(0),
                            
                        Forms\Components\TextInput::make('hourly_rate_snapshot')
                            ->label('Ставка (снимок)')
                            ->numeric()
                            ->minValue(0),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Заметки')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('work_date')
                    ->label('Дата')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Исполнитель')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('role')
                    ->label('Роль')
                    ->formatStateUsing(fn ($state) => $state === 'brigadier' ? 'Бригадир' : 'Исполнитель')
                    ->badge()
                    ->color(fn ($state) => $state === 'brigadier' ? 'warning' : 'gray'),
                    
                Tables\Columns\TextColumn::make('workRequest.request_number')
                    ->label('Заявка')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('specialty.name')
                    ->label('Специальность')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'planned' => 'gray',
                        'active' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Начало')
                    ->time(),
                    
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Окончание')
                    ->time(),
                    
                Tables\Columns\TextColumn::make('worked_minutes')
                    ->label('Минуты')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} мин" : '-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Роль')
                    ->options([
                        'executor' => 'Исполнитель',
                        'brigadier' => 'Бригадир',
                    ]),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'planned' => 'Запланирована',
                        'active' => 'Активна',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                    ]),
                    
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Исполнитель')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('work_date')
                    ->label('Дата работы')
                    ->form([
                        Forms\Components\DatePicker::make('work_date_from')
                            ->label('С даты'),
                        Forms\Components\DatePicker::make('work_date_to')
                            ->label('По дату'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['work_date_from'], fn($q, $date) => $q->whereDate('work_date', '>=', $date))
                            ->when($data['work_date_to'], fn($q, $date) => $q->whereDate('work_date', '<=', $date));
                    }),
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
        return [
            // Связи с локациями, фото, расходами
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
