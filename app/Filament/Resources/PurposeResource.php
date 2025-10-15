<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurposeResource\Pages;
use App\Models\Purpose;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurposeResource extends Resource
{
    protected static ?string $model = Purpose::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    protected static ?string $navigationGroup = 'Управление проектами';
    
    protected static ?string $navigationLabel = 'Назначения';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название назначения')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('category')
                            ->label('Категория')
                            ->options([
                                'construction' => 'Застройка',
                                'installation' => 'Монтаж/Демонтаж',
                                'maintenance' => 'Уход',
                                'administrative' => 'Административные',
                                'other' => 'Прочие',
                            ])
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Статус')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активно')
                            ->default(true),
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
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50)
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('category')
                    ->label('Категория')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'construction' => 'Застройка',
                        'installation' => 'Монтаж/Демонтаж',
                        'maintenance' => 'Уход',
                        'administrative' => 'Административные',
                        'other' => 'Прочие',
                    })
                    ->colors([
                        'warning' => 'construction',
                        'primary' => 'installation', 
                        'success' => 'maintenance',
                        'gray' => 'administrative',
                        'info' => 'other',
                    ]),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('payer_rules_count')
                    ->label('Правил оплаты')
                    ->counts('payerRules'),
                
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
                        'construction' => 'Застройка',
                        'installation' => 'Монтаж/Демонтаж', 
                        'maintenance' => 'Уход',
                        'administrative' => 'Административные',
                        'other' => 'Прочие',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurposes::route('/'),
            'create' => Pages\CreatePurpose::route('/create'),
            'edit' => Pages\EditPurpose::route('/{record}/edit'),
        ];
    }
}
