<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory,
        Notifiable,
        PasskeyAuthenticatable,
        TwoFactorAuthenticatable,
        HasApiTokens,
        HasRoles,
        SoftDeletes;

    /**
     * Set the user's role to 'user'.
     *
     * @return static
     */
    public function user(): static
    {
        return $this->state(fn () => ['role' => 'user']);
    }

    /**
     * Set the user's role to 'admin'.
     *
     * @return static
     */
    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    /**
     * Set the user's role to 'super_admin'.
     *
     * @return static
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
            'deleted_at' => 'datetime',
        ];
    }
}
