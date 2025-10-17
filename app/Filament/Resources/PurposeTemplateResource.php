<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurposeTemplateResource\Pages;
use App\Models\PurposeTemplate;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PurposeTemplateResource extends Resource
{
    protected static ?string $model = PurposeTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    
    protected static ?string $navigationGroup = 'Справочники';
    
    protected static ?string $navigationLabel = 'Шаблоны назначений';
    
    protected static ?string $modelLabel = 'шаблон назначения';
    
    protected static ?string $pluralModelLabel = 'Шаблоны назначений';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название назначения')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Например: Монтаж, Демонтаж, Уход за растениями'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Подробное описание назначения...'),
                    ]),
                
                Forms\Components\Section::make('Настройки оплаты по умолчанию')
                    ->schema([
                        Forms\Components\Select::make('default_payer_selection_type')
                            ->label('Тип выбора плательщика по умолчанию')
                            ->options([
                                'strict' => 'Строгая привязка',
                                'optional' => 'Опциональный выбор', 
                                'address_based' => 'Зависит от адреса',
                            ])
                            ->default('strict')
                            ->required()
                            ->helperText('Будет использоваться при создании назначения из шаблона'),
                        
                        Forms\Components\TextInput::make('default_payer_company')
                            ->label('Компания-плательщик по умолчанию')
                            ->maxLength(255)
                            ->placeholder('ЦЕХ, БС, ЦФ, УС и т.д.'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активный шаблон')
                            ->default(true)
                            ->helperText('Неактивные шаблоны не будут показываться при выборе'),
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
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('default_payer_selection_type')
                    ->label('Тип оплаты')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'strict' => 'Строгая',
                        'optional' => 'Выбор', 
                        'address_based' => 'По адресу',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'strict' => 'success',
                        'optional' => 'warning',
                        'address_based' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('default_payer_company')
                    ->label('Плательщик по умолчанию'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('default_payer_selection_type')
                    ->label('Тип оплаты')
                    ->options([
                        'strict' => 'Строгая привязка',
                        'optional' => 'Опциональный выбор',
                        'address_based' => 'По адресу',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные шаблоны'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // ДЕЙСТВИЕ: Создать назначение из шаблона
                Tables\Actions\Action::make('createPurpose')
                    ->label('Создать в проекте')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Select::make('project_id')
                            ->label('Проект')
                            ->options(Project::where('status', 'active')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Выберите проект'),
                    ])
                    ->action(function (PurposeTemplate $record, array $data) {
                        $project = Project::find($data['project_id']);
                        
                        // Создаем назначение из шаблона
                        $purpose = $record->createPurposeForProject($project);
                        
                        Notification::make()
                            ->title('Назначение создано')
                            ->body("Шаблон '{$record->name}' успешно добавлен в проект '{$project->name}'")
                            ->success()
                            ->send();
                    }),
                    
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
            // Пока без связей
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurposeTemplates::route('/'),
            'create' => Pages\CreatePurposeTemplate::route('/create'),
            'edit' => Pages\EditPurposeTemplate::route('/{record}/edit'),
        ];
    }
}
