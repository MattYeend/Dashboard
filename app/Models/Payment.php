<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Traits\BelongsToOrganisation;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int $status_id
 * @property int|null $parent_payment_id
 * @property PaymentType $type
 * @property PaymentMethod $method
 * @property string $amount
 * @property string $currency
 * @property string|null $reference
 * @property string|null $stripe_payment_intent_id
 * @property Carbon $paid_at
 * @property string|null $notes
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Invoice $invoice
 * @property-read PaymentStatus $status
 * @property-read Payment|null $parentPayment
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'method',
    'amount',
    'paid_at',
    'reference',
    'notes',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
])]
class Payment extends Model implements Auditable
{
    /** 
     * @use HasFactory<PaymentFactory> 
     */
    use BelongsToOrganisation,
        HasFactory,
        SoftDeletes;

    public const DEFAULT_CURRENCY = 'GBP';

    /**
     * Get the invoice this payment belongs to.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the payment status.
     *
     * @return BelongsTo<PaymentStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'status_id');
    }

    /**
     * Get the payment this refund reverses.
     *
     * @return BelongsTo<Payment, $this>
     */
    public function parentPayment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_payment_id');
    }

    /**
     * Get the refunds raised against this payment.
     *
     * @return HasMany<Payment, $this>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(self::class, 'parent_payment_id');
    }

    /**
     * Get the user who created this payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Whether the payment currently holds the given system status key.
     *
     * Uses loadMissing() so it is safe under Model::shouldBeStrict().
     */
    public function isStatus(string $systemKey): bool
    {
        $this->loadMissing('status');

        return $this->status?->system_key === $systemKey;
    }

    /**
     * Whether the row is a completed (money has moved) entry.
     */
    public function isCompleted(): bool
    {
        return $this->isStatus(PaymentStatus::KEY_COMPLETED);
    }

    /**
     * Whether the row is an incoming payment, as opposed to a refund.
     */
    public function isPayment(): bool
    {
        return $this->type === PaymentType::Payment;
    }

    /**
     * Whether the row is a refund.
     */
    public function isRefund(): bool
    {
        return $this->type === PaymentType::Refund;
    }

    /**
     * Get a snapshot of the payment's auditable attributes.
     *
     * Free text (notes) and meta are left out of the audit trail on purpose.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'status_id' => $this->status_id,
            'parent_payment_id' => $this->parent_payment_id,
            'type' => $this->type?->value,
            'method' => $this->method?->value,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'paid_at' => $this->paid_at?->toIso8601String(),
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'paid_at' => 'immutable_datetime',
            'meta' => 'array',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }
}