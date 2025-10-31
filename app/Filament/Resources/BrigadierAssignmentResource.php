<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrigadierAssignmentResource\Pages;
use App\Models\BrigadierAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrigadierAssignmentResource extends Resource
{
    protected static ?string $model = BrigadierAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Управление персоналом';
    protected static ?string $navigationLabel = 'Назначения бригадиров';
    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'назначение бригадира';
    protected static ?string $pluralModelLabel = 'Назначения бригадиров';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Назначение бригадира')
                    ->schema([
                        Forms\Components\Select::make('brigadier_id')
                            ->label('Бригадир')
                            ->relationship('brigadier', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name),
                            
                        Forms\Components\Select::make('initiator_id')
                            ->label('Инициатор назначения')
                            ->relationship('initiator', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name),
                            
                        Forms\Components\Toggle::make('can_create_requests')
                            ->label('Может создавать заявки')
                            ->default(false),

                        Forms\Components\Textarea::make('comment')
                            ->label('Комментарий к назначению')
                            ->maxLength(65535)
                            ->rows(3)
                            ->placeholder('Укажите дополнительную информацию для бригадира...')
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Плановый адрес')
                    ->schema([
                        Forms\Components\Select::make('planned_address_id')
                            ->label('Официальный адрес')
                            ->relationship('plannedAddress', 'short_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                            
                        Forms\Components\Textarea::make('planned_custom_address')
                            ->label('Неофициальный адрес')
                            ->maxLength(65535)
                            ->rows(2)
                            ->placeholder('Введите адрес вручную...')
                            ->nullable(),
                            
                        Forms\Components\Toggle::make('is_custom_planned_address')
                            ->label('Использовать неофициальный адрес')
                            ->default(false),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Даты назначения')
                    ->description('Укажите даты, на которые назначается бригадир')
                    ->schema([
                        Forms\Components\Repeater::make('assignment_dates')
                            ->label('Даты назначения')
                            ->relationship('assignment_dates')
                            ->schema([
                                Forms\Components\DatePicker::make('assignment_date')
                                    ->label('Дата')
                                    ->required()
                                    ->native(false),
                                    
                                Forms\Components\Select::make('status')
                                    ->label('Статус подтверждения')
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
                                    ->visible(fn (callable $get) => $get('status') === 'rejected'),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['assignment_date'] ?? null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brigadier.full_name')
                    ->label('Бригадир')
                    ->searchable(['name', 'surname'])
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('initiator.full_name')
                    ->label('Инициатор')
                    ->searchable(['name', 'surname'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('can_create_requests')
                    ->label('Может создавать заявки')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('dates_count')
                    ->label('Кол-во дат')
                    ->getStateUsing(fn ($record) => $record->assignment_dates()->count())
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('confirmed_dates_count')
                    ->label('Подтверждено дат')
                    ->getStateUsing(fn ($record) => $record->assignment_dates()->where('status', 'confirmed')->count())
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('planned_address.short_name')
                    ->label('Плановый адрес')
                    ->placeholder('Не указан')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\Filter::make('can_create_requests')
                    ->label('Может создавать заявки')
                    ->query(fn ($query) => $query->where('can_create_requests', true)),
                    
                Tables\Filters\SelectFilter::make('brigadier_id')
                    ->label('Бригадир')
                    ->relationship('brigadier', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('initiator_id')
                    ->label('Инициатор')
                    ->relationship('initiator', 'name')
                    ->searchable()
                    ->preload(),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
