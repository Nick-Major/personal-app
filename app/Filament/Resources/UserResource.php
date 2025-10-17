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
use Spatie\Permission\Models\Permission;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

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
                            ->maxLength(255),
                            
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
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('telegram_id')
                            ->label('Telegram ID')
                            ->maxLength(255),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Роли и специальности')
                    ->schema([
                        Forms\Components\Toggle::make('is_contractor')
                            ->label('Подрядчик')
                            ->reactive(),
                            
                        Forms\Components\Toggle::make('is_always_brigadier')
                            ->label('Всегда бригадир'),
                            
                        Forms\Components\Select::make('contractor_id')
                            ->label('Компания-подрядчик')
                            ->relationship('contractor', 'name')
                            ->visible(fn (callable $get) => $get('is_contractor')),
                            
                        Forms\Components\BelongsToManyCheckboxList::make('specialties')
                            ->label('Специальности')
                            ->relationship('specialties', 'name')
                            ->searchable(),
                            
                        Forms\Components\Select::make('roles')
                            ->label('Роли в системе')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($set, $state) {
                                // Сбрасываем права при смене роли
                                $set('permissions', []);
                            }),
                            
                        Forms\Components\Select::make('permissions')
                            ->label('Специальные права')
                            ->relationship('permissions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->options(function ($get) {
                                $roles = $get('roles') ?? [];
                                
                                // Показываем право редактирования БД только Инициаторам и Диспетчерам
                                $allowedRoles = ['initiator', 'dispatcher'];
                                $hasAllowedRole = !empty(array_intersect($allowedRoles, $roles));
                                
                                if ($hasAllowedRole) {
                                    return Permission::where('name', 'edit_database')
                                        ->orWhere('name', 'like', 'view_%')
                                        ->pluck('name', 'name') // ИСПРАВЛЕНО: убираем description
                                        ->map(function ($name) {
                                            return match($name) {
                                                'edit_database' => '📊 Редактирование базы данных',
                                                'view_projects' => '👀 Просмотр проектов',
                                                'view_purposes' => '👀 Просмотр назначений',
                                                'view_addresses' => '👀 Просмотр адресов',
                                                'view_work_requests' => '👀 Просмотр заявок',
                                                default => $name
                                            };
                                        });
                                }
                                
                                return [];
                            })
                            ->helperText(function ($get) {
                                $roles = $get('roles') ?? [];
                                $allowedRoles = ['initiator', 'dispatcher'];
                                $hasAllowedRole = !empty(array_intersect($allowedRoles, $roles));
                                
                                if ($hasAllowedRole) {
                                    return '✅ Можете дать право на редактирование БД этому пользователю';
                                }
                                return '⚠️ Права редактирования БД доступны только Инициаторам и Диспетчерам';
                            }),
                    ]),
                    
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
                Tables\Columns\TextColumn::make('surname')
                    ->label('Фамилия')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('patronymic')
                    ->label('Отчество')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                    
                Tables\Columns\IconColumn::make('is_contractor')
                    ->label('Подрядчик')
                    ->boolean(),
                    
                Tables\Columns\IconColumn::make('is_always_brigadier')
                    ->label('Бригадир')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('specialties.name')
                    ->label('Специальности')
                    ->badge()
                    ->separator(', '),
                    
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
                    
                Tables\Columns\IconColumn::make('can_edit_database')
                    ->label('Редакт. БД')
                    ->getStateUsing(fn ($record) => $record->hasPermissionTo('edit_database'))
                    ->boolean()
                    ->trueIcon('heroicon-o-cog-6-tooth')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->hasPermissionTo('edit_database') 
                        ? 'Может редактировать БД' 
                        : 'Не может редактировать БД'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_contractor')
                    ->label('Только подрядчики')
                    ->query(fn ($query) => $query->where('is_contractor', true)),
                    
                Tables\Filters\Filter::make('is_always_brigadier')
                    ->label('Только бригадиры')
                    ->query(fn ($query) => $query->where('is_always_brigadier', true)),
                    
                Tables\Filters\SelectFilter::make('specialties')
                    ->label('Специальность')
                    ->relationship('specialties', 'name')
                    ->multiple(),
                    
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Роль')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                    
                Tables\Filters\Filter::make('can_edit_database')
                    ->label('Может редактировать БД')
                    ->query(fn ($query) => $query->whereHas('permissions', function ($q) {
                        $q->where('name', 'edit_database');
                    })),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggle_database_edit')
                    ->label('Право редакт. БД')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->action(function (User $record) {
                        if ($record->hasPermissionTo('edit_database')) {
                            $record->revokePermissionTo('edit_database');
                            Notification::make()
                                ->title('Право отозвано')
                                ->body("{$record->name} больше не может редактировать БД")
                                ->success()
                                ->send();
                        } else {
                            // Проверяем что пользователь Инициатор или Диспетчер
                            if ($record->hasAnyRole(['initiator', 'dispatcher'])) {
                                $record->givePermissionTo('edit_database');
                                Notification::make()
                                    ->title('Право выдано')
                                    ->body("{$record->name} теперь может редактировать БД")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Ошибка')
                                    ->body('Право редактирования БД можно давать только Инициаторам и Диспетчерам')
                                    ->danger()
                                    ->send();
                            }
                        }
                    })
                    ->visible(fn () => auth()->user()->hasRole('admin'))
                    ->color(fn (User $record) => $record->hasPermissionTo('edit_database') ? 'danger' : 'success')
                    ->tooltip(fn (User $record) => $record->hasPermissionTo('edit_database') 
                        ? 'Отозвать право редактирования БД' 
                        : 'Дать право редактирования БД'),
                        
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
        return auth()->user()->hasRole('admin');
    }
}
