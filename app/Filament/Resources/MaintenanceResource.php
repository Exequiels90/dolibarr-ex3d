<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Mantenimiento';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Mantenimiento';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Máquina')
                    ->description('Detalles sobre la impresora 3D')
                    ->schema([
                        Forms\Components\TextInput::make('machine_name')
                            ->label('Nombre de la Máquina')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('ej., Bambu Lab A1')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Seguimiento de Mantenimiento')
                    ->description('Monitoreo de horas de impresión y programación de mantenimiento')
                    ->schema([
                        Forms\Components\TextInput::make('total_print_hours')
                            ->label('Horas Totales de Impresión')
                            ->required()
                            ->numeric()
                            ->suffix('horas')
                            ->step(0.1)
                            ->inputMode('decimal')
                            ->placeholder('0.0')
                            ->default(0),
                        
                        Forms\Components\TextInput::make('last_maintenance_hours')
                            ->label('Último Mantenimiento a')
                            ->required()
                            ->numeric()
                            ->suffix('horas')
                            ->step(0.1)
                            ->inputMode('decimal')
                            ->placeholder('0.0')
                            ->default(0),
                        
                        Forms\Components\TextInput::make('maintenance_interval_hours')
                            ->label('Intervalo de Mantenimiento')
                            ->required()
                            ->numeric()
                            ->suffix('horas')
                            ->placeholder('100')
                            ->default(100),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Estado de Mantenimiento')
                    ->description('Estado actual del mantenimiento y alertas')
                    ->schema([
                        Forms\Components\Placeholder::make('hours_until_maintenance')
                            ->label('Horas Hasta el Mantenimiento')
                            ->content(function ($get) {
                                $total = $get('total_print_hours');
                                $last = $get('last_maintenance_hours');
                                $interval = $get('maintenance_interval_hours');
                                
                                if ($total && $last && $interval) {
                                    $nextMaintenance = $last + $interval;
                                    $hoursUntil = $nextMaintenance - $total;
                                    
                                    if ($hoursUntil < 0) {
                                        return "<span class='text-red-600 font-bold'>" . abs($hoursUntil) . " horas VENCIDAS</span>";
                                    } elseif ($hoursUntil <= 10) {
                                        return "<span class='text-yellow-600 font-bold'>" . $hoursUntil . " horas restantes</span>";
                                    } else {
                                        return "<span class='text-green-600'>" . $hoursUntil . " horas restantes</span>";
                                    }
                                }
                                return 'N/A';
                            })
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Notas Adicionales')
                    ->description('Notas de mantenimiento y observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->placeholder('Mantenimiento realizado, observaciones, etc.')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Acciones Rápidas')
                    ->description('Acciones comunes de mantenimiento')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Action::make('add_print_hours')
                                ->label('Agregar Horas de Impresión')
                                ->icon('heroicon-o-clock')
                                ->form([
                                    Forms\Components\TextInput::make('hours_to_add')
                                        ->label('Horas a Agregar')
                                        ->required()
                                        ->numeric()
                                        ->suffix('horas')
                                        ->step(0.1)
                                        ->default(1.0),
                                ])
                                ->action(function (array $data, callable $get, callable $set) {
                                    $currentHours = $get('total_print_hours');
                                    $newHours = $currentHours + $data['hours_to_add'];
                                    $set('total_print_hours', $newHours);
                                }),
                            
                            Forms\Components\Action::make('perform_maintenance')
                                ->label('Realizar Mantenimiento Ahora')
                                ->icon('heroicon-o-wrench-screwdriver')
                                ->color('success')
                                ->action(function (callable $get, callable $set) {
                                    $currentHours = $get('total_print_hours');
                                    $set('last_maintenance_hours', $currentHours);
                                }),
                        ])
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('machine_name')
                    ->label('Nombre de la Máquina')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('formatted_total_hours')
                    ->label('Horas Totales de Impresión')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('total_print_hours', $direction)),
                
                Tables\Columns\TextColumn::make('formatted_hours_until_maintenance')
                    ->label('Estado de Mantenimiento')
                    ->html()
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy(
                            fn ($query) => $query->selectRaw('(last_maintenance_hours + maintenance_interval_hours) - total_print_hours'),
                            $direction
                        );
                    }),
                
                Tables\Columns\IconColumn::make('maintenance_required')
                    ->label('Mantenimiento Requerido')
                    ->boolean()
                    ->getStateUsing(fn (Maintenance $record): bool => $record->isMaintenanceDue())
                    ->trueColor('danger')
                    ->falseColor('success'),
                
                Tables\Columns\TextColumn::make('last_maintenance_hours')
                    ->label('Último Mantenimiento')
                    ->formatStateUsing(fn ($state) => $state . 'h')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('maintenance_interval_hours')
                    ->label('Intervalo')
                    ->formatStateUsing(fn ($state) => $state . 'h')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('maintenance_status')
                    ->label('Maintenance Status')
                    ->options([
                        'due' => 'Maintenance Due',
                        'approaching' => 'Maintenance Approaching',
                        'ok' => 'OK',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'due') {
                            $query->whereRaw('(last_maintenance_hours + maintenance_interval_hours) <= total_print_hours');
                        } elseif ($data['value'] === 'approaching') {
                            $query->whereRaw('(last_maintenance_hours + maintenance_interval_hours) - total_print_hours BETWEEN 0 AND 10');
                        } elseif ($data['value'] === 'ok') {
                            $query->whereRaw('(last_maintenance_hours + maintenance_interval_hours) - total_print_hours > 10');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('add_hours')
                    ->label('Add Hours')
                    ->icon('heroicon-o-clock')
                    ->form([
                        Forms\Components\TextInput::make('hours')
                            ->label('Hours to Add')
                            ->required()
                            ->numeric()
                            ->step(0.1)
                            ->default(1.0),
                    ])
                    ->action(function (Maintenance $record, array $data) {
                        $record->addPrintHours($data['hours']);
                    }),
                
                Tables\Actions\Action::make('perform_maintenance')
                    ->label('Perform Maintenance')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('success')
                    ->action(function (Maintenance $record) {
                        $record->performMaintenance();
                    })
                    ->visible(fn (Maintenance $record) => $record->isMaintenanceDue() || $record->isMaintenanceApproaching()),
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
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}
