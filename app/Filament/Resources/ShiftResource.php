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
    protected static ?string $navigationGroup = 'Учет работ';
    protected static ?string $navigationLabel = 'Смены';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'смена';
    protected static ?string $pluralModelLabel = 'Смены';

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

                // 🔧 ДОБАВЛЯЕМ НОВУЮ СЕКЦИЮ ДЛЯ РАСЧЕТОВ
                Forms\Components\Section::make('Настройки расчета')
                    ->schema([
                        Forms\Components\Toggle::make('no_lunch')
                            ->label('Работа без обеда')
                            ->helperText('+1 дополнительный час к оплате')
                            ->default(false),
                            
                        Forms\Components\Toggle::make('has_transport_fee')
                            ->label('Транспортная надбавка')
                            ->helperText('Фиксированная сумма за трансфер')
                            ->default(false),
                            
                        Forms\Components\TextInput::make('base_rate')
                            ->label('Базовая ставка (руб/час)')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->default(0)
                            ->helperText('Ставка специальности + надбавка вида работ'),
                            
                        Forms\Components\TextInput::make('worked_minutes')
                            ->label('Отработано минут')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Автоматически пересчитывается в часы'),
                    ])->columns(2),

                Forms\Components\Section::make('Результаты расчета')
                    ->schema([
                        Forms\Components\Placeholder::make('base_amount')
                            ->label('Базовая сумма')
                            ->content(fn ($record) => $record ? number_format($record->base_amount, 0, ',', ' ') . ' ₽' : '0 ₽'),
                            
                        Forms\Components\Placeholder::make('no_lunch_bonus')
                            ->label('Бонус за обед')
                            ->content(fn ($record) => $record ? number_format($record->no_lunch_bonus, 0, ',', ' ') . ' ₽' : '0 ₽'),
                            
                        Forms\Components\Placeholder::make('transport_fee_amount')
                            ->label('Транспорт')
                            ->content(fn ($record) => $record ? number_format($record->transport_fee_amount, 0, ',', ' ') . ' ₽' : '0 ₽'),
                            
                        Forms\Components\Placeholder::make('expenses_amount')
                            ->label('Операционные расходы')
                            ->content(fn ($record) => $record ? number_format($record->expenses_amount, 0, ',', ' ') . ' ₽' : '0 ₽'),
                            
                        Forms\Components\Placeholder::make('calculated_total')
                            ->label('ИТОГО к выплате')
                            ->content(fn ($record) => $record ? number_format($record->calculated_total, 0, ',', ' ') . ' ₽' : '0 ₽')
                            ->extraAttributes(['class' => 'font-bold text-lg']),
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

                // 🔧 ДОБАВЛЯЕМ НОВЫЕ КОЛОНКИ
                Tables\Columns\IconColumn::make('no_lunch')
                    ->label('Без обеда')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\IconColumn::make('has_transport_fee')
                    ->label('Транспорт')
                    ->boolean()
                    ->trueIcon('heroicon-o-truck')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('warning')
                    ->falseColor('gray'),

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

                Tables\Columns\TextColumn::make('calculated_total')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->color('success')
                    ->weight('medium'),

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
                // ... существующие фильтры остаются без изменений ...
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('work_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Можно добавить связь с расходами
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
