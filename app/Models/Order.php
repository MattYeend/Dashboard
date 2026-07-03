<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $orderable_type
 * @property int $orderable_id
 * @property string $order_number
 * @property string $title
 * @property string|null $description
 * @property string|null $notes
 * @property string $subtotal
 * @property string $discount_amount
 * @property string $tax_amount
 * @property string $total_amount
 * @property Carbon|null $ordered_at
 * @property Carbon|null $due_at
 * @property Carbon|null $completed_at
 * @property int|null $status_id
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Model $orderable
 * @property-read OrderStatus|null $status
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'orderable_type',
    'orderable_id',
    'order_number',
    'title',
    'description',
    'notes',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'total_amount',
    'ordered_at',
    'due_at',
    'completed_at',
    'status_id',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
    'deleted_by',
    'deleted_at',
    'restored_by',
    'restored_at',
])]
class Order extends Model implements Auditable
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory,
        SoftDeletes;

    /**
     * Get the parent orderable model (e.g. Customer, Account).
     *
     * @return MorphTo<Model, $this>
     */
    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the status assigned to this order.
     *
     * @return BelongsTo<OrderStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    /**
     * Get the user who created this order.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this order.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this order.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this order.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the order's auditable attributes.
     *
     * Used by the audit log to capture before/after state on create,
     * update, delete and restore actions.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'orderable_id',
            'orderable_type',
            'order_number',
            'title',
            'description',
            'notes',
            'subtotal',
            'discount_amount',
            'tax_amount',
            'total_amount',
            'ordered_at',
            'due_at',
            'completed_at',
            'status_id',
            'meta',
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'ordered_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'meta' => 'array',
            'restored_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * This method is called when the model is booted, and is used to
     * register model event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generate a unique order number.
     */
    protected static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.strtoupper(str()->random(8));
        } while (static::withTrashed()->where('order_number', $number)->exists());

        return $number;
    }
}
