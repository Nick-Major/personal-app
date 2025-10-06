<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkRequestResource\Pages;
use App\Filament\Resources\WorkRequestResource\RelationManagers;
use App\Models\WorkRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkRequestResource extends Resource
{
    protected static ?string $model = WorkRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('initiator_id')
                    ->relationship('initiator', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('brigadier_id')
                    ->relationship('brigadier', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\TextInput::make('specialization')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Select::make('executor_type')
                    ->options([
                        'our_staff' => 'Наш персонал',
                        'contractor' => 'Подрядчик',
                    ])
                    ->required(),
                    
                Forms\Components\TextInput::make('workers_count')
                    ->required()
                    ->numeric(),
                    
                Forms\Components\TextInput::make('shift_duration')
                    ->required()
                    ->numeric()
                    ->suffix('часов'),
                    
                Forms\Components\TextInput::make('project')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('purpose')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('payer_company')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Textarea::make('comments')
                    ->columnSpanFull(),
                    
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликована',
                        'in_work' => 'В работе',
                        'staffed' => 'Укомплектована',
                        'in_progress' => 'Выполняется',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                    ])
                    ->required()
                    ->default('draft'),
                    
                Forms\Components\Select::make('dispatcher_id')
                    ->relationship('dispatcher', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->searchable()
                    ->sortable()
                    ->label('Номер заявки'),
                    
                Tables\Columns\TextColumn::make('initiator.name')
                    ->searchable()
                    ->sortable()
                    ->label('Инициатор'),
                    
                Tables\Columns\TextColumn::make('brigadier.name')
                    ->searchable()
                    ->sortable()
                    ->label('Бригадир'),
                    
                Tables\Columns\TextColumn::make('specialization')
                    ->searchable()
                    ->sortable()
                    ->label('Специализация'),
                    
                Tables\Columns\TextColumn::make('executor_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'our_staff' => 'Наш персонал',
                        'contractor' => 'Подрядчик',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'our_staff' => 'success',
                        'contractor' => 'warning',
                    })
                    ->label('Тип исполнителя'),
                    
                Tables\Columns\TextColumn::make('workers_count')
                    ->sortable()
                    ->label('Кол-во человек'),
                    
                Tables\Columns\TextColumn::make('project')
                    ->searchable()
                    ->sortable()
                    ->label('Проект'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Черновик',
                        'published' => 'Опубликована',
                        'in_work' => 'В работе',
                        'staffed' => 'Укомплектована',
                        'in_progress' => 'Выполняется',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'info',
                        'in_work' => 'warning',
                        'staffed' => 'success',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->label('Статус'),
                    
                Tables\Columns\TextColumn::make('dispatcher.name')
                    ->searchable()
                    ->sortable()
                    ->label('Диспетчер'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->label('Создана'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликована',
                        'in_work' => 'В работе',
                        'staffed' => 'Укомплектована',
                        'in_progress' => 'Выполняется',
                        'completed' => 'Завершена',
                        'cancelled' => 'Отменена',
                    ])
                    ->label('Статус'),
                    
                Tables\Filters\SelectFilter::make('executor_type')
                    ->options([
                        'our_staff' => 'Наш персонал',
                        'contractor' => 'Подрядчик',
                    ])
                    ->label('Тип исполнителя'),
                    
                Tables\Filters\SelectFilter::make('initiator_id')
                    ->relationship('initiator', 'name')
                    ->label('Инициатор')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListWorkRequests::route('/'),
            'create' => Pages\CreateWorkRequest::route('/create'),
            'edit' => Pages\EditWorkRequest::route('/{record}/edit'),
        ];
    }
}
