<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurposeAddressRuleResource\Pages;
use App\Models\PurposeAddressRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurposeAddressRuleResource extends Resource
{
    protected static ?string $model = PurposeAddressRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    protected static ?string $navigationGroup = 'Управление проектами';
    
    protected static ?string $navigationLabel = 'Правила по адресам';
    
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Условия правила')
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Проект')
                            ->relationship('project', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive(),
                        
                        Forms\Components\Select::make('purpose_id')
                            ->label('Назначение')
                            ->relationship('purpose', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function ($get) {
                                $projectId = $get('project_id');
                                if (!$projectId) {
                                    return \App\Models\Purpose::all()->pluck('name', 'id');
                                }
                                return \App\Models\Purpose::where('project_id', $projectId)->pluck('name', 'id');
                            })
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\Select::make('address_id')
                            ->label('Адрес')
                            ->relationship('address', 'name')
                            ->searchable()
                            ->preload()
                            ->options(function ($get) {
                                $projectId = $get('project_id');
                                if (!$projectId) {
                                    return \App\Models\Address::all()->pluck('name', 'id');
                                }
                                return \App\Models\Address::where('project_id', $projectId)->pluck('name', 'id');
                            })
                            ->helperText('Оставьте пустым для общего правила')
                            ->nullable(),
                        
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
                            ->maxValue(10)
                            ->helperText('1 - высший приоритет'),
                    ])->columns(2),
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

                Tables\Columns\TextColumn::make('purpose.name')
                    ->label('Назначение')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('address.name')
                    ->label('Адрес')
                    ->formatStateUsing(fn ($state) => $state ?: 'Общее правило')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payer_company')
                    ->label('Компания-плательщик')
                    ->searchable()
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('priority')
                    ->label('Приоритет')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "{$state}"),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
                
                Tables\Filters\SelectFilter::make('purpose')
                    ->relationship('purpose', 'name'),
                
                Tables\Filters\SelectFilter::make('address')
                    ->relationship('address', 'name')
                    ->placeholder('Все адреса'),
                
                Tables\Filters\Filter::make('general_rules')
                    ->label('Только общие правила')
                    ->query(fn ($query) => $query->whereNull('address_id')),
                
                Tables\Filters\Filter::make('specific_rules')
                    ->label('Только правила по адресам')
                    ->query(fn ($query) => $query->whereNotNull('address_id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Дублировать')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (PurposeAddressRule $record) {
                        $newRecord = $record->replicate();
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurposeAddressRules::route('/'),
            'create' => Pages\CreatePurposeAddressRule::route('/create'),
            'edit' => Pages\EditPurposeAddressRule::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
