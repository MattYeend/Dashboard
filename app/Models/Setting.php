<?php

namespace App\Models;

use App\Contracts\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $site_name
 * @property string $support_email
 * @property string $timezone
 * @property string $date_format
 * @property bool $maintenance_mode
 * @property bool $allow_registrations
 * @property int $default_pagination
 * @property string $default_locale
 * @property bool $two_factor_required
 * @property int $session_timeout_minutes
 * @property int $max_login_attempts
 * @property int|null $password_expiry_days
 * @property array<string, mixed>|null $meta
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 */
#[Fillable([
    'site_name',
    'support_email',
    'timezone',
    'date_format',
    'maintenance_mode',
    'allow_registrations',
    'default_pagination',
    'default_locale',
    'two_factor_required',
    'session_timeout_minutes',
    'max_login_attempts',
    'password_expiry_days',
    'meta',
    'created_by',
    'updated_by',
])]
class Setting extends Model implements Auditable
{
    /**
     * Get the user who created this settings row.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this settings row.
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a snapshot of the setting's auditable attributes.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return $this->only([
            'id',
            'site_name',
            'support_email',
            'timezone',
            'date_format',
            'maintenance_mode',
            'allow_registrations',
            'default_pagination',
            'default_locale',
            'two_factor_required',
            'session_timeout_minutes',
            'max_login_attempts',
            'password_expiry_days',
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
            'maintenance_mode' => 'boolean',
            'allow_registrations' => 'boolean',
            'two_factor_required' => 'boolean',
            'meta' => 'array',
        ];
    }
}
