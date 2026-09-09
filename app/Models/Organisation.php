<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\OrganisationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Billable;
use Spatie\Multitenancy\Models\Tenant;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property array|null $meta
 * @property Carbon|null $deleted_at
 * @property Carbon|null $restored_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $stripe_id
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property Carbon|null $trial_ends_at
 */
#[Fillable([
    'name',
    'slug',
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
class Organisation extends Tenant implements Auditable
{
    /**
     * @use HasFactory<OrganisationFactory>
     */
    use Billable,
        HasFactory,
        SoftDeletes;

    /**
     * Get the users who are members of this organisation.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(OrganisationMembership::class)
            ->withPivot([
                'status',
                'role',
                'invitation_token',
                'invited_at',
                'joined_at',
                'invited_by',
                'created_by',
                'updated_by',
            ])
            ->withTimestamps();
    }

    /**
     * Get only the users with an active membership.
     *
     * @return BelongsToMany<User, Organisation>
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot(
            'status', OrganisationMembership::STATUS_ACTIVE,
            'role',
        );
    }

    /**
     * Determine whether the given user holds an owner or admin role
     * within this organisation, and can therefore manage billing.
     */
    public function hasBillingRoleFor(User $user): bool
    {
        return $this->activeUsers()
            ->wherePivot('user_id', $user->id)
            ->wherePivotIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Add a user as an active member of the organisation immediately,
     * bypassing the invitation flow. Used when membership is granted
     * directly rather than via InvitationService::accept() — e.g. the
     * organisation's creator, or test/seeder setup.
     */
    public function addActiveMember(int $userId, ?int $createdBy = null): void
    {
        $this->users()->syncWithoutDetaching([
            $userId => [
                'status' => OrganisationMembership::STATUS_ACTIVE,
                'joined_at' => now(),
                'created_by' => $createdBy ?? $userId,
            ],
        ]);
    }

    /**
     * Get the user who created this organisation.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this organisation.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this organisation.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this organisation.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get a snapshot of the organisation's auditable attributes.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'name',
            'slug',
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
            'meta' => 'array',
            'deleted_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }
}
