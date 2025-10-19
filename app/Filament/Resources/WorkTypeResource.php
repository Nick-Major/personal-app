<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkTypeResource\Pages;
use App\Models\WorkType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkTypeResource extends Resource
{
    protected static ?string $model = WorkType::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    // ДОБАВЛЯЕМ РУССКИЕ LABELS И ГРУППУ
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Типы работ';
    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'тип работ';
    protected static ?string $pluralModelLabel = 'Типы работ';

    public static function getPageLabels(): array
    {
        return [
            'index' => 'Типы работ',
            'create' => 'Создать тип работ',
            'edit' => 'Редактировать тип работ',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название типа работ')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Например: Монтаж, Демонтаж, Уход за растениями...')
                            ->validationMessages([
                                'unique' => 'Тип работ с таким названием уже существует',
                            ]),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('Подробное описание типа работ...')
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('category')
                            ->label('Категория')
                            ->options([
                                'construction' => 'Строительные работы',
                                'gardening' => 'Садовые работы',
                                'decoration' => 'Декоративные работы',
                                'maintenance' => 'Обслуживание',
                                'technical' => 'Технические работы',
                                'other' => 'Другое',
                            ])
                            ->default('other')
                            ->required()
                            ->helperText('Выберите категорию для группировки типов работ'),
                            
                        Forms\Components\Toggle::make('requires_special_equipment')
                            ->label('Требует спецоборудования')
                            ->default(false)
                            ->helperText('Отметьте если для этого типа работ требуется специальное оборудование'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активный тип работ')
                            ->default(true)
                            ->helperText('Неактивные типы работ не будут показываться при выборе'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Дополнительные настройки')
                    ->schema([
                        Forms\Components\TextInput::make('default_duration_hours')
                            ->label('Продолжительность по умолчанию (часы)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.5)
                            ->placeholder('0')
                            ->helperText('Стандартная продолжительность работ в часах'),
                            
                        Forms\Components\TextInput::make('complexity_level')
                            ->label('Уровень сложности')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->default(1)
                            ->helperText('Уровень сложности от 1 (простой) до 5 (сложный)'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('category')
                    ->label('Категория')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'construction' => '🏗️ Строительные',
                        'gardening' => '🌿 Садовые',
                        'decoration' => '🎨 Декоративные',
                        'maintenance' => '🔧 Обслуживание',
                        'technical' => '⚙️ Технические',
                        'other' => '📁 Другое',
                        default => $state
                    })
                    ->color(fn ($state) => match($state) {
                        'construction' => 'warning',
                        'gardening' => 'success',
                        'decoration' => 'pink',
                        'maintenance' => 'info',
                        'technical' => 'gray',
                        'other' => 'gray',
                        default => 'gray'
                    })
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('requires_special_equipment')
                    ->label('Спецоборудование')
                    ->boolean()
                    ->trueIcon('heroicon-o-cog')
                    ->falseIcon('heroicon-o-wrench-screwdriver')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('default_duration_hours')
                    ->label('Продолжительность')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} ч" : 'Не указана')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('complexity_level')
                    ->label('Сложность')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        1 => 'success',
                        2 => 'success',
                        3 => 'warning',
                        4 => 'warning',
                        5 => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state))
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                    
                // Tables\Columns\TextColumn::make('work_requests_count')
                //     ->label('Заявок')
                //     ->counts('workRequests')
                //     ->sortable()
                //     ->badge()
                //     ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Категория')
                    ->options([
                        'construction' => 'Строительные работы',
                        'gardening' => 'Садовые работы',
                        'decoration' => 'Декоративные работы',
                        'maintenance' => 'Обслуживание',
                        'technical' => 'Технические работы',
                        'other' => 'Другое',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('requires_special_equipment')
                    ->label('Спецоборудование')
                    ->placeholder('Все типы работ')
                    ->trueLabel('Требуют спецоборудования')
                    ->falseLabel('Не требуют спецоборудования'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->placeholder('Все типы работ')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
                    
                Tables\Filters\Filter::make('complexity_level')
                    ->label('Уровень сложности')
                    ->form([
                        Forms\Components\Select::make('complexity_level')
                            ->label('Уровень сложности')
                            ->options([
                                1 => '⭐ Очень простой',
                                2 => '⭐⭐ Простой',
                                3 => '⭐⭐⭐ Средний',
                                4 => '⭐⭐⭐⭐ Сложный',
                                5 => '⭐⭐⭐⭐⭐ Очень сложный',
                            ])
                            ->multiple(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['complexity_level'],
                            fn($query, $levels) => $query->whereIn('complexity_level', $levels)
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                    
                // Tables\Actions\Action::make('view_work_requests')
                //     ->label('Заявки')
                //     ->icon('heroicon-o-document-text')
                //     ->url(fn (WorkType $record) => \App\Filament\Resources\WorkRequestResource::getUrl('index', [
                //         'tableFilters[work_type_id][values]' => [$record->id]
                //     ]))
                //     ->color('gray'),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->emptyStateHeading('Нет типов работ')
            ->emptyStateDescription('Создайте первый тип работ.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Создать тип работ'),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            // Можно добавить RelationManager для заявок с этим типом работ
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkTypes::route('/'),
            'create' => Pages\CreateWorkType::route('/create'),
            'edit' => Pages\EditWorkType::route('/{record}/edit'),
        ];
    }
}
