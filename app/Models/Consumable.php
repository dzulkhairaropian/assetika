<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consumable extends Model
{
    use SoftDeletes;

    protected $table = 'consumables';

    protected $fillable = [
        'product_id',
        'status_id',
        'notes',
        'quantity',
        'low_threshold',
        'purchase_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'low_threshold' => 'integer',
        'purchase_cost' => 'decimal:2',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function transactions()
    {
        return $this->hasMany(ConsumableTransaction::class)->orderBy('transaction_date', 'desc');
    }

    protected static function booted()
    {
        static::saving(function (self $model) {
            $statusId = $model->determineStatusId();
            if ($statusId !== null) {
                $model->status_id = $statusId;
            }
        });

        static::creating(function (self $model) {
            $statusId = $model->determineStatusId();
            if ($statusId !== null) {
                $model->status_id = $statusId;
            }
        });

        static::updating(
            function (self $model) {
                $statusId = $model->determineStatusId();
                if ($statusId !== null) {
                    $model->status_id = $statusId;
                }
            }
        );
    }

    private function determineStatusId(): ?int
    {
        $quantity = (int) ($this->quantity ?? 0);
        $threshold = (int) ($this->low_threshold ?? 0);

        $statusName = $quantity <= 0
            ? 'Out of Stock'
            : ($quantity <= $threshold ? 'Low Stock' : 'In Stock');

        $product = $this->relationLoaded('product') ? $this->product : $this->load('product')->product;
        if (! $product || ! $product->master_category_id) {
            $status = Status::where('name', $statusName)->whereNull('master_category_id')->first();
            if ($status) {
                return $status->id;
            }

            $status = Status::where('name', $statusName)->first();

            return $status?->id;
        }

        $status = Status::where('name', $statusName)
            ->where('master_category_id', $product->master_category_id)
            ->first();

        if (! $status) {
            $status = Status::where('name', $statusName)->whereNull('master_category_id')->first();
        }

        return $status?->id;
    }

    /**
     * Get the status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status?->color ?? 'primary';
    }

    /**
     * Get the status badge icon
     */
    public function getStatusIconAttribute(): string
    {
        return $this->status?->icon ?? 'lucide-info';
    }

    /**
     * Check if consumable is low on stock
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_threshold && $this->quantity > 0;
    }

    /**
     * Check if consumable is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Check if consumable is in stock
     */
    public function isInStock(): bool
    {
        return $this->quantity > $this->low_threshold;
    }
}
