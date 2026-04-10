<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilamentResource\Pages;
use App\Models\Filament as FilamentModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FilamentResource extends Resource
{
    protected static ?string $model = FilamentModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Gestión de Producción';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Filamento';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Filamentos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Filamento')
                    ->description('Ingrese los detalles para este tipo de filamento')
                    ->schema([
                        Forms\Components\TextInput::make('brand_type')
                            ->label('Marca/Tipo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej., PLA, PETG, ABS')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('color')
                            ->label('Color')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej., Negro, Blanco, Rojo'),
                        
                        Forms\Components\TextInput::make('cost_per_kg')
                            ->label('Costo por KG')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->inputMode('decimal')
                            ->placeholder('0.00'),
                        
                        Forms\Components\TextInput::make('spool_weight_g')
                            ->label('Peso del Carrete (g)')
                            ->required()
                            ->numeric()
                            ->suffix('g')
                            ->placeholder('1000')
                            ->default(1000),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand_type')
                    ->label('Marca/Tipo')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('color')
                    ->label('Color')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match(strtolower($record->color)) {
                        'black' => 'gray',
                        'white' => 'white',
                        'red' => 'danger',
                        'blue' => 'primary',
                        'green' => 'success',
                        'yellow' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('cost_per_kg')
                    ->label('Costo por KG')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('spool_weight_g')
                    ->label('Peso del Carrete')
                    ->formatStateUsing(fn ($state) => $state . 'g')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Productos')
                    ->counts('products')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('color')
                    ->label('Color')
                    ->options(fn () => FilamentModel::pluck('color', 'color')->unique()),
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
            'index' => Pages\ListFilaments::route('/'),
            'create' => Pages\CreateFilament::route('/create'),
            'edit' => Pages\EditFilament::route('/{record}/edit'),
        ];
    }
}
