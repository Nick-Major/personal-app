<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    protected static ?string $navigationGroup = 'Управление проектами';
    
    protected static ?string $navigationLabel = 'Адреса';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация об адресе')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название места')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('address')
                            ->label('Полный адрес')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('coordinates')
                            ->label('Координаты (широта, долгота)')
                            ->placeholder('55.7558, 37.6173'),
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
                
                Tables\Columns\TextColumn::make('address')
                    ->label('Адрес')
                    ->searchable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(30)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('address_programs_count')
                    ->label('В программах')
                    ->counts('addressPrograms'),
                
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
                //
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
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
