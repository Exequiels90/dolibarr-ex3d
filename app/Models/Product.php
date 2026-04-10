<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'filament_id',
        'total_grams',
        'printing_time_hours',
        'post_processing_cost',
        'safety_margin_percentage',
    ];

    protected $casts = [
        'total_grams' => 'decimal:2',
        'printing_time_hours' => 'decimal:2',
        'post_processing_cost' => 'decimal:2',
        'safety_margin_percentage' => 'decimal:2',
    ];

    public function filament(): BelongsTo
    {
        return $this->belongsTo(Filament::class);
    }

    public function additionalSupplies(): BelongsToMany
    {
        return $this->belongsToMany(AdditionalSupply::class, 'product_additional_supply')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function workQueue(): HasMany
    {
        return $this->hasMany(WorkQueue::class);
    }

    public function calculateFilamentCost(): float
    {
        if (!$this->filament) {
            return 0;
        }

        return ($this->total_grams * $this->filament->cost_per_kg) / 1000;
    }

    public function calculateMachineCost(): float
    {
        $hourlyRate = (float) env('HOURLY_MACHINE_RATE', 5.0);
        return $this->printing_time_hours * $hourlyRate;
    }

    public function calculateAdditionalSuppliesCost(): float
    {
        return $this->additionalSupplies->sum(function ($supply) {
            return $supply->unit_cost * $supply->pivot->quantity;
        });
    }

    public function calculateSubtotalCost(): float
    {
        return $this->calculateFilamentCost() 
            + $this->calculateMachineCost() 
            + $this->calculateAdditionalSuppliesCost() 
            + $this->post_processing_cost;
    }

    public function calculateTotalProductionCost(): float
    {
        $subtotal = $this->calculateSubtotalCost();
        $margin = $subtotal * ($this->safety_margin_percentage / 100);
        
        return $subtotal + $margin;
    }

    public function getFormattedProductionCostAttribute(): string
    {
        return '$' . number_format($this->calculateTotalProductionCost(), 2);
    }

    public function getFormattedFilamentCostAttribute(): string
    {
        return '$' . number_format($this->calculateFilamentCost(), 2);
    }

    public function getFormattedMachineCostAttribute(): string
    {
        return '$' . number_format($this->calculateMachineCost(), 2);
    }

    public function getFormattedSuppliesCostAttribute(): string
    {
        return '$' . number_format($this->calculateAdditionalSuppliesCost(), 2);
    }

    public function getFormattedPrintingTimeAttribute(): string
    {
        $hours = floor($this->printing_time_hours);
        $minutes = round(($this->printing_time_hours - $hours) * 60);
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }
}
