<?php

namespace App\Models;

use App\Contracts\Auditable;
use App\Models\Organisation;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string $locale
 * @property array<string, mixed>|null $meta
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property CarbonImmutable|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property int|null $restored_by
 * @property CarbonImmutable|null $restored_at
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read CarbonImmutable|null $deleted_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 * @property-read User|null $deleter
 * @property-read User|null $restorer
 * @property-read Collection<int, Contact> $contacts
 * @property-read Collection<int, Order> $orders
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, Pipeline> $pipelines
 * @property-read Collection<int, DashboardWidgetPreference> $dashboardWidgetPreferences
 */
#[Fillable([
    'name',
    'email',
    'password',
    'locale',
    'meta',
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
    use Billable,
        HasApiTokens,
        HasFactory,
        HasRoles,
        Notifiable,
        PasskeyAuthenticatable,
        SoftDeletes,
        TwoFactorAuthenticatable;

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
     * Get the orders associated with this user.
     *
     * @return MorphMany<Order, $this>
     */
    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    /**
     * Get the addresses associated with this user.
     *
     * @return MorphMany<Address, $this>
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the pipelines assigned to this user.
     *
     * @return HasMany<Pipeline, $this>
     */
    public function pipelines(): HasMany
    {
        return $this->hasMany(Pipeline::class, 'assigned_to');
    }

    /**
     * Get the dashboard widget layout preferences for this user.
     *
     * @return HasMany<DashboardWidgetPreference, $this>
     */
    public function dashboardWidgetPreferences(): HasMany
    {
        return $this->hasMany(DashboardWidgetPreference::class);
    }

    /**
     * Get the custom dashboard widgets created by this user.
     *
     * @return HasMany<CustomDashboardWidget, $this>
     */
    public function customDashboardWidgets(): HasMany
    {
        return $this->hasMany(CustomDashboardWidget::class);
    }

    /**
     * Get the organisations this user belongs to.
     *
     * @return BelongsToMany<Organisation, $this>
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_user')
            ->withTimestamps();
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
     * Supported locale codes and their display labels.
     *
     * @var array<string, string>
     */
    public const LOCALES = [
        'en_GB' => 'English (UK)',
        'en_US' => 'English (US)',
        'fr_FR' => 'French',
        'de_DE' => 'German',
        'es_ES' => 'Spanish',
    ];

    /**
     * The three mutually exclusive application tier roles. A user
     * holds exactly one of these at a time, driving the `role` column.
     *
     * @var list<string>
     */
    public const TIER_ROLES = [
        'Super Admin',
        'Admin',
        'User',
    ];

    /**
     * Functional (non-tier) roles that can be layered on top of a
     * user's primary tier role.
     *
     * @var list<string>
     */
    public const FUNCTIONAL_ROLES = [
        'Manager',
        'Editor',
        'Viewer',
        'Moderator',
        'Support',
        'Analyst',
        'Guest',
    ];

    /**
     * Map a display role string (`role` column value) to its
     * corresponding Spatie tier role name.
     */
    public static function tierRoleNameFor(string $displayRole): string
    {
        return match ($displayRole) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            default => 'User',
        };
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
        $this->syncRoles([self::tierRoleNameFor($role)]);

        $this->syncDisplayRoleFromSpatie();
    }

    /**
     * Assign a primary tier role plus zero or more additional functional
     * roles (e.g. Moderator, Support), replacing the user's full role
     * set in one operation.
     *
     * @param  array<int, string>  $functionalRoles
     */
    public function assignRoles(string $tierRole, array $functionalRoles = []): void
    {
        $functionalRoles = array_diff($functionalRoles, self::TIER_ROLES);

        $this->syncRoles([$tierRole, ...array_values($functionalRoles)]);

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
            'locale',
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
            'email_verified_at' => 'immutable_datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'immutable_datetime',
            'meta' => 'array',
            'deleted_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
        ];
    }
}
