<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_name',
        'total_print_hours',
        'last_maintenance_hours',
        'maintenance_interval_hours',
        'notes',
    ];

    protected $casts = [
        'total_print_hours' => 'decimal:2',
        'last_maintenance_hours' => 'decimal:2',
        'maintenance_interval_hours' => 'integer',
    ];

    public function getNextMaintenanceHours(): float
    {
        return $this->last_maintenance_hours + $this->maintenance_interval_hours;
    }

    public function getHoursUntilMaintenance(): float
    {
        return $this->getNextMaintenanceHours() - $this->total_print_hours;
    }

    public function isMaintenanceDue(): bool
    {
        return $this->total_print_hours >= $this->getNextMaintenanceHours();
    }

    public function isMaintenanceApproaching(): bool
    {
        $hoursUntil = $this->getHoursUntilMaintenance();
        return $hoursUntil <= 10 && $hoursUntil > 0;
    }

    public function getMaintenanceStatus(): string
    {
        if ($this->isMaintenanceDue()) {
            return 'overdue';
        } elseif ($this->isMaintenanceApproaching()) {
            return 'approaching';
        } else {
            return 'ok';
        }
    }

    public function getMaintenanceStatusColor(): string
    {
        return match($this->getMaintenanceStatus()) {
            'overdue' => 'danger',
            'approaching' => 'warning',
            'ok' => 'success',
            default => 'gray'
        };
    }

    public function getFormattedTotalHoursAttribute(): string
    {
        return number_format($this->total_print_hours, 1) . 'h';
    }

    public function getFormattedHoursUntilMaintenanceAttribute(): string
    {
        $hours = $this->getHoursUntilMaintenance();
        
        if ($hours < 0) {
            return abs($hours) . 'h overdue';
        } elseif ($hours <= 10) {
            return $hours . 'h remaining';
        } else {
            return 'OK';
        }
    }

    public function addPrintHours(float $hours): void
    {
        $this->total_print_hours += $hours;
        $this->save();
    }

    public function performMaintenance(): void
    {
        $this->last_maintenance_hours = $this->total_print_hours;
        $this->save();
    }
}
