<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $addressable_type
 * @property int $addressable_id
 * @property string $address_line_one
 * @property string|null $address_line_two
 * @property string|null $town
 * @property string $city
 * @property string|null $county
 * @property string|null $postcode
 * @property string $country
 * @property bool $is_primary
 * @property Carbon|null $deleted_at
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Model $addressable
 */
#[Fillable([
    'addressable_type',
    'addressable_id',
    'address_line_one',
    'address_line_two',
    'town',
    'city',
    'county',
    'postcode',
    'country',
    'is_primary',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
    'deleted_by',
    'deleted_at',
    'restored_by',
    'restored_at',
])]
class Address extends Model implements Auditable
{
    /**
     * @use HasFactory<AddressFactory>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * Get the parent addressable model (e.g. User, Company).
     *
     * @return MorphTo<Model, $this>
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this address.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this address.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this address.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this address.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the address's auditable attributes.
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
            'addressable_id',
            'addressable_type',
            'address_line_one',
            'address_line_two',
            'town',
            'city',
            'county',
            'postcode',
            'country',
            'is_primary',
            'meta',
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_primary' => 'boolean',
            'deleted_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }
}
