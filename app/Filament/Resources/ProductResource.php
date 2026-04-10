<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Filament;
use App\Models\AdditionalSupply;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?string $navigationGroup = 'Gestión de Producción';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'Producto';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Productos';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Producto')
                    ->description('Información básica sobre el producto')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Especificaciones de Impresión 3D')
                    ->description('Especificaciones técnicas de Bambu Studio')
                    ->schema([
                        Forms\Components\Select::make('filament_id')
                            ->label('Tipo de Filamento')
                            ->relationship('filament', 'brand_type')
                            ->getOptionLabelFromRecordUsing(fn (Filament $record) => "{$record->brand_type} - {$record->color}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('filament_cost_display', $state ? Filament::find($state)->formatted_cost : '')),
                        
                        Forms\Components\Placeholder::make('filament_cost_display')
                            ->label('Costo del Filamento por KG')
                            ->content(fn ($get) => $get('filament_id') ? Filament::find($get('filament_id'))->formatted_cost : ''),
                        
                        Forms\Components\TextInput::make('total_grams')
                            ->label('Gramos Totales')
                            ->required()
                            ->numeric()
                            ->suffix('g')
                            ->step(0.01)
                            ->inputMode('decimal')
                            ->placeholder('0.00')
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('printing_time_hours')
                            ->label('Tiempo de Impresión (Horas)')
                            ->required()
                            ->numeric()
                            ->suffix('horas')
                            ->step(0.01)
                            ->inputMode('decimal')
                            ->placeholder('0.00')
                            ->reactive(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Insumos Adicionales')
                    ->description('Agregue cualquier insumo adicional necesario para este producto')
                    ->schema([
                        Forms\Components\Repeater::make('additionalSupplies')
                            ->label('Insumos')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('additional_supply_id')
                                    ->label('Ítem de Insumo')
                                    ->options(AdditionalSupply::pluck('item_name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $supply = AdditionalSupply::find($state);
                                            $set('unit_cost_display', $supply->formatted_cost);
                                        }
                                    }),
                                
                                Forms\Components\Placeholder::make('unit_cost_display')
                                    ->label('Costo Unitario')
                                    ->content(fn ($get) => $get('additional_supply_id') ? AdditionalSupply::find($get('additional_supply_id'))->formatted_cost : ''),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['additional_supply_id'] ? AdditionalSupply::find($state['additional_supply_id'])->item_name . ' (x' . $state['quantity'] . ')' : null),
                    ])
                    ->collapsed(),

                Forms\Components\Section::make('Configuración de Costos')
                    ->description('Configure costos de post-procesamiento y márgenes de seguridad')
                    ->schema([
                        Forms\Components\TextInput::make('post_processing_cost')
                            ->label('Costo de Post-Procesamiento')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->inputMode('decimal')
                            ->placeholder('0.00')
                            ->default(0)
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('safety_margin_percentage')
                            ->label('Margen de Seguridad (%)')
                            ->numeric()
                            ->suffix('%')
                            ->step(0.1)
                            ->inputMode('decimal')
                            ->placeholder('10.0')
                            ->default(10)
                            ->reactive(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Vista Previa de Cálculo de Costos')
                    ->description('Cálculo en tiempo real de costos de producción')
                    ->schema([
                        Forms\Components\Placeholder::make('filament_cost_preview')
                            ->label('Costo de Filamento')
                            ->content(function ($get) {
                                $filamentId = $get('filament_id');
                                $grams = $get('total_grams');
                                if ($filamentId && $grams) {
                                    $filament = Filament::find($filamentId);
                                    $cost = ($grams * $filament->cost_per_kg) / 1000;
                                    return '$' . number_format($cost, 2);
                                }
                                return '$0.00';
                            }),
                        
                        Forms\Components\Placeholder::make('machine_cost_preview')
                            ->label('Costo de Máquina')
                            ->content(function ($get) {
                                $hours = $get('printing_time_hours');
                                if ($hours) {
                                    $rate = (float) env('HOURLY_MACHINE_RATE', 5.0);
                                    $cost = $hours * $rate;
                                    return '$' . number_format($cost, 2);
                                }
                                return '$0.00';
                            }),
                        
                        Forms\Components\Placeholder::make('supplies_cost_preview')
                            ->label('Costo de Insumos')
                            ->content(function ($get) {
                                $supplies = $get('additionalSupplies') ?? [];
                                $totalCost = 0;
                                
                                foreach ($supplies as $supply) {
                                    if (isset($supply['additional_supply_id']) && isset($supply['quantity'])) {
                                        $supplyModel = AdditionalSupply::find($supply['additional_supply_id']);
                                        if ($supplyModel) {
                                            $totalCost += $supplyModel->unit_cost * $supply['quantity'];
                                        }
                                    }
                                }
                                
                                return '$' . number_format($totalCost, 2);
                            }),
                        
                        Forms\Components\Placeholder::make('total_cost_preview')
                            ->label('Costo Total de Producción')
                            ->content(function ($get) {
                                $filamentId = $get('filament_id');
                                $grams = $get('total_grams');
                                $hours = $get('printing_time_hours');
                                $postProcessingCost = $get('post_processing_cost') ?? 0;
                                $safetyMargin = $get('safety_margin_percentage') ?? 10;
                                $supplies = $get('additionalSupplies') ?? [];
                                
                                // Calculate filament cost
                                $filamentCost = 0;
                                if ($filamentId && $grams) {
                                    $filament = Filament::find($filamentId);
                                    $filamentCost = ($grams * $filament->cost_per_kg) / 1000;
                                }
                                
                                // Calculate machine cost
                                $machineCost = 0;
                                if ($hours) {
                                    $rate = (float) env('HOURLY_MACHINE_RATE', 5.0);
                                    $machineCost = $hours * $rate;
                                }
                                
                                // Calculate supplies cost
                                $suppliesCost = 0;
                                foreach ($supplies as $supply) {
                                    if (isset($supply['additional_supply_id']) && isset($supply['quantity'])) {
                                        $supplyModel = AdditionalSupply::find($supply['additional_supply_id']);
                                        if ($supplyModel) {
                                            $suppliesCost += $supplyModel->unit_cost * $supply['quantity'];
                                        }
                                    }
                                }
                                
                                // Calculate subtotal
                                $subtotal = $filamentCost + $machineCost + $suppliesCost + $postProcessingCost;
                                
                                // Add safety margin
                                $margin = $subtotal * ($safetyMargin / 100);
                                $total = $subtotal + $margin;
                                
                                return '<span class="text-xl font-bold text-indigo-600">$' . number_format($total, 2) . '</span>';
                            })
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->reactive(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Producto')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('filament.brand_type')
                    ->label('Filamento')
                    ->formatStateUsing(fn ($record) => $record->filament ? "{$record->filament->brand_type} - {$record->filament->color}" : 'N/A')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_grams')
                    ->label('Peso')
                    ->formatStateUsing(fn ($state) => $state . 'g')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('printing_time_hours')
                    ->label('Tiempo de Impresión')
                    ->formatStateUsing(fn ($record) => $record->formatted_printing_time)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('formatted_production_cost')
                    ->label('Costo de Producción')
                    ->html()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            fn ($query) => $query->selectRaw('(total_grams * (SELECT cost_per_kg FROM filaments WHERE filaments.id = products.filament_id) / 1000) + (printing_time_hours * ?)', [(float) env('HOURLY_MACHINE_RATE', 5.0)]),
                            $direction
                        );
                    }),
                
                Tables\Columns\TextColumn::make('work_queue_count')
                    ->label('En Cola')
                    ->counts('workQueue')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('filament')
                    ->relationship('filament', 'brand_type')
                    ->getOptionLabelFromRecordUsing(fn (Filament $record) => "{$record->brand_type} - {$record->color}"),
                
                Tables\Filters\Filter::make('high_cost')
                    ->label('High Cost Products')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(total_grams * (SELECT cost_per_kg FROM filaments WHERE filaments.id = products.filament_id) / 1000) > 10')),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
