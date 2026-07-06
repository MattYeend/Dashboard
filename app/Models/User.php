<?php

namespace App\Models;

use App\Contracts\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
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
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable implements Auditable, MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens,
        HasFactory,
        HasRoles,
        Notifiable,
        PasskeyAuthenticatable,
        SoftDeletes,
        TwoFactorAuthenticatable;

    /**
     * Plaintext password, held in memory only for the lifetime of the request.
     *
     * This is a real declared property, not a magic Eloquent attribute, so it
     * is never added to $attributes and never persisted to the database. It
     * exists purely so the welcome email can include the password that was
     * used to create the account (set explicitly wherever the user is created,
     * e.g. an admin-creation form controller).
     */
    public ?string $plainPassword = null;

    /**
     * Set the user's role to 'user'.
     */
    public function user(): static
    {
        return $this->state(fn () => ['role' => 'user']);
    }

    /**
     * Set the user's role to 'admin'.
     */
    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    /**
     * Set the user's role to 'super_admin'.
     */
    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => 'super_admin']);
    }

    /**
     * Get all contacts associated with this user.
     *
     * @return MorphMany<Contact, $this>
     */
    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    /**
     * Get the orders associated with this company.
     *
     * @return MorphMany<Order, $this>
     */
    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    /**
     * Get the user who created this user.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this user.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this user.
     *
     * @return BelongsTo<User, $this>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this user.
     *
     * @return BelongsTo<User, $this>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Sync the local display role column from the user's Spatie permission role.
     *
     * Reads the user's currently assigned Spatie role and maps it to the
     * corresponding display role string stored in the `role` column. This
     * ensures the denormalised `role` column stays consistent with Spatie's
     * role assignments after any role change.
     */
    public function syncDisplayRoleFromSpatie(): void
    {
        $role = match (true) {
            $this->hasRole('Super Admin') => 'super_admin',
            $this->hasRole('Admin') => 'admin',
            default => 'user',
        };

        $this->forceFill(['role' => $role])->save();
    }

    /**
     * Assign a Spatie role to the user based on the given application role string.
     *
     * Accepts a display role string (e.g. `'super_admin'`, `'admin'`, `'user'`),
     * maps it to the corresponding Spatie role name, and syncs it via Spatie's
     * `syncRoles()`. After the Spatie role has been assigned, the local `role`
     * column is updated to reflect the change via {@see syncDisplayRoleFromSpatie()}.
     */
    public function assignApplicationRole(string $role): void
    {
        $this->syncRoles(match ($role) {
            'super_admin' => ['Super Admin'],
            'admin' => ['Admin'],
            default => ['User'],
        });

        $this->syncDisplayRoleFromSpatie();
    }

    /**
     * Get a snapshot of the user's auditable attributes.
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
            'name',
            'email',
            'email_verified_at',
            'role',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'meta' => 'array',
            'deleted_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }
}
