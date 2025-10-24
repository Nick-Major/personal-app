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
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('user_id')
                            ->label('Исполнитель')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                // Автоматически определяем налоговый статус при выборе исполнителя
                                if ($state) {
                                    $user = \App\Models\User::find($state);
                                    if ($user && $user->tax_status_id) {
                                        $set('tax_status_id', $user->tax_status_id);
                                    }
                                    if ($user && $user->contract_type_id) {
                                        $set('contract_type_id', $user->contract_type_id);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('contractor_id')
                            ->label('Подрядчик')
                            ->relationship('contractor', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                // Автоматически определяем налоговый статус при выборе подрядчика
                                if ($state) {
                                    $contractor = \App\Models\Contractor::find($state);
                                    if ($contractor && $contractor->tax_status_id) {
                                        $set('tax_status_id', $contractor->tax_status_id);
                                    }
                                    if ($contractor && $contractor->contract_type_id) {
                                        $set('contract_type_id', $contractor->contract_type_id);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('contractor_worker_name')
                            ->label('Имя рабочего от подрядчика')
                            ->maxLength(255)
                            ->visible(fn (callable $get) => $get('contractor_id') && !$get('user_id')),

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
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('work_type_id')
                            ->label('Вид работ (для аналитики)')
                            ->relationship('workType', 'name')
                            ->searchable()
                            ->preload(),

                        // НОВЫЕ ПОЛЯ ДЛЯ НАЛОГОВОЙ СИСТЕМЫ
                        Forms\Components\Select::make('contract_type_id')
                            ->label('Тип договора')
                            ->relationship('contractType', 'name')
                            ->searchable()
                            ->preload()
                            ->live(),

                        Forms\Components\Select::make('tax_status_id')
                            ->label('Налоговый статус')
                            ->relationship('taxStatus', 'name')
                            ->searchable()
                            ->preload()
                            ->live(),
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
                            ->default('planned')
                            ->live(),

                        Forms\Components\TextInput::make('worked_minutes')
                            ->label('Отработано минут')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Автоматически пересчитывается в часы')
                            ->live(),
                    ])->columns(2),

                // 🔄 ОБНОВЛЕННАЯ СЕКЦИЯ ДЛЯ РАСЧЕТОВ ПО НОВОЙ ФОРМУЛЕ
                Forms\Components\Section::make('Расчет оплаты')
                    ->schema([
                        Forms\Components\Toggle::make('no_lunch')
                            ->label('Работа без обеда')
                            ->helperText('Бригадир подтверждает отсутствие обеда')
                            ->default(false)
                            ->live(),

                        Forms\Components\TextInput::make('hourly_rate_snapshot')
                            ->label('Ставка (руб/час)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Автоматически определяется по специальности')
                            ->live(),

                        Forms\Components\Placeholder::make('gross_amount_info')
                            ->label('Общая сумма на руки')
                            ->content(function (callable $get) {
                                $hours = $get('worked_minutes') / 60;
                                $rate = $get('hourly_rate_snapshot') ?? 0;
                                $grossAmount = $hours * $rate;
                                return number_format($grossAmount, 0, ',', ' ') . ' ₽';
                            })
                            ->extraAttributes(['class' => 'font-bold text-lg text-green-600']),

                        Forms\Components\Placeholder::make('tax_amount_info')
                            ->label('Налог')
                            ->content(function (callable $get) {
                                $hours = $get('worked_minutes') / 60;
                                $rate = $get('hourly_rate_snapshot') ?? 0;
                                $grossAmount = $hours * $rate;
                                $taxRate = \App\Models\TaxStatus::find($get('tax_status_id'))?->tax_rate ?? 0;
                                $taxAmount = $grossAmount * $taxRate;
                                return number_format($taxAmount, 0, ',', ' ') . ' ₽ (' . ($taxRate * 100) . '%)';
                            })
                            ->extraAttributes(['class' => 'text-red-600']),

                        Forms\Components\Placeholder::make('net_amount_info')
                            ->label('Сумма к оплате (после налога)')
                            ->content(function (callable $get) {
                                $hours = $get('worked_minutes') / 60;
                                $rate = $get('hourly_rate_snapshot') ?? 0;
                                $grossAmount = $hours * $rate;
                                $taxRate = \App\Models\TaxStatus::find($get('tax_status_id'))?->tax_rate ?? 0;
                                $netAmount = $grossAmount * (1 - $taxRate);
                                return number_format($netAmount, 0, ',', ' ') . ' ₽';
                            })
                            ->extraAttributes(['class' => 'font-bold text-lg text-blue-600']),
                    ])->columns(2),

                Forms\Components\Section::make('Операционные расходы')
                    ->schema([
                        Forms\Components\Placeholder::make('expenses_info')
                            ->label('Операционные расходы')
                            ->content(fn ($record) => $record ? number_format($record->expenses_amount, 0, ',', ' ') . ' ₽' : '0 ₽')
                            ->helperText('Такси, транспортные расходы, инвентарь (подтверждает инициатор/диспетчер)'),
                    ]),

                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Заметки')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Оплачено')
                            ->default(false)
                            ->helperText('Отметьте, когда смена оплачена'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('work_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Исполнитель')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn ($record) => $record->contractor_worker_name ?: '—'),

                Tables\Columns\TextColumn::make('contractor.name')
                    ->label('Подрядчик')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('workRequest.request_number')
                    ->label('Заявка')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('specialty.name')
                    ->label('Специальность')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contractType.name')
                    ->label('Тип договора')
                    ->badge()
                    ->color('gray')
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('taxStatus.name')
                    ->label('Налог')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $state ? ($record->taxStatus?->tax_rate * 100) . '%' : '—')
                    ->color(fn ($state) => $state ? 'primary' : 'gray')
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('worked_minutes')
                    ->label('Часы')
                    ->formatStateUsing(fn ($state) => $state ? round($state / 60, 1) . ' ч' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gross_amount')
                    ->label('На руки')
                    ->money('RUB')
                    ->sortable()
                    ->color('success')
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('amount_to_pay')
                    ->label('К оплате')
                    ->money('RUB')
                    ->sortable()
                    ->color('blue')
                    ->weight('medium'),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Оплата')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray')
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'planned' => 'Запланирована',
                        'active' => 'Активна',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                    ]),

                Tables\Filters\SelectFilter::make('contract_type')
                    ->label('Тип договора')
                    ->relationship('contractType', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Оплата')
                    ->placeholder('Все смены')
                    ->trueLabel('Только оплаченные')
                    ->falseLabel('Только неоплаченные'),
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
