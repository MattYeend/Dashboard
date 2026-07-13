<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $contactable_type
 * @property int $contactable_id
 * @property string|null $phone
 * @property string|null $email
 * @property Carbon|null $deleted_at
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Model $contactable
 */
#[Fillable([
    'contactable_type',
    'contactable_id',
    'phone',
    'email',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
    'deleted_by',
    'deleted_at',
    'restored_by',
    'restored_at',
])]
class Contact extends Model implements Auditable
{
    /**
     * @use HasFactory<ContactFactory>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * Get the parent contactable model (e.g. User, Lead, Customer).
     *
     * @return MorphTo<Model,$this>
     */
    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the addresses associated with this contact's owning model.
     *
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the user who created this contact.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this contact.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this contact.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this contact.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the contact's auditable attributes.
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
            'contactable_id',
            'contactable_type',
            'phone',
            'email',
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
            'deleted_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }
}
