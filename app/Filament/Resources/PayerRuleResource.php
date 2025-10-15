<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayerRuleResource\Pages;
use App\Models\PayerRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayerRuleResource extends Resource
{
    protected static ?string $model = PayerRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Управление проектами';
    
    protected static ?string $navigationLabel = 'Правила оплаты';
    
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Условия правила')
                    ->schema([
                        Forms\Components\Select::make('purpose_id')
                            ->label('Назначение')
                            ->relationship('purpose', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('address_id')
                            ->label('Адрес')
                            ->relationship('address', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('project_id')
                            ->label('Проект')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('address_program_id')
                            ->label('Адресная программа')
                            ->relationship('addressProgram', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->project->name} - {$record->address->name}")
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Настройки оплаты')
                    ->schema([
                        Forms\Components\TextInput::make('payer_company')
                            ->label('Компания-плательщик')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ЦЕХ, БС, ЦФ, УС и т.д.'),
                        
                        Forms\Components\TextInput::make('priority')
                            ->label('Приоритет')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(10),
                        
                        Forms\Components\Toggle::make('is_custom')
                            ->label('Индивидуальное определение')
                            ->helperText('Если включено, плательщик определяется каждый раз индивидуально')
                            ->reactive(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание правила')
                            ->rows(2)
                            ->columnSpanFull()
                            ->hidden(fn ($get) => $get('is_custom')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purpose.name')
                    ->label('Назначение')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('address.name')
                    ->label('Адрес')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Проект')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payer_company')
                    ->label('Плательщик')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('priority')
                    ->label('Приоритет')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_custom')
                    ->label('Индивидуально')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('purpose')
                    ->relationship('purpose', 'name'),
                
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
                
                Tables\Filters\TernaryFilter::make('is_custom')
                    ->label('Индивидуальное определение'),
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
            'index' => Pages\ListPayerRules::route('/'),
            'create' => Pages\CreatePayerRule::route('/create'),
            'edit' => Pages\EditPayerRule::route('/{record}/edit'),
        ];
    }
}
