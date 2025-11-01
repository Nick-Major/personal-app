<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Управление персоналом';
    protected static ?string $navigationLabel = 'Пользователи';
    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'пользователь';
    protected static ?string $pluralModelLabel = 'Пользователи';

    public static function getPageLabels(): array
    {
        return [
            'index' => 'Пользователи',
            'create' => 'Создать пользователя',
            'edit' => 'Редактировать пользователя',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('surname')
                            ->label('Фамилия')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('patronymic')
                            ->label('Отчество')
                            ->maxLength(255)
                            ->nullable(),
                            
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('users', 'email', ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Пользователь с таким email уже существует',
                            ]),
                            
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Контактная информация')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(20)
                            ->nullable(),
                            
                        Forms\Components\TextInput::make('telegram_id')
                            ->label('Telegram ID')
                            ->maxLength(255)
                            ->nullable(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Роли и специальности')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Роли в системе')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                // Сбрасываем тип исполнителя при снятии роли executor
                                if (!in_array('executor', $state ?? [])) {
                                    $set('contractor_id', null);
                                }
                            })
                            ->required()
                            ->validationMessages([
                                'required' => 'Выберите хотя бы одну роль',
                            ]),
                            
                        // ДОБАВЛЯЕМ ПЕРЕКЛЮЧАТЕЛЬ ТИПА ИСПОЛНИТЕЛЯ
                        Forms\Components\Radio::make('executor_type')
                            ->label('Тип исполнителя')
                            ->options([
                                'our' => '👷 Наш исполнитель (сотрудник компании)',
                                'contractor' => '🏢 Исполнитель подрядчика',
                            ])
                            ->live()
                            ->required(fn (callable $get): bool => 
                                collect($get('roles') ?? [])->contains('executor')
                            )
                            ->visible(fn (callable $get): bool => 
                                collect($get('roles') ?? [])->contains('executor')
                            )
                            ->afterStateUpdated(function ($set, $state) {
                                // При выборе "наш исполнитель" очищаем подрядчика
                                if ($state === 'our') {
                                    $set('contractor_id', null);
                                }
                            }),
                            
                        // ОБНОВЛЯЕМ ПОЛЕ ПОДРЯДЧИКА
                        Forms\Components\Select::make('contractor_id')
                            ->label('Компания-подрядчик')
                            ->relationship('contractor', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Выберите компанию-подрядчика для этого исполнителя')
                            ->visible(fn (callable $get): bool => 
                                collect($get('roles') ?? [])->contains('executor') && 
                                $get('executor_type') === 'contractor'
                            )
                            ->required(fn (callable $get): bool => 
                                collect($get('roles') ?? [])->contains('executor') && 
                                $get('executor_type') === 'contractor'
                            )
                            ->validationMessages([
                                'required' => 'Для исполнителя подрядчика необходимо выбрать компанию',
                            ]),

                        // НОВЫЕ ПОЛЯ ДЛЯ НАЛОГОВОЙ СИСТЕМЫ
                        Forms\Components\Select::make('contract_type_id')
                            ->label('Тип договора')
                            ->relationship('contractType', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                // Сбрасываем налоговый статус при смене типа договора
                                $set('tax_status_id', null);
                            })
                            ->helperText('Форма договора с исполнителем')
                            ->visible(fn (callable $get): bool =>
                                collect($get('roles') ?? [])->contains('executor') &&
                                $get('executor_type') === 'our'
                            ),

                        Forms\Components\Select::make('tax_status_id')
                            ->label('Налоговый статус')
                            ->relationship(
                                name: 'taxStatus',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, callable $get) => 
                                    $query->where('contract_type_id', $get('contract_type_id'))
                                          ->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->helperText('Налоговый режим для расчетов')
                            ->visible(fn (callable $get): bool =>
                                collect($get('roles') ?? [])->contains('executor') &&
                                $get('executor_type') === 'our' &&
                                $get('contract_type_id')
                            ),    
                            
                        Forms\Components\BelongsToManyCheckboxList::make('specialties')
                            ->label('Специальности')
                            ->relationship('specialties', 'name')
                            ->searchable()
                            ->helperText('Специальности, по которым пользователь может работать'),
                    ]),
                    
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Заметки')
                            ->maxLength(65535)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ФИО')
                    ->searchable(['name', 'surname', 'patronymic'])
                    ->sortable()
                    ->weight('medium'),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип пользователя')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->isExternalContractor()) return '👑 Подрядчик';
                        if ($record->isOurExecutor()) return '👷 Наш исполнитель';
                        if ($record->isContractorExecutor()) return '🏢 Исполнитель подрядчика';
                        if ($record->isInitiator()) return '📋 Инициатор';
                        if ($record->isDispatcher()) return '📞 Диспетчер';
                        return '❓ Другое';
                    })
                    ->colors([
                        '👑 Подрядчик' => 'warning',
                        '👷 Наш исполнитель' => 'success', 
                        '🏢 Исполнитель подрядчика' => 'info',
                        '📋 Инициатор' => 'primary',
                        '📞 Диспетчер' => 'gray',
                    ]),
                    
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'admin' => '👑 Админ',
                        'initiator' => '📋 Инициатор',
                        'dispatcher' => '📞 Диспетчер',
                        'executor' => '👷 Исполнитель',
                        'contractor' => '🏢 Подрядчик',
                        default => $state
                    })
                    ->colors([
                        'danger' => 'admin',
                        'success' => 'initiator',
                        'warning' => 'dispatcher',
                        'info' => 'executor',
                        'gray' => 'contractor',
                    ]),
                    
                Tables\Columns\TextColumn::make('contractor.name')
                    ->label('Подрядчик')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state) => $state ?: '—'),
                    
                Tables\Columns\TextColumn::make('specialties.name')
                    ->label('Специальности')
                    ->badge()
                    ->separator(', ')
                    ->limitList(2)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('contractType.name')
                    ->label('Тип договора')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('taxStatus.name')
                    ->label('Налоговый статус')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $state ? "{$state} (" . ($record->taxStatus?->tax_rate * 100) . "%)" : '—')
                    ->color(fn ($state) => $state ? 'primary' : 'gray')
                    ->toggleable()
                    ->placeholder('—'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Роль')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                    
                Tables\Filters\SelectFilter::make('contractor_id')
                    ->label('Подрядчик')
                    ->relationship('contractor', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('our_executors')
                    ->label('👷 Наши исполнители')
                    ->query(fn ($query) => $query->ourExecutors()),

                Tables\Filters\Filter::make('contractor_executors')
                    ->label('🏢 Исполнители подрядчиков')
                    ->query(fn ($query) => $query->contractorExecutors()),

                Tables\Filters\Filter::make('external_contractors')
                    ->label('👑 Подрядчики')
                    ->query(fn ($query) => $query->externalContractors()),

                Tables\Filters\Filter::make('initiators')
                    ->label('📋 Инициаторы')
                    ->query(fn ($query) => $query->role('initiator')),

                Tables\Filters\Filter::make('dispatchers')
                    ->label('📞 Диспетчеры')
                    ->query(fn ($query) => $query->role('dispatcher')),

                Tables\Filters\SelectFilter::make('specialties')
                    ->label('Специальность')
                    ->relationship('specialties', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('contract_type')
                    ->label('Тип договора')
                    ->relationship('contractType', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('tax_status')
                    ->label('Налоговый статус')
                    ->relationship('taxStatus', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                    
                Tables\Actions\Action::make('view_shifts')
                    ->label('Смены')
                    ->icon('heroicon-o-calendar')
                    ->url(fn (User $record) => ShiftResource::getUrl('index', [
                        'tableFilters[user][values]' => [$record->id]
                    ]))
                    ->color('gray')
                    ->hidden(fn ($record) => !$record->canHaveShifts()),
                    
                Tables\Actions\Action::make('view_assignments')
                    ->label('Назначения бригадиром')
                    ->icon('heroicon-o-user-plus')
                    ->url(fn (User $record) => AssignmentResource::getUrl('index', [
                        'tableFilters[brigadier][values]' => [$record->id]
                    ]))
                    ->color('gray')
                    ->hidden(fn ($record) => !$record->canHaveShifts()),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->emptyStateHeading('Нет пользователей')
            ->emptyStateDescription('Создайте первого пользователя.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Создать пользователя'),
            ])
            ->defaultSort('surname', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SpecialtiesRelationManager::class,
            RelationManagers\InitiatedWorkRequestsRelationManager::class,
            RelationManagers\BrigadierWorkRequestsRelationManager::class,
            RelationManagers\DispatcherWorkRequestsRelationManager::class,
            RelationManagers\ShiftsRelationManager::class,
            RelationManagers\BrigadierAssignmentsRelationManager::class,
            RelationManagers\InitiatorGrantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'initiator', 'dispatcher']);
    }
}
