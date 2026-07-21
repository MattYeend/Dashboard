<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\RegistrationInterestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $company
 * @property string|null $message
 * @property array|null $meta
 * @property Carbon|null $deleted_at
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'name',
    'email',
    'phone',
    'company',
    'message',
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
class RegistrationInterest extends Model implements Auditable
{
    /**
     * @use HasFactory<RegistrationInterestFactory>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * Get the user who created this record.
     *
     * Always null in practice - submissions come from guests - but kept
     * for consistency with the shared audit trail pattern.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this record.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this record.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the record's auditable attributes.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'name',
            'email',
            'phone',
            'company',
            'message',
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
