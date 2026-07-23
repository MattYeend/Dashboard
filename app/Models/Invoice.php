<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $invoice_number
 * @property int|null $company_id
 * @property int|null $order_id
 * @property int|null $status_id
 * @property Carbon|null $issue_date
 * @property Carbon|null $due_date
 * @property Carbon|null $sent_at
 * @property Carbon|null $paid_at
 * @property int $subtotal
 * @property int $tax_total
 * @property int $total
 * @property string $currency
 * @property string|null $notes
 * @property array|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property Carbon|null $restored_at
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Company|null $company
 * @property-read Contact|null $contact
 * @property-read Address|null $address
 * @property-read Order|null $order
 * @property-read InvoiceStatus|null $status
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 */
#[Fillable([
    'invoice_number',
    'company_id',
    'order_id',
    'status_id',
    'issue_date',
    'due_date',
    'sent_at',
    'paid_at',
    'subtotal',
    'tax_total',
    'total',
    'currency',
    'notes',
    'meta',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
    'deleted_by',
    'restored_by',
    'restored_at',
    'deleted_at',
])]
class Invoice extends Model implements Auditable
{
    /**
     * @use HasFactory<InvoiceFactory>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * Get the company this invoice belongs to.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the contact associated with this invoice.
     *
     * @return MorphOne<Contact, $this>
     */
    public function contact(): MorphOne
    {
        return $this->morphOne(Contact::class, 'contactable');
    }

    /**
     * Get the billing address associated with this invoice.
     *
     * @return MorphOne<Address, $this>
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * Get the order this invoice was raised from.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the status of this invoice.
     *
     * @return BelongsTo<InvoiceStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(InvoiceStatus::class, 'status_id');
    }

    /**
     * Get the user who created this invoice.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this invoice.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this invoice.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this invoice.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the invoice's auditable attributes.
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
            'invoice_number',
            'company_id',
            'order_id',
            'status_id',
            'issue_date',
            'due_date',
            'sent_at',
            'paid_at',
            'subtotal',
            'tax_total',
            'total',
            'currency',
            'notes',
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
            'issue_date' => 'date',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'subtotal' => 'integer',
            'tax_total' => 'integer',
            'total' => 'integer',
            'meta' => 'array',
            'deleted_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }
}
