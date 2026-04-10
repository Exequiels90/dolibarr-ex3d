<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkQueueResource\Pages;
use App\Models\WorkQueue;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkQueueResource extends Resource
{
    protected static ?string $model = WorkQueue::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Pedidos y Cola';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Cola de Trabajo';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Cola de Trabajo';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Cliente')
                    ->description('Ingrese los detalles del cliente')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nombre del Cliente')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Teléfono del Cliente')
                            ->required()
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles del Pedido')
                    ->description('Información del producto y precios')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $product = Product::find($state);
                                    $set('production_cost_display', $product->formatted_production_cost);
                                    $set('suggested_price_display', '$' . number_format($product->calculateTotalProductionCost() * 1.5, 2));
                                }
                            }),
                        
                        Forms\Components\Placeholder::make('production_cost_display')
                            ->label('Costo de Producción')
                            ->content(fn ($get) => $get('product_id') ? Product::find($get('product_id'))->formatted_production_cost : ''),
                        
                        Forms\Components\Placeholder::make('suggested_price_display')
                            ->label('Precio Sugerido (150% margen)')
                            ->content(fn ($get) => $get('product_id') ? '$' . number_format(Product::find($get('product_id'))->calculateTotalProductionCost() * 1.5, 2) : ''),
                        
                        Forms\Components\TextInput::make('agreed_price')
                            ->label('Precio Pactado')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->inputMode('decimal')
                            ->placeholder('0.00'),
                        
                        Forms\Components\DatePicker::make('delivery_date')
                            ->label('Fecha de Entrega')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estado del Pedido')
                    ->description('Estado actual del pedido')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options(WorkQueue::$statuses)
                            ->required()
                            ->default(WorkQueue::STATUS_PENDING)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                if ($state === WorkQueue::STATUS_DELIVERED && $get('product_id')) {
                                    $product = Product::find($get('product_id'));
                                    $set('profit_display', '$' . number_format($get('agreed_price') - $product->calculateTotalProductionCost(), 2));
                                }
                            }),
                        
                        Forms\Components\Placeholder::make('profit_display')
                            ->label('Ganancia Neta')
                            ->content(function ($get) {
                                $price = $get('agreed_price');
                                $productId = $get('product_id');
                                if ($price && $productId) {
                                    $product = Product::find($productId);
                                    $profit = $price - $product->calculateTotalProductionCost();
                                    $color = $profit >= 0 ? 'text-green-600' : 'text-red-600';
                                    return "<span class='{$color}'>$" . number_format(abs($profit), 2) . "</span>";
                                }
                                return '$0.00';
                            })
                            ->html(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notas Adicionales')
                    ->description('Cualquier nota adicional sobre este pedido')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($record): string => $record->getStatusBadgeColor())
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('agreed_price')
                    ->label('Precio Pactado')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('formatted_net_profit')
                    ->label('Ganancia Neta')
                    ->html()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            fn ($query) => $query->selectRaw('agreed_price - (total_grams * (SELECT cost_per_kg FROM filaments WHERE filaments.id = products.filament_id) / 1000) - (printing_time_hours * ?)', [(float) env('HOURLY_MACHINE_RATE', 5.0)]),
                            $direction
                        );
                    }),
                
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Fecha de Entrega')
                    ->date('M j, Y')
                    ->sortable()
                    ->color(fn ($record) => $record->delivery_date->isPast() && $record->status !== WorkQueue::STATUS_DELIVERED ? 'danger' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(WorkQueue::$statuses),
                
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Orders')
                    ->query(fn (Builder $query): Builder => $query->where('delivery_date', '<', now())->where('status', '!=', WorkQueue::STATUS_DELIVERED)),
                
                Tables\Filters\Filter::make('high_profit')
                    ->label('High Profit Orders')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('agreed_price - (total_grams * (SELECT cost_per_kg FROM filaments WHERE filaments.id = products.filament_id) / 1000) > 20')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options(WorkQueue::$statuses)
                            ->required(),
                    ])
                    ->action(function (WorkQueue $record, array $data) {
                        $record->update(['status' => $data['status']]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options(WorkQueue::$statuses)
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each->update(['status' => $data['status']]);
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkQueues::route('/'),
            'create' => Pages\CreateWorkQueue::route('/create'),
            'edit' => Pages\EditWorkQueue::route('/{record}/edit'),
        ];
    }
}
