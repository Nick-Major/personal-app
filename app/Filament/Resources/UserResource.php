<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
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
                            ->preload(),
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
            // Можно добавить связи для отображения смен, заявок и т.д.
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
}
