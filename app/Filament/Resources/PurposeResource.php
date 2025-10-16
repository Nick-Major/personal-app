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
                        Forms\Components\Select::make('project_id')
                            ->label('Проект')
                            ->relationship('project', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Название назначения')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Настройки')
                    ->schema([
                        Forms\Components\Toggle::make('has_custom_payer_selection')
                            ->label('Ручной выбор плательщика')
                            ->helperText('Если включено, можно будет выбирать компанию при создании заявки')
                            ->default(false),
                        
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
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Проект')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),
                
                Tables\Columns\IconColumn::make('has_custom_payer_selection')
                    ->label('Ручной выбор')
                    ->boolean()
                    ->trueIcon('heroicon-o-hand-raised')
                    ->falseIcon('heroicon-o-cog')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('payer_companies_count')
                    ->label('Вариантов оплаты')
                    ->counts('payerCompanies'),
                
                Tables\Columns\TextColumn::make('address_rules_count')
                    ->label('Правил по адресам')
                    ->counts('addressRules'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
                
                Tables\Filters\TernaryFilter::make('has_custom_payer_selection')
                    ->label('Ручной выбор плательщика'),
                
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

    public static function getRelations(): array
    {
        return [
            // Исправляем названия Relation Managers
            \App\Filament\Resources\RelationManagers\PurposePayerCompaniesRelationManager::class,
            \App\Filament\Resources\RelationManagers\PurposeAddressRulesRelationManager::class,
        ];
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
