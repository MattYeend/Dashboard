<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Auditable;
use App\Traits\BelongsToOrganisation;
use Database\Factories\QuoteItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $quote_id
 * @property int|null $product_id
 * @property int $position
 * @property string $name
 * @property string|null $description
 * @property string $unit
 * @property string $quantity
 * @property string $unit_price
 * @property string $discount_percent
 * @property string $tax_rate
 * @property string $line_total
 * @property string $line_tax
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Quote $quote
 * @property-read Product|null $product
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'quote_id',
    'product_id',
    'position',
    'name',
    'description',
    'unit',
    'quantity',
    'unit_price',
    'discount_percent',
    'tax_rate',
    'line_total',
    'line_tax',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
])]
class QuoteItem extends Model implements Auditable
{
    /** 
     * @use HasFactory<QuoteItemFactory> 
     */
    use BelongsToOrganisation,
        HasFactory,
        SoftDeletes;

    /**
     * The most lines a single quote can hold.
     */
    public const MAX_PER_QUOTE = 200;

    /**
     * Get the quote this line belongs to.
     *
     * @return BelongsTo<Quote, $this>
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Get the product this line was created from, if any.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this line.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this line.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this line.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this line.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the line's auditable attributes.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'quote_id',
            'product_id',
            'position',
            'name',
            'description',
            'unit',
            'quantity',
            'unit_price',
            'discount_percent',
            'tax_rate',
            'line_total',
            'line_tax',
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
            'position' => 'integer',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_tax' => 'decimal:2',
            'meta' => 'array',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
        ];
    }
}
