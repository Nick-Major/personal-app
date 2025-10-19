<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecialtyResource\Pages;
use App\Models\Specialty;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SpecialtyResource extends Resource
{
    protected static ?string $model = Specialty::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    // ДОБАВЛЯЕМ РУССКИЕ LABELS И ГРУППУ
    protected static ?string $navigationGroup = 'Справочники';
    protected static ?string $navigationLabel = 'Специальности';
    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'специальность';
    protected static ?string $pluralModelLabel = 'Специальности';

    public static function getPageLabels(): array
    {
        return [
            'index' => 'Специальности',
            'create' => 'Создать специальность',
            'edit' => 'Редактировать специальность',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название специальности')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Например: Садовник, Декоратор, Администратор...')
                            ->validationMessages([
                                'unique' => 'Специальность с таким названием уже существует',
                            ]),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('Подробное описание специальности...')
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('base_hourly_rate')
                            ->label('Базовая ставка (руб/час)')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->placeholder('0')
                            ->helperText('Базовая почасовая ставка для этой специальности'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активная специальность')
                            ->default(true)
                            ->helperText('Неактивные специальности не будут показываться при выборе'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Категория')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label('Категория')
                            ->options([
                                'gardening' => 'Садоводство',
                                'decoration' => 'Декорирование',
                                'administration' => 'Администрирование',
                                'technical' => 'Технические работы',
                                'other' => 'Другое',
                            ])
                            ->default('other')
                            ->required()
                            ->helperText('Выберите категорию для группировки специальностей'),
                    ]),
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
                        'gardening' => '🌿 Садоводство',
                        'decoration' => '🎨 Декорирование',
                        'administration' => '📊 Администрирование',
                        'technical' => '🔧 Технические',
                        'other' => '📁 Другое',
                        default => $state
                    })
                    ->color(fn ($state) => match($state) {
                        'gardening' => 'success',
                        'decoration' => 'warning',
                        'administration' => 'info',
                        'technical' => 'gray',
                        'other' => 'gray',
                        default => 'gray'
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('base_hourly_rate')
                    ->label('Ставка')
                    ->money('RUB')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', ' ') . ' ₽/час' : 'Не указана'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Пользователей')
                    ->counts('users')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Категория')
                    ->options([
                        'gardening' => 'Садоводство',
                        'decoration' => 'Декорирование',
                        'administration' => 'Администрирование',
                        'technical' => 'Технические работы',
                        'other' => 'Другое',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные')
                    ->placeholder('Все специальности')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
                    
                Tables\Filters\Filter::make('has_users')
                    ->label('С пользователями')
                    ->query(fn ($query) => $query->has('users')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Редактировать'),
                    
                Tables\Actions\Action::make('view_users')
                    ->label('Пользователи')
                    ->icon('heroicon-o-users')
                    ->url(fn (Specialty $record) => \App\Filament\Resources\UserResource::getUrl('index', [
                        'tableFilters[specialties][values]' => [$record->id]
                    ]))
                    ->color('gray'),
                    
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->emptyStateHeading('Нет специальностей')
            ->emptyStateDescription('Создайте первую специальность.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Создать специальность'),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            // Можно добавить RelationManager для пользователей с этой специальностью
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecialties::route('/'),
            'create' => Pages\CreateSpecialty::route('/create'),
            'edit' => Pages\EditSpecialty::route('/{record}/edit'),
        ];
    }
}
