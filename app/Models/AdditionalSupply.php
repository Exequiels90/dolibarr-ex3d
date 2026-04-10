<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdditionalSupply extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'unit_cost',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_additional_supply')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function getFormattedCostAttribute(): string
    {
        return '$' . number_format($this->unit_cost, 2);
    }
}
