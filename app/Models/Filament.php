<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filament extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_type',
        'color',
        'cost_per_kg',
        'spool_weight_g',
    ];

    protected $casts = [
        'cost_per_kg' => 'decimal:2',
        'spool_weight_g' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getFormattedCostAttribute(): string
    {
        return '$' . number_format($this->cost_per_kg, 2);
    }

    public function getFormattedWeightAttribute(): string
    {
        return $this->spool_weight_g . 'g';
    }
}
