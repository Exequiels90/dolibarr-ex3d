<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdditionalSupplyResource\Pages;
use App\Models\AdditionalSupply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdditionalSupplyResource extends Resource
{
    protected static ?string $model = AdditionalSupply::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Gestión de Producción';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Insumo Adicional';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Insumos Adicionales';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Insumo Adicional')
                    ->description('Ingrese los detalles para este ítem de insumo adicional')
                    ->schema([
                        Forms\Components\TextInput::make('item_name')
                            ->label('Nombre del Ítem')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej., Inserto de Acero Inoxidable, Anillo de Llavero')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Costo Unitario')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->inputMode('decimal')
                            ->placeholder('0.00'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Nombre del Ítem')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Costo Unitario')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Usado en Productos')
                    ->counts('products')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdditionalSupplies::route('/'),
            'create' => Pages\CreateAdditionalSupply::route('/create'),
            'edit' => Pages\EditAdditionalSupply::route('/{record}/edit'),
        ];
    }
}
