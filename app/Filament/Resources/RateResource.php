<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RateResource\Pages;
use App\Filament\Resources\RateResource\RelationManagers;
use App\Models\Rate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class RateResource extends Resource
{
    protected static ?string $model = Rate::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Ставки';

    protected static ?string $modelLabel = 'Ставка';

    protected static ?string $pluralModelLabel = 'Ставки';

    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->label('Пользователь')
                            ->helperText('Оставьте пустым для базовой ставки специальности')
                            ->placeholder('Выберите пользователя'),

                        Forms\Components\Select::make('specialty_id')
                            ->relationship('specialty', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Специальность')
                            ->placeholder('Выберите специальность'),

                        Forms\Components\Select::make('work_type_id')
                            ->relationship('workType', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Вид работ')
                            ->placeholder('Выберите вид работ'),

                        Forms\Components\TextInput::make('hourly_rate')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->prefix('₽')
                            ->label('Ставка (руб/час)')
                            ->helperText('Числовое значение ставки в рублях за час'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Период действия')
                    ->schema([
                        Forms\Components\DatePicker::make('effective_from')
                            ->label('Действует с')
                            ->nullable()
                            ->helperText('Если не указано, действует бессрочно с момента создания'),

                        Forms\Components\DatePicker::make('effective_to')
                            ->label('Действует до')
                            ->nullable()
                            ->helperText('Если не указано, действует бессрочно'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name') // ← меняем user.name на user.full_name
                    ->label('Пользователь')
                    ->placeholder('Базовая')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('specialty.name')
                    ->label('Специальность')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('workType.name')
                    ->label('Вид работ')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('hourly_rate')
                    ->label('Ставка')
                    ->money('RUB')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('effective_from')
                    ->label('Действует с')
                    ->date('d.m.Y')
                    ->placeholder('Бессрочно')
                    ->sortable(),

                Tables\Columns\TextColumn::make('effective_to')
                    ->label('Действует до')
                    ->date('d.m.Y')
                    ->placeholder('Бессрочно')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('specialty')
                    ->relationship('specialty', 'name')
                    ->label('Специальность')
                    ->preload()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('workType')
                    ->relationship('workType', 'name')
                    ->label('Вид работ')
                    ->preload()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name) // ← добавляем для фильтра
                    ->label('Пользователь')
                    ->preload()
                    ->searchable(),

                Tables\Filters\Filter::make('individual_rates')
                    ->query(fn (Builder $query) => $query->whereNotNull('user_id'))
                    ->label('Только индивидуальные'),

                Tables\Filters\Filter::make('base_rates')
                    ->query(fn (Builder $query) => $query->whereNull('user_id'))
                    ->label('Только базовые'),

                Tables\Filters\Filter::make('active_rates')
                    ->query(fn (Builder $query) => $query->where(function($q) {
                        $q->whereNull('effective_from')->orWhere('effective_from', '<=', now());
                    })->where(function($q) {
                        $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
                    }))
                    ->label('Только активные ставки'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Редактировать'),
                Tables\Actions\DeleteAction::make()->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRates::route('/'),
            'create' => Pages\CreateRate::route('/create'),
            'edit' => Pages\EditRate::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
