<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkRequestResource\Pages;
use App\Models\WorkRequest;
use App\Models\Category;
use App\Models\Project;
use App\Models\Purpose;
use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkRequestResource extends Resource
{
    protected static ?string $model = WorkRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Учет работ';
    protected static ?string $navigationLabel = 'Заявки';
    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'заявка';
    protected static ?string $pluralModelLabel = 'Заявки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('request_number')
                            ->label('Номер заявки')
                            ->disabled()
                            ->default('auto-generated'),

                        Forms\Components\Select::make('initiator_id')
                            ->label('Инициатор')
                            ->relationship('initiator', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('brigadier_id')
                            ->label('Бригадир')
                            ->relationship('brigadier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // ЗАМЕНЯЕМ specialty_id на category_id
                        Forms\Components\Select::make('category_id')
                            ->label('Категория специалистов')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Автоматически определяем доступные типы исполнителей
                                $category = Category::find($state);
                                if ($category) {
                                    $hasOur = $category->hasOurExecutors();
                                    $hasContractors = $category->availableContractors()->isNotEmpty();
                                    
                                    // Сбрасываем тип исполнителя при смене категории
                                    $set('executor_type', null);
                                    
                                    // Обновляем опции типа исполнителя
                                    $options = [];
                                    if ($hasOur) $options['our_staff'] = 'Наш персонал';
                                    if ($hasContractors) {
                                        $contractors = $category->availableContractors();
                                        foreach ($contractors as $contractor) {
                                            $options["contractor_{$contractor->id}"] = "Подрядчик: {$contractor->name}";
                                        }
                                    }
                                    // Здесь нужно обновить options для executor_type
                                }
                            }),

                        Forms\Components\Select::make('work_type_id')
                            ->label('Вид работ')
                            ->relationship('workType', 'name')
                            ->searchable()
                            ->preload(),

                        // НОВОЕ: Адрес
                        Forms\Components\Select::make('address_id')
                            ->label('Адрес выполнения работ')
                            ->relationship('address', 'short_name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Address $record) => $record->short_name . ' - ' . $record->full_address),
                    ])->columns(2),

                Forms\Components\Section::make('Проект и назначение')
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Проект')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('purpose_id')
                            ->label('Назначение')
                            ->relationship('purpose', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Дата и параметры работ')
                    ->schema([
                        Forms\Components\DatePicker::make('work_date')
                            ->label('Дата выполнения работ')
                            ->required()
                            ->native(false),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Время начала работ')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i'),

                        Forms\Components\Select::make('executor_type')
                            ->label('Тип исполнителя')
                            ->options(function ($get) {
                                $categoryId = $get('category_id');
                                if (!$categoryId) return [];
                                
                                $category = Category::find($categoryId);
                                if (!$category) return [];
                                
                                $options = [];
                                if ($category->hasOurExecutors()) {
                                    $options['our_staff'] = 'Наш персонал';
                                }
                                
                                $contractors = $category->availableContractors();
                                foreach ($contractors as $contractor) {
                                    $options["contractor_{$contractor->id}"] = "Подрядчик: {$contractor->name}";
                                }
                                
                                return $options;
                            })
                            ->required()
                            ->live(),

                        // НОВОЕ: ФИО исполнителей
                        Forms\Components\Textarea::make('executor_names')
                            ->label('ФИО исполнителей')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Для персонализированных: Иванов Иван, Петров Петр...')
                            ->helperText(function ($get) {
                                $executorType = $get('executor_type');
                                if (str_starts_with($executorType, 'contractor_')) {
                                    return 'Укажите ФИО конкретных исполнителей или оставьте пустым для обезличенного персонала';
                                }
                                return 'Укажите ФИО наших исполнителей';
                            }),

                        Forms\Components\TextInput::make('workers_count')
                            ->label('Количество рабочих')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        // ПЕРЕИМЕНОВАНО: shift_duration → estimated_shift_duration
                        Forms\Components\TextInput::make('estimated_shift_duration')
                            ->label('Ориентировочная продолжительность смены (часы)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->step(0.5),
                    ])->columns(2),

                Forms\Components\Section::make('Финансовая информация')
                    ->schema([
                        Forms\Components\TextInput::make('selected_payer_company')
                            ->label('Компания-плательщик')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_custom_payer')
                            ->label('Ручной выбор плательщика')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        // ПЕРЕИМЕНОВАНО: comments → additional_info
                        Forms\Components\Textarea::make('additional_info')
                            ->label('Дополнительная информация')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'draft' => 'Черновик',
                                'published' => 'Опубликована',
                                'in_progress' => 'В работе',
                                'staffed' => 'Укомплектована',
                                'completed' => 'Завершена',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\Select::make('dispatcher_id')
                            ->label('Диспетчер')
                            ->relationship('dispatcher', 'name')
                            ->searchable()
                            ->preload(),

                        // НОВОЕ: Общее кол-во отработанных часов
                        Forms\Components\TextInput::make('total_worked_hours')
                            ->label('Общее кол-во отработанных часов')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.1)
                            ->disabled()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('work_date')
                    ->label('Дата работ')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Время начала')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('initiator.name')
                    ->label('Инициатор')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('brigadier.name')
                    ->label('Бригадир')
                    ->searchable()
                    ->sortable(),

                // ОБНОВЛЯЕМ: category вместо specialty
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Категория')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('executor_type')
                    ->label('Тип')
                    ->formatStateUsing(function ($state) {
                        if ($state === 'our_staff') return 'Наш персонал';
                        if (str_starts_with($state, 'contractor_')) {
                            $contractorId = str_replace('contractor_', '', $state);
                            $contractor = \App\Models\Contractor::find($contractorId);
                            return $contractor ? "Подрядчик: {$contractor->name}" : 'Подрядчик';
                        }
                        return $state;
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'our_staff' ? 'success' : 'warning'),

                Tables\Columns\TextColumn::make('workers_count')
                    ->label('Кол-во')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_shift_duration')
                    ->label('Продолжительность')
                    ->suffix(' ч')
                    ->sortable(),

                // НОВОЕ: ФИО исполнителей (укороченное)
                Tables\Columns\TextColumn::make('executor_names')
                    ->label('Исполнители')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->executor_names)
                    ->placeholder('Не указаны'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'draft' => 'gray',
                        'published' => 'info',
                        'in_progress' => 'warning',
                        'staffed' => 'success',
                        'completed' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_worked_hours')
                    ->label('Отработано часов')
                    ->suffix(' ч')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликована',
                        'in_progress' => 'В работе',
                        'staffed' => 'Укомплектована',
                        'completed' => 'Завершена',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('work_date')
                    ->label('Дата работ')
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
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                Tables\Actions\ViewAction::make()
                    ->label('Просмотреть'),
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
            // Связи со сменами и т.д.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkRequests::route('/'),
            'create' => Pages\CreateWorkRequest::route('/create'),
            'edit' => Pages\EditWorkRequest::route('/{record}/edit'),
        ];
    }
}
