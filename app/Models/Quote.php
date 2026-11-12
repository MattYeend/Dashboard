<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable;
use App\Traits\BelongsToOrganisation;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $quotable_type
 * @property int $quotable_id
 * @property string $quote_number
 * @property string $title
 * @property string|null $description
 * @property string|null $notes
 * @property string|null $terms
 * @property string $subtotal
 * @property string $discount_amount
 * @property string $tax_amount
 * @property string $total_amount
 * @property string $currency
 * @property Carbon|null $issued_at
 * @property Carbon|null $valid_until
 * @property Carbon|null $sent_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $declined_at
 * @property Carbon|null $expired_at
 * @property Carbon|null $converted_at
 * @property int|null $status_id
 * @property int|null $converted_order_id
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Model $quotable
 * @property-read QuoteStatus|null $status
 * @property-read Order|null $convertedOrder
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'quotable_type',
    'quotable_id',
    'quote_number',
    'title',
    'description',
    'notes',
    'terms',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'total_amount',
    'currency',
    'issued_at',
    'valid_until',
    'status_id',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
])]
class Quote extends Model implements Auditable
{
    /** 
     * @use HasFactory<QuoteFactory>
     */
    use BelongsToOrganisation,
        HasFactory,
        SoftDeletes;

    /**
     * Get the customer the quote is addressed to (e.g. Company, User).
     *
     * @return MorphTo<Model, $this>
     */
    public function quotable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the status assigned to this quote.
     *
     * @return BelongsTo<QuoteStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(QuoteStatus::class, 'status_id');
    }

    /**
     * Get the order this quote was converted into, if any.
     *
     * @return BelongsTo<Order, $this>
     */
    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    /**
     * Get the user who created this quote.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this quote.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this quote.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this quote.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Whether the quote currently holds the given system status key.
     *
     * Uses loadMissing() so it is safe under Model::shouldBeStrict().
     */
    public function isStatus(string $systemKey): bool
    {
        $this->loadMissing('status');

        return $this->status?->system_key === $systemKey;
    }

    /**
     * Get a snapshot of the quote's auditable attributes.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'quotable_id',
            'quotable_type',
            'quote_number',
            'title',
            'description',
            'notes',
            'terms',
            'subtotal',
            'discount_amount',
            'tax_amount',
            'total_amount',
            'currency',
            'issued_at',
            'valid_until',
            'sent_at',
            'accepted_at',
            'declined_at',
            'expired_at',
            'converted_at',
            'status_id',
            'converted_order_id',
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
            'issued_at' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'accepted_at' => 'immutable_datetime',
            'declined_at' => 'immutable_datetime',
            'expired_at' => 'immutable_datetime',
            'converted_at' => 'immutable_datetime',
            'meta' => 'array',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Quote $quote): void {
            if (empty($quote->quote_number)) {
                $quote->quote_number = static::generateQuoteNumber();
            }
        });
    }

    /**
     * Generate a unique quote number.
     */
    protected static function generateQuoteNumber(): string
    {
        do {
            $number = 'QUO-'.strtoupper(str()->random(8));
        } while (DB::table('quotes')->where('quote_number', $number)->exists());

        return $number;
    }
}
