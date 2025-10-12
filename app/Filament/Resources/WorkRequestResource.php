<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkRequestResource\Pages;
use App\Models\WorkRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkRequestResource extends Resource
{
    protected static ?string $model = WorkRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

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
                    
                Forms\Components\Section::make('Дата и параметры работ')
                    ->schema([
                        Forms\Components\DatePicker::make('work_date')
                            ->label('Дата выполнения работ')
                            ->required()
                            ->native(false),

                            // ДОБАВЬТЕ ЭТО ПОЛЕ
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Время начала работ')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i'),
                            
                        Forms\Components\Select::make('executor_type')
                            ->label('Тип исполнителя')
                            ->options([
                                'our_staff' => 'Наш персонал',
                                'contractor' => 'Подрядчик',
                            ])
                            ->required(),
                            
                        Forms\Components\TextInput::make('workers_count')
                            ->label('Количество рабочих')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                            
                        Forms\Components\TextInput::make('shift_duration')
                            ->label('Продолжительность смены (часы)')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Финансовая информация')
                    ->schema([
                        Forms\Components\TextInput::make('project')
                            ->label('Проект')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('purpose')
                            ->label('Назначение')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('payer_company')
                            ->label('Компания-плательщик')
                            ->maxLength(255),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Textarea::make('comments')
                            ->label('Комментарии')
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
                    ->date()
                    ->sortable(),

                    // ДОБАВЬТЕ ЭТУ КОЛОНКУ
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
                    
                Tables\Columns\TextColumn::make('specialty.name')
                    ->label('Специальность')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('executor_type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($state) => $state === 'our_staff' ? 'Наш' : 'Подрядчик')
                    ->badge()
                    ->color(fn ($state) => $state === 'our_staff' ? 'success' : 'warning'),
                    
                Tables\Columns\TextColumn::make('workers_count')
                    ->label('Кол-во')
                    ->sortable(),
                    
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
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime()
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
                    
                Tables\Filters\SelectFilter::make('executor_type')
                    ->label('Тип исполнителя')
                    ->options([
                        'our_staff' => 'Наш персонал',
                        'contractor' => 'Подрядчик',
                    ]),
                    
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
