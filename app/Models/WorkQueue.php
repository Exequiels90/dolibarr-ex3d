<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'product_id',
        'agreed_price',
        'delivery_date',
        'status',
        'notes',
        'delivered_at',
    ];

    protected $casts = [
        'agreed_price' => 'decimal:2',
        'delivery_date' => 'date',
        'delivered_at' => 'datetime',
        'status' => 'string',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PRINTER = 'in_printer';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_DELIVERED = 'delivered';

    public static array $statuses = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_IN_PRINTER => 'En Impresora',
        self::STATUS_FINISHED => 'Terminado',
        self::STATUS_DELIVERED => 'Entregado',
    ];

    public static array $statusColors = [
        self::STATUS_PENDING => 'warning',
        self::STATUS_IN_PRINTER => 'info',
        self::STATUS_FINISHED => 'success',
        self::STATUS_DELIVERED => 'primary',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateNetProfit(): float
    {
        if (!$this->product) {
            return 0;
        }

        return $this->agreed_price - $this->product->calculateTotalProductionCost();
    }

    public function getFormattedAgreedPriceAttribute(): string
    {
        return '$' . number_format($this->agreed_price, 2);
    }

    public function getFormattedNetProfitAttribute(): string
    {
        $profit = $this->calculateNetProfit();
        $color = $profit >= 0 ? 'text-green-600' : 'text-red-600';
        
        return "<span class='{$color}'>$" . number_format(abs($profit), 2) . "</span>";
    }

    public function getStatusBadgeColor(): string
    {
        return self::$statusColors[$this->status] ?? 'gray';
    }

    public function getStatusLabel(): string
    {
        return self::$statuses[$this->status] ?? 'Unknown';
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_IN_PRINTER,
            self::STATUS_FINISHED
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInPrinter($query)
    {
        return $query->where('status', self::STATUS_IN_PRINTER);
    }

    public function scopeFinished($query)
    {
        return $query->where('status', self::STATUS_FINISHED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }
}
